<?php
class OgrSchema extends Schema {

	function createEnumerationTable($enumeration, $dbSchema) {
		$this->logger->log('<br><b>Create Enumeration Tables: ' . $enumeration['name'] . '</b> (' . $enumeration['xmi_id'] . ')');

		$table = new Table($enumeration['name']);

		# read Values
		$enumType = new EnumType($enumeration['name'], $this->logger);
		$enumType->setSchemas($this->umlSchema, $dbSchema);
		$enumType->setId($enumeration['id']);
		$table->values = $enumType->getValues($enumeration);

		# definiere Attribute
		$wert_type = $enumType->getWertType();
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

	function createFeatureTypeTables($stereotype, $parent, $class, $attributPath = array()) {
		$this->logger->log('<br><b>Create ' . $stereotype . ': ' . $class['name'] .' </b>');
#		$this->logger->log('<br><b>Klasse: ' . $class['name'] . '</b> (' . $class['xmi_id'] . ')');

		# Erzeuge FeatueType
		$featureType = new FeatureType($class['name'], $parent, $this->logger, $this->umlSchema);
		$featureType->ogrSchema = $this;
		$featureType->setId($class['id']);
		$featureType->primaryKey = 'gml_id';
		if ($parent != null)
			$this->logger->log(' abgeleitet von: <b>' . $parent->alias . '</b>');

		$this->logger->log('<br><b>Hole Attribute und verfolge dabei Datentypen bis zum Ende.</b>');

		$featureType->getAttributesUntilLeafs($featureType->alias, array());
		
		$featureType->flattenAttributes();
		
		$featureType->outputFlattenedAttributes();

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

			$sql .= $featureType->asFlattenedSql();
		}

		foreach($subClasses as $subClass) {
			# übergibt den featureType als parent an die Sub-Klassen
			$sql .= $this->createFeatureTypeTables($stereotype, $featureType, $subClass);
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

		$featureType->getAttributesUntilLeafs($featureType->alias, array());

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