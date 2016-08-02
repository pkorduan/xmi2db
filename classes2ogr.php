<?php
echo '<!DOCTYPE html>
<html lang="de">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  </head>
  <body>';
  /*****************************************************************************
  * classes2ogr
  * @author: Christian Seip (https://github.com/chseip/)
  * Third Step of getting from an UML model to instance, i.e. GML feature classes.
  * Uses the UML model in the database (used xmi2db for that) to create ALTER TABLE statements that makes complex datatypes flat, i.e. adds columns for each attribute of the complex datatype linked to.
  ******************************************************************************/
  include( dirname(__FILE__) . "/conf/database_conf.php");

  
  //$sql = 'SET search_path = ' . CLASSES_SCHEMA . ', public;';

  $log = '';
  $logSame = '';
	# Get all classes of the gml schema
	$classes = getClasses();
	//go thorugh each class of the gml schema and check if there any complex attributes for each of them
	foreach($classes as $class) {
		$log .= 'Klasse <b>'.$class['table_name'].'</b>';
		$foundInUML = false;
		#output('<br><b>Klasse: ' . $class['table_name'] . '</b>');
		//lookup class in UML model
		$classUML = getClassWithName($class['table_name']);
		if ($classUML) {
			#output('<br>Klasse im UML schema gefunden!');
			$foundInUML = true;
		}
		//if not found in UML model, maybe the name was truncted, now lookup the full name and check again
		//take every char after the last space, e.g. 'Association AX_LagebezeichnungMitHausnummer2AX_HistorischesFlurstueckOhneRaumbezug' oder 'Aufzählung AX_Punktstabilitaet_Hoehenfestpunkt_GueteDesVermarkungstraegers'
		else {
			$commentClass = getClassComment($class['table_name'])[0]['obj_description'];
			#output('<br>Klasse im UML schema gefunden!');
			$classUML = getClassWithName(substr($commentClass, strripos($commentClass, ' ')+1, strlen($commentClass)));
			if ($classUML) {
				#output('<br>Klasse im UML schema gefunden!');
				$foundInUML = true;
			}
		}
		//If the class was found in the UML model (otherwise we couldn't replace anything) and if the class isn't on the ommit list, look for complex attributes and flatten them
		if ($foundInUML && !array_keys($classesOut, $classUML[0]['name'], true)) {
			$log .= ' gibt es im UML Schema.<br>';
			$attrNames = array();
			//for faster processing a specfic class may be chosen, just for testing purposes
			//if ($classUML[0]['name'] == 'AX_Flurstueck') {
			//if ($classUML[0]['name'] == 'AX_BenutzergruppeMitZugriffskontrolle') {
			//if ($classUML[0]['name'] == 'AX_Gebaeude') {
			//if ($classUML[0]['name'] == 'AX_Gebiet_Verwaltungsgemeinschaft') {
			//if ($classUML[0]['name'] == 'AX_MarkanterGelaendepunkt') {
			//if ($classUML[0]['name'] == 'AA_Objekt') {//-> 1. Verweis auf codeList "AA_Anlassart" wird nicht erkannt, 2. mehr die Spalte "anlass|aa_anlassart|veränderung der angaben zum objektartenbe2"
			//if ($classUML[0]['name'] == 'AX_HistorischesFlurstueckOhneRaumbezug') {//-> mehrfach Spalte "sonstigeeigenschaften|ax_sonstigeeigenschaften_flurstueck|anga2"
			//if ($classUML[0]['name'] == 'AX_Verwaltungsgemeinschaft') {//-> administrativeFunktion verweist auf CodeListe "ax_administrative_funktion", die nicht als CodeListe erkannt wird
			//if ($classUML[0]['name'] == 'AX_Schleuse') {//-> Doku Test
				//get all the attributes of the class
				$attributes = getAttributes($classUML[0]['id']);
				#output('<br>Attribute: ');
				//set two things for logging purposes
				$firstSame = false;
				$replaced = false;
				//check every attribute
				foreach($attributes as $attribute) {
					$attributeInfo = getAttributeInfo($attribute);
					#output('<br><b>attributeInfo '.$attribute['name']. ': </b>' . print_r($attributeInfo));
					#output('<br>Name: '.$attribute['name']);
					//begin list of attribute names to check for duplicates later
					array_push($attrNames, $attribute['name']);
					//Get Origin Class of Attribute and if Attribute is of complex type Classifier or Stereotyp
					//Do not replace anything if Attribute is of CodeList or Enumaration Type
					if ($attributeInfo[0]['datatype'] || $attributeInfo[0]['classifier'] && ($attribute['classifier_stereotype']!=='codeList' && $attribute['classifier_stereotype']!=='enumeration')) {
						//the type is either stored in 'datatype' or 'classifier', e.g. 'ax_gemeindekennzeichen' of 'gemeindezugehoerigkeit' in 'AX_Flurstueck'
						$type = $attributeInfo[0]['datatype'] . $attributeInfo[0]['classifier'];
						#output('<br>Datatype Typ: '.$type);
						//get info about complex data type (class) linked to
						$originClass = getClassWithName($type);
						#output('<br>classUML:'.print_r($originClass));
						//If an originClass was returned (which should always be the case)
						//AA_Anlassart of AA_Objekt is special, it is its own stereotype which is a codelist and should not be used
						//Same for ax_administrative_funktion of AX_Verwaltungsgemeinschaft which is an enumeration
						//Do not do anything for enumerations and codelists!
						if ($originClass && ($originClass[0]['type_name']!=='codeList' && $originClass[0]['type_name']!=='enumeration') && !array_keys($attributesOut, $attribute['name'], true)) {
							$replaced = true;
							$log .= '&#09;'.$attribute['name'].' wird vom komplexen Typ '.$type.' in flaches Schema überführt:<br>';
							//get statement that deletes the original attribut, e.g. "gemeindezugehoerigkeit"
							$sql .= deleteColumnTable($class['table_name'], $attribute['name']);
							$log .= '&#09;&#09;Attribut<i> '.$attribute['name'].'</i> wird gelöscht';
							#output('<br><b>originClass: </b>' . print_r($originClass));
							#output('<br>Pakethierarchie: ');
							#getSuperPackages($originClass[0]['package_id']);
							$originAttributes = getAttributes($originClass[0]['id']);
							#output('<br>Orgin Attribute: ');
							//go through attributes of complex data type to store them in the table linking to it
							foreach($originAttributes as $originAttribute) {
								$attributeInfo = getAttributeInfo($originAttribute);
								#output('<br><b>attributeInfo '.$attribute['name']. ': </b>' . print_r($attributeInfo));
								#output('<br>Name: '.$originAttribute['name']);
								//build new name for attribute, e.g. "gemeindezugehoerigkeit|ax_gemeindekennzeichen|regierungsbezirk"
								//"original attribute name|complex data type class|nth attribute of complex data type class"
								$newAttributeName = strtolower($attribute['name'].'|'.$type.'|'.$originAttribute['name']);
								$newAttributeNameComment = $newAttributeName;
								#output('<br>Neuer Name: '.$newAttributeName);
								//check if new attribute name is too long and truncate it if neccessary
								if (strlen($newAttributeName)>62) {
									$newAttributeName = substr($newAttributeName, 0, 62);
									#output('<br>Name zu lang, deshalb kurz: '.$newAttributeName);
								}
								#Test if attribute name is already used (happens because of truncation)
								foreach ($attrNames as $attrName) {
									if ($newAttributeName==$attrName) {
										if (!$firstSame) {
											$logSame .= '<br>Klasse <b>'.$class['table_name'].'</b>';
											$firstSame = true;
										}
										#output('<br>###Doppeltes Attribut gefunden: '.$newAttributeName);
										$sameOriginal = $newAttributeName;
										$last = substr($newAttributeName, -1);
										//check if there already is anumber at the end, if so increase it by 1
										if (intval($last)!=0) $newAttributeName = substr($newAttributeName, 0, strlen($newAttributeName)-1).(intval($last)+1);
										//or (no number athe end) add "2" at the end of the attribute name
										else $newAttributeName = $newAttributeName.'2';
									}
								}
								//push original name (e.g. 'land') of attribute into array
								array_push($attrNames, $originAttribute['name']);
								#output('<br>Pushe Original Name: '.$originAttribute['name']);
								//push original new name (e.g. 'gemeindezugehoerigkeit|ax_gemeindekennzeichen|land') of attribute into array
								array_push($attrNames, $newAttributeName);
								#output('<br>Pushe Neuer Name: '.$newAttributeName);
								//create SQL statement to add colum for new attribute (one simple attribute of a complex attribute)
								$log .= '<br>&#09;&#09;Neues Attribut <i>'.$originAttribute['name'].'</i> aus '. $type .' kommt als <i>'.$newAttributeName.'</i> hinzu ';
								if (intval(substr($newAttributeName, -1))!=0) $logSame .= '<br>&#09;Doppeltes Attribut gefunden (eigentlicher Name <i>'.$originAttribute['name'].'</i>): <i>'.$sameOriginal.'</i> wurde umbenannt in <i>'.$newAttributeName.'</i>';
								$originAttribute['name'] = $newAttributeName;
								//get statement that adds the new attribute to the table
								$sql .= addColumnTable($class['table_name'], $originAttribute, $newAttributeNameComment);
							}
							$log .= '<br>';
						}
					}
					#output('<br>');
					#output(print_r($attrNames));
				}
				if(!$replaced) $log .= "=> nichts ersetzt.<br>";
			//}//End if class name
		}
		else if(!array_keys($classesOut, $classUML[0]['name'], true)) $log .= ' gibt es nicht im UML Schema.<br>=> nichts ersetzt/getan.<br>';
		else $log .= ' wurde ausgeschlossen in der conf Datei.<br>=> nichts ersetzt/getan.<br>';
	}
