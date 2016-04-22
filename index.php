<!DOCTYPE html>
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

      window.location = 'use-xmi2db.php?truncate=' + truncate + '&file=' + file + '&schema=' + schema + '&basepackage=' + basepkg + '&argo=' + argo;
    }
  </script>
  <title>XPlanung XMI2DB Converter</title>
  <?php include('conf/database_conf.php'); ?>
  </head>
  <body>
  <div class="container">
    <h3>xmi2db</h4>
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
    <div class="text-center" id="queryButton">
    <!--<button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="document.location.href='use-xmi2db.php?truncate=1&file=xplanerweitert20150609.xmi&schema=xplan_argotest&basepackage=Raumordnungsplan_Kernmodell'"><span class="glyphicon glyphicon-ok"> </span> Suche passende Begriffe</button>-->
    <button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="exefunction()"><span class="glyphicon glyphicon-ok"> </span> Fülle DB mit XMI Inhalten</button>
    </div>
  </div>
  </body>
</html>

