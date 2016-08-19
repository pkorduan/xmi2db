<?php
class EnumType {

	function EnumType($name, $logger) {
		$this->alias = $name;
		$this->name = strtolower(substr($name, 0, 58));
		if ($this->name != $this->alias)
			$this->comments[] = 'Alias: "' . $this->alias . '"';
		$this->values = new Data();
		$this->id = 0;
		$this->logger = $logger;
	}

	function setSchemas($umlSchema, $gmlSchema) {
		$this->umlSchema = $umlSchema;
		$this->gmlSchema = $gmlSchema;
	}

	function setId($id) {
		$this->id = $id;
	}

	function getWerte() {
		return array_map(
			function ($value) {
				$text = (ctype_digit($value[0])) ? $value[0] : "'" . $value[0] . "'";
				return $text;
			},
			$this->values->rows
		);
	}

	function getValues() {
		$sql = "
SELECT
		a.initialvalue_body,
		a.name
FROM
	" . $this->umlSchema->schemaName . ".uml_classes c JOIN 
	" . $this->umlSchema->schemaName . ".uml_attributes a ON c.id = a.uml_class_id
WHERE
	uml_class_id = " . $this->id . "
";
		$this->logger->log('<br><b>Get Enum Values: </b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');

		$query = pg_query($this->umlSchema->dbConn, $sql);
		while($row = pg_fetch_assoc($query)) {
			if ($row['initialvalue_body'] == '') {
				$parts = explode('=', $row['name']);
				if (trim($parts[1]) == '' )
					$wert = $parts[0];
				else
					$wert = $parts[1];
			}
			else {
				$wert = str_replace(array('`', '´', '+'), '', $row['initialvalue_body']);
			}
			if ($wert == trim($row['name']))
				$row['name'] = 'NULL';
			$this->values->addRow(array(
				$wert,
				trim($row['name'])
			));
		}
		return $this->values;
	}

	function asSql() {
		$sql = "
DO $$
BEGIN
IF NOT EXISTS (
	SELECT
		1
	FROM
		pg_type t JOIN
		pg_namespace ns ON (t.typnamespace = ns.oid)
	WHERE
		t.typname = '" . $this->name . "'
		AND ns.nspname = '" . $this->gmlSchema->schemaName . "'
) THEN
CREATE TYPE " . $this->gmlSchema->schemaName . "." . $this->name . " AS ENUM 
	(" . implode(', ', $this->getWerte()) . ");
END IF;
END$$;
";
		return $sql;
	}
}
?>