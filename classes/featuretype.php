<?php
class FeatureType {

	function __construct($name, $parent, $logger, $umlSchema) {
		$this->alias = $name;
		$this->name = $this->getName($name);
		$this->comments = array();
		if ($this->name != $this->alias)
			$this->comments[] = 'Alias: "' . $this->alias . '"';
		$this->attributes = array();
		$this->attributes_until_leafs = array();
		$this->associationEnds = array();
		$this->primaryKey = '';
		$this->parent = $parent;
		$this->withOids = true;
		$this->values = new Data();
		$this->umlSchema = $umlSchema;
		$this->logger = $logger;
		$this->stereotype = 'featuretype';
	}

	public static function getName($name) {
		return strtolower(substr($name, 0, PG_MAX_NAME_LENGTH));
	}

	function setId($id) {
		$this->id = $id;
	}

	function addAttribute($attribute) {
		$this->attributes[] = $attribute;
	}

	function setAssociationEnds($class) {
		# lade navigierbare Assoziationsenden von 1:n Assoziationen
		foreach($this->umlSchema->getAssociationEnds($class) AS $end) {
			$associationEnd = new AssociationEnd(
				$end['b_name'],
				$end['a_class_name'],
				$end['b_class_name'],
				$this->logger
			);
			$associationEnd->stereotype = 'FeatureType';
			$associationEnd->setMultiplicity($end['b_multiplicity_range_lower'], $end['b_multiplicity_range_upper']);
			$this->addAssociationEnd($associationEnd);
		}
		$this->logger->log($this->associationsAsTable());
	}

	function getAttributesUntilLeafs($type, $parts) {
		$return_attributes = array();
		$attributes = $this->umlSchema->getClassAttributes($type);
		foreach ($attributes AS $attribute) {
			if (!empty($attribute['attribute_name'])) {
				if (empty($parts)) {
					$parent = $this;
				}
				else {
					$parent = new Datatype($attribute['class_name'], 'datatype', $this->logger);
				}
				$attributeObj = new Attribute(
					$attribute['attribute_name'],
					$attribute['attribute_datatype'],
					$this->logger,
					$parent,
					$parts
				);
				$attributeObj->setStereoType($attribute['attribute_stereotype']);
				$attributeObj->setMultiplicity($attribute['multiplicity_range_lower'], $attribute['multiplicity_range_upper']);
				$new_path = $parts;
				array_push($new_path, $attributeObj);
				if (in_array(strtolower($attribute['attribute_stereotype']), array('datatype', 'union'))) {
					foreach ($this->getAttributesUntilLeafs($attribute['attribute_datatype'], $new_path) AS $child_attribute) {
						$return_attributes[] = $child_attribute;
					}
				}
				else {
					$return_attributes[] = $new_path;
				}
			}
		}
		$this->attributes_until_leafs = $return_attributes;
		return $return_attributes;
	}

	function flattenAttributes() {
		if ($this->parent != null AND !empty($this->parent->attributes)) {
			foreach($this->parent->attributes AS $parent_attribute) {
				$parent_attribute->parts[0]->parent->alias = $this->alias;
				$parent_attribute->setNameFromParts();
				$this->attributes[] = $parent_attribute;
			}
		}
		foreach($this->attributes_until_leafs AS $attribute_parts) {
			$attribute = end($attribute_parts);
			$attribute->parts = $attribute_parts;
			$attribute->setNameFromParts();
			$this->attributes[] = $attribute;
		}
	}

	function getParentsAttributes() {
		if ($this->parent == null)
			return array();
		else
			return array_merge(
				$this->parent->attributes,
				$this->parent->getParentsAttributes()
			);
	}

	function getParentsAssociationEnds() {
		if ($this->parent == null)
			return array();
		else
			return array_merge(
				$this->parent->associationEnds,
				$this->parent->getParentsAssociationEnds()
			);
	}

	function unifyShortNames($level) {
		$multiple_occured = false;
		foreach($this->attributes AS $a) {
			$frequency = 0;
			foreach($this->attributes AS $b) {
				if ($a->short_name == $b->short_name) {
					$frequency++;
				}
			}
			$a->frequency = $frequency;
			if ($frequency > 1) {
				$multiple_occured = true;
			}
		}
		if ($multiple_occured AND $level < 10) {
			foreach($this->attributes AS $a) {
				$n = count($a->parts) - $level - 1;
				if ($a->frequency > 1 AND $n > -1) {
					$this->logger->log('<br>Attribut: ' . $a->short_name);
					$a->short_name = $a->parts[$n]->name . '_' . $a->short_name;
					$this->logger->log(' umbenannt nach: ' . $a->short_name);
				}
			}
			$this->unifyShortNames($level++);
		}
	}

