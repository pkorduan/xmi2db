<?php
class OgrSchema extends Schema {

	function createFeatureTypeTables($stereotype, $parent, $class, $attributPath = '') {
		$this->logger->log('<br><b>Create ' . $stereotype . ': ' . $class['name'] .' </b>');
		# Erzeuge FeatueType
		$featureType = new FeatureType($class['name'], $parent, $this->logger, $this->umlSchema);
		$featureType->setId($class['id']);
		$featureType->primaryKey = 'gml_id';
		if ($parent != null)
			$this->logger->log(' abgeleitet von: <b>' . $parent->alias . '</b>');
		?>
		<table><?php
		foreach ($featureType->getFlattenedAttributes($featureType->alias, array()) AS $names) {
			$attribute = new Attribute(
				implode('_', $names),
				'undefined',
				$featureType
			);
			$short_name = $attribute->getFlattenedName($names);
			echo '<tr>';
			echo '<td>' . $attribute->alias . '</td><td>';
			if (strlen($short_name) > 58)
				echo '<u>' .  $short_name . '</u>';
			else
				echo $short_name;
			echo '</td>';
			echo '</tr>';
		} ?>
		</table><?php

		/*
		foreach($featureType->getAttributes() AS $attribute) {
			if ($attributePath != '')
				$pathPart = $attributPath . '|' . $class['name'] . '|' . $attribute['name'];
			else
				$pathPart = $class['name'] . '|' . $attribute['name'];
			
			# Create Attribute
			$this->logger->log('<br>§1');
			$featureTypeAttribute = new Attribute(
				$attribute['name'],
				$attribute['datatype'],
				$featureType,
				$pathPart
			);
			$featureTypeAttribute->setStereoType($attribute['stereotype']);
			$featureTypeAttribute->attribute_type = $attribute['attribute_type'];
			$featureTypeAttribute->setMultiplicity($attribute['multiplicity_range_lower'], $attribute['multiplicity_range_upper']);

			# if attribute is a complex type
			if ($this->umlSchema->stereoTypeAllreadyExists($featureTypeAttribute->datatype, $featureTypeAttribute->stereotype)) {
				# get the attributes form the leaf data type of this type
				$this->logger->log('<br>Attribute: ' . $featureTypeAttribute->name . ' ist komplex und hat Attribute:');
				foreach($this->getAttributesFromComplexType($featureTypeAttribute->datatype, $featureTypeAttribute->stereotype) AS $complexeAttribute) {
					$a = $complexeAttribute;
					$this->logger->log('<br>' . $complexeAttribute->path . ' ' . $complexeAttribute->flattened_name);
					$this->logger->log('<br>' . $a->flattened_name);
					$complexeAttribute->flattened_name = $complexeAttribute->flattened_name;
					
					$featureType->addAttribute($complexeAttribute);
					$this->attributes[] = $complexeAttribute;
				}
			}
			else {
				$featureType->addAttribute($featureTypeAttribute);
				$this->attributes[] = $featureTypeAttribute;
			}
		}
		$this->logger->log($featureType->attributesAsTable());
		*/

		# lade navigierbare Assoziationsenden von 1:n Assoziationen
		foreach($this->umlSchema->getAssociationEnds($class) AS $end) {
			$associationEnd = new AssociationEnd(
				$end['b_name'],
				$end['a_class_name'],
				$end['b_class_name'],
				$this->logger
			);
			$associationEnd->stereotype = 'FeatureType';
			$associationEnd->setMultiplicity($end['b_multiplicity_range_lower'], $end['b_multiplicity_range_upper']);
			$featureType->addAssociationEnd($associationEnd);
		}
		$this->logger->log($featureType->associationsAsTable());

		$sql = $featureType->asFlattenedSql();

		$this->logger->log('<pre>' . $sql . '</pre>');
		
		# lade abgeleitete Klassen
		$subClasses = $this->umlSchema->getSubUmlClasses($stereotype, $class);

		/*
		# Für alle abgeleiteten Klassen
		foreach($subClasses as $subClass) {
			$this->logger->log('<br><b>Sub' . $stereotype . ': ' . $subClass['name'] . '</b> (' . $subClass['xmi_id'] . ')');
			$sql .= $this->createFeatureTypeTables($stereotype, $featureType, $subClass);
		}
		*/

		return $sql;
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