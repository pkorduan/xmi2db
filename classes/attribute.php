<?php
class Attribute {

  function __construct($name, $datatype, $parent = '', $parts = array(), $not_null = false, $default = '', $comment = '') {
    $this->alias = $name;
    $this->name = $this->getName($name);
    $this->brackets = '';
    $this->parent = $parent;
    $this->parts = parts;
    $this->datatype = strtolower(substr($datatype, 0, PG_MAX_NAME_LENGTH));
    $this->datatype_alias = $datatype;
    $this->attribute_type = '';
    $this->stereotype = '';
    $this->stereotype_alias = '';
    $this->multiplicity = '';
    $this->multiplicity_lower = '';
    $this->multiplicity_upper = '';
    $this->database_type = '';

    $this->database_type_alias = $database_type; # langer Name
    $this->database_type = strtolower(substr($database_type, 0, 58)); # verkürzter Name

    $this->not_null = $not_null;
    $this->default = $default;
    $this->comment = $comment;
    $this->attributes_name = '';
    $this->path_name = '';
    $this->frequency = 0;
  }

  public static function getName($name) {
    return strtolower(substr($name, 0, PG_MAX_NAME_LENGTH));
  }

  function setNameFromParts() {
    $this->path_name = implode(
      '_',
      array_map(
        function($part) {
          return $part->parent->alias . '_' . $part->alias;
        },
        $this->parts
      )
    );

    $this->attributes_name = implode(
      '_',
      array_map(
        function($part) {
          return $part->name;
        },
        $this->parts
      )
    );

    $this->short_name = end($this->parts)->name;
  }

  function getComment($table_name) {
    $sql = "
COMMENT ON COLUMN " . $table_name . "." . $this->name . " IS '";
    $sql .= trim($this->alias . ' ' . $this->stereotype_alias . ' ' . $this->datatype_alias);
    $sql .= ' ' . $this->multiplicity;
    $sql .= "';";
    return $sql;
  }

  function getAttributePath() {
    $parts = $this->parts;
    $attribute_path = $parts[0]->alias;
    array_shift($parts);
    if (!empty($parts)) {
      $attribute_path .= '|' . implode(
        '|',
        array_map(
          function($part) {
            return $part->parent->alias . '|' . $part->alias;
          },
          $parts
        )
      );
    }
    return $attribute_path;
  }

  function getFlattenedComment($table_name) {
    $attribute_path = $this->getAttributePath();
    $sql = "
COMMENT ON COLUMN " . $table_name . "." . $this->short_name . " IS '";
    $sql .= $attribute_path . ' ' . $this->stereotype_alias . ' ' . $this->datatype_alias;
    $sql .= ' ' . $this->multiplicity;
    $sql .= "';";
    return $sql;
  }

  function setMultiplicity($lower, $upper) {
    $this->multiplicity_lower = ($lower == '-1' OR intval($lower) > 1) ? '*' : $lower;
    $this->multiplicity_upper = ($upper == '-1' OR intval($upper) > 1) ? '*' : $upper;
    if ($this->multiplicity_lower == $this->multiplicity_upper)
      $this->multiplicity = $this->multiplicity_lower;
    else {
      if (empty($this->multiplicity_lower))
        $this->multiplicity_lower = '0';
      $this->multiplicity = $this->multiplicity_lower . '..' . $this->multiplicity_upper;
    }
    return $this->multiplicity;
  }

  function setStereoType($stereotype) {
    $this->stereotype = strtolower(substr($stereotype, 0, PG_MAX_NAME_LENGTH));
    $this->stereotype_alias = $stereotype;
  }

