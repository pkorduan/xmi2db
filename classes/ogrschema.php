<?php
class OgrSchema extends Schema {
	
  function create_delete_trigger() {
    $sql = str_replace('schema_name', $this->schemaName, file_get_contents('../sql/delete_trigger.sql'));
    if (!empty(GEOMETRY_EPSG_CODE)) {
      $sql = str_replace(':alkis_epsg', GEOMETRY_EPSG_CODE, $sql);
    }
    return $sql;
  }

  function create_ax_fortfuehrungsauftrag() {
    $sql = file_get_contents('../sql/ax_fortfuehrungsauftrag.sql');
    if(PG_WITH_OIDS) {
      $sql .= "
ALTER TABLE ax_fortfuehrungsauftrag SET WITH OIDS;";
    }
    return $sql;
  }

  function createEnumerationTable($enumeration, $dbSchema) {
    if ($this->is_table_filtered($enumeration['name'])) {
      $this->logger->log("<br>Ignoriere Enumeration: {$enumeration['name']}");
    }

    $this->logger->log('<br><b>Erzeuge Enumerationstabellen: ' . $enumeration['name'] . '</b> (' . $enumeration['xmi_id'] . ')');

    $table = new Table($enumeration['name']);

    # read Values
    $enumType = new EnumType($enumeration['name'], $this->logger);
    $enumType->setSchemas($this->umlSchema, $dbSchema);
    $enumType->setId($enumeration['id']);
    $table->values = $enumType->getValues($enumeration);

    # definiere Attribute
    $wert_type = (empty($enumType)) ? PG_CHARACTER_VARYING : $enumType->getWertType();
    $attribute = new Attribute('wert', $wert_type);
    $table->addAttribute($attribute);
    $attribute = new Attribute('beschreibung', PG_CHARACTER_VARYING);
    $table->addAttribute($attribute);
    $attribute = new Attribute('dokumentation', PG_CHARACTER_VARYING);
    $table->addAttribute($attribute);

    # definiere Primärschlüssel
    $table->primaryKey = 'wert';

    $this->logger->log($table->values->asTable($table->attributes));

#   if (
#     $table->values->rows[0][0] != $table->values->rows[0][1] AND
#     $table->values->rows[0][1] != 'NULL'
#   )

    # definiere Commentare
    $table->addComment('UML-Typ: Enumeration');

    $sql .= $table->asSql();

    $this->enumerations[$enumType->name] = $enumType;

    $this->logger->log('<pre>' . $tableSql . '</pre>');

    return $sql;
  }

  function is_table_filtered($class_name) {
    $this->logger->log("<br>Prüfe ob {$class_name} komplett ignoriert wird.");
    return (
      array_key_exists($class_name, $GLOBALS['filter']) and
      !is_array($GLOBALS['filter'][$class_name])
    );
  }

