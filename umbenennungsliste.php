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
	$logger->level = 0;
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
		$ogrSchema->listFeatureTypesAttributes('FeatureType', null, $topClass);
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

?>