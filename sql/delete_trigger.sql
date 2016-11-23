-- Table: schema_name.delete
CREATE TABLE schema_name.delete (
  ogc_fid serial NOT NULL,
  typename character varying, -- Objektart, also Name der Tabelle, aus der das Objekt zu löschen ist.
  featureid character varying, -- gml_id des zu löschenden Objekts.
  context character varying, -- Operation 'delete', 'replace' oder 'update'.
  safetoignore character varying, -- Attribut safeToIgnore von wfsext:Replace
  replacedby character varying, -- gml_id des Objekts, das featureid ersetzt
  anlass character varying[],
  endet character(20),
  ignored boolean DEFAULT false, -- Löschsatz wurde ignoriert
  dummy geometry(Point,25832),
  CONSTRAINT delete_pk PRIMARY KEY (ogc_fid)
) WITH (OIDS=TRUE);
COMMENT ON TABLE schema_name.delete
  IS 'Hilfstabelle für das Speichern von Löschinformationen.';
COMMENT ON COLUMN schema_name.delete.typename IS 'Objektart, also Name der Tabelle, aus der das Objekt zu löschen ist.';
COMMENT ON COLUMN schema_name.delete.featureid IS 'gml_id des zu löschenden Objekts.';
COMMENT ON COLUMN schema_name.delete.context IS 'Operation ''delete'', ''replace'' oder ''update''.';
COMMENT ON COLUMN schema_name.delete.safetoignore IS 'Attribut safeToIgnore von wfsext:Replace';
COMMENT ON COLUMN schema_name.delete.replacedby IS 'gml_id des Objekts, das featureid ersetzt';
COMMENT ON COLUMN schema_name.delete.ignored IS 'Löschsatz wurde ignoriert';

