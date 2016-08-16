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
echo '<!DOCTYPE html>
<html lang="de">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	</head>
	<body>';
	#*****************************************************************************
	# 
	#*****************************************************************************

	# Initialize the umlSchema object
	$umlSchema = new Schema(UML_SCHEMA, $logger);
	$umlSchema->openConnection(PG_HOST, PG_DBNAME, PG_USER, PG_PASSWORD);
	$umlSchema->logger->level = 0;
	$umlSchema->logger->debug = true;

	# Initialize the gmlSchema object
	$ogrSchema = new OgrSchema(OGR_SCHEMA, $logger);
	$ogrSchema->umlSchema = $umlSchema;
	$sql = $ogrSchema->asSql();

	#**************
	# Enumerations
	#**************
	# Erzeuge Enummerations
	foreach($umlSchema->getEnumerations() AS $enumeration) {
		$sql .= $umlSchema->createEnumerationTable($enumeration, $ogrSchema);
	}
	$logger->log('<br><hr><br>');

	#***********
	# CodeLists
	#***********
	# Lade CodeLists
	foreach($umlSchema->getCodeLists() AS $code_list) {
		$sql .= $umlSchema->createCodeListTable($code_list);
	}
	$logger->log('<br><hr><br>');

	#***********
	# Unions
	#***********
	# Lade oberste Klassen vom Typ Union
	$topDataTypes = $umlSchema->getTopUmlClasses('Union');

	# Für alle oberen Unions
	foreach($topDataTypes as $topDataType) {
		$umlSchema->logger->log('<br><b>Top UnionType: ' . $topDataType['name'] . '</b> (' . $topDataType['xmi_id'] . ')');
		$sql .= $umlSchema->createComplexDataTypes('Union', $topDataType, $ogrSchema);
	}
	$logger->log('<br><hr><br>');

	#********************************************
	# Create DataTypes not definend in UML-Model
	#********************************************
	$sql .= $umlSchema->createExternalDataTypes($ogrSchema);
	$logger->log('<br><hr><br>');
	
	#***********
	# DataTypes
	#***********
	$dataTypes = array();
	# Lade oberste Klassen vom Typ DataType
	$topDataTypes = $umlSchema->getTopUmlClasses('DataType');

	# Für alle oberen Datentypen
	foreach($topDataTypes as $topDataType) {
		$umlSchema->logger->log('<br><b>Top DataType: ' . $topDataType['name'] . '</b> (' . $topDataType['xmi_id'] . ')');
		$sql .= $umlSchema->createComplexDataTypes('DataType', $topDataType, $ogrSchema);
	}
	$logger->log('<br><hr><br>');

	#**************
	# FeatureTypes
	#**************
	# Lade oberste Klassen vom Typ FeatureType, die von keinen anderen abgeleitet wurden
	$topClasses = $umlSchema->getTopUmlClasses('FeatureType');
	
	# Für alle oberen Klassen
	foreach($topClasses as $topClass) {
		$ogrSchema->logger->log('<br><b>TopKlasse: ' . $topClass['name'] . '</b> (' . $topClass['xmi_id'] . ')');
		$sql .= $ogrSchema->createFeatureTypeTables('FeatureType', null, $topClass);
	}
	$logger->log('<br><hr><br>');

#	$gmlSchema->execSql($sql);

?><pre><?php
	echo $sql;
?></pre>
<?php
echo '	</body>
</html>';
?>