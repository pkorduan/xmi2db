<?php
class EnumType {

	function EnumType($name, $logger) {
		$this->alias = $name;
		$this->name = strtolower(substr($name, 0, 58));
		if ($this->name != $this->alias)
			$this->comments[] = 'Alias: "' . $this->alias . '"';
		$this->values = new Values();
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

	function getValues() {
		$sql = "
SELECT
		CASE
			WHEN a.initialvalue_body IS NULL OR a.initialvalue_body = '' THEN a.name
			ELSE a.initialvalue_body
		END AS key,
		a.name AS value
FROM
	" . $this->umlSchema->schemaName . ".uml_classes c JOIN 
	" . $this->umlSchema->schemaName . ".uml_attributes a ON c.id = a.uml_class_id
WHERE
	uml_class_id = " . $this->id . "
";
		$this->logger->log(' <b>Get Enum Values: </b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');

		$query = pg_query($this->umlSchema->dbConn, $sql);
		while($rs = pg_fetch_assoc($query)) {
			$this->values->addValue($rs);
		}
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
CREATE TYPE " . $this->name . " AS ENUM 
" . $this->values->asSql() . ";
END IF;
END$$;
";
		return $sql;
	}
}
?>