  function createFeatureTypeList($stereotype, $parent, $class) {
    global $indent;
    if ($this->is_table_filtered($class['name'])) {
      $this->logger->log("<br>Ignoriere FeatureType: {$class['name']} komplett");
      return "";
    }

    $this->logger->log('<br><b>Erzeuge ' . $stereotype . ': ' . $class['name'] . ' abgeleitet von ' . $parent->name . '</b> (' . $parent->alias . ')');

    $featureType = new FeatureType($class['name'], $parent, $this->logger, $this->umlSchema, $this->enumerations);
    $featureType->ogrSchema = $this;
		$featureType->isAbstract = $class['isAbstract'];
    $featureType->setId($class['id']);
    $featureType->primaryKey = 'ogc_fid';
    $featureType->primaryKeyType = 'serial NOT NULL';
    $featureType->primaryKeyNullable = false;

    if ($parent != null)
      $this->logger->log(' abgeleitet von: <b>' . $parent->name . '</b>');

    $featureType->attribute_filter = $this->filter[$class['name']]['attribute'];
    if (!is_array($attribute_filter))
      $featureType->attribute_filter = array();

    $this->logger->log('<br><b>Hole Attribute und verfolge dabei Datentypen bis zum Ende.</b>');
    $featureType->getAttributesUntilLeaves($featureType->alias, $stereotype, array());

    $featureType->flattenAttributes();
				
    $featureType->outputFlattenedAttributes();
    if ($this->logger->level > 0)
      $featureType->outputFlattenedAttributeTable();

    $featureType->setAssociationEnds($class);
		
		if (array_key_exists($class['name'], $this->featureTypes)) {
			# Attribute mit denen aus vorigen Durchläufen dieses Featuretypes mergen (für Featuretypes, die aus mehreren Klassen abgeleitet sind)
			$featureType->attributes = array_merge($featureType->attributes, $this->featureTypes[$class['name']]->attributes);
			$featureType->associationEnds = array_merge($featureType->associationEnds, $this->featureTypes[$class['name']]->associationEnds);
		}		

    $res = array();

    # lade abgeleitete Klassen
    $subClasses = $this->umlSchema->getSubUmlClasses($class);
    if (empty($subClasses)) {
      $featureType->unifyShortNames();
      $this->renameList = array_merge(
        $this->renameList,
        $featureType->outputFlattenedAttributes()
      );
      if ($this->logger->level > 0)
        $featureType->outputFlattenedAttributeTable();
      $res[] = $featureType;
    }
    else {
			$indent++;
      $res = array_merge($res, $this->createFeatureTypes($stereotype, $featureType, $subClasses));
			$indent--;
    }
		
		if (strtolower($class['stereotype']) == 'featuretype') {
			$this->featureTypes[$class['name']] = $featureType;		
		}

    return $res;
  }

