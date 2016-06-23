<?php
	$a = "abc";
	$tabNameAssoc = array();
echo '<!DOCTYPE html>
<html lang="de">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  </head>
  <body>';
  /*****************************************************************************
  * 
  ******************************************************************************/
  include( dirname(__FILE__) . "/conf/database_conf.php");

  $sql = 'SET search_path = ' . CLASSES_SCHEMA . ', public;
DROP SCHEMA ' . CLASSES_SCHEMA . ' CASCADE;
CREATE SCHEMA ' . CLASSES_SCHEMA . ';
';
  # Lade oberste Klassen, die von keinen anderen abgeleitet wurden
  $topClasses = getTopClasses();
  
  # Für alle oberen Klassen
  foreach($topClasses as $topClass) {
    output('<br><b>TopKlasse: ' . $topClass['name'] . '</b> (' . $topClass['xmi_id'] . ')');
    $sql .= createClassTables(null, $topClass);
  }

  # Lade Enummerations
  $enumerations = getEnumerations();

  # Für alle Enumerations
  foreach($enumerations AS $enumeration) {
    output('<br><b>Enumeration: ' . $enumeration['name'] . '</b> (' . $enumeration['xmi_id'] . ')');
    $sql .= createEnumerationTable($enumeration);
  }

  # Lade CodeLists
  $code_lists = getCodeLists();

  # Für alle CodeLists //Fixed, was: $enumeration['name'] etc.
  foreach($code_lists AS $code_list) {
    output('<br><b>CodeList: ' . $code_list['name'] . '</b> (' . $code_list['xmi_id'] . ')');
    $sql .= createCodeListTable($code_list);
  }
  
  # Lade Associations
  $associations = getAssociations();
  foreach($associations AS $association) {
    $text = '<br><b>Association: ' . $association['assoc_id'] . '</b><br>' .
      $association['a_class'] . ' hat ' . $association['a_num'] . ' ' . $association['b_class'] . ' über ' . $association['a_rel'] . '<br>';
    if ($association['b_rel'] != '')
      $text .= $association['b_class'] . ' hat ' . $association['b_num'] . ' ' . $association['b_rel'];
    if ($association['a_num'] == 'n' AND $association['b_num'] == 'n') {
      $assoc_table = strtolower($association['a_class'] . '2' . $association['b_class']);
      $text .= '<br>Lege n:m Tabelle ' . $assoc_table . ' an.';
      $sql .= createAssociationTable($association);
    }
    output($text);
  }
?>
<pre><?php
  echo $sql;
