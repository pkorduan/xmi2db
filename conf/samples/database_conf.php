<?php
  define('SCRIPT_ORIGIN', 'https://gdi-service.de/3rdparty/');
  $rel_path = (!array_key_exists('SCRIPT_NAME', $_SERVER) || basename($_SERVER['SCRIPT_NAME']) != 'index.php' ? '../' : '');
  echo $rel_path;
  define(
    'VERSION',
    preg_replace(
      "/\r|\n/",
      "",
      file($rel_path . 'README.md')[3]
    )
  );
  error_reporting(E_ALL & ~E_NOTICE);

  $loglevel = ($_REQUEST['loglevel'] != '') ? $_REQUEST['loglevel'] : '0';
  define('LOGLEVEL', $loglevel);

  define('PG_HOST', 'localhost');
  define('PG_DBNAME', 'postgres');
  define('PG_USER', 'postgres');
  define('PG_PASSWORD', 'postgres');
  define('PG_PORT', '5432');
  define('PG_MAX_NAME_LENGTH', 58); // Maximale Länge von Tabellen, Type und Attributnamen
  define('PG_GML_ID', 'character(16)');
  define('PG_CHARACTER_VARYING', 'character varying');
  define('PG_BOOLEAN', 'character varying');
  define('PG_DATETIME', 'timestamp without time zone');
  define('PG_DATE', 'date');
  define('PG_WITH_OIDS', true);
  define('PG_WITH_IDENTIFIER', true);
  define('PG_CREATE_TABLE', 'CREATE TABLE IF EXISTS');
  define('CREATE_SCHEMA', true);
  define('WITH_DELETE_TRIGGER', true);
  define('WITH_INDEXES', false);

  $umlSchemaVar = ($_REQUEST['umlSchema'] != '') ? $_REQUEST['umlSchema'] : 'aaa_uml';
  define('UML_SCHEMA', $umlSchemaVar);

  $gmlSchemaVar = ($_REQUEST['gmlSchema'] != '') ? $_REQUEST['gmlSchema'] : 'aaa_gml';
  define('CLASSES_SCHEMA', $gmlSchemaVar);

  $ogrSchemaVar = ($_REQUEST['ogrSchema'] != '') ? $_REQUEST['ogrSchema'] : 'aaa_ogr';
  define('OGR_SCHEMA', $ogrSchemaVar);

  $geometryColumnVar = ($_REQUEST['geometryColumn'] != '') ? $_REQUEST['geometryColumn'] : 'wkb_geometry';
  define('GEOMETRY_COLUMN_NAME', $geometryColumnVar);

  $epsgCode = ($_REQUEST['epsgCode'] != '') ? $_REQUEST['epsgCode'] : '';
  define('GEOMETRY_EPSG_CODE', $epsgCode);

  define('LINESTRING_AS_GEOMETRY', true);

  define('WITH_UUID_OSSP', false);

  define('FILTER_FILE', $rel_path . 'conf/filter_conf.json');
  define('FILTER_INFO', 'Ohne Attribute objektkoordinaten.');

  define('RENAME_OPTIONAL_FIRST', false);
  define('RENAME_ZEIGT_AUF_EXTERNES', true);
  define('COMMENTS', true);
  define('WITH_NRW_KOM', false);

  # Definition of the model conf file
  define('SCHEMA_CONF_FILE', $rel_path . 'conf/model_aaa_conf.php');

  define('WITH_CODE_LISTS', ($_REQUEST['withCodeLists'] == '1'));
?>