?>
<pre><?php
  echo "<h2>SQL</h2>".$sql;
  echo "<hr><h2>Doku Ersetzungen von komplexen Datentypen in flaches Schema</h2>".$log;
  echo "<hr><h2>Doku gleichlautendende Attributnamen</h2>".$logSame;
?></pre>
<?php
	//global $a;
  /*****************************************************************************
  * Funktionen
  ******************************************************************************/
  function output($text) {
    global $a;
	if (DEBUG) {
      //echo '<br>' . $text;
	  echo $text;
    }
  }
  
  /**
  * Lade alle Generalisierungen, die selber nicht von anderen abgeleitet sind
  **/
  function getClassComment($class) {
    global $db_conn;

	//http://stackoverflow.com/questions/5664094/getting-list-of-table-comments-in-postgresql
	$sql = "
      SELECT
      obj_description('" . CLASSES_SCHEMA . "." . $class . "'::regclass)
    ";

    #output('<b>Get Class Comment: </b><br>');
    #output('<pre>' . $sql . '</pre>');
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
    $result = pg_fetch_all(
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }
  function getClasses() {
    global $db_conn;

	//http://stackoverflow.com/questions/15644152/list-tables-in-a-postgresql-schema
	$sql = "
      SELECT
        *
      FROM information_schema.tables 
      WHERE table_schema = '" . CLASSES_SCHEMA . "'
	  ORDER BY table_name
    ";

    #output('<b>Get Classes: </b><br>');
    #output('<pre>' . $sql . '</pre>');
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
    $result = pg_fetch_all(
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }
   
  function getClassWithName($class) {
    global $db_conn;
    /*
	$sql = "
      SELECT
        *
      FROM
        " . UML_SCHEMA . ".uml_classes
	  WHERE
	    lower(name) = lower('" . $class . "')
    ";
	*/
	$sql = "
      SELECT
        c.name, c.id, s.name AS type_name
      FROM
        " . UML_SCHEMA . ".uml_classes c LEFT JOIN " . UML_SCHEMA . ".stereotypes s ON c.stereotype_id = s.xmi_id
	  WHERE
	    lower(c.name) = lower('" . $class . "')
    ";
    #output('<b>Get Class with name: </b><br>');
    #output('<pre>' . $sql . '</pre>');
	//Fixed: 'pg_query(): Query failed: ERROR: invalid byte sequence for encoding "UTF8"'
    $result = pg_fetch_all(
      pg_query($db_conn, utf8_encode($sql))
    );
    if ($result == false) $result = array();
    return $result;
  }
 
  function getAttributes($class) {
    global $db_conn;

	//http://stackoverflow.com/questions/13723301/postgresql-select-one-of-two-fields-depending-on-which-is-empty
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
        " . UML_SCHEMA . ".uml_attributes a LEFT JOIN
        " . UML_SCHEMA . ".datatypes as d1 ON a.datatype = d1.xmi_id LEFT JOIN
        " . UML_SCHEMA . ".datatypes as d2 ON a.classifier = d2.xmi_id LEFT JOIN
		" . UML_SCHEMA . ".uml_classes c ON a.classifier = c.xmi_id LEFT JOIN
		" . UML_SCHEMA . ".stereotypes s ON c.stereotype_id = s.xmi_id
      WHERE
        uml_class_id = " . $class . "
    ";
    #output('<b>Get Attributes: </b>');
    #output('<pre>' . $sql . '</pre>');
    $result = pg_fetch_all(
      pg_query($db_conn, $sql)
    );
    if ($result == false) $result = array();
    return $result;
  }
  
  function getAttributeInfo($attribute) {
	global $db_conn;
	
	    $sql = "
      SELECT
        *
      FROM
        " . UML_SCHEMA . ".classes_attributes_types_gen
      WHERE
        attribute_id = " . $attribute['id'] . "
    ";
    #output('<b>Get AttributeInfos: </b>');
    #output('<pre>' . $sql . '</pre>');
    $result = pg_fetch_all(
      pg_query($db_conn, $sql)
    );
    if ($result == false) $result = array();
    return $result;  
  }

  function addColumnTable($tabname, $attribute, $comment = NULL) {
	$sql = "ALTER TABLE "  . CLASSES_SCHEMA . '.' . $tabname . "
ADD ";
	$sql .= createAttributeDefinition($attribute)."
";
	$sql .= 'COMMENT ON COLUMN '  . CLASSES_SCHEMA . '.' . $tabname . '."' . $attribute['name'] . '"'."
IS '" . $comment . 
"';
";
	#output('<br><b>Get addColumnTable: </b>');
    #output('<pre>' . $sql . '</pre>');
	return $sql;
  }

  function deleteColumnTable($tabname, $colname) {
	$sql = "
ALTER TABLE "  . CLASSES_SCHEMA . '.' . $tabname . "
DROP COLUMN ".$colname.";
";
	#output('<br><b>Get deleteColumnTable: </b>');
    #output('<pre>' . $sql . '</pre>');
	return $sql;
  }
  
  function createAttributeDefinition($attribute) {
    $sql = '"'.$attribute['name'].'"';
    $sql .= ' ' . createDataType($attribute['datatype']);
	#output('<br>Type AAAA: '.print_r($attribute));
    $sql .= ';';
    return $sql;
  }
 
  function createDataType($datatype) {
      $sql = '';
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
    if ($sql == '')
      $sql = 'text';
    return $sql;
  }
echo '</body>
</html>';
?>