-- Function: schema_name.delete_feature_hist()
CREATE OR REPLACE FUNCTION schema_name.delete_feature_hist()
RETURNS trigger AS
$BODY$
	DECLARE
		n INTEGER;
		vbeginnt TEXT;
		replgml TEXT;
		featgml TEXT;
		s TEXT;
	BEGIN
		NEW.context := coalesce(lower(NEW.context),'delete');

		--IF NEW.anlass IS NULL THEN		-- am 28.06.2016 angepasst
		--	NEW.anlass := '';
		--END IF;
		featgml := substr(NEW.featureid, 1, 16); -- gml_id ohne Timestamp

		IF length(NEW.featureid)=32 THEN
			-- beginnt-Zeit der zu ersetzenden Vorgaenger-Version des Objektes
			vbeginnt := substr(NEW.featureid, 17, 4) || '-'
				|| substr(NEW.featureid, 21, 2) || '-'
				|| substr(NEW.featureid, 23, 2) || 'T'
				|| substr(NEW.featureid, 26, 2) || ':'
				|| substr(NEW.featureid, 28, 2) || ':'
				|| substr(NEW.featureid, 30, 2) || 'Z' ;
		ELSIF length(NEW.featureid)=16 THEN
			-- Ältestes nicht gelöschtes Objekt
			EXECUTE 'SELECT min(beginnt) FROM ' || NEW.typename
			        || ' WHERE gml_id=''' || featgml || ''''
			        || ' AND endet IS NULL'
				INTO vbeginnt;

			IF vbeginnt IS NULL THEN
				RAISE EXCEPTION '%: Keinen Kandidaten zum Löschen gefunden.', NEW.featureid;
			END IF;
		ELSE
			RAISE EXCEPTION '%: Identifikator gescheitert.', NEW.featureid;
		END IF;

		IF NEW.context='delete' THEN
			NEW.endet := to_char(CURRENT_TIMESTAMP AT TIME ZONE 'UTC','YYYY-MM-DD"T"HH24:MI:SS"Z"');

		ELSIF NEW.context='update' THEN
			IF NEW.endet IS NULL THEN
				RAISE EXCEPTION '%: Endedatum nicht gesetzt', NEW.featureid;
			END IF;

		ELSIF NEW.context='replace' THEN
			NEW.safetoignore := lower(NEW.safetoignore);
			replgml := substr(NEW.replacedby, 1, 16); -- ReplcedBy gml_id ohne Timestamp
			IF NEW.safetoignore IS NULL THEN
				RAISE EXCEPTION '%: safeToIgnore nicht gesetzt.', NEW.featureid;
			ELSIF NEW.safetoignore<>'true' AND NEW.safetoignore<>'false' THEN
				RAISE EXCEPTION '%: safeToIgnore ''%'' ungültig (''true'' oder ''false'' erwartet).', NEW.featureid, NEW.safetoignore;
			END IF;

			IF length(NEW.replacedby)=32 AND NEW.replacedby<>NEW.featureid THEN
				NEW.endet := substr(NEW.replacedby, 17, 4) || '-'
					|| substr(NEW.replacedby, 21, 2) || '-'
					|| substr(NEW.replacedby, 23, 2) || 'T'
					|| substr(NEW.replacedby, 26, 2) || ':'
					|| substr(NEW.replacedby, 28, 2) || ':'
					|| substr(NEW.replacedby, 30, 2) || 'Z' ;
			END IF;

			-- Satz-Paarung Vorgänger-Nachfolger in der Objekttabelle suchen.
			-- Der Vorgänger muss noch beendet werden. Der Nachfolger kann bereits beendet sein.
			-- Das "beginn" des Nachfolgers anschließend als "endet" des Vorgaengers verwenden.
			-- Normalfall bei NBA-Aktualisierungslaeufen. v=Vorgänger, n=Nachfolger.
			IF NEW.endet IS NULL THEN
				EXECUTE 'SELECT min(n.beginnt) FROM ' || NEW.typename || ' n'
					|| ' JOIN ' || NEW.typename || ' v ON v.ogc_fid<n.ogc_fid'
					|| ' WHERE v.gml_id=''' || featgml
					|| ''' AND n.gml_id=''' || replgml
					|| ''' AND v.endet IS NULL'
					INTO NEW.endet;
			--	RAISE NOTICE 'endet setzen fuer Vorgaenger % ', NEW.endet;
			END IF;

			-- Satz-Paarung Vorgänger-Nachfolger in der Objekttabelle suchen.
			-- Der Vorgänger ist bereits beendet worden weil "endet" in den Daten gefüllt war.
			-- Dieser Fall kommt bei der Erstabgabe mit Vollhistorie vor.
			IF NEW.endet IS NULL THEN
				EXECUTE 'SELECT min(n.beginnt) FROM ' || NEW.typename || ' n'
					|| ' JOIN ' || NEW.typename || ' v ON v.endet=n.beginnt '
					|| ' WHERE v.gml_id=''' || featgml
					|| ''' AND n.gml_id=''' || replgml
					|| ''' AND v.beginnt=''' || vbeginnt || ''''
					INTO NEW.endet;

				IF NOT NEW.endet IS NULL THEN
				--	RAISE NOTICE '%: Vorgaenger ist schon endet', NEW.featureid;
					NEW.ignored=false;
					RETURN NEW;
				END IF;
			END IF;

			IF NEW.endet IS NULL THEN -- "endet" für den Vorgänger konnte nicht ermittelt werden
				IF NEW.safetoignore='false' THEN
					RAISE EXCEPTION '%: Beginn des ersetzenden Objekts % nicht gefunden.', NEW.featureid, NEW.replacedby;
				END IF;
				NEW.ignored=true;
				RETURN NEW;
			END IF; 
		ELSE
			RAISE EXCEPTION '%: Ungültiger Kontext % (''delete'', ''replace'' oder ''update'' erwartet).', NEW.featureid, NEW.context;
		END IF;

		-- Vorgaenger schema_name-Objekt nun beenden
		s := 'UPDATE ' || NEW.typename || ' SET endet=''' || NEW.endet || '''';			-- am 28.06.2016 angepasst
		IF NEW.context='update' AND NEW.anlass IS NOT NULL THEN
			s := s || ',anlass = ''' || NEW.anlass::text || '''';
		END IF;
		s := s || ' WHERE gml_id=''' || featgml || ''' AND beginnt=''' || vbeginnt || '''' ;

		EXECUTE s;
		GET DIAGNOSTICS n = ROW_COUNT;
	  --RAISE NOTICE 'SQL[%]:%', n, s;
		IF n<>1 THEN
			RAISE EXCEPTION '%: % schlug fehl [%]', NEW.featureid, NEW.context, n;
		END IF;

		NEW.ignored := false;
		RETURN NEW;
	END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;

-- Trigger: delete_feature_trigger on schema_name.delete
CREATE TRIGGER delete_feature_trigger
  BEFORE INSERT
  ON schema_name.delete
  FOR EACH ROW
  EXECUTE PROCEDURE schema_name.delete_feature_hist();
