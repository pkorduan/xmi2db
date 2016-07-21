<?php
class FeatureType {

	function __construct($name, $logger, $umlSchema) {
		$this->alias = $name;
		$this->name = $this->getName($name);
		$this->comments = array();
		if ($this->name != $this->alias)
			$this->comments[] = 'Alias: "' . $this->alias . '"';
		$this->attributes = array();
		$this->associationEnds = array();
		$this->primaryKey = '';
		$this->inherits = '';
		$this->inherits_alias = '';
		$this->withOids = true;
		$this->values = new Data();
		$this->umlSchema = $umlSchema;
		$this->logger = $logger;
	}

	public static function getName($name) {
		return strtolower(substr($name, 0, PG_MAX_NAME_LENGTH));
	}

	function setId($id) {
		$this->id = $id;
	}

	function set_inheritance($inherits) {
		$this->inherits = strtolower(substr($inherits, 0, PG_MAX_NAME_LENGTH));
		$this->inherits_alias = $inherits;
	}

	function addAttribute($attribute) {
		$this->attributes[] = $attribute;
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
			$this->umlSchema->execSql($sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getKeys() {
		return array_map(
			function($attribute) {
				return $attribute['name'];
			},
			$this->attributes
		);
	}

	function attributesAsTable() {
		if (empty($this->attributes)) {
			$html = '<br>Keine Attribute gefunden.';
		}
		else {
			$html = '<table border="1"><tr><th>Attribut</th><th>Attributtyp</th><th>Stereotyp</th><th>Datentyp</th><th>Multiplizität</th><th>Default</th></tr>';
			# für jedes Attribut erzeuge Attributzeilen
			foreach($this->attributes AS $i => $attribute) {
				$html .= '<tr><td>' . $attribute->name . '</td><td>' .
								$attribute->attribute_type . '</td><td>' .
								$attribute->stereotype . '</td><td>' .
								$attribute->datatype . '</td><td>' .
								$attribute->multiplicity . '</td><td>' .
								$attribute->default . '</td></tr>';
				$sql .= '
		';
			}
			$html .= '</table><p>';
		}
		return $html;
	}

	function associationsAsTable() {
		if (empty($this->associationEnds)) {
			$html = '<br>Keine Assoziationen gefunden.';
		}
		else {
			$html = '<table border="1"><tr><th>Attribut</th><th>Stereotyp</th><th>Datentyp</th><th>Multiplizität</th></tr>';
			# für jedes Attribut erzeuge Attributzeilen
			foreach($this->associationEnds AS $i => $associationEnd) {
				$html .= '<tr><td>' . $associationEnd->name . '</td><td>' .
								$associationEnd->stereotype . '</td><td>' .
								$associationEnd->zeigt_auf_alias . '</td><td>' .
								$associationEnd->multiplicity . '</td>' .
				$sql .= '</tr>
		';
			}
			$html .= '</table><p>';
		}
		return $html;
	}

	function addAssociationEnd($associationEnd) {
		$this->associationEnds[] = $associationEnd;
	}

	function addComment($comment) {
		$this->comments[] = $comment;
	}

	function asSql() {
		$attribute_parts = array();
		$sql = "
CREATE TABLE IF NOT EXISTS " . $this->name . " (
";

		# Ausgabe id
		if ($this->inherits == '') {
			$part .= "\t" . $this->primaryKey;
			if (WITH_UUID_OSSP) {
				$part .= " uuid NOT NULL DEFAULT uuid_generate_v1mc(),";
			}
			else {
				$part .= " text";
			}
			$attribute_parts[] = $part;
		}

		# Ausgabe Attribute
		$attribute_parts = array_merge(
			$attribute_parts,
			array_map(
				function($attribute) {
					return $attribute->asSql();
				},
				$this->attributes
			)
		);

		# Ausgabe Assoziationsenden
		$attribute_parts = array_merge(
			$attribute_parts,
			array_map(
				function($associationsEnd) {
					return $associationsEnd->asSql();
				},
				$this->associationEnds
			)
		);

		# Ausgabe Primary Key
		if ($this->primaryKey != '')
			$attribute_parts[] = "CONSTRAINT " . $this->name . '_pkey PRIMARY KEY (' . $this->primaryKey . ')';

		# Zusammenfügen der Attributteile
		$sql .= implode(",\n", $attribute_parts);

		$sql .= '
)';

		# Ausgabe Vererbung
		if ($this->inherits != '')
			$sql .= ' INHERITS (' . $this->inherits . ')';

		# Ausgabe WITH OIDS
		if ($this->withOids)
			$sql .= ' WITH OIDS';

		$sql .= ';
';	# Tabellenende

		# Ausgabe Tabellenkommentare
		if (!empty($this->comments)) {
			$sql .= "\nCOMMENT ON TABLE " . $this->name . " IS '" .
				implode(', ', $this->comments) . "';";
		}

		# Ausgabe Attributkommentare
		foreach($this->attributes AS $attribute) {
			$sql .= $attribute->getComment($this->name);
		}

		# Ausgabe Assoziationskommentare
		foreach($this->associationEnds AS $associationEnd) {
			$sql .= $associationEnd->getComment($this->name);
		}

		# Ausgabe Tabellen Values
		if (!empty($this->values->rows)) {
			$sql .= "\nINSERT INTO " . $this->name . ' (' .
				implode(
					',',
					array_map(
						function($attribute) {
							return $attribute->name;
						},
						$this->attributes
					)
				) .
			") VALUES \n";
			$sql .= $this->values->asSql();
			$sql .= ';';
		}

		return $sql;
	}

}
?>