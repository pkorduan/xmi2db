<?php
	// +----------------------------------------------------------------------+
	// | uml2db                                                               |
	// | Ableitungen von Datenbankmodellen aus UML-Modellen im XMI-Format     |
	// +----------------------------------------------------------------------+
	// | Author: Peter Korduan <peter.korduan@gdi-service.de>                 |
	// | Licence: GPL https://www.gnu.org/licenses/gpl-3.0.de.html            |
	// +----------------------------------------------------------------------+
  include('conf/database_conf.php');
?><!DOCTYPE html>
<html lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.7.0/bootstrap-table.min.js"></script>
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.7.0/bootstrap-table.min.css">	
	<link rel="stylesheet" href="lib/pascoul/pascoul.css">
	
	<script src="lib/pascoul/pascoul.js"></script>
	
	<script language="javascript" type="text/javascript">
		function execXmi2Db() {
			var selectedConf = document.getElementById("selectedConf");
			var conf = selectedConf.options[selectedConf.selectedIndex].value;
		
			var selectedFile = document.getElementById("selectedFile");
			var file = selectedFile.options[selectedFile.selectedIndex].value;
			//alert(file);

			var selecteddbSchemaUML = document.getElementById("dbSchemaUML");
			var dbSchemaUML = selecteddbSchemaUML.options[selecteddbSchemaUML.selectedIndex].value;
			//var umlSchema = document.getElementById("xmi2db_umlSchema").value;
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

			//window.location = 'converter/xmi2db.php?truncate=' + truncate + '&file=' + file + '&schema=' + umlSchema + '&basepackage=' + basepkg + '&argo=' + argo;
			
			var args = {};
			args['url_params'] = new Array ();
			args['url_params']['url'] = "converter/xmi2db.php";
			args['url_params']['truncate'] = truncate;
			args['url_params']['schema'] = dbSchemaUML;
			args['url_params']['basepackage'] = basepkg;
			args['url_params']['file'] = file;
			args['url_params']['argo'] = argo;
			args['url_params']['conf'] = conf;
			
			//Styling the progress div/box
			args['html_params'] = new Array ();
			args['html_params']['divLogBoxStyle'] = "border:1px solid #000; padding:10px; width:300px; height:250px; overflow:auto; background:#eee;";
			args['html_params']['progressDivTitle'] = "Progress";
			args['html_params']['percentageSpanStyle'] = "text-align:right; display:block; margin-top:5px;";
			
			Pascoul.init(args);
			Pascoul.startTask();
		}

		function execDb2Classes() {
			var selectedConf = document.getElementById("selectedConf");
			var conf = selectedConf.options[selectedConf.selectedIndex].value;
			
			var umlSchema = document.getElementById("db2classes_umlSchema").value,
					gmlSchema = document.getElementById("db2classes_gmlSchema").value,
					createUserInfoColumns = document.getElementById('createUserInfoColumns').checked,
					url = 'converter/db2classes.php',
					params = [];
			params.push('conf=' + conf);
			
			if (umlSchema) params.push('umlSchema=' + umlSchema);
			if (gmlSchema) params.push('gmlSchema=' + gmlSchema);
			if (createUserInfoColumns) params.push('createUserInfoColumns=1');
			if (params.length > 0) url += '?';

			window.location = url + params.join('&');
		}

		function execDb2Ogr() {
			var selectedConf = document.getElementById("selectedConf");
			var conf = selectedConf.options[selectedConf.selectedIndex].value;
			
			var umlSchema = document.getElementById("db2ogr_umlSchema").value,
					ogrSchema = document.getElementById("db2ogr_ogrSchema").value;

			window.location = 'converter/db2ogr.php?umlSchema=' + umlSchema + '&ogrSchema=' + ogrSchema + '&conf=' + conf;
		}
		
		function test() {
			var selectedConf = document.getElementById("selectedConf");
			var conf = selectedConf.options[selectedConf.selectedIndex].value;

			window.location = 'converter/test.php?conf=' + conf;
		}
	</script>
	<title>UML to DB model</title>
	</head>
	<body>
	<div class="container">
		<h2>Ableitung von PostgreSQL-Datenbankmodellen aus UML-Modellen</h2>
		<?php echo VERSION; ?>
		<br>
		Gewählte Konfigurationsdatei:
		<select class="form-control" id="selectedConf">
			<option value="<?php echo CONF_FILE; ?>"><?php echo CONF_FILE; ?></option>
		</select>
		
		<h3>xmi2db</h3>
		xmi2db überträgt die UML-Modell Elemente der ausgewählten xmi Datei in das ausgewählte Datenbank Schema. Eingelesen werden nur die Elemente ab dem ausgewählten Basispaket.
		<h4>Gewählte Pakete</h4>
		<i>Folgende Pakete wurden laut <?php echo CONF_FILE; ?> ausgewählt:</i><br>
		<ul class="list-unstyled">
		<?php
			include(CONF_FILE);
			if (PACKAGES!='PACKAGES') {
				$packages = str_replace("'", "", PACKAGES);
				$packages = explode(";", $packages);
				foreach ($packages as $package) {
					echo '<li class="col-md-6">'.$package.'</li>';
				}
			}
			else echo "Keine Pakete gewählt!";
		?>
		</ul>
		<i><b>(Beachte: Hierchien sind unbedingt zu beachten bei der Angabe der Pakete in der database_conf.php! Das heißt: Möchte man Pakete in einem XPlan Modell auswählen, muss man das oberste Paket "XPlanGML 4.1" unbedingt mitangeben. Möchte man "BP_Bebauung" wählen, muss auch das Paket "Bebauungsplan" gewählt werden, da sich "BP_Bebauung" in "Bebauungsplan" befindet.)</b></i>
	</div>
	<div class="container">
		<h4>Dateiauswahl</h4>
		<i>Zur Auswahl weiterer Dateien diese vorher auf dem Server in das Unterverzeichnis xmis dieser Anwendung ablegen.</i><br>
		<select class="form-control" id="selectedFile"><?php
			$files = scandir('xmis');
			foreach ($files AS $i => $file) {
				$path_parts = pathinfo($file);
				if (!is_dir($file) and $path_parts['extension'] == 'xmi') { ?>
					<option value="../xmis/<?php echo $file; ?>" <?php if ($file == '2016-06-30_Modell_EA-xmi12-uml14.xmi') echo 'selected'; ?>><?php echo $file; ?></option><?php
				}
			} ?>
		</select>
		<h4>Schemaauswahl/-eingabe</h4>
		<i>Das Schema wird entsprechend der gewählten Konfiguration (laut database_conf.php) in der Datenbank "<?php echo PG_DBNAME; ?>" angelegt.</i><br>
		<select class="form-control" id="dbSchemaUML">
		<?php
			$schemas = str_replace("'", "", SCHEMAS);
			$schemas = explode(";", $schemas);
			foreach ($schemas as $schema) {
				echo '<option value="'.$schema.'_uml">'.$schema.'_uml</option>';
			}
		?>
		</select>
		<!-- wird das nochgebraucht?
		<input type="text" id="xmi2db_umlSchema" name="umlSchema" list="xmi2db_umlSchemaName" size="50"/ value="<?php //echo UML_SCHEMA; ?>">
		<datalist id="xmi2db_umlSchemaName">
			<option value="<?php //echo UML_SCHEMA; ?>" selected><?php //echo UML_SCHEMA; ?></option>
		</datalist>
		-->
		<h4>BasePackageauswahl/-eingabe</h4>
		<i>Bei einem EA-Export des XPlan-Modells "XPlanGML 4.1" wählen, bei einem ArgoUML Export leer lassen oder ein Package eintragen, falls man nur das eine laden möchte.</i>
		<input type="text" id="basepkg" name="basepkg" list="basepkgNameListe" size="50"/>
		<datalist id="basepkgNameListe">
			<option value="XPlanGML 4.1" selected>XPlanGML 4.1</option>
			<option value="Raumordnungsplan_Kernmodell">Raumordnungsplan_Kernmodell</option>
		</datalist>
		<!--input type="text" id="basepkg" name="basepkg" list="basepkgNameListe" value="XPlanGML 4.1"/>
		<datalist id="basepkgNameListe">
			<option value="XPlanGML 4.1">XPlanGML 4.1</option>
			<option value="Raumordnungsplan_Kernmodell">Raumordnungsplan_Kernmodell</option>
			<option value="">Alle Pakete</option>
		</datalist-->
		<div class="checkbox">
			<label><input type="checkbox" value="checked" id="truncate" checked="checked"> Tabellen vor dem Einlesen leeren</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" id="argo">Argo Export mit ISO19136 Profil</label>
		</div>
		Das Befüllen der Datenbank mit den Inhalten der XMI-Datei, insbesondere der tagged values, kann einige Minuten dauern!
		<div class="text-center" id="queryButton">
		<button type="submit" class="btn btn-primary btn-sm" id="execXmi2Db" onclick="execXmi2Db()">
			<span class="glyphicon glyphicon-ok"> </span> Fülle DB mit XMI Inhalten</button>
		<!--<button type="submit" class="btn btn-primary btn-sm" id="testBtn" onclick="test()">
			<span class="glyphicon glyphicon-ok"> </span> Test</button>-->
		<button type="button" class="btn btn-danger btn-sm" id="cancelXmi2Db" onclick="Pascoul.stopTask();">
			<span class="glyphicon glyphicon-remove"> </span> Abbrechen</button>
		</div>

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
		<select class="form-control" id="db2classes_umlSchema">
		<?php
			$schemas = str_replace("'", "", SCHEMAS);
			$schemas = explode(";", $schemas);
			foreach ($schemas as $schema) {
				echo '<option value="'.$schema.'_uml">'.$schema.'_uml</option>';
			}
		?>
		</select>
		<!--
		<input type="text" id="db2classes_umlSchema" name="umlSchema" list="db2classes_umlSchemaListe" size="50" value="<?php //echo UML_SCHEMA; ?>"/>
		<datalist id="db2classes_umlSchemaListe">
			<option value="<?php //echo UML_SCHEMA; ?>" selected><?php //echo UML_SCHEMA; ?></option>
		</datalist>
		-->
		
		<h4>GML-Klassenschema</h4>
		<i>Das Schema in dem die GML-Tabellen und Datentypen angelegt werden sollen.</i><br>
		<select class="form-control" id="db2classes_gmlSchema">
		<?php
			$schemas = str_replace("'", "", SCHEMAS);
			$schemas = explode(";", $schemas);
			foreach ($schemas as $schema) {
				echo '<option value="'.$schema.'_gml">'.$schema.'_gml</option>';
			}
		?>
		</select>
		<!--
		<input type="text" id="db2classes_gmlSchema" name="gmlSchema" list="db2classes_gmlSchemaListe" size="50" value="<?php //echo CLASSES_SCHEMA; ?>"/>
		<datalist id="db2classes_gmlSchemaListe">
			<option value="<?php //echo CLASSES_SCHEMA; ?>" selected><?php //echo CLASSES_SCHEMA; ?></option>
		</datalist>
		-->
		
		<div class="checkbox">
			<label><input type="checkbox" id="createUserInfoColumns"> Spalten für user_id, created_at, updated_at und konvertierung_id an alle FeatureType-Tabellen anhängen.</label>
		</div>

		<div class="text-center" id="queryButton">
		<button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="execDb2Classes()"><span class="glyphicon glyphicon-ok"> </span> Erzeuge GML-Klassenschema</button>
		</div>

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
		<select class="form-control" id="db2ogr_umlSchema">
		<?php
			$schemas = str_replace("'", "", SCHEMAS);
			$schemas = explode(";", $schemas);
			foreach ($schemas as $schema) {
				echo '<option value="'.$schema.'_uml">'.$schema.'_uml</option>';
			}
		?>
		</select>
		
		<!--
		<input type="text" id="db2ogr_umlSchema" name="umlSchema" list="db2ogr_umlSchemaListe" size="50" value="<?php //echo UML_SCHEMA; ?>"/>
		<datalist id="db2ogr_umlSchemaListe">
			<option value="<?php //echo UML_SCHEMA; ?>" selected><?php //echo UML_SCHEMA; ?></option>
		</datalist>
		-->
		
		<h4>OGR-Schema</h4>
		<i>Das Schema in dem die GML-Tabellen und Datentypen angelegt werden sollen.</i><br>
		<select class="form-control" id="db2ogr_ogrSchema">
		<?php
			$schemas = str_replace("'", "", SCHEMAS);
			$schemas = explode(";", $schemas);
			foreach ($schemas as $schema) {
				echo '<option value="'.$schema.'_ogr">'.$schema.'_ogr</option>';
			}
		?>
		</select>
		<!--
		<input type="text" id="db2ogr_ogrSchema" name="ogrSchema" list="db2ogr_ogrSchemaListe" size="50" value="<?php //echo OGR_SCHEMA; ?>"/>
		<datalist id="db2ogr_ogrSchemaListe">
			<option value="<?php //echo OGR_SCHEMA; ?>" selected><?php //echo OGR_SCHEMA; ?></option>
		</datalist>
		-->
		
		<div class="text-center" id="queryButton">
		<button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="execDb2Ogr()"><span class="glyphicon glyphicon-ok"> </span> Erzeuge OGR-Schema</button>
		</div>	
	</div>
	<script language="javascript" type="text/javascript">
		$("#dbSchemaUML").change(function(){
			var schemaUML = $(this).val();
			var schemaOGR = schemaUML.substr(0,schemaUML.lastIndexOf("_")+1) + 'ogr';
			var schemaGML = schemaUML.substr(0,schemaUML.lastIndexOf("_")+1) + 'gml';
			$("#db2classes_umlSchema").val(schemaUML);
			$("#db2ogr_umlSchema").val(schemaUML);
			$("#db2ogr_ogrSchema").val(schemaOGR);
			$("#db2classes_gmlSchema").val(schemaGML);
		});
	</script>
	</body>
</html>