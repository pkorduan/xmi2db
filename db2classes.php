<?php
	include('conf/database_conf.php');
	include('classes/logger.php');
	include('classes/databaseobject.php');
	include('classes/schema.php');
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

	# Initialize the gmlSchema object
	$gmlSchema = new Schema(CLASSES_SCHEMA, $logger);
	$sql = $gmlSchema->asSql();

	#**************
	# Enumerations
	#**************
	# Erzeuge Enummerations
	foreach($umlSchema->getEnumerations() AS $enumeration) {
		$logger->log('<br><b>Create Enumeration: ' . $enumeration['name'] . '</b> (' . $enumeration['xmi_id'] . ')');

		$table = new Table($enumeration['name']);

		# definiere Attribute
		$attribute = new Attribute('wert', 'character varying');
		$table->addAttribute($attribute);
		$attribute = new Attribute('beschreibung', 'character varying');
		$table->addAttribute($attribute);

		# definiere Primärschlüssel
		$table->primaryKey = 'wert';

		# read Values
		$enumType = new EnumType($enumeration['name'], $logger);
		$enumType->setSchemas($umlSchema, $gmlSchema);
		$enumType->setId($enumeration['id']);
		$table->values = $enumType->getValues($enumeration);
		$logger->log($table->values->asTable($table->attributes));

		$tableSql = $table->asSql();
		$logger->log('<pre>' . $tableSql . '</pre>');
		$sql .= $tableSql;
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
		$sql .= $umlSchema->createComplexDataTypes('Union', $topDataType);
	}
	$logger->log('<br><hr><br>');

	#********************************************
	# Create DataTypes not definend in UML-Model
	#********************************************
	
	#*******************************
	# SC_CRS
	#*******************************
	$dataType = new DataType('sc_crs', 'DataType', $logger);
	$dataType->setUmlSchema($umlSchema);
	$dataType->setId(0);

	# create Attributes
	$dataTypeAttribute = new Attribute(
		'scope','CharacterString',
		$dataType->name
	);
	$dataTypeAttribute->setStereoType('CharacterString');
	$dataTypeAttribute->attribute_type = 'ISO 19136 GML Type';
	$dataTypeAttribute->setMultiplicity('1', '-1');
	$logger->log(
		'<b>' . $dataTypeAttribute->name . '</b>
		datatype: <b>' . $dataTypeAttribute->datatype .'</b>
		stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>'
	);
	$dataType->addAttribute($dataTypeAttribute);

	# Create Comments
	$comment  = $dataTypeAttribute->attribute_type . ': ' . $dataTypeAttribute->name;
	$comment .= ' ' . $dataTypeAttribute->multiplicity;
	$dataType->addComment($comment);

	# Erzeuge SQL und registriere DataType in Liste
	$sql .= $dataType->asSql();
	$umlSchema->dataTypes[$dataType->namea] = $dataType;

	#*******************************
	# doubleList
	#*******************************
	$dataType = new DataType('doubleList', 'DataType', $logger);
	$dataType->setUmlSchema($umlSchema);
	$dataType->setId(0);

	# create Attributes
	$dataTypeAttribute = new Attribute(
		'list','Sequence',
		$dataType->name
	);
	$dataTypeAttribute->setStereoType('Sequence');
	$dataTypeAttribute->attribute_type = 'ISO 19136 GML Type';
	$dataTypeAttribute->setMultiplicity('0', '1');
	$logger->log(
		'<b>' . $dataTypeAttribute->name . '</b>
		datatype: <b>' . $dataTypeAttribute->datatype .'</b>
		stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>'
	);
	$dataType->addAttribute($dataTypeAttribute);

	# Create Comments
	$comment  = $dataTypeAttribute->attribute_type . ': ' . $dataTypeAttribute->name;
	$dataType->addComment($comment);

	# Erzeuge SQL und registriere DataType in Liste
	$sql .= $dataType->asSql();
	$umlSchema->dataTypes[$dataType->namea] = $dataType;

	#*******************************
	# Measure
	#*******************************
	$dataType = new DataType('Measure', 'DataType', $logger);
	$dataType->setUmlSchema($umlSchema);
	$dataType->setId(0);

	# create Attributes
	$dataTypeAttribute = new Attribute(
		'value','Integer',
		$dataType->name
	);
	$dataTypeAttribute->setStereoType('DataType');
	$dataTypeAttribute->attribute_type = 'ISO 19136 GML Type';
	$dataTypeAttribute->setMultiplicity('0', '1');
	$logger->log(
		'<b>' . $dataTypeAttribute->name . '</b>
		datatype: <b>' . $dataTypeAttribute->datatype .'</b>
		stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>'
	);
	$dataType->addAttribute($dataTypeAttribute);

	# Create Comments
	$comment  = $dataTypeAttribute->attribute_type . ': ' . $dataTypeAttribute->name;
	$dataType->addComment($comment);

	# Erzeuge SQL und registriere DataType in Liste
	$sql .= $dataType->asSql();
	$umlSchema->dataTypes[$dataType->namea] = $dataType;

	#***********
	# DataTypes
	#***********
	$dataTypes = array();
	# Lade oberste Klassen vom Typ DataType
	$topDataTypes = $umlSchema->getTopUmlClasses('DataType');

	# Für alle oberen Datentypen
	foreach($topDataTypes as $topDataType) {
		$umlSchema->logger->log('<br><b>Top DataType: ' . $topDataType['name'] . '</b> (' . $topDataType['xmi_id'] . ')');
		$sql .= $umlSchema->createComplexDataTypes('DataType', $topDataType);
	}
	$logger->log('<br><hr><br>');

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
	$logger->log('<br><hr><br>');

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
	$logger->log('<br>Ende Debug Ausgabe<br><hr><br>');

#	$classSchema->execSql($sql);

?><pre><?php
	echo $sql;
?></pre>
<?php
echo '	</body>
</html>';
?>