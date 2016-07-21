<?php
class DataType {

	function DataType($name, $stereotype, $logger) {
		$this->name = strtolower(substr($name, 0, PG_MAX_NAME_LENGTH));
		$this->alias = $name;
		$this->stereotype = strtolower(substr($stereotype, 0, PG_MAX_NAME_LENGTH));
		$this->stereotype_alias = $stereotype;
		$this->comments = array();
		if ($this->name != $this->alias)
			$this->comments[] = 'Alias: "' . $this->alias . '"';
		$this->attributes = array();
		$this->inherits = '';
		$this->id = 0;
		$this->logger = $logger;
	}

	function setUmlSchema($schema) {
		$this->umlSchema = $schema;
	}

	function setId($id) {
		$this->id = $id;
	}

	function getAttributes() {
		$sql = "
SELECT
	a.name AS name,
	CASE
		WHEN d.name IS NULL THEN cc.name
		ELSE d.name
	END AS datatype, 
	CASE
		WHEN d.name IS NULL THEN cs.name
		ELSE ds.name
	END AS stereotype,
	CASE
		WHEN d.name IS NULL THEN CASE
			WHEN cs.name IS NULL THEN NULL
			ELSE 'UML-Classifier'
		END
		ELSE 'UML-DataType'
	END AS attribute_type,
	a.multiplicity_range_lower::integer,
	a.multiplicity_range_upper,
	a.initialvalue_body
FROM
	" . $this->umlSchema->schemaName . ".uml_classes c JOIN 
	" . $this->umlSchema->schemaName . ".uml_attributes a ON c.id = a.uml_class_id LEFT JOIN
	" . $this->umlSchema->schemaName . ".datatypes d ON a.datatype = d.xmi_id LEFT JOIN
	" . $this->umlSchema->schemaName . ".uml_classes dc ON d.name = dc.name LEFT JOIN
	" . $this->umlSchema->schemaName . ".stereotypes ds ON dc.stereotype_id = ds.xmi_id Left JOIN
	" . $this->umlSchema->schemaName . ".uml_classes cc ON a.classifier = cc.xmi_id LEFT JOIN
	" . $this->umlSchema->schemaName . ".stereotypes cs ON cc.stereotype_id = cs.xmi_id
WHERE
	uml_class_id = " . $this->id . "
";
		$this->logger->log('<br><b>Get Attributes: </b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');

		$result = pg_fetch_all(
			pg_query($this->umlSchema->dbConn, $sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function addAttribute($attribute) {
		$this->attributes[] = $attribute;
	}

	function addComment($comment) {
		$this->comments[] = $comment;
	}

	function asSql() {
		$sql = "
DO $$
BEGIN
IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = '" . $this->name . "') THEN
CREATE TYPE " . $this->name . " AS (
" . implode(",\n", array_map(
			function($attribute) {
				return $attribute->asSQL();
			},
			$this->attributes
		)) . "
);
END IF;
END$$;";

		# Ausgabe der Kommentare
		if (!empty($comments)) {
			$sql .= "\nCOMMENT ON DATATYPE " . $this->name . " IS '" .
				implode(', ', $this->comments) . "';";
		}
		return $sql;
	}
}
?>