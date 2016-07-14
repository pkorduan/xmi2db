<?php
class DatabaseObject {

	function DatabaseObject($name, $logger) {
		$this->name = $name;
		$this->logger = $logger;
	}

	function openConnection(
		$host = 'localhost',
		$dbname = 'postgres',
		$user = 'postgres',
		$password = 'postgres'
	) {
		$this->dbConn = pg_connect(
			 "host=" . $host .
			" dbname=" . $dbname .
			" user=" . $user .
			" password=" . $password
		) or exit (
			 "Es konnte keine Verbindung zum Datenbankserver hergestellt werden."
		 );
		return $this->dbConn;
	}

}
?>