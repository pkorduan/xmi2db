<?php
include('conf/database_conf.php');
$db_conn = pg_connect(
	 "host=" . PG_HOST .
	" dbname=" . PG_DBNAME .
	" user=" . PG_USER .
	" password=" . PG_PASSWORD
) or exit (
	 "Es konnte keine Verbindung zum Datenbankserver hergestellt werden."
);

echo '<!DOCTYPE html>
<html lang="de">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	</head>
	<body>';
	$featureType = 'AX_Grundstueck_Bestandsnachweis'; ?>
	<b><?php echo $featureType; ?></b>
	<table><?php
	foreach (getAttributes($featureType, array()) AS $attributes) {
		$short_name = renameAttributeName($attributes);
		echo '<tr>';
		echo '<td>' . implode('_', $attributes) . '</td><td>';
		if (strlen($short_name) > 58)
			echo '<u>' .  $short_name . '</u>';
		else
			echo $short_name;
		echo '</td>';
		echo '</tr>';
	} ?>
</table>
<?php
/*****************************************************************************
* Funktionen
******************************************************************************/
function renameAttributeName($name) {
	$n = count($name);
	$return_name = $name[0];
	if ($n > 2) # füge den vorletzen hinzu wenn es mehr als zwei Namesteile sind
		$return_name .= '_' . $name[$n-2];
	if ($n > 1) # füge den letzten hinzu wenn es mehr als einer ist
		$return_name .= '_' . $name[$n-1];
	return $return_name;
}


function getAttributes($type, $path) {
	$return_attributes = array();
	$attributes = queryAttributes($type);
	foreach ($attributes AS $attribute) {
		$new_path = $path;
		array_push($new_path, $attribute['attribute_name']);
		if ($attribute['attribute_stereotype'] == 'datatype') {
			foreach (getAttributes($attribute['attribute_datatype'], $new_path) AS $child_attribute) {
				$return_attributes[] = $child_attribute;
			}
		}
		else {
			$return_attributes[] = $new_path;
		}
	}
	return $return_attributes;
}

function queryAttributes($class_name) {
	$sql = "
		SET search_path=aaa_uml, public;
		SELeCT
			c.class_name,
			c.attribute_name,
			c.attribute_datatype,
			lower(c.attribute_stereotype) attribute_stereotype
		FROM
			" . UML_SCHEMA . ".classes_with_attributes c
		WHERE
			c.class_name like '" . $class_name . "'
		ORDER by
			c.class_name,
			c.attribute_name
";

	$result = execSql($sql);
	return $result;
}

function execSql($sql) {
	global $db_conn;
#	echo '<br>' . $sql;
	$result = pg_fetch_all(
		pg_query($db_conn, $sql)
	);
	if ($result == false) $result = array();
	return $result;
}

echo '	</body>
	</html>';
?>