	function unifyShortNamesWithFirst($level) {
		$multiple_occured = false;
		foreach($this->attributes AS $a) {
			$frequency = 0;
			foreach($this->attributes AS $b) {
				if ($a->short_name == $b->short_name) {
					$frequency++;
				}
			}
			$a->frequency = $frequency;
			if ($frequency > 1) {
				$multiple_occured = true;
			}
		}
		if ($multiple_occured AND $level < 10) {
			foreach($this->attributes AS $a) {
				$n = count($a->parts) - $level - 1;
				if ($a->frequency > 1 AND $n > -1) {
					$this->logger->log('<br>Attribut: ' . $a->short_name);
					$this->logger->log('<br>level: ' . $level . ' path' . $a->path_name);
					if ($level == 1) {
						$a->short_name = $a->parts[0]->name . '_' . $a->short_name;
					}
					else {
						$a->short_name = $a->parts[$n]->name . '_' . $a->short_name;
					}
					$this->logger->log(' umbenannt nach: ' . $a->short_name);
				}
			}
			$this->unifyShortNames($level++);
		}
	}

	function getFlattenedName() {
		$n = count($this->attribute_names);
		$return_name = $this->attribute_names[0]->name;
		if ($n > 2) # füge den vorletzen hinzu wenn es mehr als zwei Namesteile sind
			$return_name .= '_' . $this->attribute_names[$n-2]->name;
		if ($n > 1) # füge den letzten hinzu wenn es mehr als einer ist
			$return_name .= '_' . $this->attribute_names[$n-1]->name;
		return $return_name;
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

	function outputFlattenedAttributes() {
		$output = array();
		$html = '';
		if (empty($this->attributes))
			$html .= '<br>keine Attribute';
		else {
			$html .= '<table>
				<th>Pfad</th><th>Name</th><th>Kurzname</th><th>Stereotype</th><th>UML-Datatype</th><th>Databasetype</th><th>Multipliziät</th>';
				$num_attributes = 0;
				foreach ($this->attributes AS $attribute) {
					if ($attribute->short_name != end($attribute->parts)->name) {
					#	if (strpos(strtolower($attribute->path_name), 'zeigtaufexternes') === false) {
							# collect renamed attributes
							$output[$attribute->path_name] = $attribute->short_name;
					#	}
					}
					$html .= '<tr>';
					$html .=  '<td>' . $attribute->path_name . '</td>';
					$html .=  '<td>';
					$html .=  $attribute->attributes_name;
					if (strlen($attribute->attributes_name) > 58)
						$html .=  '(*)';
					$html .=  '</td>';
					$html .=  '<td>';
					$html .=  $attribute->short_name;
					$html .=  '</td>';
					$html .=  '<td>';
					$html .=  $attribute->stereotype;
					$html .=  '</td>';
					$html .=  '<td>';
					$html .=  $attribute->datatype;
					$html .=  '</td>';
					$html .=  '<td>';
					$html .=  $attribute->get_database_type();
					$html .=  '</td>';
					$html .=  '<td>';
					$html .=  $attribute->multiplicity;
					$html .=  '</td>';
					$html .=  '</tr>';
				}
			$html .= '</table>';
		}
		$this->logger->log($html);
		return $output;
	}

	function asSql() {
		$attribute_parts = array();
		$sql = "
CREATE TABLE IF NOT EXISTS " . $this->name . " (
";

		# Ausgabe id
		if ($this->parent == null) {
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
		if ($this->parent != null)
			$sql .= ' INHERITS (' . $this->parent->name . ')';

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

	function asFlattenedSql() {
		$attribute_parts = array();
		$sql = "

CREATE TABLE IF NOT EXISTS " . $this->name . " (
";
		# Ausgabe id
		$attribute_parts[] .= "\t" . $this->primaryKey . ' text';

		# Ausgabe Attribute
		$attribute_parts = array_merge(
			$attribute_parts,
			array_map(
				function($attribute) {
					return $attribute->asFlattenedSql();
				},
				$this->attributes
			)
		);

		if ($this->parent != null) {
			# Ausgabe vererbter Assoziationsenden
			$attribute_parts = array_merge(
				$attribute_parts,
				array_map(
					function($associationsEnd) {
						return $associationsEnd->asSql();
					},
					$this->getParentsAssociationEnds()
				)
			);
		}

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
			$sql .= $attribute->getFlattenedComment($this->name);
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