?></pre>
<?php
	//global $a;
  /*****************************************************************************
  * Funktionen
  ******************************************************************************/
  function output($text) {
    global $a;
	//global $tabNameAssoc;
	//array_push($tabNameAssoc, $a."def");
	if (DEBUG) {
      echo '<br>' . $text;
    }
  }
  /**
  * Lade alle Generalisierungen, die selber nicht von anderen abgeleitet sind
  **/
  function getTopClasses() {
    global $db_conn;
    $sql = "
      SELECT
        c.id,
        c.xmi_id,
        c.name
      FROM
        " . UML_SCHEMA . ".packages p LEFT JOIN
        " . UML_SCHEMA . ".uml_classes c ON p.id = c.package_id LEFT JOIN
        " . UML_SCHEMA . ".stereotypes s ON c.stereotype_id = s.xmi_id
      WHERE
        general_id = '-1' AND
        s.name LIKE '%eatureType' AND
        p.name IN (" . PACKAGES . ")
    ";
    output('<b>Get TopClasses: </b><br>');
    output('<pre>' . $sql . '</pre>');
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
    $result = pg_fetch_all(
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }

  function getSubClasses($class) {
    global $db_conn;
    $sql = "
      SELECT
        c.id,
        c.xmi_id,
        c.name
      FROM
        " . UML_SCHEMA . ".class_generalizations g LEFT JOIN
        " . UML_SCHEMA . ".uml_classes p ON g.parent_id = p.xmi_id JOIN
        " . UML_SCHEMA . ".uml_classes c ON g.child_id = c.xmi_id LEFT JOIN
        " . UML_SCHEMA . ".packages pa ON c.package_id = pa.id
      WHERE
        p.xmi_id = '" . $class['xmi_id'] . "' AND
        pa.name IN (" . PACKAGES . ")
    ";
    output('<b>Get SubClasses</b>');
    output('<pre>' . $sql . '</pre>');
    $result = pg_fetch_all(
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }
  
  function getEnumerations() {
    global $db_conn;
    $sql = "
      SELECT
        c.id,
        c.name
      FROM
        " . UML_SCHEMA . ".packages p LEFT JOIN
        " . UML_SCHEMA . ".uml_classes c ON p.id = c.package_id LEFT JOIN
        " . UML_SCHEMA . ".stereotypes s ON c.stereotype_id = s.xmi_id
      WHERE
        lower(s.name) = 'enumeration' AND
        p.name IN (" . PACKAGES . ")
    ";
    output('<b>Get Enumerations</b>');
    output('<pre>' . $sql . '</pre>');
    $result = pg_fetch_all(
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }

  function getCodeLists() {
    global $db_conn;
    $sql = "
      SELECT
        c.id,
        c.name
      FROM
        " . UML_SCHEMA . ".packages p LEFT JOIN
        " . UML_SCHEMA . ".uml_classes c ON p.id = c.package_id LEFT JOIN
        " . UML_SCHEMA . ".stereotypes s ON c.stereotype_id = s.xmi_id
      WHERE
        s.name LIKE '%odeList' AND
        p.name IN (" . PACKAGES . ")
    ";
    output('<b>Get CodeList</b>');
    output('<pre>' . $sql . '</pre>');
    $result = pg_fetch_all(
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }
  
  function getAttributes($class) {
    global $db_conn;

    $sql = "
      SELECT
        a.name,
        d.name AS datatype,
        c.name AS classifier,
        s.name AS classifier_stereotype,
        a.multiplicity_range_lower::integer,
        a.multiplicity_range_upper,
        a.initialvalue_body
      FROM
        " . UML_SCHEMA . ".uml_attributes a LEFT JOIN
        " . UML_SCHEMA . ".datatypes d ON a.datatype = d.xmi_id LEFT JOIN
        " . UML_SCHEMA . ".uml_classes c ON a.classifier = c.xmi_id LEFT JOIN
        " . UML_SCHEMA . ".stereotypes s ON c.stereotype_id = s.xmi_id
      WHERE
        uml_class_id = " . $class['id'] . "
    ";
    output('<b>Get Attributes: </b>');
    output('<pre>' . $sql . '</pre>');
    $result = pg_fetch_all(
      pg_query($db_conn, $sql)
    );
    if ($result == false) $result = array();
    return $result;
  }

  function getAssociations() {
    global $db_conn;
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
            " . UML_SCHEMA .".association_ends ae
          GROUP BY
            assoc_id
          ORDER BY
            a_id
        ) c JOIN
        " . UML_SCHEMA . ".association_ends a ON a.id = c.a_id JOIN
        " . UML_SCHEMA . ".association_ends b ON b.id = c.b_id JOIN
        " . UML_SCHEMA . ".uml_classes ca ON a.participant = ca.xmi_id JOIN
        " . UML_SCHEMA . ".uml_classes cb ON b.participant = cb.xmi_id JOIN
        " . UML_SCHEMA . ".packages pa ON ca.package_id = pa.id JOIN
        " . UML_SCHEMA . ".packages pb ON cb.package_id = pb.id
      WHERE
        pa.name IN (" . PACKAGES . ") AND
        pb.name IN (" . PACKAGES . ")
    ";
    output('<b>Get Associations: </b>');
    output('<pre>' . $sql . '</pre>');
    $result = pg_fetch_all(
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }

  function createDataType($datatype, $classifier_stereotype, $multiplicity) {
    $sql = '';
    if ($datatype != '') {
		//Fixed: more datatypes
      switch (true) {
        case (strtolower($datatype) == 'characterstring'):
          $sql = 'character varying';
          break;
		case in_array(strtolower($datatype), array(
			'date',
			'datetime',
			'TM_Duration'
		)):
		  $sql = 'date';
          break;
        case in_array(strtolower($datatype), array(
            'integer',
            'int'
          )):
          $sql = 'integer';
          break;
        case (strtolower($datatype) == 'boolean'):
          $sql = 'boolean';
          break;
        case in_array(strtolower($datatype), array(
            'angle',
            'length',
            'decimal',
            'volume',
            'area',
            'tm_duration',
			'real',
			'distance'
          )):
          $sql = 'double precision';
          break;
		case in_array($datatype, array(
            'GM_Point',
            'DirectPosition'
          )):
          $sql = 'geometry(POINT)';
          break;
		case ($datatype == 'GM_Curve'):
          $sql = 'geometry(LINESTRING)';
          break;
		case ($datatype == 'GM_MultiCurve'):
          $sql = 'geometry(MULTILINESTRING)';
          break;
		case ($datatype == 'GM_MultiPoint'):
          $sql = 'geometry(MULTIPOINT)';
          break;
		case ($datatype == 'GM_MultiSurface'):
          $sql = 'geometry(MULTIPOLYGON)';
          break;
		case ($datatype == 'GM_Surface'):
          $sql = 'geometry(POLYGON)';
          break;
		case ($datatype == 'GM_Object'):
          $sql = 'geometry';
          break;
      }
    }
    else {
      switch ($classifier_stereotype) {
	  //Fixed: Cases etc. for AAA
        case 'Enumeration':
		case 'enumeration':
		case 'enum':
          $sql = 'character varying';
          break;
        case 'CodeList':
		case 'codeList':
          $sql = 'integer';
          break;
        case 'DataType':
		case 'dataType':
          $sql = 'uuid';
          break;
        case 'Union':
          $sql = 'geometry';
          break;
      }
    }
    if ($sql == '')
      $sql = 'text';
    if ($multiplicity == '-1') {
      $sql .= '[]';
    }
    return $sql;
  }

  function createAttributeDefinition($attribute) {
    $sql = $attribute['name'];
    $sql .= ' ' . createDataType(
      $attribute['datatype'],
      $attribute['classifier_stereotype'],
      $attribute['multiplicity_range_upper']
    );
    if ($attribute['multiplicity_range_lower'] > '0')
      $sql .= ' NOT NULL';
    if ($attribute['initialvalue_body'] != '')
      $sql .= " DEFAULT '" . $attribute['initialvalue_body'] . "'";
    $sql .= ',';
    return $sql;
  }

  function createAttributeComment($class_name, $attribute_name, $classifier, $classifier_stereotype) {
    $sql = "
COMMENT ON COLUMN " . strtolower($class_name) . "." . $attribute_name . " IS '" . $classifier_stereotype . " " . $classifier . "';";
    return $sql;
  }

  function createClassTables($superClass, $class) {
    # Erzeuge Create Table Statement
    $table = strtolower($class['name']);
	

    $sql = "CREATE TABLE IF NOT EXISTS " . $table . " (";
	if (strlen($table)>63) $sql .= 'Tab Name zu lang xyz ';
    if ($superClass == null) {
      $sql .= "
  gml_id uuid NOT NULL DEFAULT uuid_generate_v1mc(),";
    }

    # lade Attribute
    $attributes = getAttributes($class);

    # für jedes Attribut erzeuge Attributzeilen
    foreach($attributes AS $i => $attribute) {
      $sql .= '
  ';
      $sql .= createAttributeDefinition($attribute);
    }

    $sql .= '
  CONSTRAINT ' . $table . '_pkey PRIMARY KEY (gml_id)
)';
    if ($superClass != null) {
      # leite von superClass ab
      $sql .= '
INHERITS ('. strtolower($superClass['name']) . ')';
      $sql .= '
WITH OIDS';
    }
    $sql .= ";
COMMENT ON TABLE " . $table . " IS 'Tabelle " . $class['name'];
    if ($superClass != null)
      $sql .= " abgeleitet von " . $superClass['name'];
    $sql .= "';";
    # für jedes Attribut erzeuge Kommentar, wenn der type ein
    # Datentyp ist
    foreach($attributes AS $i => $attribute) {
      if ($attribute['classifier'] != '')
        $sql .= createAttributeComment(
          $class['name'],
          $attribute['name'],
          $attribute['classifier'], 
          $attribute['classifier_stereotype']
        );
    }
    $sql .= '

';
    output('<pre>' . $sql . '</pre>');
    
    # lade abgeleitete Klassen
    $subClasses = getSubClasses($class);
    # Für alle abgeleiteten Klassen
    foreach($subClasses as $subClass) {
      output('<br><b>SubKlasse: ' . $subClass['name'] . '</b> (' . $subClass['xmi_id'] . ')');
      $sql .= createClassTables($class, $subClass);
    }

    return $sql;
  }

  function createEnumerationTable($class) {
	//Fixed: Table identifier max length is 63 (with "_pkey" only 58!)
    $table = strtolower($class['name']);
	$table_orig = $table;
	if (strlen($table)>58) $table = substr($table, 0, 58);
	$isInt = false;

    # lade Values
    $values = getAttributes($class);
    if (empty($values)) return $sql;

    $i = 0;
    $sqlValues = "
INSERT INTO " . $table . " (wert, beschreibung)
VALUES
";
    # für jeden Value erzeuge Datenzeile
    for($i=0; $i < count($values); $i++) {
      if ($i > 0)
        $sqlValues .= ",
";
      $value = $values[$i];
      if ($value['initialvalue_body'] == '') {
        $parts = explode('=', $value['name']);
        if (trim($parts[1]) == '' )
          $wert = $i;
        else
          $wert = $parts[1];
      }
      else
        $wert = str_replace(array('`', '´', '+'), '', $value['initialvalue_body']);
	  //Fixed for non-integer values
	  if (gettype($wert) == "integer") {
		$isInt = true;
		$sqlValues .= "  (" . trim($wert) . ", '" . trim($value['name']) . "')";
		}
	  else $sqlValues .= "  ('" . trim($wert) . "', '" . trim($value['name']) . "')";
    };
    $sqlValues .= ";\n";

	# Erzeuge Create Table Statement
	$sqlBegin = "";
	//Fixed for non-integer values
	
	if ($isInt) {
		$sqlBegin = "
CREATE TABLE IF NOT EXISTS " . $table . " (
  wert integer,
  beschreibung character varying,
  CONSTRAINT " . $table . "_pkey PRIMARY KEY (wert)
);
COMMENT ON TABLE " . $table . " IS 'Aufzählung " . $class['name'] . "';
";}
	else {
		$sqlBegin = "
CREATE TABLE IF NOT EXISTS " . $table . " (
  wert character varying,
  beschreibung character varying,
  CONSTRAINT " . $table . "_pkey PRIMARY KEY (wert)
);
COMMENT ON TABLE " . $table . " IS 'Aufzählung " . $class['name'] . "';
";}

	//Fixed: Table identifier max length is 63 (with "_pkey" only 58!)
	$sql = $sqlBegin . $sqlValues;
	if (strlen($table_orig)>58) $sql .= "
ALTER TABLE " . $table . " ADD COLUMN " . $table . " character varying(255);
COMMENT ON COLUMN " . $table .".". $table ."
IS '" . $table_orig . 
"';
";
    output('<pre>' . $sql . '</pre>');
    return $sql;
  }

  function createCodeListTable($class) {
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
    output('<pre>' . $sql . '</pre>');
    return $sql;    
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
	if (strlen($table_orig)>58) $sql .= "
ALTER TABLE " . $table . " ADD COLUMN " . $table . " character varying(255);
COMMENT ON COLUMN " . $table .".". $table ."
IS '" . $table_orig . 
"';
";
    output($sql);
    return $sql;
  }
echo '</body>
</html>';
?>