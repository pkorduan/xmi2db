<?php
	include('conf/database_conf.php');
	include('classes/logger.php');
	include('classes/databaseobject.php');
	include('classes/schema.php');
	include('classes/table.php');
	include('classes/attribute.php');
	include('classes/value.php');
	include('classes/datatype.php');
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

	#*********
	# Schema
	#*********
	$classSchema = new Schema(CLASSES_SCHEMA, $logger);
	$sql = $classSchema->asSql();

	#**************
	# Enumerations
	#**************
	# Erzeuge Enummerations
	foreach($umlSchema->getEnumerations() AS $enumeration) {
		$sql .= $umlSchema->createEnumerationTable($enumeration) . "\n";
	}
	$umlSchema->logger->log('<br><hr><br>');

	#***********
	# CodeLists
	#***********
	# Lade CodeLists
	foreach($umlSchema->getCodeLists() AS $code_list) {
		$sql .= $umlSchema->createCodeListTable($code_list);
	}
	$umlSchema->logger->log('<br><hr><br>');

	#***********
	# Unions
	#***********
	# Lade oberste Klassen vom Typ Union
	$topDataTypes = $umlSchema->getTopUmlClasses('Union');

	# Für alle oberen Unions
	foreach($topDataTypes as $topDataType) {
		$umlSchema->logger->log('<br><b>Top UnionType: ' . $topDataType['name'] . '</b> (' . $topDataType['xmi_id'] . ')');
		$umlSchema->createComplexDataTypes('Union', $topDataType);
	}
	$umlSchema->logger->log('<br><hr><br>');

	#***********
	# DataTypes
	#***********#
	$dataTypes = array();
	# Lade oberste Klassen vom Typ DataType
	$topDataTypes = $umlSchema->getTopUmlClasses('DataType');

	# Für alle oberen Datentypen
	foreach($topDataTypes as $topDataType) {
		$umlSchema->logger->log('<br><b>Top DataType: ' . $topDataType['name'] . '</b> (' . $topDataType['xmi_id'] . ')');
		$sql .= $umlSchema->createComplexDataTypes($topDataType, $topDataType);
	}
	$umlSchema->logger->log('<br><hr><br>');

	#**************
	# FeatureTypes
	#**************
	# Lade oberste Klassen vom Typ FeatureType, die von keinen anderen abgeleitet wurden
	$topClasses = $umlSchema->getTopUmlClasses('FeatureType');
	
	# Für alle oberen Klassen
	foreach($topClasses as $topClass) {
		$umlSchema->logger->log('<br><b>TopKlasse: ' . $topClass['name'] . '</b> (' . $topClass['xmi_id'] . ')');
		$sql .= $umlSchema->createFeatureTypeTables('FeatureType', null, $topClass);
	}
	$umlSchema->logger->log('<br><hr><br>');

	/*
	#******************
	# n:m Associations
	#******************
	# Lade n:m Associations
	$associations = $umlSchema->getAssociations();
	foreach($associations AS $association) {
		$text = '<br><b>Association: ' . $association['assoc_id'] . '</b><br>' .
			$association['a_class'] . ' hat ' . $association['a_num'] . ' ' . $association['b_class'] . ' über ' . $association['a_rel'] . '<br>';
		if ($association['b_rel'] != '')
			$text .= $association['b_class'] . ' hat ' . $association['b_num'] . ' ' . $association['b_rel'];
		if ($association['a_num'] == 'n' AND $association['b_num'] == 'n') {
			$assoc_table = strtolower($association['a_class'] . '2' . $association['b_class']);
			$text .= '<br>Lege n:m Tabelle ' . $assoc_table . ' an.';
			$sql .= $umlSchema->createAssociationTable($association);
		}
		$umlSchema->logger->log($text);
	}
*/
	$umlSchema->logger->log('<br>Ende Debug Ausgabe<br><hr><br>');

	#execSql($sql);

?><pre><?php
	echo $sql;
?></pre>
<?php
	/*****************************************************************************
	* Funktionen
	******************************************************************************/
	function execSql($sql) {
		global $db_conn;
		global $log_sql;
		$log_sql .= $sql;
		pg_query($db_conn, $sql);
	}

echo '	</body>
</html>';
?>