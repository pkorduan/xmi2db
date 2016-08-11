<?php
	include('conf/database_conf.php');
	include('classes/logger.php');
	include('classes/databaseobject.php');
	include('classes/schema.php');
	include('classes/ogrschema.php');
	include('classes/table.php');
	include('classes/attribute.php');
	include('classes/data.php');
	include('classes/datatype.php');
	include('classes/enumtype.php');
	include('classes/associationend.php');
	include('classes/featuretype.php');
	$tabNameAssoc = array();
	$log_sql = '';
	$logger = new Logger(LOGLEVEL);

	#*****************************************************************************
	# 
	#*****************************************************************************

	# Initialize the umlSchema object
	$umlSchema = new Schema(UML_SCHEMA, $logger);
	$umlSchema->openConnection(PG_HOST, PG_DBNAME, PG_USER, PG_PASSWORD);

	# Initialize the gmlSchema object
	$ogrSchema = new OgrSchema(OGR_SCHEMA, $logger);
	$ogrSchema->umlSchema = $umlSchema;
	$sql = $ogrSchema->asSql();

	#**************
	# FeatureTypes
	#**************
	# Lade oberste Klassen vom Typ FeatureType, die von keinen anderen abgeleitet wurden
	$topClasses = $umlSchema->getTopUmlClasses('FeatureType');

	# Für alle oberen Klassen
	foreach($topClasses as $topClass) {
		listFeatureTypesAttributes('FeatureType', null, $topClass);
	}
	# Sortiere Ausgabeliste
	ksort($ogrSchema->renameList);
	header('Content-Type: application/json');
	$json = '{';
	foreach($ogrSchema->renameList AS $key => $value) {
		$json .= "\n	\"{$key}\":\"{$value}\"";
	}
	$json .= "\n}";
	echo $json;
	$logger->log('<br><hr><br>');

	function listFeatureTypesAttributes($stereotype, $parent, $class, $attributPath = '') {
		global $logger, $umlSchema, $ogrSchema;

		$logger->log('<br><b>Klasse: ' . $class['name'] . '</b> (' . $class['xmi_id'] . ')');

		# Erzeuge FeatueType
		$featureType = new FeatureType($class['name'], $parent, $logger, $umlSchema);
		$featureType->setId($class['id']);
		$featureType->primaryKey = 'gml_id';
		if ($parent != null)
			$logger->log(' abgeleitet von: <b>' . $parent->alias . '</b>');

		$featureType->getAttributesUntilLeafs($featureType->alias, array());

		$featureType->flattenAttributes();

		# lade abgeleitete Klassen
		$subClasses = $umlSchema->getSubUmlClasses($stereotype, $class);
		if (empty($subClasses)) {
			$featureType->unifyShortNames(1);
			$ogrSchema->renameList = array_merge(
				$ogrSchema->renameList,
				$featureType->outputFlattenedAttributes()
			);
		}

		foreach($subClasses as $subClass) {
			listFeatureTypesAttributes($stereotype, $featureType, $subClass);
		}
	}

?>