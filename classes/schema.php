<?php
class Schema {

	function Schema($name, $logger) {
		$this->schemaName = $name;
		$this->logger = $logger;
		$this->unionTypes = array();
		$this->dataTypes = array();
		$this->enumerations = array();
		$this->codeLists = array();
		$this->featureTypes = array();
		$this->attributes = array();
		$this->renameList = array();
		$this->unions = array();
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

	function execSQL($sql) {
		return pg_query($this->dbConn, $sql);
	}

	function asSql() {
		$sql  = 'DROP SCHEMA IF EXISTS ' . $this->schemaName . " CASCADE;\n";
		$sql .= 'CREATE SCHEMA ' . $this->schemaName . ";\n";
		$sql .= 'SET search_path = ' . $this->schemaName . ", public;\n";
		if (WITH_UUID_OSSP) {
			$sql .= 'CREATE EXTENSION IF NOT EXISTS "uuid-ossp"' . ";\n";
		}
		return $sql;
	}

	/**
	* Lade alle Generalisierungen, die selber nicht von anderen abgeleitet sind
	**/
	function getTopUmlClasses($stereotype) {
		$sql = "
SELECT
	c.id,
	c.xmi_id,
	c.name
FROM
	" . $this->schemaName . ".packages p LEFT JOIN
	" . $this->schemaName . ".uml_classes c ON p.id = c.package_id LEFT JOIN
	" . $this->schemaName . ".stereotypes s ON c.stereotype_id = s.xmi_id
WHERE
	general_id = '-1' AND
	lower(s.name) LIKE '" . strtolower($stereotype) . "' AND
	p.name IN (" . PACKAGES . ")
";
		$this->logger->log(' <b>Get Top ' . $stereotype . 's: </b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');

	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
		$result = pg_fetch_all(
			pg_query($this->dbConn, $sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getDataType($name) {
		$sql = "
SELECT
	*
FROM
	". $this->schemaName . ".datatypes
WHERE
	lower(name) = '" . strtolower($name) . "';
";
		$this->logger->log(' <b>Get DataType</b>');
		$this->logger->log('<pre>' . $sql . '</pre>');
		$result = pg_fetch_all(
			pg_query($this->dbConn, $sql)
		);
		if ($result == false) $result = array();
		$this->logger->log('DatenTyp mit Namen: <b>' . $name . '</b> gefunden.');
		$this->logger->log('<br>');
		return $result[0];
	}


	function getClassesFromInformationSchema() {
		#http://stackoverflow.com/questions/15644152/list-tables-in-a-postgresql-schema
		$sql = "
			SELECT
				*
			FROM
				information_schema.tables 
			WHERE
				table_schema = '" . CLASSES_SCHEMA . "'
			ORDER BY
				table_name
		";

		#output('<b>Get Classes: </b><br>');
		#output('<pre>' . $sql . '</pre>');
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
		$result = pg_fetch_all(
			pg_query($this->dbConn, utf8_encode($sql))
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getClass($name) {
		$sql = "
SELECT
	c.*,
	s.name AS type_name
FROM
	". $this->schemaName . ".uml_classes c LEFT JOIN
	". $this->schemaName . ".stereotypes s ON c.stereotype_id = s.xmi_id
WHERE
	lower(c.name) = '" . strtolower($name) . "';
";
		$this->logger->log(' Get Class for DataType <b>' . $name . '</b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');

		$result = pg_fetch_all(
			pg_query($this->dbConn, $sql)
		);
		if ($result == false)
			$result = array();
		if (count($result) > 1)
			echo 'Fehler: Für Klasse ' . $name . ' gibt es mehr als ein Eintrag in der Tabelle uml_classes.';
		return $result;
	}

	function getSubUmlClasses($stereotype, $class) {
		$sql = "
SELECT
	c.id,
	c.xmi_id,
	c.name
FROM
	" . $this->schemaName . ".class_generalizations g LEFT JOIN
	" . $this->schemaName . ".uml_classes p ON g.parent_id = p.xmi_id JOIN
	" . $this->schemaName . ".uml_classes c ON g.child_id = c.xmi_id LEFT JOIN
	" . $this->schemaName . ".packages pa ON c.package_id = pa.id
WHERE
	p.xmi_id = '" . $class['xmi_id'] . "' AND
	pa.name IN (" . PACKAGES . ")";
	
#	if ($this->logger->debug) {
#		$sql .= "
#			AND c.name in ('AA_Objekt', 'AA_NREO', 'AA_Benutzer', 'AX_Benutzer');
#		";
#	}
		$this->logger->log(' <br><b>Get SubClasses</b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');
		$result = pg_fetch_all(
			pg_query($this->dbConn, $sql)
		);
		if ($result == false) $result = array();
		if (empty($result))
			$this->logger->log('<br>keine');
		foreach($result AS $row) {
			$this->logger->log('<br>' . $row['name']);
		}
		$this->logger->log('<br>');
		return $result;
	}
	
	function getEnumerations() {
		$sql = "
SELECT
	c.id,
	c.xmi_id,
	c.name
FROM
	" . $this->schemaName . ".packages p LEFT JOIN
	" . $this->schemaName . ".uml_classes c ON p.id = c.package_id LEFT JOIN
	" . $this->schemaName . ".stereotypes s ON c.stereotype_id = s.xmi_id
WHERE
	lower(s.name) = 'enumeration' AND
	p.name IN (" . PACKAGES . ")
";
		$this->logger->log('<br><b>Get Enumerations</b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');
		$result = pg_fetch_all(
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
			$this->execSql($sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getCodeLists() {
		$sql = "
SELECT
	c.id,
	c.name,
	c.xmi_id
FROM
	" . $this->schemaName . ".packages p LEFT JOIN
	" . $this->schemaName . ".uml_classes c ON p.id = c.package_id LEFT JOIN
	" . $this->schemaName . ".stereotypes s ON c.stereotype_id = s.xmi_id
WHERE
	s.name LIKE '%odeList' AND
	p.name IN (" . PACKAGES . ")
";
		$this->logger->log('<b>Get CodeList</b>');
		$this->logger->log('<pre>' . $sql . '</pre>');
		$result = pg_fetch_all(
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
			$this->execSql($sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getClassComment($class) {
		#http://stackoverflow.com/questions/5664094/getting-list-of-table-comments-in-postgresql
		$sql = "
			SELECT
				obj_description('" . $this->schemaName . "." . $class . "'::regclass)
		";

		$result = pg_fetch_all(
			pg_query($this->dbConn, utf8_encode($sql))
		);
		if ($result == false) $result = array();
		return $result;
  }

	function getAttributes($class) {
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
	" . $this->schemaName . ".uml_classes c JOIN 
	" . $this->schemaName . ".uml_attributes a ON c.id = a.uml_class_id LEFT JOIN
	" . $this->schemaName . ".datatypes d ON a.datatype = d.xmi_id LEFT JOIN
	" . $this->schemaName . ".uml_classes dc ON d.name = dc.name LEFT JOIN
	" . $this->schemaName . ".stereotypes ds ON dc.stereotype_id = ds.xmi_id Left JOIN
	" . $this->schemaName . ".uml_classes cc ON a.classifier = cc.xmi_id LEFT JOIN
	" . $this->schemaName . ".stereotypes cs ON cc.stereotype_id = cs.xmi_id
WHERE
	uml_class_id = " . $class['id'] . "
";
		$this->logger->log(' <b>Get Attributes: </b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');

		$result = pg_fetch_all(
			$this->execSql($sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getAttributesWithDatatype($class) {
		#http://stackoverflow.com/questions/13723301/postgresql-select-one-of-two-fields-depending-on-which-is-empty
		$sql = "
			SELECT
				a.name,
				a.id,
				(CASE WHEN (d1.name IS NULL OR d1.name = '') THEN d2.name ELSE d1.name END) AS datatype,
				s.name AS classifier_stereotype,
				a.multiplicity_range_lower::integer,
				a.multiplicity_range_upper,
				a.initialvalue_body
			FROM
				" . $this->schemaName . ".uml_attributes a LEFT JOIN
				" . $this->schemaName . ".datatypes as d1 ON a.datatype = d1.xmi_id LEFT JOIN
				" . $this->schemaName . ".datatypes as d2 ON a.classifier = d2.xmi_id LEFT JOIN
				" . $this->schemaName . ".uml_classes c ON a.classifier = c.xmi_id LEFT JOIN
				" . $this->schemaName . ".stereotypes s ON c.stereotype_id = s.xmi_id
			WHERE
				uml_class_id = " . $class . "
		";

		$result = pg_fetch_all(
			pg_query($this->dbConn, $sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getAttributeInfo($attribute) {
		$sql = "
			SELECT
				*
			FROM
				" . UML_SCHEMA . ".classes_attributes_types_gen
			WHERE
				attribute_id = " . $attribute['id'] . "
		";
		$result = pg_fetch_all(
			pg_query($this->dbConn, $sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getClassAttributes($class_name) {
		$sql = "
			SET search_path = " . $this->schemaName . ", public;
			SELECT
				c.class_name,
				c.attribute_name,
				c.attribute_datatype,
				lower(c.attribute_stereotype) attribute_stereotype,
				c.multiplicity_range_lower,
				c.multiplicity_range_upper
			FROM
				" . $this->schemaName . ".classes_with_attributes c
			WHERE
				c.class_name like '" . $class_name . "'
			ORDER by
				c.class_name,
				c.attribute_name
		";

		$this->logger->log('<br><b>Get Attributes for class: ' . $class_name . '</b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');

		$result = pg_fetch_all(
			pg_query($this->dbConn, $sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	# Lade AssociationEnds for classes
	function getAssociationEnds($class) {
		$sql = "
SELECT
	ca.name a_class_name,
	b.id b_id,
	CASE WHEN b.name = '<undefined>' AND NOT b.\"isNavigable\" THEN 'inversZu_' || a.name || '_' || cb.name ELSE b.name END AS b_name,
	-- b.name b_name,
	b.multiplicity_range_lower b_multiplicity_range_lower,
	b.multiplicity_range_upper b_multiplicity_range_upper,
	a.id a_id,
	a.name a_name,
	a.multiplicity_range_lower a_multiplicity_range_lower,
	a.multiplicity_range_upper a_multiplicity_range_upper,
	cb.name b_class_name
FROM
	" . $this->schemaName . ".uml_classes ca JOIN
	" . $this->schemaName . ".association_ends a ON (ca.xmi_id = a.participant) JOIN
	" . $this->schemaName . ".association_ends b ON (a.assoc_id = b.assoc_id) JOIN
	" . $this->schemaName . ".uml_classes cb ON (cb.xmi_id = b.participant)
WHERE
	a.id != b.id
	AND ca.name = '" . $class['name'] . "'
	-- AND b.\"isNavigable\"
		";
		$this->logger->log(' <br><b>Get 1:n Association Ends for Class: ' . $class['name'] . '</b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');
		$result = pg_fetch_all(
			$this->execSql($sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function getAssociations() {
		$sql = "
			SELECT
				c.assoc_id,
				ca.name a_class,
				b.name a_rel,
				CASE WHEN b.multiplicity_range_upper = '-1'
					THEN 'n'
					ELSE b.multiplicity_range_upper
				END a_num,
				b.\"isNavigable\",
				cb.name b_class,
				a.name b_rel,
				CASE WHEN a.multiplicity_range_upper = '-1'
					THEN 'n'
					ELSE a.multiplicity_range_upper
				END b_num,
				a.\"isNavigable\"
			FROM
				(
					SELECT
						assoc_id,
						min(id) AS a_id,
						max(id) AS b_id
					FROM
						" . $this->schemaName .".association_ends ae
					GROUP BY
						assoc_id
					ORDER BY
						a_id
				) c JOIN
				" . $this->schemaName . ".association_ends a ON a.id = c.a_id JOIN
				" . $this->schemaName . ".association_ends b ON b.id = c.b_id JOIN
				" . $this->schemaName . ".uml_classes ca ON a.participant = ca.xmi_id JOIN
				" . $this->schemaName . ".uml_classes cb ON b.participant = cb.xmi_id JOIN
				" . $this->schemaName . ".packages pa ON ca.package_id = pa.id JOIN
				" . $this->schemaName . ".packages pb ON cb.package_id = pb.id
			WHERE
				pa.name IN (" . PACKAGES . ") AND
				pb.name IN (" . PACKAGES . ")
		";
		$this->logger->log(' <b>Get Associations: </b>');
		$this->logger->log(' <textarea cols="5" rows="1">' . $sql . '</textarea>');
		$result = pg_fetch_all(
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
			$this->execSql($sql)
		);
		if ($result == false) $result = array();
		return $result;
	}

	function createAttributeType($datatype, $stereotype, $multiplicity) {
		#$this->logger->log('<br>createAttributeType with datatype: ' . $datatype . ' and stereotype: ' . $stereotype);
		$sql = '';

		if (in_array($stereotype, array(
			'datatype',
			'codelist',
			'enumeration',
			'union'
		))) {
			$sql = $datatype;
		}
		else {
			switch (true) {
				# text
				case in_array($datatype, array(
						''
					)) :
					$sql = 'text';
				break;

				# character varying
				case in_array($datatype, array(
						'characterstring',
						'<undefined>',
						'enumeration',
						'enum',
						'uri'
					)) :
					$sql = 'character varying';
				break;

				# date
				case in_array($datatype, array(
						'date',
						'datetime',
						'tm_duration'
					)) :
					$sql = 'date';
				break;

				# integer
				case in_array($datatype, array(
						'integer',
						'int',
						'codelist'
					)):
					$sql = 'integer';
				break;

				# boolean
				case ($datatype == 'boolean'):
					$sql = 'boolean';
				break;

				# double precision
				case in_array($datatype, array(
						'angle',
						'length',
						'decimal',
						'volume',
						'area',
						'real',
						'distance'
					)):
					$sql = 'double precision';
				break;

				# uuid
				case in_array($datatype, array(
						'datatype'
					)):
					$sql = 'uuid';
				break;

				# geometry
				case in_array($datatype, array(
						'gm_point',
						'directposition'
					)):
					$sql = 'geometry(POINT)';
				break;

				case ($datatype == 'gm_curve'):
					$sql = 'geometry(LINESTRING)';
				break;

				case ($datatype == 'gm_multicurve'):
					$sql = 'geometry(MULTILINESTRING)';
				break;

				case ($datatype == 'gm_multipoint'):
					$sql = 'geometry(MULTIPOINT)';
				break;

				case ($datatype == 'gm_multisurface'):
					$sql = 'geometry(MULTIPOLYGON)';
				break;

				case ($datatype == 'gm_surface'):
					$sql = 'geometry(POLYGON)';
				break;

				case in_array($datatype, array(
						'gm_object',
						'union'
					)):
					$sql = 'geometry';
				break;
			} # end of switch
		}

		if ($sql == '')
			$sql = 'text';

		if ($multiplicity == '-1' OR $multiplicity == '*' OR intval($multiplicity) > 1) {
			$sql .= '[]';
		}
		return $sql;
	}

	function complexDataTypeExists($datatype) {
		global $db_conn;

		$sql = "
			SELECT exists (
				SELECT
					1
				FROM
					pg_type
				WHERE
					typname = '" . $datatype . "'
			)
		";
		$result = pg_fetch_array(
			pg_query($db_conn, $sql)
		);
		$typeExists = ($result[0] == 't');
		if (!$typeExists)
			$this->logger->log('<br>Komplexer Type <b>' . $datatype . '</b> existiert noch nicht.');
		return $typeExists;
	}

	function createAttributes($attributes) {
		$this->outputAttributeHtml($attributes);
		$sql = '';
		foreach($attributes AS $attribute) {
			if ($sql != '') {
				$sql .= ',
	';
			}
			$sql .= $this->createAttributeDefinition($attribute);
		}
		return $sql;
	}

	function createAttributeDefinition($attribute) {
		$sql = strtolower($attribute['name']);
		
		$sql .= ' ' . $this->createAttributeType(
			strtolower($attribute['datatype']),
			strtolower($attribute['stereotype']),
			$attribute['multiplicity_range_upper']
		);
		if ($attribute['multiplicity_range_lower'] > '0')
			$sql .= ' NOT NULL';
		if ($attribute['initialvalue_body'] != '')
			$sql .= " DEFAULT '" . trim(str_replace('{frozen}', '', $attribute['initialvalue_body'])) . "'";
		return $sql;
	}

	function createAttributeComment($class, $attribute) {
		$sql = "
COMMENT ON COLUMN " . strtolower($class['name']) . "." . strtolower($attribute['name']) . " IS '";
		if (!empty($attribute['attribute_type']))
			$sql .= $attribute['attribute_type'] . ': ' . $attribute['datatype'];
		if (!empty($attribute['stereotype']))
			$sql .= ' Stereotyp: ' . $attribute['stereotype'];
		$sql .= ' ' . $this->createMultiplicityText(
			$attribute['multiplicity_range_lower'],
			$attribute['multiplicity_range_upper']
		);
		$sql .= "';";
		return $sql;
	}

	function createMultiplicityText($lower, $upper) {
		$lower =	($lower == '-1' OR intval($lower) > 1) ? '*' : $lower;
		$upper =	($upper == '-1' OR intval($upper) > 1) ? '*' : $upper;
		if ($lower == $upper)
			$text = $lower;
		else {
			if (empty($lower))
				$lower = '0';
			$text = '[' . $lower . '..' . $upper . ']';
		}
		return $text;
	}

	function createComplexDataTypes($stereotype, $class, $dbSchema, $attributPath = '') {
		$this->logger->log('<br><b>Create ' . $stereotype . ' ' . $class['name'] . '</b> (' . $class['xmi_id'] . ') id: ' . $class['id']);
		$sql = '';
		$dataType = new DataType($class['name'], $stereotype, $this->logger);
		if (!$this->stereoTypeAllreadyExists($dataType->name, $dataType->stereotype)) {
			# union oder datentyp existiert noch nicht, jetzt erzeugen
			$dataType->setSchemas($this, $dbSchema);
			$dataType->setId($class['id']);
			$attributes = $dataType->getAttributes();
			$this->logger->log('<ul>');
			foreach($attributes AS $attribute) {
				$this->logger->log('<li>');
				if ($attributPath != '')
					$pathPart = $attributPath . '|' . $class['name'] . '|' . $attribute['name'];
				else
					$pathPart = $class['name'] . '|' . $attribute['name'];
				# erzeuge Attributdefinition
				$dataTypeAttribute = new Attribute(
					$attribute['name'],
					$attribute['datatype'],
					$dataType,
					$pathPart
				);
				$dataTypeAttribute->setStereoType($attribute['stereotype']);
				$dataTypeAttribute->attribute_type = $attribute['attribute_type'];
				$dataTypeAttribute->setMultiplicity($attribute['multiplicity_range_lower'], $attribute['multiplicity_range_upper']);
				$this->logger->log('<b>' . $attribute['name'] . '</b> datatype: <b>' . $dataTypeAttribute->datatype .'</b> stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>');
				$dataType->addAttribute($dataTypeAttribute);
				$this->attributes[] = $dataTypeAttribute;

				# Falls Stereotype des Attributes ein union oder datatype ist und in UML-Schema existiert erzeuge den Typ
				if (in_array($dataTypeAttribute->stereotype,	array('union', 'datatype'))) {
					# Prüfe ob der DataType über haupt einer ist
					$dataTypeClass = $this->getClass($dataTypeAttribute->datatype_alias);
					if (!empty($dataTypeClass)) {
						# erzeuge diesen typ und hänge in an die Liste der erzeugten Datentypen an.
						$sql .= $this->createComplexDataTypes($dataTypeAttribute->stereotype, $dataTypeClass[0], $dbSchema, $pathPart);
					}
				}

				$comment = '';
				if (!empty($attribute['attribute_type']))
					$comment .= $attribute['attribute_type'] . ': ' . $attribute['datatype'];
				if (!empty($attribute['stereotype']))
					$comment .= ' Stereotyp: ' . $attribute['stereotype'];
				$comment .= ' ' . $this->createMultiplicityText(
					$attribute['multiplicity_range_lower'],
					$attribute['multiplicity_range_upper']
				);
				$dataType->addComment($comment);
				$this->logger->log('</li>');
			}
			$this->logger->log('</ul>');
			$sql .= $dataType->asSql();

			if ($stereotype == 'Union')
				$this->unionTypes[$dataType->name] = $dataType;
			if ($stereotype == 'DataType')
				$this->dataTypes[$dataType->name] = $dataType;

			# lade abgeleitete Klassen
			$subClasses = $this->getSubUmlClasses($stereotype, $class);

			# Für alle abgeleiteten Klassen
			foreach($subClasses as $subClass) {
				$sql .= $this->createComplexDataTypes($stereotype, $subClass, $dbSchema);
			}
			$this->logger->log('<br><pre>' . $sql . '</pre>');

			return $sql;
		}
	}

	#
	# return true if the datatype of $attribute
	# allready exists in the array of $existingTypes or
	# the datatype contain the text 'geometry('
	#
	function stereoTypeAllreadyExists($datatype, $stereotype) {
		#	array_map(function($type) { echo ', ' . $type->name; }, $this->dataTypes);
		$this->logger->log('<br>Datatype: ' . $datatype . ' Stereotype: ' . $stereotype);
		$typeList = array();
		if ($stereotype == 'union')
			$typeList = $this->unionTypes;
		if ($stereotype == 'datatype')
			$typeList = $this->dataTypes;

		return (
			array_key_exists($datatype, $typeList) OR
			(strpos($datatype, 'geometry(') !== false)
		) ? true : false;
	}

	function createFeatureTypeTables($stereotype, $parent, $class, $attributPath ='') {
		$this->logger->log('<br><b>Create ' . $stereotype . ': ' . $class['name'] .' </b>');
		# Erzeuge FeatueType
		$featureType = new FeatureType($class['name'], $parent, $this->logger, $this);
		$featureType->setId($class['id']);
		if ($parent == null) {
			$featureType->primaryKey = 'gml_id';
		}
		else {
			$this->logger->log(' abgeleitet von: <b>' . $parent->alias . '</b>');
		}

		foreach($featureType->getAttributes() AS $attribute) {
			if ($attributePath != '')
				$pathPart = $attributPath . '|' . $class['name'] . '|' . $attribute['name'];
			else
				$pathPart = $class['name'] . '|' . $attribute['name'];
			$featureTypeAttribute = new Attribute(
				$attribute['name'],
				$attribute['datatype'],
				$featureType,
				$pathPart
			);
			$featureTypeAttribute->setStereoType($attribute['stereotype']);
			$featureTypeAttribute->attribute_type = $attribute['attribute_type'];
			$featureTypeAttribute->setMultiplicity($attribute['multiplicity_range_lower'], $attribute['multiplicity_range_upper']);
			$featureType->addAttribute($featureTypeAttribute);
			$this->attributes[] = $featureTypeAttribute;
		}
		$this->logger->log($featureType->attributesAsTable());

		# lade navigierbare Assoziationsenden von 1:n Assoziationen
		foreach($this->getAssociationEnds($class) AS $end) {
			$associationEnd = new AssociationEnd(
				$end['b_name'],
				$end['a_class_name'],
				$end['b_class_name'],
				$this->logger
			);
			$associationEnd->stereotype = 'FeatureType';
			$associationEnd->setMultiplicity($end['b_multiplicity_range_lower'], $end['b_multiplicity_range_upper']);
			$featureType->addAssociationEnd($associationEnd);
		}
		$this->logger->log($featureType->associationsAsTable());

		$sql = $featureType->asSql();

		$this->logger->log('<pre>' . $sql . '</pre>');
		
		# lade abgeleitete Klassen
		$subClasses = $this->getSubUmlClasses($stereotype, $class);
		# Für alle abgeleiteten Klassen
		foreach($subClasses as $subClass) {
			$this->logger->log('<br><b>Sub' . $stereotype . ': ' . $subClass['name'] . '</b> (' . $subClass['xmi_id'] . ')');
			$sql .= $this->createFeatureTypeTables($stereotype, $featureType, $subClass);
		}
		return $sql;
	}

	function createEnumerationTable($enumeration, $dbSchema) {
		$this->logger->log('<br><b>Create Enumeration Tables: ' . $enumeration['name'] . '</b> (' . $enumeration['xmi_id'] . ')');

		$table = new Table('enum_' . $enumeration['name']);

		# read Values
		$enumType = new EnumType($enumeration['name'], $this->logger);
		$enumType->setSchemas($this, $dbSchema);
		$enumType->setId($enumeration['id']);
		$table->values = $enumType->getValues($enumeration);

		# definiere Attribute
		$wert_type = (ctype_digit($table->values->rows[0][0])) ? 'integer' : 'character varying';
		$attribute = new Attribute('wert', $wert_type);
		$table->addAttribute($attribute);
		$attribute = new Attribute('beschreibung', 'character varying');
		$table->addAttribute($attribute);

		# definiere Primärschlüssel
		$table->primaryKey = 'wert';

		$this->logger->log($table->values->asTable($table->attributes));

		$sql  = $enumType->asSql();
		if (
			$table->values->rows[0][0] != $table->values->rows[0][1] AND
			$table->values->rows[0][1] != 'NULL'
		)
		$sql .= $table->asSql();
		$this->logger->log('<pre>' . $tableSql . '</pre>');
		return $sql;
	}

	function createCodeListTable($code_list) {
		$this->logger->log('<br><b>CodeList: ' . $code_list['name'] . '</b> (' . $code_list['xmi_id'] . ')');

		$table = new Table($code_list['name']);

		# definiere Attribute
		$attribute = new Attribute('id', 'integer');
		$table->addAttribute($attribute);
		$attribute = new Attribute('name', 'character varying');
		$table->addAttribute($attribute);
		$attribute = new Attribute('status', 'character varying');
		$table->addAttribute($attribute);
		$attribute = new Attribute('definition', 'text');
		$table->addAttribute($attribute);
		$attribute = new Attribute('additional_information', 'text');
		$table->addAttribute($attribute);

		# definiere Primärschlüssel
		$table->primaryKey = 'id';

		# definiere Commentare
		$table->addComment('UML-Typ: Code Liste');

		$sql = $table->asSql();
		$this->logger->log('<pre>' . $sql . '</pre>');

		return $sql;
/*		
		$table = strtolower($class['name']);

		# Erzeuge Create Table Statement
		$sql = "
CREATE TABLE IF NOT EXISTS " . $table . " (
	id integer,
	name character varying,
	status character varying,
	definition text,
	description text,
	additional_information text,
	CONSTRAINT " . $table . "_pkey PRIMARY KEY (id)
);
COMMENT ON TABLE " . $table . " IS 'Code Liste " . $class['name'] . "';
";
		$this->logger->log('<pre>' . $sql . '</pre>');
		return $sql;		
*/
	}

	function createAssociationTable($association) {
		//Fixed: Table identifier max length is 63
		$delimiter = '_zu_';
		$table = strtolower($association['a_class'] . $delimiter . $association['b_class']);
		$table_orig = $table;
		if (strlen($table)>63) $table = substr($table, 0, 63);
		//Fixed: Check if table already exists (e.g. aa_reo double assoc results in two 'AA_REO2AA_REO' tables)
		global $tabNameAssoc;
		foreach ($tabNameAssoc as $tabname) {
			if ($table==$tabname) {
				$last = substr($table, -1);
				if (intval($last)!=0) $table = substr($table, 0, strlen($table)-1).(intval($last)+1);
				else $table = $table.$delimiter;
			}
		}
		array_push($tabNameAssoc, $table);
		if ($association['a_class'] == $association['b_class']) {
			$key1 = '1_gml_id';
			$key2 = '2_gml_id';
		} else {
			$key1 = $key2 = 'gml_id';
		}

		$sql = "\n
CREATE TABLE IF NOT EXISTS {$table} (
	" . strtolower($association['a_class']) . "_{$key1} integer,
	" . strtolower($association['b_class']) . "_{$key2} integer,
	PRIMARY KEY (" . strtolower($association['a_class']) . "_{$key1}, " . strtolower($association['b_class']) . "_{$key2})
);
COMMENT ON TABLE {$table} IS 'Association {$association['a_class']} {$delimiter} {$association['b_class']}';";

		if ($association['a_rel'] != '') {
			//Fixed for self-associations (e.g. aa_reo)
			if ($association['a_class'] == $association['b_class']) {
				$sql .= "
COMMENT ON COLUMN " . $table . "." . strtolower($association['a_class']) . "1_gml_id IS '" . $association['a_rel'] ."';";
			}
			else {
			$sql .= "
COMMENT ON COLUMN " . $table . "." . strtolower($association['a_class']) . "_gml_id IS '" . $association['a_rel'] ."';";		
			}
		}
		if ($association['b_rel'] != '') {
			if ($association['a_class'] == $association['b_class']) {
				//Fixed for self-associations (e.g. aa_reo)
				$sql .= "
COMMENT ON COLUMN " . $table . "." . strtolower($association['b_class']) . "2_gml_id IS '" . $association['b_rel'] ."';";
			}
			else{
				$sql .= "
COMMENT ON COLUMN " . $table . "." . strtolower($association['b_class']) . "_gml_id IS '" . $association['b_rel'] ."';";
			}
		}

		//Fixed: Table identifier max length is 63
		if (strlen($table_orig) > PG_MAX_NAME_LENGTH) $sql .= "
ALTER TABLE " . $table . " ADD COLUMN " . $table . " character varying(255);
COMMENT ON COLUMN " . $table .".". $table ."
IS '" . $table_orig . 
"';
";
		$this->logger->log($sql);
		return $sql;
	}

	function createExternalDataTypes($dbSchema) {
		$this->logger->log('<b>Create external data types:</b>');
		#*******************************
		# SC_CRS
		#*******************************
		$dataType = new DataType('sc_crs', 'DataType', $this->logger);
		$this->logger->log('<br><b>' . $dataType->name . '</b>');
		$dataType->setSchemas($this, $dbSchema);
		$dataType->setId(0);

		# create Attributes
		$dataTypeAttribute = new Attribute(
			'scope','CharacterString',
			$dataType->name
		);
		$dataTypeAttribute->setStereoType('CharacterString');
		$dataTypeAttribute->attribute_type = 'ISO 19136 GML Type';
		$dataTypeAttribute->setMultiplicity('1', '-1');
		$this->logger->log(
			'<br>attribute: <b>' . $dataTypeAttribute->name . '</b>
			datatype: <b>' . $dataTypeAttribute->datatype .'</b>
			stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>'
		);
		$dataType->addAttribute($dataTypeAttribute);

		# Create Comments
		$comment  = $dataTypeAttribute->attribute_type . ': ' . $dataTypeAttribute->name;
		$comment .= ' ' . $dataTypeAttribute->multiplicity;
		$dataType->addComment($comment);

		# Erzeuge SQL und registriere DataType in Liste
		$dataTypeSql = $dataType->asSql();
		$this->logger->log('<pre>' . $dataTypeSql . '</pre>');
		$sql .= $dataTypeSql;
		$this->dataTypes[$dataType->name] = $dataType;

		#*******************************
		# doubleList
		#*******************************
		$dataType = new DataType('doubleList', 'DataType', $this->logger);
		$this->logger->log('<br><b>' . $dataType->name . '</b>');
		$dataType->setSchemas($this, $dbSchema);
		$dataType->setId(0);

		# create Attributes
		$dataTypeAttribute = new Attribute(
			'list','Sequence',
			$dataType->name
		);
		$dataTypeAttribute->setStereoType('Sequence');
		$dataTypeAttribute->attribute_type = 'ISO 19136 GML Type';
		$dataTypeAttribute->setMultiplicity('0', '1');
		$this->logger->log(
			'<br>attribute: <b>' . $dataTypeAttribute->name . '</b>
			datatype: <b>' . $dataTypeAttribute->datatype .'</b>
			stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>'
		);
		$dataType->addAttribute($dataTypeAttribute);

		# Create Comments
		$comment  = $dataTypeAttribute->attribute_type . ': ' . $dataTypeAttribute->name;
		$dataType->addComment($comment);

		# Erzeuge SQL und registriere DataType in Liste
		$dataTypeSql = $dataType->asSql();
		$this->logger->log('<pre>' . $dataTypeSql . '</pre>');
		$sql .= $dataTypeSql;
		$this->dataTypes[$dataType->name] = $dataType;

		#*******************************
		# Measure
		#*******************************
		$dataType = new DataType('Measure', 'DataType', $this->logger);
		$this->logger->log('<br><b>' . $dataType->name . '</b>');
		$dataType->setSchemas($umlSchema, $dbSchema);
		$dataType->setId(0);

		# create Attributes
		$dataTypeAttribute = new Attribute(
			'value','Integer',
			$dataType->name
		);
		$dataTypeAttribute->setStereoType('DataType');
		$dataTypeAttribute->attribute_type = 'ISO 19136 GML Type';
		$dataTypeAttribute->setMultiplicity('0', '1');
		$this->logger->log(
			'<br>attribute: <b>' . $dataTypeAttribute->name . '</b>
			datatype: <b>' . $dataTypeAttribute->datatype .'</b>
			stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>'
		);
		$dataType->addAttribute($dataTypeAttribute);

		# Create Comments
		$comment  = $dataTypeAttribute->attribute_type . ': ' . $dataTypeAttribute->name;
		$dataType->addComment($comment);

		# Erzeuge SQL und registriere DataType in Liste
		$dataTypeSql = $dataType->asSql();
		$this->logger->log('<pre>' . $dataTypeSql . '</pre>');
		$sql .= $dataTypeSql;
		$this->dataTypes[$dataType->name] = $dataType;
		return $sql;
	}

	function outputAttributeHtml($attributes) {
		$html = '<table border="1"><tr><th>Attribut</th><th>Datentyp</th><th>Stereotyp</th><th>Attributtyp</th><th>Multiplizität</th><th>Default</th></tr>';

		# für jedes Attribut erzeuge Attributzeilen
		foreach($attributes AS $i => $attribute) {
			$html .= '<tr><td>' . $attribute['name'] . '</td><td>' .
							$attribute['datatype'] . '</td><td>' .
							$attribute['stereotype'] . '</td><td>' .
							$attribute['attribut_type'] . '</td><td>' .
							$this->createMultiplicityText(
								$attribute['multiplicity_range_lower'],
								$attribute['multiplicity_range_upper']
							) . '</td><td>' .
							$attribute['initialvalue_body'] . '</td></tr>';
			$sql .= '
	';
		}
		$html .= '</table><p>';

		if (empty($attributes)) {
			$this->logger->log('Keine Attribute gefunden.');
		}
		else {
			$this->logger->log($html);
		}
	}

	function outputAttributeListHtml() {
		$html = '<table>
	<tr>
		<th>Stereotyp</th>
		<th>Klasse</th>
		<th>Attribut</th>
		<th>Pfad</th>
		<th>Multiplizität</th>
	</tr>';
		foreach($this->attributes AS $attribute) {
			$html .= '
	<tr>
		<td>' . $attribute->parent->stereotype . '</td>
		<td>' . $attribute->parent->name . '</td>
		<td>' . $attribute->alias . '</td>
		<td>' . $attribute->path_name . '</td>
		<td>' . $attribute->multiplicity . '</td>
	</tr>';
		}
		$html .= '
</table>';
		return $html;
	}

	function outputDataTypeListHtml() {
		$html = '<table>
	<tr>
		<th>Name</th>
	</tr>';
		foreach($this->dataTypes AS $dataType) {
			$html .= '
	<tr>
		<td>' . $dataType->name . '</td>
	</tr>';
		}
		$html .= '
</table>';
		return $html;
	}

	function outputUnionListHtml() {
		$html = '<table>
	<tr>
		<th>Name</th>
	</tr>';
		foreach($this->unionTypes AS $unionType) {
			$html .= '
	<tr>
		<td>' . $unionType->name . '</td>
	</tr>';
		}
		$html .= '
</table>';
		return $html;
	}
}
?>