  function get_database_type($with_enum_type = true, $with_codelist_type = true) {
    $database_type = $this->datatype;
    $defined_types = array(
      'datatype'
    );

    if ($with_enum_type) {
      $defined_types[] = 'enumeration';
    }

    if (in_array($this->stereotype, $defined_types)) {
      $database_type = $this->datatype;
    }
    else {
      switch (true) {
        # text
        case in_array($this->datatype, array(
            '',
            'featurecollection',
            'li_lineage',
            'sequence',
            'genericname',
            'query',
            'transaction',
            'dq_result',
            'md_identifier',
            'ci_citation'
          )) :
          $database_type = 'text';
        break;

        # character varying
        case in_array($this->datatype, array(
            'characterstring',
            '<undefined>',
            'uri'
          )) :
          $database_type = 'character varying';
        break;
        
        # external datatypes
        case in_array($this->datatype, array(
            'sc_crs',
            'doublelist',
            'measure',
            'dq_evaluationmethodtypecode'
          )) :
          $database_type = 'character varying';
        break;

        # enumerations from stereotype
        case ($this->stereotype == 'enumeration') :
          if ($this->name == 'advstandardmodell') {
            #echo '<br>attribut: ' . $this->name . ' datatype: ' . $this->datatype . ' stereotype: ' . $this->stereotype . ' parent: ' . $this->parent->name . ' ogrSchema: ' . $this->parent->ogrSchema->schemaName . ' num enumerations: ' . count($this->parent->ogrSchema->enumerations);
            # . ' enumtype: ' . $enumType->name . ' wert typ: ' . $enumType->getWertType() . ' database type: ' . $database_type;
          }
          if ($with_enum_type) {
            $database_type = $this->datatype;
          }
          else {
            if (!empty($this->parent->ogrSchema->schemaName)) {
              $enumType = $this->parent->ogrSchema->enumerations[$this->datatype];
              $database_type = (empty($enumType)) ? 'character varying' : $enumType->getWertType();
            }
          }
        break;

        # enumerations from datatype
        case in_array($this->datatype, array(
            'enumeration',
            'enum'
          )) :
          $database_type = 'character varying';
        break;

        # code list from stereotype
        case ($this->stereotype == 'codelist') :
          if ($with_codelist_type) {
            $database_type = $this->datatype;
          }
          else {
            $database_type = 'text';
          }
        break;

        # date
        case in_array($this->datatype, array(
            'date',
            'tm_duration'
          )) :
          $database_type = 'date';
        break;

        # datetime
        case in_array($this->datatype, array(
            'datetime'
          )) :
          $database_type = 'timestamp without time zone';
        break;

        # integer
        case in_array($this->datatype, array(
            'integer',
            'int',
            'codelist'
          )):
          $database_type = 'integer';
        break;

        # boolean
        case ($this->datatype == 'boolean'):
          $database_type = 'character varying';
        break;

        # double precision
        case in_array($this->datatype, array(
            'angle',
            'length',
            'decimal',
            'volume',
            'area',
            'real',
            'distance'
          )):
          $database_type = 'double precision';
        break;

        # uuid
        case in_array($this->datatype, array(
            'datatype'
          )):
          $database_type = 'uuid';
        break;

        # geometry
        case in_array($this->datatype, array(
            'gm_object',
            'union',
            'xp_variablegeometrie'
          )):
          $database_type = 'geometry';
        break;

        case in_array($this->datatype, array(
            'gm_point',
            'directposition'
          )):
          $database_type = 'geometry(POINT)';
        break;

        case in_array($this->datatype, array(
            'gm_multipoint',
            'xp_punktgeometrie'
          )):
          $database_type = 'geometry(MULTIPOINT)';
        break;

        case ($this->datatype == 'gm_curve'):
          if (LINESTRING_AS_GEOMETRY)
            $database_type = 'geometry';
          else
            $database_type = 'geometry(LINESTRING)';
        break;

        case in_array($this->datatype, array(
            'gm_multicurve',
            'xp_liniengeometrie',
            'gm_compositecurve'
          )):
          if (LINESTRING_AS_GEOMETRY)
            $database_type = 'geometry';
          else
            $database_type = 'geometry(MULTILINESTRING)';
        break;

        case ($this->datatype == 'gm_surface'):
          $database_type = 'geometry(POLYGON)';
        break;

        case in_array($this->datatype, array(
            'gm_triangulatedsurface',
            'xp_flaechengeometrie',
            'gm_solid',
            'gm_compositesolid',
            'gm_multisurface'
          )):
          $database_type = 'geometry(MULTIPOLYGON)';
        break;
      } # end of switch
    }

    if ($database_type == '')
      $database_type = 'text';

    $this->database_type = $database_type;
    return $database_type;
  }
  
