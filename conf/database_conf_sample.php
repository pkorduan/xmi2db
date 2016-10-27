<?php
	define(
		'VERSION',
		preg_replace(
			"/\r|\n/",
			"", 
			file((basename($_SERVER['SCRIPT_NAME']) != 'index.php' ? '../' : '') . 'README.md')[3])
	);
	error_reporting(E_ALL & ~E_NOTICE);

	$loglevel = ($_REQUEST['loglevel'] != '') ? $_REQUEST['loglevel'] : '0';
	define('LOGLEVEL', $loglevel);

	define('PG_HOST', 'localhost');
	define('PG_DBNAME', 'postgres');
	define('PG_USER', 'postgres');
	define('PG_PASSWORD', 'postgres');
	define('PG_MAX_NAME_LENGTH', 58); // Maximale Länge von Tabellen, Type und Attributnamen

	$umlSchemaVar = ($_REQUEST['umlSchema'] != '') ? $_REQUEST['umlSchema'] : 'aaa_uml';
	define('UML_SCHEMA', $umlSchemaVar);

	$gmlSchemaVar = ($_REQUEST['gmlSchema'] != '') ? $_REQUEST['gmlSchema'] : 'aaa_gml';
	define('CLASSES_SCHEMA', $gmlSchemaVar);

	$ogrSchemaVar = ($_REQUEST['ogrSchema'] != '') ? $_REQUEST['ogrSchema'] : 'aaa_ogr';
	define('OGR_SCHEMA', $ogrSchemaVar);

	define('OGR_GEOMETRY_COLUMN_NAME', 'wkb_geometry');

	define('WITH_UUID_OSSP', false);

	define('FILTER_FILE','../conf/filter.json');

	# Definition of the model conf file
	define('CONF_FILE','../conf/model_aaa.php');
?>