<?php
class OgrSchema extends Schema {

	function create_delete_trigger() {
		$sql = str_replace('schema_name', $this->schemaName, file_get_contents('../sql/delete_trigger.sql'));
		if (!empty(GEOMETRY_EPSG_CODE)) {
			$sql = str_replace('25832', GEOMETRY_EPSG_CODE, $sql);
		}
		return $sql;
	}

	function create_ax_fortfuehrungsauftrag() {
		$sql = file_get_contents('../sql/ax_fortfuehrungsauftrag.sql');
		return $sql;
	}

	function createEnumerationTable($enumeration, $dbSchema) {
		if ($this->is_table_filtered($enumeration['name'])) {
			$this->logger->log("<br>Ignoriere Enumeration: {$enumeration['name']}");
		}

		$this->logger->log('<br><b>Create Enumeration Tables: ' . $enumeration['name'] . '</b> (' . $enumeration['xmi_id'] . ')');

		$table = new Table($enumeration['name']);

		# read Values
		$enumType = new EnumType($enumeration['name'], $this->logger);
		$enumType->setSchemas($this->umlSchema, $dbSchema);
		$enumType->setId($enumeration['id']);
		$table->values = $enumType->getValues($enumeration);

		# definiere Attribute
		$wert_type = (empty($enumType)) ? 'chacacter varying' : $enumType->getWertType();
		$attribute = new Attribute('wert', $wert_type);
		$table->addAttribute($attribute);
		$attribute = new Attribute('beschreibung', 'character varying');
		$table->addAttribute($attribute);

		# definiere Primärschlüssel
		$table->primaryKey = 'wert';

		$this->logger->log($table->values->asTable($table->attributes));

#		if (
#			$table->values->rows[0][0] != $table->values->rows[0][1] AND
#			$table->values->rows[0][1] != 'NULL'
#		)

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

	function createFeatureTypeTables($stereotype, $parent, $class, $attributPath = array(), $createUserInfoColumns = false) {
		if ($this->is_table_filtered($class['name'])) {
			$this->logger->log("<br>Ignoriere FeatureType: {$class['name']} komplett");
		}
		else {
			$this->logger->log('<br><b>Create ' . $stereotype . ': ' . $class['name'] .' </b>');

			# Erzeuge FeatueType
			$featureType = new FeatureType($class['name'], $parent, $this->logger, $this->umlSchema, $this->enumerations);
			$featureType->ogrSchema = $this;

			$featureType->setId($class['id']);
			$featureType->primaryKey = 'ogc_fid';
			$featureType->primaryKeyType = 'serial NOT NULL';
			$featureType->primaryKeyNullable = false;

			if ($parent != null)
				$this->logger->log(' abgeleitet von: <b>' . $parent->alias . '</b>');

			$featureType->attribute_filter = $this->filter[$class['name']]['attribute'];
			if (!is_array($attribute_filter))
				$featureType->attribute_filter = array();

			$this->logger->log('<br><b>Hole Attribute und verfolge dabei Datentypen bis zum Ende.</b>');
			$featureType->getAttributesUntilLeafs($featureType->alias, $stereotype, array());

			$featureType->flattenAttributes();

			$featureType->outputFlattenedAttributes();
			if ($this->logger->level > 0)
				$featureType->outputFlattendedAttributTable();

			$featureType->setAssociationEnds($class);

			#echo '<pre>' . $featureType->asFlattenedSql() . '</pre>';

			# lade abgeleitete Klassen
			$subClasses = $this->umlSchema->getSubUmlClasses($stereotype, $class);
			if (empty($subClasses)) {
				$featureType->unifyShortNames(1);
				$this->renameList = array_merge(
					$this->renameList,
					$featureType->outputFlattenedAttributes()
				);
				if ($this->logger->level > 0)
					$featureType->outputFlattendedAttributTable();
				$sql .= $featureType->asFlattenedSql();
			}

			foreach($subClasses as $subClass) {
				# übergibt den featureType als parent an die Sub-Klassen
				$sql .= $this->createFeatureTypeTables($stereotype, $featureType, $subClass);
			}
		}

		return $sql;
	}

	function listFeatureTypesAttributes($stereotype, $parent, $class, $with_first_attrib_name = false) {
		$this->logger->log('<br><b>Klasse: ' . $class['name'] . '</b> (' . $class['xmi_id'] . ')');

		# Erzeuge FeatueType
		$featureType = new FeatureType($class['name'], $parent, $this->logger, $this->umlSchema);
		$featureType->setId($class['id']);
		$featureType->primaryKey = 'gml_id';
		if ($parent != null)
			$this->logger->log(' abgeleitet von: <b>' . $parent->alias . '</b>');

		$featureType->getAttributesUntilLeafs($featureType->alias, $stereotype, array());

		$featureType->flattenAttributes();

		# lade abgeleitete Klassen
		$subClasses = $this->umlSchema->getSubUmlClasses($stereotype, $class);
		if (empty($subClasses)) {
			if ($with_first_attrib_name) {
				$featureType->unifyShortNamesWithFirst(1);
			}
			else {
				$featureType->unifyShortNames(1);
			}
			$this->renameList = array_merge(
				$this->renameList,
				$featureType->outputFlattenedAttributes()
			);
			if ($this->logger->level > 0)
				$featureType->outputFlattendedAttributTable();
		}

		foreach($subClasses as $subClass) {
			$this->listFeatureTypesAttributes($stereotype, $featureType, $subClass, $with_first_attrib_name);
		}
	}

	function getAttributesFromComplexType($datatype, $stereotype) {
		if ($stereotype == 'union')
			$typeList = $this->umlSchema->unionTypes;
		if ($stereotype == 'datatype')
			$typeList = $this->umlSchema->dataTypes;

		$attributes = array();

		foreach ($typeList[$datatype]->attributes AS $attribute) {
			if ($this->umlSchema->stereoTypeAllreadyExists($attribute->datatype, $attribute->stereotype)) {
				array_merge($attributes, $this->getAttributesFromComplexType($attribute->datatype, $attribute->stereotype));
			} else {
				$attributes[] = $attribute;
			}
		}

		return $attributes;
	}
	
}
?>