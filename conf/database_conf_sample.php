<?php
  $db_server = "localhost"; // Hostname
  $db_user = "postgres"; // Benutzername
  $db_pass = "postgres"; // Kennwort
  $db_name = "osm_routing"; // Name der Datenbank
  $db_conn  = pg_connect("host=".$db_server." dbname=".$db_name." user=".$db_user." password=".$db_pass) or exit ("Es konnte keine Verbindung zum Datenbankserver hergestellt werden."); // Verbindung zum Datenbankserver
?>