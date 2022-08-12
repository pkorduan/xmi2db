---
--- NRW/HB(/ASD)-KOM
---

CREATE TABLE ks_bauwerkoderanlagefuerindustrieundgewerbe (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  bauwerksfunktion integer NOT NULL,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_bauwerkoderanlagefuerindustrieundgewerbe_gml ON ks_bauwerkoderanlagefuerindustrieundgewerbe USING btree (gml_id,beginnt);
CREATE INDEX ks_bauwerkoderanlagefuerindustrieundgewerbe_endet ON ks_bauwerkoderanlagefuerindustrieundgewerbe USING btree (endet);
SELECT AddGeometryColumn('ks_bauwerkoderanlagefuerindustrieundgewerbe', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_bauwerkoderanlagefuerindustrieundgewerbe_wkb_geometry_idx ON ks_bauwerkoderanlagefuerindustrieundgewerbe USING gist (wkb_geometry);
CREATE INDEX ks_bauwerkoderanlagefuerindustrieundgewerbe_istabgeleitetaus ON ks_bauwerkoderanlagefuerindustrieundgewerbe USING gin (istabgeleitetaus);
CREATE INDEX ks_bauwerkoderanlagefuerindustrieundgewerbe_traegtbeizu ON ks_bauwerkoderanlagefuerindustrieundgewerbe USING gin (traegtbeizu);
CREATE INDEX ks_bauwerkoderanlagefuerindustrieundgewerbe_hatdirektunten ON ks_bauwerkoderanlagefuerindustrieundgewerbe USING gin (hatdirektunten);
CREATE INDEX ks_bauwerkoderanlagefuerindustrieundgewerbe_istteilvon ON ks_bauwerkoderanlagefuerindustrieundgewerbe USING gin (istteilvon);

COMMENT ON TABLE ks_bauwerkoderanlagefuerindustrieundgewerbe IS 'FeatureType: "KS_BauwerkOderAnlageFuerIndustrieUndGewerbe"';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_bauwerkoderanlagefuerindustrieundgewerbe.bauwerksfunktion IS 'bauwerksfunktion  KS_Bauwerksfunktion_BauwerkOderAnlageFuerIndustrieUndGewerbe 1';

CREATE TABLE ks_einrichtungenundanlageninoeffentlichenbereichen (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  art integer NOT NULL,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_euaiob_gml ON ks_einrichtungenundanlageninoeffentlichenbereichen USING btree (gml_id,beginnt);
CREATE INDEX ks_euaiob_endet ON ks_einrichtungenundanlageninoeffentlichenbereichen USING btree (endet);
SELECT AddGeometryColumn('ks_einrichtungenundanlageninoeffentlichenbereichen', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_euaiob_wkb_geometry_idx ON ks_einrichtungenundanlageninoeffentlichenbereichen USING gist (wkb_geometry);
CREATE INDEX ks_euaiob_istabgeleitetaus ON ks_einrichtungenundanlageninoeffentlichenbereichen USING gin (istabgeleitetaus);
CREATE INDEX ks_euaiob_traegtbeizu ON ks_einrichtungenundanlageninoeffentlichenbereichen USING gin (traegtbeizu);
CREATE INDEX ks_euaiob_hatdirektunten ON ks_einrichtungenundanlageninoeffentlichenbereichen USING gin (hatdirektunten);
CREATE INDEX ks_euaiob_istteilvon ON ks_einrichtungenundanlageninoeffentlichenbereichen USING gin (istteilvon);

COMMENT ON TABLE ks_einrichtungenundanlageninoeffentlichenbereichen IS 'FeatureType: "KS_EinrichtungenUndAnlagenInOeffentlichenBereichen"';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_einrichtungenundanlageninoeffentlichenbereichen.art IS 'art  KS_Art_EinrichtungenUndAnlagenInOeffentlichenBereichen 1';

CREATE TABLE ks_sonstigesbauwerkodersonstigeeinrichtung (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  bauwerksfunktion integer NOT NULL,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_sonstigesbauwerkodersonstigeeinrichtung_gml ON ks_sonstigesbauwerkodersonstigeeinrichtung USING btree (gml_id,beginnt);
CREATE INDEX ks_sonstigesbauwerkodersonstigeeinrichtung_endet ON ks_sonstigesbauwerkodersonstigeeinrichtung USING btree (endet);
SELECT AddGeometryColumn('ks_sonstigesbauwerkodersonstigeeinrichtung', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_sonstigesbauwerkodersonstigeeinrichtung_wkb_geometry_idx ON ks_sonstigesbauwerkodersonstigeeinrichtung USING gist (wkb_geometry);
CREATE INDEX ks_sonstigesbauwerkodersonstigeeinrichtung_istabgeleitetaus ON ks_sonstigesbauwerkodersonstigeeinrichtung USING gin (istabgeleitetaus);
CREATE INDEX ks_sonstigesbauwerkodersonstigeeinrichtung_traegtbeizu ON ks_sonstigesbauwerkodersonstigeeinrichtung USING gin (traegtbeizu);
CREATE INDEX ks_sonstigesbauwerkodersonstigeeinrichtung_hatdirektunten ON ks_sonstigesbauwerkodersonstigeeinrichtung USING gin (hatdirektunten);
CREATE INDEX ks_sonstigesbauwerkodersonstigeeinrichtung_istteilvon ON ks_sonstigesbauwerkodersonstigeeinrichtung USING gin (istteilvon);

COMMENT ON TABLE ks_sonstigesbauwerkodersonstigeeinrichtung IS 'FeatureType: "KS_SonstigesBauwerkOderSonstigeEinrichtung"';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerkodersonstigeeinrichtung.bauwerksfunktion IS 'bauwerksfunktion  KS_Bauwerksfunktion_SonstigesBauwerkOderSonstigeEinrichtung 1';

CREATE TABLE ks_topographischeauspraegung (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  objektart integer NOT NULL,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_topographischeauspraegung_gml ON ks_topographischeauspraegung USING btree (gml_id,beginnt);
CREATE INDEX ks_topographischeauspraegung_endet ON ks_topographischeauspraegung USING btree (endet);
SELECT AddGeometryColumn('ks_topographischeauspraegung', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_topographischeauspraegung_wkb_geometry_idx ON ks_topographischeauspraegung USING gist (wkb_geometry);
CREATE INDEX ks_topographischeauspraegung_istabgeleitetaus ON ks_topographischeauspraegung USING gin (istabgeleitetaus);
CREATE INDEX ks_topographischeauspraegung_traegtbeizu ON ks_topographischeauspraegung USING gin (traegtbeizu);
CREATE INDEX ks_topographischeauspraegung_hatdirektunten ON ks_topographischeauspraegung USING gin (hatdirektunten);
CREATE INDEX ks_topographischeauspraegung_istteilvon ON ks_topographischeauspraegung USING gin (istteilvon);

COMMENT ON TABLE ks_topographischeauspraegung IS 'FeatureType: "KS_TopographischeAuspraegung"';
COMMENT ON COLUMN ks_topographischeauspraegung.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_topographischeauspraegung.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_topographischeauspraegung.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_topographischeauspraegung.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_topographischeauspraegung.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_topographischeauspraegung.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_topographischeauspraegung.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_topographischeauspraegung.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_topographischeauspraegung.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_topographischeauspraegung.objektart IS 'objektart  KS_Objektart_TopographischeAuspraegung 1';

CREATE TABLE ks_strassenverkehrsanlage (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  art integer NOT NULL,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_strassenverkehrsanlage_gml ON ks_strassenverkehrsanlage USING btree (gml_id,beginnt);
CREATE INDEX ks_strassenverkehrsanlage_endet ON ks_strassenverkehrsanlage USING btree (endet);
SELECT AddGeometryColumn('ks_strassenverkehrsanlage', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_strassenverkehrsanlage_wkb_geometry_idx ON ks_strassenverkehrsanlage USING gist (wkb_geometry);
CREATE INDEX ks_strassenverkehrsanlage_istabgeleitetaus ON ks_strassenverkehrsanlage USING gin (istabgeleitetaus);
CREATE INDEX ks_strassenverkehrsanlage_traegtbeizu ON ks_strassenverkehrsanlage USING gin (traegtbeizu);
CREATE INDEX ks_strassenverkehrsanlage_hatdirektunten ON ks_strassenverkehrsanlage USING gin (hatdirektunten);
CREATE INDEX ks_strassenverkehrsanlage_istteilvon ON ks_strassenverkehrsanlage USING gin (istteilvon);

COMMENT ON TABLE ks_strassenverkehrsanlage IS 'FeatureType: "KS_Strassenverkehrsanlage"';
COMMENT ON COLUMN ks_strassenverkehrsanlage.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_strassenverkehrsanlage.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_strassenverkehrsanlage.art IS 'art  KS_Art_Strassenverkehrsanlage 1';

CREATE TABLE ks_einrichtunginoeffentlichenbereichen (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  art integer NOT NULL,
  oberflaechenmaterial integer,
  material integer,
  bezeichnung character varying,
  zustand integer,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_einrichtunginoeffentlichenbereichen_gml ON ks_einrichtunginoeffentlichenbereichen USING btree (gml_id,beginnt);
CREATE INDEX ks_einrichtunginoeffentlichenbereichen_endet ON ks_einrichtunginoeffentlichenbereichen USING btree (endet);
SELECT AddGeometryColumn('ks_einrichtunginoeffentlichenbereichen', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_einrichtunginoeffentlichenbereichen_wkb_geometry_idx ON ks_einrichtunginoeffentlichenbereichen USING gist (wkb_geometry);
CREATE INDEX ks_einrichtunginoeffentlichenbereichen_istabgeleitetaus ON ks_einrichtunginoeffentlichenbereichen USING gin (istabgeleitetaus);
CREATE INDEX ks_einrichtunginoeffentlichenbereichen_traegtbeizu ON ks_einrichtunginoeffentlichenbereichen USING gin (traegtbeizu);
CREATE INDEX ks_einrichtunginoeffentlichenbereichen_hatdirektunten ON ks_einrichtunginoeffentlichenbereichen USING gin (hatdirektunten);
CREATE INDEX ks_einrichtunginoeffentlichenbereichen_istteilvon ON ks_einrichtunginoeffentlichenbereichen USING gin (istteilvon);

COMMENT ON TABLE ks_einrichtunginoeffentlichenbereichen IS 'FeatureType: "KS_EinrichtungInOeffentlichenBereichen"';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.art IS 'art  KS_Art_EinrichtungInOeffentlichenBereichen 1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.oberflaechenmaterial IS 'oberflaechenmaterial  KS_Oberflaechenmaterial_KommunaleBauwerkeEinrichtungen 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.material IS 'material  KS_Material_EinrichtungInOeffentlichenBereichen 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.bezeichnung IS 'bezeichnung  CharacterString 0..1';
COMMENT ON COLUMN ks_einrichtunginoeffentlichenbereichen.zustand IS 'zustand  KS_Zustand_KommunaleBauwerkeEinrichtungen 0..1';

CREATE TABLE ks_bauwerkanlagenfuerverundentsorgung (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  art integer NOT NULL,
  bezeichnung character varying,
  zustand integer,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_bauwerkanlagenfuerverundentsorgung_gml ON ks_bauwerkanlagenfuerverundentsorgung USING btree (gml_id,beginnt);
CREATE INDEX ks_bauwerkanlagenfuerverundentsorgung_endet ON ks_bauwerkanlagenfuerverundentsorgung USING btree (endet);
SELECT AddGeometryColumn('ks_bauwerkanlagenfuerverundentsorgung', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_bauwerkanlagenfuerverundentsorgung_wkb_geometry_idx ON ks_bauwerkanlagenfuerverundentsorgung USING gist (wkb_geometry);
CREATE INDEX ks_bauwerkanlagenfuerverundentsorgung_istabgeleitetaus ON ks_bauwerkanlagenfuerverundentsorgung USING gin (istabgeleitetaus);
CREATE INDEX ks_bauwerkanlagenfuerverundentsorgung_traegtbeizu ON ks_bauwerkanlagenfuerverundentsorgung USING gin (traegtbeizu);
CREATE INDEX ks_bauwerkanlagenfuerverundentsorgung_hatdirektunten ON ks_bauwerkanlagenfuerverundentsorgung USING gin (hatdirektunten);
CREATE INDEX ks_bauwerkanlagenfuerverundentsorgung_istteilvon ON ks_bauwerkanlagenfuerverundentsorgung USING gin (istteilvon);

COMMENT ON TABLE ks_bauwerkanlagenfuerverundentsorgung IS 'FeatureType: "KS_BauwerkAnlagenFuerVerUndEntsorgung"';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.art IS 'art  KS_Art_BauwerkAnlagenFuerVerUndEntsorgung 1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.bezeichnung IS 'bezeichnung  CharacterString 0..1';
COMMENT ON COLUMN ks_bauwerkanlagenfuerverundentsorgung.zustand IS 'zustand  KS_Zustand_BauwerkOderAnlageFuerVerUndEntsorgung 0..1';

CREATE TABLE ks_sonstigesbauwerk (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  bauwerksfunktion integer NOT NULL,
  bezeichnung character varying,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_sonstigesbauwerk_gml ON ks_sonstigesbauwerk USING btree (gml_id,beginnt);
CREATE INDEX ks_sonstigesbauwerk_endet ON ks_sonstigesbauwerk USING btree (endet);
SELECT AddGeometryColumn('ks_sonstigesbauwerk', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_sonstigesbauwerk_wkb_geometry_idx ON ks_sonstigesbauwerk USING gist (wkb_geometry);
CREATE INDEX ks_sonstigesbauwerk_istabgeleitetaus ON ks_sonstigesbauwerk USING gin (istabgeleitetaus);
CREATE INDEX ks_sonstigesbauwerk_traegtbeizu ON ks_sonstigesbauwerk USING gin (traegtbeizu);
CREATE INDEX ks_sonstigesbauwerk_hatdirektunten ON ks_sonstigesbauwerk USING gin (hatdirektunten);
CREATE INDEX ks_sonstigesbauwerk_istteilvon ON ks_sonstigesbauwerk USING gin (istteilvon);

COMMENT ON TABLE ks_sonstigesbauwerk IS 'FeatureType: "KS_SonstigesBauwerk"';
COMMENT ON COLUMN ks_sonstigesbauwerk.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_sonstigesbauwerk.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_sonstigesbauwerk.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerk.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerk.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerk.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_sonstigesbauwerk.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_sonstigesbauwerk.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerk.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_sonstigesbauwerk.bauwerksfunktion IS 'bauwerksfunktion  KS_Bauwerksfunktion_SonstigesBauwerk 1';
COMMENT ON COLUMN ks_sonstigesbauwerk.bezeichnung IS 'bezeichnung  CharacterString 0..1';

CREATE TABLE ks_einrichtungimstrassenverkehr (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  art integer NOT NULL,
  oberflaechenmaterial integer,
  bezeichnung character varying,
  zustand integer,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_einrichtungimstrassenverkehr_gml ON ks_einrichtungimstrassenverkehr USING btree (gml_id,beginnt);
CREATE INDEX ks_einrichtungimstrassenverkehr_endet ON ks_einrichtungimstrassenverkehr USING btree (endet);
SELECT AddGeometryColumn('ks_einrichtungimstrassenverkehr', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_einrichtungimstrassenverkehr_wkb_geometry_idx ON ks_einrichtungimstrassenverkehr USING gist (wkb_geometry);
CREATE INDEX ks_einrichtungimstrassenverkehr_istabgeleitetaus ON ks_einrichtungimstrassenverkehr USING gin (istabgeleitetaus);
CREATE INDEX ks_einrichtungimstrassenverkehr_traegtbeizu ON ks_einrichtungimstrassenverkehr USING gin (traegtbeizu);
CREATE INDEX ks_einrichtungimstrassenverkehr_hatdirektunten ON ks_einrichtungimstrassenverkehr USING gin (hatdirektunten);
CREATE INDEX ks_einrichtungimstrassenverkehr_istteilvon ON ks_einrichtungimstrassenverkehr USING gin (istteilvon);

COMMENT ON TABLE ks_einrichtungimstrassenverkehr IS 'FeatureType: "KS_EinrichtungImStrassenverkehr"';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.art IS 'art  KS_Art_EinrichtungImStrassenverkehr 1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.oberflaechenmaterial IS 'oberflaechenmaterial  KS_Oberflaechenmaterial_KommunaleBauwerkeEinrichtungen 0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.bezeichnung IS 'bezeichnung  CharacterString 0..1';
COMMENT ON COLUMN ks_einrichtungimstrassenverkehr.zustand IS 'zustand  KS_Zustand_KommunaleBauwerkeEinrichtungen 0..1';

CREATE TABLE ks_verkehrszeichen (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  gefahrzeichen integer[],
  vorschriftzeichen integer[],
  richtzeichen integer[],
  verkehrseinrichtung integer[],
  zusatzzeichen integer[],
  bezeichnung character varying,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_verkehrszeichen_gml ON ks_verkehrszeichen USING btree (gml_id,beginnt);
CREATE INDEX ks_verkehrszeichen_endet ON ks_verkehrszeichen USING btree (endet);
SELECT AddGeometryColumn('ks_verkehrszeichen', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_verkehrszeichen_wkb_geometry_idx ON ks_verkehrszeichen USING gist (wkb_geometry);
CREATE INDEX ks_verkehrszeichen_istabgeleitetaus ON ks_verkehrszeichen USING gin (istabgeleitetaus);
CREATE INDEX ks_verkehrszeichen_traegtbeizu ON ks_verkehrszeichen USING gin (traegtbeizu);
CREATE INDEX ks_verkehrszeichen_hatdirektunten ON ks_verkehrszeichen USING gin (hatdirektunten);
CREATE INDEX ks_verkehrszeichen_istteilvon ON ks_verkehrszeichen USING gin (istteilvon);

COMMENT ON TABLE ks_verkehrszeichen IS 'FeatureType: "KS_Verkehrszeichen"';
COMMENT ON COLUMN ks_verkehrszeichen.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_verkehrszeichen.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_verkehrszeichen.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_verkehrszeichen.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_verkehrszeichen.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_verkehrszeichen.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_verkehrszeichen.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_verkehrszeichen.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_verkehrszeichen.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_verkehrszeichen.gefahrzeichen IS 'gefahrzeichen  KS_Gefahrzeichen_Verkehrszeichen 0..*';
COMMENT ON COLUMN ks_verkehrszeichen.vorschriftzeichen IS 'vorschriftzeichen  KS_Vorschriftzeichen_Verkehrszeichen 0..*';
COMMENT ON COLUMN ks_verkehrszeichen.richtzeichen IS 'richtzeichen  KS_Richtzeichen_Verkehrszeichen 0..*';
COMMENT ON COLUMN ks_verkehrszeichen.verkehrseinrichtung IS 'verkehrseinrichtung  KS_Verkehrseinrichtung_Verkehrszeichen 0..*';
COMMENT ON COLUMN ks_verkehrszeichen.zusatzzeichen IS 'zusatzzeichen  KS_Zusatzzeichen_Verkehrszeichen 0..*';
COMMENT ON COLUMN ks_verkehrszeichen.bezeichnung IS 'bezeichnung  CharacterString 0..1';

CREATE TABLE ks_einrichtungimbahnverkehr (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  art integer NOT NULL,
  bezeichnung character varying,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_einrichtungimbahnverkehr_gml ON ks_einrichtungimbahnverkehr USING btree (gml_id,beginnt);
CREATE INDEX ks_einrichtungimbahnverkehr_endet ON ks_einrichtungimbahnverkehr USING btree (endet);
SELECT AddGeometryColumn('ks_einrichtungimbahnverkehr', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_einrichtungimbahnverkehr_wkb_geometry_idx ON ks_einrichtungimbahnverkehr USING gist (wkb_geometry);
CREATE INDEX ks_einrichtungimbahnverkehr_istabgeleitetaus ON ks_einrichtungimbahnverkehr USING gin (istabgeleitetaus);
CREATE INDEX ks_einrichtungimbahnverkehr_traegtbeizu ON ks_einrichtungimbahnverkehr USING gin (traegtbeizu);
CREATE INDEX ks_einrichtungimbahnverkehr_hatdirektunten ON ks_einrichtungimbahnverkehr USING gin (hatdirektunten);
CREATE INDEX ks_einrichtungimbahnverkehr_istteilvon ON ks_einrichtungimbahnverkehr USING gin (istteilvon);

COMMENT ON TABLE ks_einrichtungimbahnverkehr IS 'FeatureType: "KS_EinrichtungImBahnverkehr"';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.art IS 'art  KS_Art_EinrichtungImBahnverkehr 1';
COMMENT ON COLUMN ks_einrichtungimbahnverkehr.bezeichnung IS 'bezeichnung  CharacterString 0..1';

CREATE TABLE ks_bauwerkimgewaesserbereich (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  bauwerksfunktion integer NOT NULL,
  bezeichnung character varying,
  zustand integer,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_bauwerkimgewaesserbereich_gml ON ks_bauwerkimgewaesserbereich USING btree (gml_id,beginnt);
CREATE INDEX ks_bauwerkimgewaesserbereich_endet ON ks_bauwerkimgewaesserbereich USING btree (endet);
SELECT AddGeometryColumn('ks_bauwerkimgewaesserbereich', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_bauwerkimgewaesserbereich_wkb_geometry_idx ON ks_bauwerkimgewaesserbereich USING gist (wkb_geometry);
CREATE INDEX ks_bauwerkimgewaesserbereich_istabgeleitetaus ON ks_bauwerkimgewaesserbereich USING gin (istabgeleitetaus);
CREATE INDEX ks_bauwerkimgewaesserbereich_traegtbeizu ON ks_bauwerkimgewaesserbereich USING gin (traegtbeizu);
CREATE INDEX ks_bauwerkimgewaesserbereich_hatdirektunten ON ks_bauwerkimgewaesserbereich USING gin (hatdirektunten);
CREATE INDEX ks_bauwerkimgewaesserbereich_istteilvon ON ks_bauwerkimgewaesserbereich USING gin (istteilvon);

COMMENT ON TABLE ks_bauwerkimgewaesserbereich IS 'FeatureType: "KS_BauwerkImGewaesserbereich"';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.bauwerksfunktion IS 'bauwerksfunktion  KS_Bauwerksfunktion_BauwerkImGewaesserbereich 1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.bezeichnung IS 'bezeichnung  CharacterString 0..1';
COMMENT ON COLUMN ks_bauwerkimgewaesserbereich.zustand IS 'zustand  KS_Zustand_BauwerkImGewaesserbereich 0..1';

CREATE TABLE ks_vegetationsmerkmal (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  bewuchs integer,
  zustand integer,
  breitedesobjekts double precision,
  name character varying,
  bezeichnung character varying,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_vegetationsmerkmal_gml ON ks_vegetationsmerkmal USING btree (gml_id,beginnt);
CREATE INDEX ks_vegetationsmerkmal_endet ON ks_vegetationsmerkmal USING btree (endet);
SELECT AddGeometryColumn('ks_vegetationsmerkmal', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_vegetationsmerkmal_wkb_geometry_idx ON ks_vegetationsmerkmal USING gist (wkb_geometry);
CREATE INDEX ks_vegetationsmerkmal_istabgeleitetaus ON ks_vegetationsmerkmal USING gin (istabgeleitetaus);
CREATE INDEX ks_vegetationsmerkmal_traegtbeizu ON ks_vegetationsmerkmal USING gin (traegtbeizu);
CREATE INDEX ks_vegetationsmerkmal_hatdirektunten ON ks_vegetationsmerkmal USING gin (hatdirektunten);
CREATE INDEX ks_vegetationsmerkmal_istteilvon ON ks_vegetationsmerkmal USING gin (istteilvon);

COMMENT ON TABLE ks_vegetationsmerkmal IS 'FeatureType: "KS_Vegetationsmerkmal"';
COMMENT ON COLUMN ks_vegetationsmerkmal.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_vegetationsmerkmal.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_vegetationsmerkmal.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_vegetationsmerkmal.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.bewuchs IS 'bewuchs  KS_Bewuchs_Vegetationsmerkmal 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.zustand IS 'zustand  KS_Zustand_Vegetationsmerkmal 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.breitedesobjekts IS 'breiteDesObjekts  Length 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.name IS 'name  CharacterString 0..1';
COMMENT ON COLUMN ks_vegetationsmerkmal.bezeichnung IS 'bezeichnung  CharacterString 0..1';

CREATE TABLE ks_bauraumoderbodenordnungsrecht (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  artderfestlegung integer,
  bezeichnung character varying,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_bauraumoderbodenordnungsrecht_gml ON ks_bauraumoderbodenordnungsrecht USING btree (gml_id,beginnt);
CREATE INDEX ks_bauraumoderbodenordnungsrecht_endet ON ks_bauraumoderbodenordnungsrecht USING btree (endet);
SELECT AddGeometryColumn('ks_bauraumoderbodenordnungsrecht', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_bauraumoderbodenordnungsrecht_wkb_geometry_idx ON ks_bauraumoderbodenordnungsrecht USING gist (wkb_geometry);
CREATE INDEX ks_bauraumoderbodenordnungsrecht_istabgeleitetaus ON ks_bauraumoderbodenordnungsrecht USING gin (istabgeleitetaus);
CREATE INDEX ks_bauraumoderbodenordnungsrecht_traegtbeizu ON ks_bauraumoderbodenordnungsrecht USING gin (traegtbeizu);
CREATE INDEX ks_bauraumoderbodenordnungsrecht_hatdirektunten ON ks_bauraumoderbodenordnungsrecht USING gin (hatdirektunten);
CREATE INDEX ks_bauraumoderbodenordnungsrecht_istteilvon ON ks_bauraumoderbodenordnungsrecht USING gin (istteilvon);

COMMENT ON TABLE ks_bauraumoderbodenordnungsrecht IS 'FeatureType: "KS_BauRaumOderBodenordnungsrecht"';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.artderfestlegung IS 'artDerFestlegung  KS_ArtDerFestlegung_BauRaumOderBauordnungsrecht 0..1';
COMMENT ON COLUMN ks_bauraumoderbodenordnungsrecht.bezeichnung IS 'bezeichnung  CharacterString 0..1';

CREATE TABLE ks_kommunalerbesitz (
  ogc_fid serial NOT NULL,
  gml_id character(16) NOT NULL,
  anlass character varying[],
  beginnt character(20) NOT NULL,
  endet character(20),
  advstandardmodell character varying[],
  sonstigesmodell character varying[],
  zeigtaufexternes_art character varying[],
  zeigtaufexternes_name character varying[],
  zeigtaufexternes_uri character varying[],
  istabgeleitetaus character(16)[],
  traegtbeizu character(16)[],
  hatdirektunten character(16)[],
  istteilvon character(16)[],

  zustaendigkeit character varying,
  nutzung character varying,

  PRIMARY KEY (ogc_fid)
);

CREATE UNIQUE INDEX ks_kommunalerbesitz_gml ON ks_kommunalerbesitz USING btree (gml_id,beginnt);
CREATE INDEX ks_kommunalerbesitz_endet ON ks_kommunalerbesitz USING btree (endet);
SELECT AddGeometryColumn('ks_kommunalerbesitz', 'wkb_geometry', :alkis_epsg, 'GEOMETRY', 2);
CREATE INDEX ks_kommunalerbesitz_wkb_geometry_idx ON ks_kommunalerbesitz USING gist (wkb_geometry);
CREATE INDEX ks_kommunalerbesitz_istabgeleitetaus ON ks_kommunalerbesitz USING gin (istabgeleitetaus);
CREATE INDEX ks_kommunalerbesitz_traegtbeizu ON ks_kommunalerbesitz USING gin (traegtbeizu);
CREATE INDEX ks_kommunalerbesitz_hatdirektunten ON ks_kommunalerbesitz USING gin (hatdirektunten);
CREATE INDEX ks_kommunalerbesitz_istteilvon ON ks_kommunalerbesitz USING gin (istteilvon);

COMMENT ON TABLE ks_kommunalerbesitz IS 'FeatureType: "KS_KommunalerBesitz"';
COMMENT ON COLUMN ks_kommunalerbesitz.anlass IS 'anlass codelist AA_Anlassart 0..*';
COMMENT ON COLUMN ks_kommunalerbesitz.beginnt IS 'lebenszeitintervall|AA_Lebenszeitintervall|beginnt  DateTime 1';
COMMENT ON COLUMN ks_kommunalerbesitz.endet IS 'lebenszeitintervall|AA_Lebenszeitintervall|endet  DateTime 0..1';
COMMENT ON COLUMN ks_kommunalerbesitz.advstandardmodell IS 'modellart|AA_Modellart|advStandardModell enumeration AA_AdVStandardModell 0..1';
COMMENT ON COLUMN ks_kommunalerbesitz.sonstigesmodell IS 'modellart|AA_Modellart|sonstigesModell codelist AA_WeitereModellart 0..1';
COMMENT ON COLUMN ks_kommunalerbesitz.zeigtaufexternes_art IS 'zeigtAufExternes|AA_Fachdatenverbindung|art  URI 1';
COMMENT ON COLUMN ks_kommunalerbesitz.zeigtaufexternes_name IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|name   0..1';
COMMENT ON COLUMN ks_kommunalerbesitz.zeigtaufexternes_uri IS 'zeigtAufExternes|AA_Fachdatenverbindung|fachdatenobjekt|AA_Fachdatenobjekt|uri  URI 0..1';
COMMENT ON COLUMN ks_kommunalerbesitz.wkb_geometry IS 'wkb_geometry  GM_Object 0..1';
COMMENT ON COLUMN ks_kommunalerbesitz.zustaendigkeit IS 'zustaendigkeit  CharacterString 0..1';
COMMENT ON COLUMN ks_kommunalerbesitz.nutzung IS 'nutzung  CharacterString 0..1';

CREATE TABLE ks_bauwerksfunktion_bauwerkoderanlagefuerindustrieundgewerbe (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_bauwerksfunktion_bauwerkoderanlagefuerindustrieundgewerbe IS 'Alias: "KS_Bauwerksfunktion_BauwerkOderAnlageFuerIndustrieUndGewerbe", UML-Typ: Enumeration';
INSERT INTO ks_bauwerksfunktion_bauwerkoderanlagefuerindustrieundgewerbe (wert,beschreibung,dokumentation) VALUES
('1380', 'Schieberkappe', ''),
('1390', 'Einsteigeschacht', ''),
('1372', 'Unterflurhydrant', ''),
('1250', 'Mast', ''),
('1371', 'Oberflurhydrant', '');

CREATE TABLE ks_bauwerksfunktion_bauwerkimgewaesserbereich (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_bauwerksfunktion_bauwerkimgewaesserbereich IS 'Alias: "KS_Bauwerksfunktion_BauwerkImGewaesserbereich", UML-Typ: Enumeration';
INSERT INTO ks_bauwerksfunktion_bauwerkimgewaesserbereich (wert,beschreibung,dokumentation) VALUES
('1200', 'Einlass/Auslass', ''),
('1100', 'Rohrdurchlass', '');

CREATE TABLE ks_art_strassenverkehrsanlage (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_art_strassenverkehrsanlage IS 'Alias: "KS_Art_Strassenverkehrsanlage", UML-Typ: Enumeration';
INSERT INTO ks_art_strassenverkehrsanlage (wert,beschreibung,dokumentation) VALUES
('1010', 'Fahrbahnbegrenzungslinie', '');

CREATE TABLE ks_bewuchs_vegetationsmerkmal (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_bewuchs_vegetationsmerkmal IS 'Alias: "KS_Bewuchs_Vegetationsmerkmal", UML-Typ: Enumeration';
INSERT INTO ks_bewuchs_vegetationsmerkmal (wert,beschreibung,dokumentation) VALUES
('1013', 'Solitärstrauch', ''),
('1102', 'Heckenkante, links', ''),
('1012', 'Laubbaum', ''),
('1103', 'Heckenmitte', ''),
('2200', 'erhw. Nadelbaum', '''erhw.Nadelbaum'' beschreibt die Zugehörigkeit eines einzeln stehenden, erhaltungswürdigen Baumes zur Gruppe der Nadelhölzer.'),
('3100', 'Beet', ''),
('1100', 'Rasen', 'Rasen bezeichnet eine Vegetationsdecke aus Gräsern, die feste verwurzelt, durch Wurzeln und Ausläufer mit der Vegetationstrageschicht verbunden ist und nicht landwirtschaftlich genutzt wird. Rasen findet Verwendung in Gärten oder Grünanlagen (z.B. Sportplatz, Golfplatz) und wird meist kurz geschnitten.'),
('1101', 'Heckenkante, rechts', ''),
('2100', 'erhw. Laubbaum', '''erhw.Laubbaum'' beschreibt die Zugehörigkeit eines einzeln stehenden, erhaltungswürdigen Baumes zur Gruppe der Laubhölzer.'),
('1011', 'Nadelbaum', '');

CREATE TABLE ks_bauwerksfunktion_sonstigesbauwerkodersonstigeeinrichtung (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_bauwerksfunktion_sonstigesbauwerkodersonstigeeinrichtung IS 'Alias: "KS_Bauwerksfunktion_SonstigesBauwerkOderSonstigeEinrichtung", UML-Typ: Enumeration';
INSERT INTO ks_bauwerksfunktion_sonstigesbauwerkodersonstigeeinrichtung (wert,beschreibung,dokumentation) VALUES
('1655', 'Rampe, Barrierefreiheit', ''),
('1721', 'Stützmauer, rechts', ''),
('1740', 'Zaun', ''),
('1701', 'Mauerkante, rechts', ''),
('1782', 'Springbrunnen, Zierbrunnen ', ''),
('1630', 'Treppenunterkante', ''),
('1780', 'Brunnen', ''),
('1702', 'Mauerkante, links', ''),
('1781', 'Brunnen (Trinkwasserversorgung)', ''),
('1703', 'Mauermitte', ''),
('1723', 'Stützmauermitte', ''),
('1722', 'Stützmauer, links', ''),
('1620', 'Treppe', ''),
('1640', 'Kellereingang', ''),
('1660', 'Kellerschacht', '');

CREATE TABLE ks_art_bauwerkanlagenfuerverundentsorgung (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_art_bauwerkanlagenfuerverundentsorgung IS 'Alias: "KS_Art_BauwerkAnlagenFuerVerUndEntsorgung", UML-Typ: Enumeration';
INSERT INTO ks_art_bauwerkanlagenfuerverundentsorgung (wert,beschreibung,dokumentation) VALUES
('2200', 'Schieberkappe Wasser', ''),
('3100', 'Stahlgittermast ohne Sockel', ''),
('1200', 'Peilrohr', '''Peilrohr'' ist ein Rohr zum Messen des Wasserstandes'),
('3300', 'Hochspannungsmast', ''),
('1300', 'Wasserhahn', '''Wasserhahn'' ist eine Vorrichtung zum Öffnen und Schließen von Wasserleitungen'),
('3200', 'Sockel für Gittermast', ''),
('2100', 'Schieberkappe Gas', ''),
('1100', 'städtischer Entwässerungsgraben', '''städtischer Entwässerungsgraben'' ist eine längere schmale Vertiefung im Erdboden zu dessen Entwässerung'),
('1500', 'Kanaldeckel', ''),
('3400', 'Stahlgittermast mit Sockel', ''),
('1400', 'Wasserschieber', '');

CREATE TABLE ks_art_einrichtungimstrassenverkehr (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_art_einrichtungimstrassenverkehr IS 'Alias: "KS_Art_EinrichtungImStrassenverkehr", UML-Typ: Enumeration';
INSERT INTO ks_art_einrichtungimstrassenverkehr (wert,beschreibung,dokumentation) VALUES
('2000', 'ruhender Verkehr', ''),
('9999', 'Sonstiges', ''),
('3100', 'Radweg', ''),
('3300', 'Öffentlicher Platz', ''),
('3200', 'Parkplatz', ''),
('4100', 'Radarkontrolle', ''),
('1100', 'ruhender Verkehr', ''),
('3120', 'Fußweg', ''),
('3600', 'Furt', ''),
('3140', 'Rad- und Fußweg', ''),
('3150', 'Wirtschaftsweg', ''),
('3000', 'Fahrbahn', ''),
('3500', 'Fahrbahnteiler, Mittelinsel', ''),
('2200', 'Rinne', ''),
('2100', 'Bordstein', '');

CREATE TABLE ks_objektart_topographischeauspraegung (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_objektart_topographischeauspraegung IS 'Alias: "KS_Objektart_TopographischeAuspraegung", UML-Typ: Enumeration';
INSERT INTO ks_objektart_topographischeauspraegung (wert,beschreibung,dokumentation) VALUES
('9000', 'sonstige topographische Linie', '');

CREATE TABLE ks_art_einrichtungenundanlageninoeffentlichenbereichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_art_einrichtungenundanlageninoeffentlichenbereichen IS 'Alias: "KS_Art_EinrichtungenUndAnlagenInOeffentlichenBereichen", UML-Typ: Enumeration';
INSERT INTO ks_art_einrichtungenundanlageninoeffentlichenbereichen (wert,beschreibung,dokumentation) VALUES
('3100', 'Bank', ''),
('1160', 'Postdepot', ''),
('1140', 'Feuermelder', ''),
('1360', 'Haltestelle, Wartestand', ''),
('1340', 'Freistehende Warntafel', ''),
('1100', 'Kommunikationseinrichtung', ''),
('1300', 'Verkehrszeichen', ''),
('9001', 'sonstiges Schild', ''),
('1510', 'Tor', ''),
('1710', 'Leuchtsäule', ''),
('1910', 'Fahnenmast', ''),
('1700', 'Säule, Werbefläche', ''),
('2600', 'Flutlichtmast', ''),
('2101', 'Straßensinkkasten, seitlich - an Bordsteinkante', ''),
('1500', 'Bahnübergang, Schranke', ''),
('2100', 'Straßensinkkasten', ''),
('1130', 'Notrufeinrichtung', ''),
('1310', 'Verkehrsampel', ''),
('1110', 'Fernsprechhäuschen', ''),
('3310', 'Fahrradständer', ''),
('1200', 'Kabelkasten, Schaltkasten', ''),
('1120', 'Briefkasten', ''),
('1410', 'Kilometerstein, -tafel', ''),
('1320', 'Freistehende Hinweistafel, -zeichen', ''),
('1620', 'Laterne, elektrisch', ''),
('3200', 'Spielgerät', ''),
('3120', 'Blumenkübel', ''),
('2102', 'Straßensinkkasten, mittig', ''),
('2210', 'Abfalleimer', ''),
('9999', 'Sonstiges', ''),
('2400', 'Uhr', ''),
('2200', 'Müllbox', ''),
('2105', 'Kastenrinne', ''),
('1150', 'Polizeirufsäule', ''),
('1400', 'Markierungshinweise, -steine', '');

CREATE TABLE ks_art_einrichtunginoeffentlichenbereichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_art_einrichtunginoeffentlichenbereichen IS 'Alias: "KS_Art_EinrichtungInOeffentlichenBereichen", UML-Typ: Enumeration';
INSERT INTO ks_art_einrichtunginoeffentlichenbereichen (wert,beschreibung,dokumentation) VALUES
('1400', 'Abfalleimer', ''),
('1700', 'Tisch', ''),
('5200', 'Sport-/Spielflächen', ''),
('5300', 'Grab', ''),
('1500', 'Postdepot', ''),
('5100', 'sonstige Flächen', ''),
('5000', 'Weg (nicht in Verkehrsanlagen)', ''),
('1600', 'Blumenkübel', ''),
('1200', 'Spielgerät', 'Ein ''Spielgerät'' dient dem Erwerb von Wahrnehmungsleistungen, motorischen Fähigkeiten und Intelligenzleistungen des Kindes auf Spielplätzen o.ä.'),
('1100', 'Bank', 'Bank ist eine Sitzgelegenheit aus Holz, Stein o.ä., die mehreren Personen Platz bietet'),
('1300', 'Fahrradständer', ''),
('5500', 'Randbefestigung, Einfassung', '');

CREATE TABLE ks_oberflaechenmaterial_kommunalebauwerkeeinrichtungen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_oberflaechenmaterial_kommunalebauwerkeeinrichtungen IS 'Alias: "KS_Oberflaechenmaterial_KommunaleBauwerkeEinrichtungen", UML-Typ: Enumeration';
INSERT INTO ks_oberflaechenmaterial_kommunalebauwerkeeinrichtungen (wert,beschreibung,dokumentation) VALUES
('2010', 'Kunststoff', '''Kunststoff'' bedeutet, dass die Oberfläche aus Kunststoff besteht.'),
('1060', 'Gehwegplatten', '''Gehwegplatten'' bedeutet, dass es sich um eine mit Gehwegplatten ausgelegte Oberfläche handelt.'),
('1020', 'Bituminöser Belag', '''Bituminöser Belag'' bedeutet, dass die Oberfläche aus einem bituminösen Belag besteht.'),
('1040', 'Wassergebundener Belag', '''Wassergebundener Belag'' bedeutet, dass die Oberfläche aus einem wassergebundenem Belag besteht.'),
('3020', 'Rindenmulch', '''Rindenmulch'' bedeutet, dass die Oberfläche mit Rindenmulch bedeckt ist.'),
('4010', 'Schotterrasen', '''Schotterrasen'' bedeutet, dass die Oberfläche aus Schotterrasen besteht.'),
('1010', 'Asphalt', '''Asphalt'' bedeutet, dass die Oberfläche aus Asphalt besteht.'),
('1050', 'Pflaster', '''Pflaster'' bedeutet, dass es sich um eine gepflasterte Oberfläche handelt.'),
('3010', 'Sand', '''Sand'' bedeutet, dass die Oberfläche mit Sand bedeckt ist.'),
('9999', 'Sonstiges', ''),
('1030', 'Beton', '''Beton'' bedeutet, dass die Oberfläche aus Beton besteht.');

CREATE TABLE ks_material_einrichtunginoeffentlichenbereichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_material_einrichtunginoeffentlichenbereichen IS 'Alias: "KS_Material_EinrichtungInOeffentlichenBereichen", UML-Typ: Enumeration';
INSERT INTO ks_material_einrichtunginoeffentlichenbereichen (wert,beschreibung,dokumentation) VALUES
('9999', 'Sonstiges', ''),
('3000', 'Holz', '''Holz'' bedeutet, dass ''KS_EinrichtungInOeffentlichenBereichen'' aus Holz besteht.'),
('1000', 'Stein', '''Stein'' bedeutet, dass ''KS_EinrichtungInOeffentlichenBereichen'' aus Stein besteht.'),
('2000', 'Metall', '''Metall'' bedeutet, dass ''KS_EinrichtungInOeffentlichenBereichen'' aus Metall besteht.'),
('4000', 'Kunststoff', '''Kunststoff'' bedeutet, dass ''KS_EinrichtungInOeffentlichenBereichen'' aus Kunststoff besteht.');

CREATE TABLE ks_zustand_kommunalebauwerkeeinrichtungen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_zustand_kommunalebauwerkeeinrichtungen IS 'Alias: "KS_Zustand_KommunaleBauwerkeEinrichtungen", UML-Typ: Enumeration';
INSERT INTO ks_zustand_kommunalebauwerkeeinrichtungen (wert,beschreibung,dokumentation) VALUES
('1100', 'unbefestigt', '');

CREATE TABLE ks_zustand_bauwerkoderanlagefuerverundentsorgung (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_zustand_bauwerkoderanlagefuerverundentsorgung IS 'Alias: "KS_Zustand_BauwerkOderAnlageFuerVerUndEntsorgung", UML-Typ: Enumeration';
INSERT INTO ks_zustand_bauwerkoderanlagefuerverundentsorgung (wert,beschreibung,dokumentation) VALUES
('4100', 'Offen', '''Offen'' bedeutet, dass ''Bauwerk oder Anlage für Ver- und Entsorgung'' allgemein zugänglich ist.'),
('2100', 'Außer Betrieb, stillgelegt, verlassen', '''Außer Betrieb, stillgelegt, verlassen'' bedeutet, dass sich ''Bauwerk oder Anlage für Ver- und Entsorgung'' nicht mehr in regelmäßiger, der Bestimmung entsprechenden Nutzung befindet.'),
('4200', 'Verschlossen', '''Verschlossen'' bedeutet, dass ''Bauwerk oder Anlage für Ver- und Entsorgung'' nicht allgemein zugänglich ist.'),
('2200', 'Verfallen, zerstört', '''Verfallen, zerstört'' bedeutet, dass sich der ursprüngliche Zustand von ''Bauwerk oder Anlage für Ver- und Entsorgung'' durch menschliche oder zeitliche Einwirkungen so verändert hat, dass eine Nutzung nicht mehr möglich ist.');

CREATE TABLE ks_bauwerksfunktion_sonstigesbauwerk (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_bauwerksfunktion_sonstigesbauwerk IS 'Alias: "KS_Bauwerksfunktion_SonstigesBauwerk", UML-Typ: Enumeration';
INSERT INTO ks_bauwerksfunktion_sonstigesbauwerk (wert,beschreibung,dokumentation) VALUES
('4000', 'Schwengelpumpe', 'Wetterschutzüberdachung5000'),
('1100', 'Balkon', ''),
('3000', 'einzelner Zaun', '');

CREATE TABLE ks_gefahrzeichen_verkehrszeichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_gefahrzeichen_verkehrszeichen IS 'Alias: "KS_Gefahrzeichen_Verkehrszeichen", UML-Typ: Enumeration';
INSERT INTO ks_gefahrzeichen_verkehrszeichen (wert,beschreibung,dokumentation) VALUES
('1100', 'Kinder', 'Zeichen 136 STVO, Kinder');

CREATE TABLE ks_vorschriftzeichen_verkehrszeichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_vorschriftzeichen_verkehrszeichen IS 'Alias: "KS_Vorschriftzeichen_Verkehrszeichen", UML-Typ: Enumeration';
INSERT INTO ks_vorschriftzeichen_verkehrszeichen (wert,beschreibung,dokumentation) VALUES
('1222', 'Pfeil geradeaus/rechts', '''Pfeil rechts/geradeaus'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Vorschriftzeichen. Sie schreiben die Fahrtrichtung vor.'),
('1223', 'Pfeil links', '''Pfeil links'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Vorschriftzeichen. Sie schreiben die Fahrtrichtung vor.'),
('1210', 'Tempo 30 Zone', '''Tempo 30 Zone'' ist ein Schild oder ein auf der Fahrbahnoberfläche einer Straße angebrachtes Zeichen zur Bestimmung der Tempo 30 Zone.'),
('1230', 'Haltelinie', '''Haltelinie'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Vorschriftzeichen. Ergänzend zu Halt- und Wartegeboten, die durch Zeichen, durch Polizeibeamte oder Lichtzeichen gegeben werden, ordnet sie an ''Hier halten''. Dasselbe gilt vor Bahnübergängen, für den der warten muss.'),
('1224', 'Pfeil geradeaus/links', '''Pfeil geradeaus/links'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Vorschriftzeichen. Sie schreiben die Fahrtrichtung vor.'),
('1225', 'Pfeil geradeaus', '''Pfeil geradeaus'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Vorschriftzeichen. Sie schreiben die Fahrtrichtung vor.'),
('1240', 'Sperrfläche', '''Sperrfläche'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Vorschriftzeichen. Eine Sperrfläche darf von Fahrzeugen nicht benutzt werden.'),
('1221', 'Pfeil rechts', '''Pfeil rechts'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Vorschriftzeichen. Sie schreiben die Fahrtrichtung vor.'),
('1100', 'Andreaskreuz', 'Vorschriftzeichen unmittelbar vor einem Bahnübergang mit der Bedeutung ''Dem Schienenverkehr Vorrang gewähren''.'),
('1300', 'Ver-/Gebotsschild', '');

CREATE TABLE ks_richtzeichen_verkehrszeichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_richtzeichen_verkehrszeichen IS 'Alias: "KS_Richtzeichen_Verkehrszeichen", UML-Typ: Enumeration';
INSERT INTO ks_richtzeichen_verkehrszeichen (wert,beschreibung,dokumentation) VALUES
('1200', 'Leitpfosten', ''),
('1111', 'Leitmarkierung durchgezogen', '''Leitmarkierung'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Richtzeichen.'),
('1110', 'Leitmarkierung unterbrochen', '''Leitmarkierung'' ist ein auf der Fahrbahnoberfläche einer Straße angebrachtes Richtzeichen. Sie besteht in der Regel aus gleich langen Strichen mit regelmäßigen Abständen. Eine Leitlinie kann auch als Warnlinie ausgeführt werden; bei der Warnlinie sind die Striche länger als die Lücken.');

CREATE TABLE ks_verkehrseinrichtung_verkehrszeichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_verkehrseinrichtung_verkehrszeichen IS 'Alias: "KS_Verkehrseinrichtung_Verkehrszeichen", UML-Typ: Enumeration';
INSERT INTO ks_verkehrseinrichtung_verkehrszeichen (wert,beschreibung,dokumentation) VALUES
('1400', 'Warnleuchte', ''),
('1210', 'Parkscheinautomat', 'Ein ''Parkscheinautomat'' ist eine Einrichtung zur Überwachung der Parkzeit.'),
('1199', 'sonstige Absperrung', ''),
('1110', 'Barriere', ''),
('1220', 'Parkuhr', 'Eine ''Parkuhr'' ist eine Einrichtung zur Überwachung der Parkzeit.'),
('1100', 'Sperrpfahl, Poller', '''Sperrpfahl, Poller'' Ein Sperrpfahl ist ein starker Pfahl aus Holz, Metall oder auch Stein. Ein Poller ist kürzer und stärker als ein Sperrpfahl'),
('1600', 'Leitplanke', '');

CREATE TABLE ks_zusatzzeichen_verkehrszeichen (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_zusatzzeichen_verkehrszeichen IS 'Alias: "KS_Zusatzzeichen_Verkehrszeichen", UML-Typ: Enumeration';
INSERT INTO ks_zusatzzeichen_verkehrszeichen (wert,beschreibung,dokumentation) VALUES
('1110', 'Schwerbehinderte', '''Schwerbehinderte'' Ist ein Schild oder auf der Fahrbahnoberfläche einer Straße angebrachtes Verkehrszeichen.'),
('1120', 'Kraftomnibus', '''Kraftomnibus frei'' Ist ein Schild oder auf der Fahrbahnoberfläche einer Straße angebrachtes Verkehrszeichen.');

CREATE TABLE ks_art_einrichtungimbahnverkehr (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_art_einrichtungimbahnverkehr IS 'Alias: "KS_Art_EinrichtungImBahnverkehr", UML-Typ: Enumeration';
INSERT INTO ks_art_einrichtungimbahnverkehr (wert,beschreibung,dokumentation) VALUES
('1200', 'Bahn-Kilometerstein', ''),
('1100', 'Gleisende, Prellbock', '''Gleisende, Prellbock'' ist der Abschluss eines Gleises einer Strecke für den Schienenverkehr. Ein Prellbock verhindert, dass ein Zug oder Waggon über das Schienenende hinausrollen kann.');

CREATE TABLE ks_zustand_bauwerkimgewaesserbereich (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_zustand_bauwerkimgewaesserbereich IS 'Alias: "KS_Zustand_BauwerkImGewaesserbereich", UML-Typ: Enumeration';
INSERT INTO ks_zustand_bauwerkimgewaesserbereich (wert,beschreibung,dokumentation) VALUES
('4000', 'Im Bau', '''Im Bau'' bedeutet, dass ''Bauwerk im Gewässerbereich'' noch nicht fertiggestellt ist. ***** Objektartengruppe: Besondere kommunale Vegetationsmerkmale ***** Definition: Die Objektartengruppe mit der Bezeichnung ''Besondere kommunale Vegetationsmerkmale'' und der Kennung ''59300'' beschreibt den flächen-, linien- oder punktförmigen zusätzlichen Bewuchs einer Grundfläche. Die Objektartengruppe umfasst die Objektart Kennung Name 59301 ''Vegetationsmerkmal''. Kennung: 59300'),
('2100', 'Außer Betrieb, stillgelegt, verlassen', '''Außer Betrieb, stillgelegt; verlassen'' bedeutet, dass sich ''Bauwerk im Gewässerbereich'' nicht mehr in regelmäßiger, der Bestimmung entsprechenden Nutzung befindet.');

CREATE TABLE ks_zustand_vegetationsmerkmal (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_zustand_vegetationsmerkmal IS 'Alias: "KS_Zustand_Vegetationsmerkmal", UML-Typ: Enumeration';
INSERT INTO ks_zustand_vegetationsmerkmal (wert,beschreibung,dokumentation) VALUES
('1100', 'Nass', '''Nass'' bezeichnet eine Vegetationsfläche, die aufgrund besonderer Bodenbeschaffenheit ganzjährig wassergesättigt ist, zeitweise auch unter Wasser stehen kann, aus kommunaler Sicht.');

CREATE TABLE ks_artderfestlegung_bauraumoderbauordnungsrecht (
  wert integer,
  beschreibung character varying,
  dokumentation character varying,
  PRIMARY KEY (wert)
);

COMMENT ON TABLE ks_artderfestlegung_bauraumoderbauordnungsrecht IS 'Alias: "KS_ArtDerFestlegung_BauRaumOderBauordnungsrecht", UML-Typ: Enumeration';
INSERT INTO ks_artderfestlegung_bauraumoderbauordnungsrecht (wert,beschreibung,dokumentation) VALUES
('1715', 'Bebauungsplan, einleitender Beschluss', ''),
('1821', 'Gestaltungssatzung', '');
