<?php
// +----------------------------------------------------------------------+
// | uml2db                                                               |
// | Ableitungen von Datenbankmodellen aus UML-Modellen im XMI-Format     |
// +----------------------------------------------------------------------+
// | Author: Peter Korduan <peter.korduan@gdi-service.de>                 |
// | Licence: GPL https://www.gnu.org/licenses/gpl-3.0.de.html            |
// +----------------------------------------------------------------------+
?><!DOCTYPE html>
<html lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.7.0/bootstrap-table.min.js"></script>
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.7.0/bootstrap-table.min.css">	
	
	<script language="javascript" type="text/javascript">
		function exefunction() {
			var selectedFile = document.getElementById("selectedFile");
			var file = selectedFile.options[selectedFile.selectedIndex].value;
			//alert(file);

			var schema = document.getElementById("schema").value;
			//alert(schema);

			var basepkg = document.getElementById("basepkg").value;
			//alert(basepkg);

			var truncateChkbx = document.getElementById("truncate").checked;
			if (truncateChkbx == true)
				var truncate = "1";
			else
				var truncate = "0";

			var argoChkbx = document.getElementById("argo").checked;
			if (argoChkbx == true)
				var argo = "1";
			else
				var argo = "0";

			window.location = 'converter/xmi2db.php?truncate=' + truncate + '&file=' + file + '&schema=' + schema + '&basepackage=' + basepkg + '&argo=' + argo;
		}
		function execDb2Classes() {
			var umlSchema = document.getElementById("umlSchema").value,
					gmlSchema = document.getElementById("gmlSchema").value;

			window.location = 'converter/db2classes.php?umlSchema=' + umlSchema + '&gmlSchema=' + gmlSchema;
		}
		function execDb2Ogr() {
			var umlSchema = document.getElementById("umlSchema").value,
					ogrSchema = document.getElementById("ogrSchema").value;

			window.location = 'converter/db2ogr.php?umlSchema=' + umlSchema + '&ogrSchema=' + ogrSchema;
		}
	</script>
	<title>UML to DB model</title>
	<?php include('conf/database_conf.php'); ?>
	</head>
	<body>
	<div class="container">
		<h2>Ableitung von PostgreSQL-Datenbankmodellen aus UML-Modellen</h2>
		<h3>xmi2db</h3>
		xmi2db überträgt die UML-Modell Elemente der ausgewählten xmi Datei in das ausgewählte Datenbank Schema. Eingelesen werden nur die Elemente ab dem ausgewählten Basispacket.
	</div>
	<div class="container">
		<h4>Dateiauswahl</h4>
		<i>Zur Auswahl weiterer Dateien diese vorher auf dem Server in das Unterverzeichnis xmis dieser Anwendung ablegen.</i><br>
		<select class="form-control" id="selectedFile"><?php
			$files = scandir('xmis');
			foreach ($files AS $i => $file) {
				$path_parts = pathinfo($file);
				if (!is_dir($file) and $path_parts['extension'] == 'xmi') { ?>
					<option value="xmis/<?php echo $file; ?>"><?php echo $file; ?></option><?php
				}
			} ?>
		</select>

		<h4>Schemaauswahl/-eingabe</h4>
		<i>Das Schema wird entsprechend der gewählten Konfiguration in der Datenbank "<?php echo $db_name; ?>" angelegt.</i><br>
		<input type="text" id="schema" name="schema" list="schemaName" size="50"/>
		<datalist id="schemaName">
			<option value="xplan_uml" selected>xplan_uml</option>
		</datalist>
		
		<h4>BasePackageauswahl/-eingabe</h4>
		<i>Bei einem EA-Export dex XPlan-Modells "XPlanGML 4.1" wählen, bei einem ArgoUML Export leer lassen oder ein Package eintragen, falls man nur das eine laden möchte.</i>
		<input type="text" id="basepkg" name="basepkg" list="basepkgName"/>
		<datalist id="basepkgName">
		<option value="XPlanGML 4.1">XPlanGML 4.1</option>
		<option value="Raumordnungsplan_Kernmodell">Raumordnungsplan_Kernmodell</option>
		</datalist>
		<div class="checkbox">
			<label><input type="checkbox" value="checked" id="truncate" checked="checked"> Tabellen vor dem Einlesen leeren</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" id="argo">Argo Export mit ISO19136 Profil</label>
		</div>
		Das Befüllen der Datenbank mit den Inhalten der XMI-Datei insbesondere der tagged values kann einige Minuten dauern!
		<div class="text-center" id="queryButton">
		<button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="exefunction()">
			<span class="glyphicon glyphicon-ok"> </span> Fülle DB mit XMI Inhalten</button>
		</div>
	</div>
	<div class="container">
		<h3>db2classes</h4>
		db2classes erzeugt ein GML-Klassenschema an Hand der mit xmi2db eingelesenen UML-Modell-Elemente.
		Das GML-Klassenschema enthält nach dem Ausführen des erzeugten SQL im ausgewählten Schema je
		<ul>
			<li>je einen PostgreSQL Enum-DataType pro Enumeration. Die Aufzählung enthält die Werte, nicht die Beschreibungen aus dem UML-Model.</li>
			<li>je eine Tabelle für Enumerations, wenn diese Beschreibungen enthält. Die Tabellennamen werden mit Perfix "enum_" versehen und befüllt mit wert und beschreibung aus dem UML-Modell.</li>
			<li>je eine leere Tabelle pro FeatureType</li>
			<li>FeatureType-Tabellen haben Attribute für die Assoziationen</li>
			<li>FeatureType-Attribute mit Kardinalität > 1 sind Arrays</li>
			<li>je eine mit den Werten befüllte Tabelle pro CodeListe (falls im UML-Modell enthalten)</li>
			<li>je einen nutzerdefinierten Postgres Datentyp pro UML DataType</li>
			<li>je eine Tabelle pro n:m Beziehung. Die Namen setzen sich aus den beteiligten Tabellen getrennt mit "_zu_" zusammen</li>
		</ul>
	</div>
	<div class="container">
		<h4>UML-Schema</h4>
		<i>Das Schema in dem vorher die UML-Elemente mit xmi2db eingelesen wurden.</i><br>
		<input type="text" id="umlSchema" name="umlSchema" list="umlSchemaListe" size="50" value="<?php echo UML_SCHEMA; ?>"/>
		<datalist id="umlSchemaListe">
			<option value="<?php echo UML_SCHEMA; ?>" selected><?php echo UML_SCHEMA; ?></option>
		</datalist>

		<h4>GML-Klassenschema</h4>
		<i>Das Schema in dem die GML-Tabellen und Datentypen angelegt werden sollen.</i><br>
		<input type="text" id="gmlSchema" name="gmlSchema" list="gmlSchemaListe" size="50" value="<?php echo CLASSES_SCHEMA; ?>"/>
		<datalist id="gmlSchemaListe">
			<option value="<?php echo CLASSES_SCHEMA; ?>" selected><?php echo CLASSES_SCHEMA; ?></option>
		</datalist>
		
		<div class="text-center" id="queryButton">
		<button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="execDb2Classes()"><span class="glyphicon glyphicon-ok"> </span> Erzeuge GML-Klassenschema</button>
		</div>
	</div>
	<div class="container">
		<h3>db2ogr</h4>
		db2ogr erzeugt aus dem UML-Modell ein flaches GML-Schema welches zum Einlesen von komplexen GML-Dateien mit ogr2ogr geeignet sein sollte. Die Tabellen der FeatureTypen enthalten alle Attribute der abgeleiteten Klassen und der verzweigenden komplexen Datentypen. Das Schema enthält nach dem Ausführen des erzeugten SQL im ausgewählten Schema je
		<ul>
			<li>eine mit den Werten befüllte Tabelle pro Enumeration</li>
			<li>eine leere Tabelle pro FeatureType</li>
			<li>eine mit den Werten befüllte Tabelle pro CodeListe (falls im UML-Modell enthalten)</li>
		</ul>
	</div>
	<div class="container">
		<h4>UML-Schema</h4>
		<i>Das Schema in dem vorher die UML-Elemente mit xmi2db eingelesen wurden.</i><br>
		<input type="text" id="umlSchema" name="umlSchema" list="umlSchemaListe" size="50" value="<?php echo UML_SCHEMA; ?>"/>
		<datalist id="umlSchemaListe">
			<option value="<?php echo UML_SCHEMA; ?>" selected><?php echo UML_SCHEMA; ?></option>
		</datalist>

		<h4>OGR-Schema</h4>
		<i>Das Schema in dem die GML-Tabellen und Datentypen angelegt werden sollen.</i><br>
		<input type="text" id="ogrSchema" name="ogrSchema" list="ogrSchemaListe" size="50" value="<?php echo OGR_SCHEMA; ?>"/>
		<datalist id="ogrSchemaListe">
			<option value="<?php echo OGR_SCHEMA; ?>" selected><?php echo OGR_SCHEMA; ?></option>
		</datalist>
		
		<div class="text-center" id="queryButton">
		<button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="execDb2Ogr()"><span class="glyphicon glyphicon-ok"> </span> Erzeuge OGR-Schema</button>
		</div>	
	</div>
	</body>
</html>

