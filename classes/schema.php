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
		$sql  = 'SET search_path = ' . $this->schemaName . ", public;\n";
		$sql .= 'DROP SCHEMA IF EXISTS ' . $this->schemaName . " CASCADE;\n";
		$sql .= 'CREATE SCHEMA ' . $this->schemaName . ";\n";
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

	function getClass($name) {
		$sql = "
SELECT
	*
FROM
  ". $this->schemaName . ".uml_classes
WHERE
  lower(name) = '" . strtolower($name) . "';
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
	pa.name IN (" . PACKAGES . ")
";
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

	# Lade AssociationEnds for classes
	function getAssociationEnds($class) {
		$sql = "
SELECT
	ca.name a_class_name,
	b.id b_id,
	b.name b_name,
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
	AND b.\"isNavigable\"
		";
		$this->logger->log(' <br><b>Get 1:n Association Ends: </b>');
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
	
	function createComplexDataTypes($stereotype, $class) {
		$this->logger->log('<br><b>Create ' . $stereotype . ' ' . $class['name'] . '</b> (' . $class['xmi_id'] . ') id: ' . $class['id']);
		$sql = '';
		$dataType = new DataType($class['name'], $stereotype, $this->logger);
		if (!$this->stereoTypeAllreadyExists($dataType->name, $dataType->stereotype)) {
			# union oder datentyp existiert noch nicht, jetzt erzeugen
			$dataType->setUmlSchema($this);
			$dataType->setId($class['id']);
			$attributes = $dataType->getAttributes();
			$this->logger->log('<ul>');
			foreach($attributes AS $attribute) {
				$this->logger->log('<li>');
				# erzeuge Attributdefinition
				$dataTypeAttribute = new Attribute(
					$attribute['name'],
					$attribute['datatype'],
					$class['name']
				);
				$dataTypeAttribute->setStereoType($attribute['stereotype']);
				$dataTypeAttribute->attribute_type = $attribute['attribute_type'];
				$dataTypeAttribute->setMultiplicity($attribute['multiplicity_range_lower'], $attribute['multiplicity_range_upper']);
				$this->logger->log('<b>' . $attribute['name'] . '</b> datatype: <b>' . $dataTypeAttribute->datatype .'</b> stereotype: <b>' . $dataTypeAttribute->stereotype . '</b>');
				$dataType->addAttribute($dataTypeAttribute);

				# Falls Stereotype des Attributes eini union oder datatype ist und als uml class existiert erzeugen den Typ
				if (in_array($dataTypeAttribute->stereotype,  array('union', 'datatype'))) {
					# Prüfe ob der DataType über haupt einer ist
					$class = $this->getClass($dataTypeAttribute->datatype_alias);
					if (!empty($class)) {
						# erzeuge diesen typ und hänge in an die Liste der erzeugten Datentypen an.
						$sql .= $this->createComplexDataTypes('DataType', $class[0]);
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
				$this->dataTypes[$dataType->namea] = $dataType;

			# lade abgeleitete Klassen
			$subClasses = $this->getSubUmlClasses($stereotype, $class);

			# Für alle abgeleiteten Klassen
			foreach($subClasses as $subClass) {
				$sql .= $this->createComplexDataTypes($stereotype, $subClass);
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
		if ($stereotype == 'union')
			$typeList = $this->unionTypes;
		if ($stereotype == 'datatype')
			$typeList = $this->dataTypes;
		return (
			array_key_exists($datatype, $typeList) OR
			(strpos($datatype, 'geometry(') !== false)
		) ? true : false;
	}

	function createFeatureTypeTables($stereotype, $superClass, $class) {
		$this->logger->log('<br><b>Create ' . $stereotype . ': ' . $class['name'] .' </b>');
		# Erzeuge FeatueType
		$featureType = new FeatureType($class['name'], $this->logger, $this);
		$featureType->setId($class['id']);
		if ($superClass == null) {
			$featureType->primaryKey = 'gml_id';
		}
		else {
			$featureType->set_inheritance($superClass['name']);
			$this->logger->log(' abgeleitet von: <b>' . $superClass['name'] . '</b>');
		}

		foreach($featureType->getAttributes() AS $attribute) {
			$featureTypeAttribute = new Attribute(
				$attribute['name'],
				$attribute['datatype'],
				$class['name']
			);
			$featureTypeAttribute->setStereoType($attribute['stereotype']);
			$featureTypeAttribute->attribute_type = $attribute['attribute_type'];
			$featureTypeAttribute->setMultiplicity($attribute['multiplicity_range_lower'], $attribute['multiplicity_range_upper']);
			$featureType->addAttribute($featureTypeAttribute);
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
/*

		# lade navigierbare Assoziationsenden von 1:n Assoziationen
		$association_ends = $this->getAssociationEnds($class);

		$html = '<table border="1"><tr><th>Class</th><th>Assoc</th><th>Multiplicity</th><th>Class name</th><th>Datentyp</th></tr>';

		# für jede Assoziation erzeuge ein Attributzeile und kommentarzeile
		foreach($association_ends AS $i => $association_end) {
			
			if (!empty($attributes))
				$sql .= ',
	';
			$html .= '<tr><td>' .
				$class['name'] . '</td><td>' .
				$association_end['b_name'] . '</td><td>' .
				$this->createMultiplicityText(
					$association_end['b_multiplicity_range_lower'],
					$association_end['b_multiplicity_range_upper']
				)	. '</td><td>' . $association_end['b_class_name'] . '</td><td>' . strtolower($association_end['b_class_name']) . '</td></tr>';

			# Belege Attributwerte an Hand der Infos aus $association_end und $class
			$attribute = array();
			$attribute['name'] = $association_end['b_name'];
			$attribute['datatype'] = $association_end['b_class_name'];
			$attribute['stereotype'] = 'FeatureType';
			$attribute['attribute_type'] = 'Assoziation zu';
			$attribute['multiplicity_range_upper'] = $association_end['b_multiplicity_range_upper'];
			$attribute['initialvalue_body'] = ''; # keine default Werte für AssociationEnds
			$attributes[] = $attribute;
			$sql .= $this->createAttributeDefinition($attribute);

			if (array_key_exists(Table::getName($association_end['b_name']), $this->featureTypes)) {
				createFeatureTypeTables
			}

			$associationEnd = new AssociationEnd($association_end['b_name'], $this->logger);
			$associationEnd->datatype = $association_end['b_class_name'];
			$associationEnd->stereotype = 'FeatureType';
			$
			
			$table->addAssociationEnd($associationEnd)
		}
		$html .= '</table><p>';
		if (empty($association_ends)) {
			$this->logger->log(' Keine Assoziationen gefunden.');
		}
		else {
			$this->logger->log($html);
		}

		$sql .= '
)';

		if ($superClass != null) {
			# leite von superClass ab
			$sql .= '
INHERITS ('. strtolower($superClass['name']) . ')';
		}
			$sql .= '
WITH OIDS';
		$sql .= ';
ALTER TABLE ' . $table->name . '
	ADD CONSTRAINT ' . $table->name . '_pkey PRIMARY KEY(' . $table->primaryKey . ');';
		$sql .= "
COMMENT ON TABLE " . $table->name . " IS 'Tabelle für featureType " . $table->alias;
		if ($superClass != null)
			$table->addComment(" abgeleitet von " . $superClass['name']);
#		$sql .= "';";
		# für jedes Attribut erzeuge Kommentar, wenn der type ein
		# Datentyp ist
		//Fixed: Was not doing anything for DataTypes, only Stereotypes so far. Now for DataTypes as well.
		foreach($attributes AS $attribute) {
			$sql .= $this->createAttributeComment($class, $attribute);
		}

*/

		$this->logger->log('<pre>' . $sql . '</pre>');
		
		# lade abgeleitete Klassen
		$subClasses = $this->getSubUmlClasses($stereotype, $class);
		# Für alle abgeleiteten Klassen
		foreach($subClasses as $subClass) {
			$this->logger->log('<br><b>Sub' . $stereotype . ': ' . $subClass['name'] . '</b> (' . $subClass['xmi_id'] . ')');
			$sql .= $this->createFeatureTypeTables($stereotype, $class, $subClass);
		}
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
		$table = strtolower($association['a_class'] . '2' . $association['b_class']);
		$table_orig = $table;
		if (strlen($table)>63) $table = substr($table, 0, 63);
		//Fixed: Check if table already exists (e.g. aa_reo double assoc results in two 'AA_REO2AA_REO' tables)
		global $tabNameAssoc;
		foreach ($tabNameAssoc as $tabname) {
			if ($table==$tabname) {
				$last = substr($table, -1);
				if (intval($last)!=0) $table = substr($table, 0, strlen($table)-1).(intval($last)+1);
				else $table = $table.'2';
			}
		}
		array_push($tabNameAssoc, $table);
		//Fixed for self-associations (e.g. aa_reo)
		if ($association['a_class'] == $association['b_class']) {
			$sql = "
CREATE TABLE IF NOT EXISTS " . $table . " (
	" . strtolower($association['a_class']) . "1_gml_id integer,
	" . strtolower($association['b_class']) . "2_gml_id integer
);
COMMENT ON TABlE " . $table . " IS 'Association " . $association['a_class'] . '2' . $association['b_class'] . "';";
		}
		else {
			$sql = "
CREATE TABLE IF NOT EXISTS " . $table . " (
	" . strtolower($association['a_class']) . "_gml_id integer,
	" . strtolower($association['b_class']) . "_gml_id integer
);
COMMENT ON TABLE " . $table . " IS 'Association " . $association['a_class'] . '2' . $association['b_class'] . "';";
		}
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

}
?>