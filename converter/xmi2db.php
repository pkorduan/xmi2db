<?php
// +----------------------------------------------------------------------+
// | xmi2db                                                               |
// | Creating SQL Queries from an xmi file                                |
// | Requirements: PHP5 with SimpleXMI Support                            |
// +----------------------------------------------------------------------+
// | Author: Christian Seip <christian.seip@gdi-service.de>               |
// +----------------------------------------------------------------------+
// based on http://www.phpclasses.org/package/2272-PHP-Generate-SQL-queries-to-import-data-from-XML-files.html

//Begin Start Pascoul
require_once('../lib/pascoul/pascoul.php');
include('../classes/xmi2db.php');
include('../classes/schema.php');
include('../classes/logger.php');

if( php_sapi_name() == 'cli' ) {
  foreach($argv as $argument) {
    if($argument==$argv[0])
      continue;

    $i = strpos($argument, '=');
    $variableName = substr($argument, 0, $i);
    $variableValue = substr($argument, $i+1);

    echo "$variableName = $variableValue\n";
    $_REQUEST[$variableName] = $variableValue;
  }
}

include('../conf/database_conf.php');

$xmi2db = new xmi2db(UML_SCHEMA);

$xmi2db->openConnection(PG_HOST, PG_DBNAME, PG_USER, PG_PASSWORD, PG_PORT);

$xmi2db->start();

$xmi2db->addExternalUmlClasses();

?>