  function createFeatureTypes($stereotype, $parent, $classes) {
		$featureTypes = [];
		foreach($classes as $class) {
			$this->logger->log('<br><b>Erzeuge Subclass: ' . $class['name'] . ' von Class ' . $parent->name . '</b>');
			$featureTypes = array_merge($featureTypes, $this->createFeatureTypeList($stereotype, $parent, $class));
		}

    if(RENAME_OPTIONAL_FIRST) {
      // Pfade feststellen auf denen umbenannt wurde

      // Einige Vorbelegungen um einerseits für die vorherige Schemaversion
      // generierte Umbenennungen beizubehalten und andererseits überlange
      // Bezeichner in ax_bauteil3d zu vermeiden.
      $renamed_paths = array(
        'qualitaetsangaben_herkunft_processstep' => 'processstep_',
        'qualitaetsangaben_herkunft_processstep_processor' => 'processstep_',
        'qualitaetsangaben_herkunft_processstep_source' => 'processstep_',
        'qualitaetsangaben_herkunft_processstep_source_source' => 'processstep_',

        'qualitaetsangaben_herkunft3d_source' => 'herkunft3d_',
        'anliegervermerk_flurstuecksnummer' => 'anliegervermerk_',
        'anliegervermerk_gemarkung' => 'anliegervermerk_',
        'zeigtaufexternes_fachdatenobjekt' => 'zeigtaufexternes_',

        'qualitaetbodenhoehe_herkunft' => 'qbodenhoehe_',
        'qualitaetbodenhoehe_herkunft_processstep' => 'qbodenhoehe_ps_',
        'qualitaetbodenhoehe_herkunft_processstep_processor' => 'qbodenhoehe_ps_',
        'qualitaetbodenhoehe_herkunft_processstep_processor_contactinfo' => 'qbodenhoehe_ps_',
        'qualitaetbodenhoehe_herkunft_processstep_processstep' => 'qbodenhoehe_ps_',
        'qualitaetbodenhoehe_herkunft_processstep_source' => 'qbodenhoehe_ps_src_',
        'qualitaetbodenhoehe_herkunft_processstep_source_source' => 'qbodenhoehe_ps_src_',
        'qualitaetbodenhoehe_herkunft_source' => 'qbodenhoehe_src_',
        'qualitaetbodenhoehe_herkunft_source_source' => 'qbodenhoehe_src_',

        'qualitaetdachhoehe_herkunft' => 'qdachhoehe_',
        'qualitaetdachhoehe_herkunft_processstep' => 'qdachhoehe_ps_',
        'qualitaetdachhoehe_herkunft_processstep_processor' => 'qdachhoehe_ps_',
        'qualitaetdachhoehe_herkunft_processstep_processor_contactinfo' => 'qdachhoehe_ps_',
        'qualitaetdachhoehe_herkunft_processstep_processstep' => 'qdachhoehe_ps_',
        'qualitaetdachhoehe_herkunft_processstep_source' => 'qdachhoehe_ps_src_',
        'qualitaetdachhoehe_herkunft_processstep_source_source' => 'qdachhoehe_ps_src_',
        'qualitaetdachhoehe_herkunft_source' => 'qdachhoehe_src_',
        'qualitaetdachhoehe_herkunft_source_source' => 'qdachhoehe_src_',
      );

      // In folgenden Elementen nur die weiteren optionalen Attribute umbenennen
      // und nicht alle (mindert vermeidbare Modelländerungen)
      $onlyoptional = array(
        'ax_benutzergruppemitzugriffskontrolle',
        'ax_benutzergruppenba',
        'ax_fortfuehrungsnachweisdeckblatt',
        'ax_gebiet_bundesland'
      );

      foreach($featureTypes as $featureType) {
				if ($featureType->isAbstract == 'f') {
					foreach($featureType->attributes as $a) {
						if($a->short_name != end($a->parts)->name) {
							$path =
								implode('_',
									array_map(
										function($part) {
											return $part->name;
										},
										array_slice($a->parts, 0, count($a->parts)-1)
									)
								);

							if(array_key_exists($path, $renamed_paths))
								continue;

							$renamed_paths[$path] = substr( $a->short_name, 0, strlen($a->short_name) - strlen(end($a->parts)->name) );
							$this->logger->log("<br>" . $featureType->alias .  ": Umbenannter Pfad: " . $path . " => " . $renamed_paths[$path] . " [" . end($a->parts)->name . " => " . $a->short_name . "; Konflikte: " . $a->conflictsAt . "]");
						}
					}
				}
      }

      // Auch nicht umbenannte Attribute auf Pfaden mit Umbenennung umbenennen
      // (längere Pfade zuerst)
      // Führt z.B. zu:
      //   zeigtaufexternes_uri statt fachdatenobjekt_uri
      //   processstep_sourcereferencesystem statt processstep_source_source_sourcereferencesystem
      foreach($featureTypes as $featureType) {
        $namelen_exceeded = false;
        foreach($featureType->attributes as $a) {
          if( !in_array($featureType->name, $onlyoptional) || $a->isOptional() ) {
            for( $i = count($a->parts) - 1; $i > 0; $i-- ) {
              $path =
                implode('_',
                  array_map(
                    function($part) {
                      return $part->name;
                    },
                    array_slice($a->parts, 0, $i)
                  )
                );

              if(array_key_exists($path, $renamed_paths)) {
                $a->short_name_orig = $a->short_name;
                $a->short_name = $renamed_paths[$path] . end($a->parts)->name;
                # echo "-- renamed " . end($a->parts)->name . " in " . $a->short_name . " in " . $featureType->alias . "\n";
                if( strlen($a->short_name) > PG_MAX_NAME_LENGTH ) {
                  $namelen_exceeded = true;
                }
                $this->logger->log("<br>" . $a->path_name . ": Pfadumbenennung von " . end($a->parts)->name . " in " . $a->short_name . " in " . $featureType->alias . "[auf umbenannten Pfad: $path].");
                break;
              }
            }
          }
        }

        if($featureType->hasCollisions() || $namelen_exceeded ) {
          // Kommt vor bei:
          // AX_Fortfuehrungsfall.verweistauf_uri => dienststellenlogo_uri
          // AX_Fortfuehrungsfall.verweistauf_uri => enthaeltewp_uri
          // AX_Fortfuehrungsfall.verweistauf_uri => verweistauf_uri
          // AX_BesondererHoehenpunkt.erfassungbesondererhoehenpunkt_ax_dqerfassungsmethodebesondererhoehenpunkt => ax_dqerfassungsmethodebesondererhoehenpunkt
          $this->logger->log("<br>" . $featureType->alias . ": Umbenennung auf gleichen Pfaden führte zu Doppeldeutigkeiten oder Überlängen!");
          foreach($featureType->attributes as $a) {
            if(property_exists($a, "short_name_orig") && ($a->frequency > 1 || strlen($a->short_name) > PG_MAX_NAME_LENGTH) )
            {
              $this->logger->log("<br>" . $featureType->alias . "." . $a->short_name . " => " . $a->short_name_orig);
              $a->short_name = $a->short_name_orig;
            }
          }
        }
      }
    }

    return $featureTypes;
  }

