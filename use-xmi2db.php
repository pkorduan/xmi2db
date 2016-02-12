<!DOCTYPE html>
<html lang="de">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  </head>
  <body><?php

include( dirname(__FILE__) . "/class.xmi2db.php");
include( dirname(__FILE__) . "/conf/database_conf.php");

// Call the class with the root element of the xmi data
$xmi2db = new xmi2db("XMI.content");
if (isset($_REQUEST['file']))
  $xmi2db->setXMIFile($_REQUEST['file']);
else
  $xmi2db->setXMIFile("XPlanGML-xmi12-uml14.xml");

if (isset($_REQUEST['schema']))
  $xmi2db->setSchema($_REQUEST['schema']);
else
  $xmi2db->setSchema("xplan_eatest");

if (isset($_REQUEST['basepackage']))
  $xmi2db->setBasePackage($_REQUEST['basepackage']);
else
  $xmi2db->setBasePackage("XPlanGML 4.1");

// Set (path to) XMI file
//$xmi2db->setXMIFile("xplan_short.xml");
//$xmi2db->setXMIFile("XPlanGML-xmi12-uml14.xml");

// Set db schema
//$xmi2db->setSchema("xplan_eatest");

// Set base package
// Only if you want to iterate through one package, if you want to iterate through all packages, uncomment!
//$xmi2db->setBasePackage("XPlanGML 4.1");

//$xmi2db->setSchema("xplan_argotest");
//$xmi2db->setBasePackage("Raumordnungsplan_Kernmodell");
//$xmi2db->setXMIFile("xplanerweitert20150609.xmi");

//Set DB connection
//$xmi2db->setConn("dbname=moro user=kvwmap password=kv_Map-12:)");
$xmi2db->setConn($db_conn);

// The table name for the insert queries
$xmi2db->setTable(array(
  "classes" => "uml_classes",
  "attributes" => "uml_attributes",
  "association_classes" => "association_classes",
  "class_generalizations" => "class_generalizations",
  "packages" => "packages",
  "stereotypes" => "stereotypes"
));

//Create Schema and Table structure if needed
$schema_sql = "
  SELECT EXISTS(
    SELECT
      schema_name
    FROM
      information_schema.schemata
    WHERE
      schema_name = '" . $_REQUEST['schema'] . "'
  )
";
#echo $schema_sql;
//echo "<br>";
$result = pg_query($db_conn, $schema_sql);
$schemaBool = pg_fetch_row($result);
if ($schemaBool[0]=='t') {
  echo "Schema vorhanden<br>";
  //Truncate if wanted
  if ($_REQUEST['truncate'] == 1) $xmi2db->truncateTables();
  $migration_files = scandir('sql');
  foreach ($migration_files as $migration_file) {
    if (strpos($migration_file, '_mig')) {
      echo 'mig file '.$migration_file.' found<br>';
      //Load SQL migration file and replace "schema_name" placeholder with desired schema name
      $result = pg_query($db_conn, str_replace('schema_name', $_REQUEST['schema'], file_get_contents('sql/'.$migration_file)));
	  if ($result) echo 'mig file '.$migration_file.' loaded<br>';
	  else echo 'mig file '.$migration_file.' NOT loaded<br>';
    }
  }
}
else {
  echo "Schema NICHT vorhanden<br>";
  //Load SQL dump file and replace "schema_name" placeholder with desired schema name
  $sql_dump = file_get_contents('sql/db-schema.sql');
  //echo $sql_dump;
  $sql_dump2 = str_replace('schema_name', $_REQUEST['schema'], $sql_dump);
  //echo $sql_dump2;
  pg_query($db_conn, $sql_dump2);
  //Check for additional migration files (e.g. 20150731_mig.sql)
  $migration_files = scandir('sql');
  foreach ($migration_files as $migration_file) {
    if (strpos($migration_file, '_mig')) {
      echo 'mig file '.$migration_file.' found<br>';
      //Load SQL migration file and replace "schema_name" placeholder with desired schema name
      $result = pg_query($db_conn, str_replace('schema_name', $_REQUEST['schema'], file_get_contents('sql/'.$migration_file)));
	  if ($result) echo 'mig file '.$migration_file.' loaded<br>';
	  else echo 'mig file '.$migration_file.' NOT loaded<br>';
    }
  }
}
//if Argo Export using ISO19136 Schema, fill stereotypes table
if ($_REQUEST['argo'] == 1) {
  $xmi2db->fillArgoStereotypes();
  $xmi2db->fillArgoDatatypes();
}

//perfom the queries
echo "<pre>";
print_r($xmi2db->getQueries());
echo "</pre>";
?></body>
</html>
