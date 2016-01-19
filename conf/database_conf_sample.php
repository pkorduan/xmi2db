<?php
  $db_server = "localhost"; // Hostname
  $db_user = "postges"; // Benutzername
  $db_pass = "postgres"; // Kennwort
  $db_name = "xplanung"; // Name der Datenbank
  $db_conn  = pg_connect("host=".$db_server." dbname=".$db_name." user=".$db_user." password=".$db_pass) or exit ("Es konnte keine Verbindung zum Datenbankserver hergestellt werden.");
  define('UML_SCHEMA', 'xplan_model');
  define('CLASSES_SCHEMA', 'gml_classes');
  define('DEBUG', false);
?>