  function createFeatureTypeTables($stereotype, $parent, $classes, $parts = array(), $createUserInfoColumns = false) {
    $sql = "";
		$this->createFeatureTypes($stereotype, $parent, $classes);
    foreach($this->featureTypes as $featureType) {
			if ($featureType->isAbstract == 'f') {
				$sql .= $featureType->asFlattenedSql();

				foreach($featureType->attributes as $a) {
					if(in_array($a->datatype, array("", "ci_rolecode")))
						continue;

					if( $a->stereotype == "enumeration" ) {
						$this->wertartenQueries[$featureType->name] = "SELECT wert::text AS k, beschreibung::text AS v,'' AS d,'" . $a->short_name . "' AS bezeichnung,'" . $featureType->name . "' AS element FROM " . $a->datatype;
					}
					else if( $a->stereotype == "codelist" ) {
						if($a->datatype == "aa_anlassart" && !WITH_CODE_LISTS)
							continue;


						$this->wertartenQueries[$featureType->name] = "SELECT id::text AS k, value::text AS v,'' AS d,'" . $a->short_name . "' AS bezeichnung,'" . $featureType->name . "' AS element FROM " . $a->datatype;
					}
				}
			}
    }

    return $sql;
  }

  function createFeatureTypeGfs($stereotype, $parent, $classes) {
    $gfs = "";
		$this->createFeatureTypes($stereotype, $parent, $classes);
    foreach($this->featureTypes as $featureType) {
			if ($featureType->isAbstract == 'f') {
				$gfs .= $featureType->asGfs();
			}
    }

    return $gfs;
  }

  function listFeatureTypesAttributes($stereotype, $parent, $classes) {
    if ($this->logger->level > 0)
      $this->createFeatureTypes($stereotype, $parent, $classes);
  }

  function getAttributesFromComplexType($datatype, $stereotype) {
    if ($stereotype == 'union')
      $typeList = $this->umlSchema->unionTypes;
    if ($stereotype == 'datatype')
      $typeList = $this->umlSchema->dataTypes;

    $attributes = array();

    foreach ($typeList[$datatype]->attributes AS $attribute) {
      if ($this->umlSchema->stereoTypeAlreadyExists($attribute->datatype, $attribute->stereotype)) {
        array_merge($attributes, $this->getAttributesFromComplexType($attribute->datatype, $attribute->stereotype));
      }
      else {
        $attributes[] = $attribute;
      }
    }

    return $attributes;
  }

  function identifier($name)
  {
    if( strlen($name) <= PG_MAX_NAME_LENGTH)
      return $name;

    if( !property_exists($this, "nameidx") ) {
        $this->nameidx = 0;
    }

    $id = ++$this->nameidx;

    return substr($name, 0, PG_MAX_NAME_LENGTH - 3) . $id;
  }
}
?>
