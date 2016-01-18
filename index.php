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
          if (truncateChkbx == true) var truncate = "1";
      else var truncate = "0";
    
      var argoChkbx = document.getElementById("argo").checked;
          if (argoChkbx == true) var argo = "1";
      else var argo = "0";
    
      window.location = 'use-xmi2db.php?truncate=' + truncate + '&file=' + file + '&schema=' + schema + '&basepackage=' + basepkg + '&argo=' + argo;
    }
  </script>
  <title>XPlanung XMI2DB Converter</title>
  </head>
  <body>
  <div class="container">
    <h4>Dateiauswahl</h4>
    <select class="form-control" id="selectedFile" size="5"><?php
      $files = scandir('xmis');
      foreach ($files AS $i => $file) {
        if (strpos($file, '.xm')) ?><option value="xmis/<?php echo $file; ?>"<?php if ($i == 2) echo ' selected'; ?>><?php echo $file; ?></option><?php
      };
      ?>
    </select>
    
    <h4>Schemaauswahl/-eingabe</h4>
    <input type="text" id="schema" name="schema" list="schemaName"/>
    <datalist id="schemaName">
    <option value="xplan_uml" selected>xplan_uml</option>
    </datalist>
    
    <h4>BasePackageauswahl/-eingabe</h4>
    <i>Bei enem EA-xport unbedingt "XPlanGML 4.1" wählen, bei einem ArgoUML Export leer lassen oder ein Package eintragen, falls man nur das eine laden m&ouml;chte.</i>
    <input type="text" id="basepkg" name="basepkg" list="basepkgName"/>
    <datalist id="basepkgName">
    <option value="XPlanGML 4.1">XPlanGML 4.1</option>
    <option value="Raumordnungsplan_Kernmodell">Raumordnungsplan_Kernmodell</option>
    </datalist>
    <div class="checkbox">
    <label><input type="checkbox" value="checked" id="truncate" checked="checked">truncate</label>
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

