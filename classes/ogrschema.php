<?php
class OgrSchema extends Schema {

	function createFeatureTypeTables($stereotype, $parent, $class, $attributPath = '') {
		$this->logger->log('<br><b>Create ' . $stereotype . ': ' . $class['name'] .' </b>');
#		$this->logger->log('<br><b>Klasse: ' . $class['name'] . '</b> (' . $class['xmi_id'] . ')');

		# Erzeuge FeatueType
		$featureType = new FeatureType($class['name'], $parent, $this->logger, $this->umlSchema);
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