  function get_gfs_type($database_type, $brackets){
    if($brackets == '[]')$list = 'List';
    switch (true){
      case in_array($database_type, array(
          'character varying',
          'text',
          'date',
          'timestamp without time zone'
        )):
        $gfs_type = 'String';
      break;
      
      case in_array($database_type, array(
          'integer'
        )):
        $gfs_type = 'Integer';
      break;
      
      case in_array($database_type, array(
          'double precision'
        )):
        $gfs_type = 'Real';
      break;
			
			case ($database_type ==	'geometry'):
				$gfs_type = '0';
			break;

			case ($database_type ==	'geometry(POINT)'):
				$gfs_type = '1';
			break;
			
			case ($database_type == 'geometry(LINESTRING)'):
				$gfs_type = '2';
			break;			
			
			case ($database_type == 'geometry(POLYGON)'):
				$gfs_type = '3';
			break;			

			case ($database_type == 'geometry(MULTIPOINT)'):
				$gfs_type = '4';
			break;

			case ($database_type == 'geometry(MULTILINESTRING)'):
				$gfs_type = '5';
			break;

			case ($database_type == 'geometry(MULTIPOLYGON)'):
				$gfs_type = '6';
			break;
    }
    return $gfs_type.$list;
  }
  
  function getBrackets() {
    $brackets = false;
    if (is_array($this->parts) and !empty($this->parts)) {
      $brackets = in_array(
        '[]',
        array_map(
          function($attribute) {
            return ($attribute->multiplicity_upper == '*' OR $attribute->multiplicity == '*' OR intval($attribute->multiplicity) > 1) ? '[]' : '';
          },
          $this->parts
        )
      );
    }
    else {
      $brackets = ($this->multiplicity_upper == '*' OR $this->multiplicity == '*' OR intval($this->multiplicity) > 1);
    }
    return $brackets ? '[]' : '';
  }

  /*
  * Ausgabe NOT NULL
  */
  function getNotNull() {
    if ($this->not_null) {
      # Wenn NOT NULL explizit beim Anlegen des Attributes gesetzt wurde
      $not_null = $this->not_null;
    }
    else {
      if (is_array($this->parts) and !empty($this->parts)) {
        # Ermittle NOT NULL aus Multipliziät des Attributes und seiner Vorgänger
        # Nur wenn alle Attribute im Pfad die Kardinaltität > 0 haben,
        # darf das Blattelement auf NOT NULL gesetzt werden.
#        $not_null = true;
#        foreach($this->parts AS $attribute) {
#          if (intval($attribute->multiplicity_lower) == 0) {
#            $not_null = false; # Attribut darf NULL sein.
#          }
#        }
    
        $not_null = !in_array(
          false,
          array_map(
            function($attribute) {
              return intval($attribute->multiplicity_lower) > 0;
            },
            $this->parts
          )
        );
      }
      else {
        # Ermittle NOT NULL nur aus multiplicity_lower des Attributes
        $not_null = (intval($this->multiplicity_lower) > 0);
      }
    }
    return ($not_null ? ' NOT NULL' : '');
  }

  function asSql($class_type) {
    $sql = "  " . $this->name . " " . $this->get_database_type() . $this->getBrackets();
    if ($class_type == 'table')
      $sql .= $this->getNotNull();

    # Ausgabe DEFAULT
    if ($this->default != '')
      $sql .= ' DEFAULT ' . $this->default;

    if ($this->name == 'genauigkeitsstufe')
      $sql .= '<br>--hier asSQL verwendet.';
    return $sql;
  }

  function asFlattenedSql() {
    #if (substr($this->get_database_type(), 0, 8) == 'geometry' and $this->name <> 'wkb_geometry') echo '<br>Klasse: ' . $this->parent->name . ' Geometriespalte: ' . $this->name . ' type: ' . $this->get_database_type();

    $sql = "  " .
      $this->short_name . " " . $this->get_database_type(false, false) . $this->getBrackets() . $this->getNotNull();

		if ($this->stereotype == 'enumeration' and $this->short_name != $this->datatype) {
		#	 $sql .= ' -- datatype: ' . $this->datatype . ' stereotype: ' . $this->stereotype;
		}

    # Ausgabe DEFAULT
    if ($this->default != '')
      $sql .= ' DEFAULT ' . $this->default;

    return $sql;
  }

  function asGfs() {
		if($this->short_name != 'wkb_geometry'){
			$elements = explode('|', $this->getAttributePath());
			if($elements[0] == 'zeigtAufExternes')$elements[0] = 'zeigtAufExternes_';
			array_pop($elements);
			$elements[] = $this->short_name;
			$gfs = "
      <PropertyDefn>
        <Name>".$this->short_name."</Name>
        <ElementPath>".implode('|', $elements)."</ElementPath>
        <Type>".$this->get_gfs_type($this->get_database_type(false, false), $this->getBrackets())."</Type>
      </PropertyDefn>";
			return $gfs;
		}
  }
}
?>