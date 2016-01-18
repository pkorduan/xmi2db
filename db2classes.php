<?php
  /*****************************************************************************
  * 
  ******************************************************************************/
  include( dirname(__FILE__) . "/conf/database_conf.php");

  $sql = 'SET search_path = gml_classes, public;
DROP SCHEMA gml_classes CASCADE;
CREATE SCHEMA gml_classes;
';
  # Lade oberste Klassen
  $topClasses = getTopClasses();
  
  # Für alle oberen Generalisierungen
  foreach($topClasses as $topClass) {
    output('<br><b>TopKlasse: ' . $topClass['name'] . '</b> (' . $topClass['xmi_id'] . ')');
    $sql .= createTableDefinition(null, $topClass);
  }
?>
<pre><?php
  echo $sql;
?></pre>
<?php
  /*****************************************************************************
  * Funktionen
  ******************************************************************************/
  function output($text) {
    $debug = false;
    if ($debug) {
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
        xplan_model.uml_classes c LEFT JOIN
        xplan_model.stereotypes s ON c.stereotype_id = s.xmi_id
      WHERE
        general_id = '-1' AND
        s.name = 'FeatureType'
    ";
    output($sql);
    $result = pg_fetch_all(
      pg_query($db_conn, $sql)
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
        xplan_model.class_generalizations g LEFT JOIN
        xplan_model.uml_classes p ON g.parent_id = p.xmi_id LEFT JOIN
        xplan_model.uml_classes c ON g.child_id = c.xmi_id
      WHERE
        p.xmi_id = '" . $class['xmi_id'] . "'
    ";
    output($sql);
    $result = pg_fetch_all(
      pg_query($db_conn, $sql)
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
        a.multiplicity_range_lower,
        a.multiplicity_range_upper
      FROM
        xplan_model.uml_attributes a LEFT JOIN
        xplan_model.datatypes d ON a.datatype = d.xmi_id LEFT JOIN
        xplan_model.uml_classes c ON a.classifier = c.xmi_id LEFT JOIN
        xplan_model.stereotypes s ON c.stereotype_id = s.xmi_id
      WHERE
        uml_class_id = " . $class['id'] . "
    ";
    output($sql);
    $result = pg_fetch_all(
      pg_query($db_conn, $sql)
    );
    if ($result == false) $result = array();
    return $result;
  }

  function createDataType($datatype, $classifier_stereotype, $multiplicity) {
    if ($datatype != '') {
      switch (true) {
        case ($datatype == 'CharacterString'):
          $sql = 'character varying';
          break;
        case in_array($datatype, array(
            'Angle',
            'Length',
            'Decimal'
          )):
          $sql = 'double precision';
          break;
        default:
          $sql = strtolower($type);
      }
    }
    else {
      switch ($classifier_stereotype) {
        case 'Enumeration':
          $sql = 'character varying';
          break;
        case 'CodeList':
          $sql = 'integer';
          break;
        case 'DataType':
          $sql = 'uuid';
          break;
        case 'Union':
          $sql = 'geometry';
          break;
        default:
          $sql = 'text';
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
    return $sql;
  }

  function createAttributeComment($class_name, $attribute_name, $classifier, $classifier_stereotype) {
    $sql = "
COMMENT ON COLUMN " . strtolower($class_name) . "." . $attribute_name . " IS '" . $classifier_stereotype . " " . $classifier . "';";
    return $sql;
  }

  function createTableDefinition($superClass, $class) {
    # Erzeuge Create Table Statement

    $sql = "CREATE TABLE IF NOT EXISTS " . strtolower($class['name']) . " (";
    if ($superClass == null) {
      $sql .= "
  gml_id uuid NOT NULL DEFAULT uuid_generate_v5(uuid_ns_url(), 'http://xplan-raumordnung.org') primary key,
  ";
    }

    # lade Attribute
    $attributes = getAttributes($class);

    # für jedes Attribut erzeuge Attributzeilen
    foreach($attributes AS $i => $attribute) {
      if ($i > 0) {
        $sql .= ",
  ";
      }
      $sql .= createAttributeDefinition($attribute);
    }

    $sql .= '
)';
    if ($superClass != null) {
      # leite von superClass ab
      $sql .= '
INHERITS ('. strtolower($superClass['name']) . ')';
    }
    $sql .= ';';
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
    output($sql);
    
    # lade abgeleitete Klassen
    $subClasses = getSubClasses($class);
    # Für alle abgeleiteten Klassen
    foreach($subClasses as $subClass) {
      output('<br><b>SubKlasse: ' . $subClass['name'] . '</b> (' . $subClass['xmi_id'] . ')');
      $sql .= createTableDefinition($class, $subClass);
    }

    return $sql;
  }
?>