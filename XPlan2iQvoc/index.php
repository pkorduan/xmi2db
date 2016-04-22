<html>
  <head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.7.0/bootstrap-table.min.js"></script>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.7.0/bootstrap-table.min.css">  
    
    <script language="javascript" type="text/javascript">
            function exefunction(){
        var selectedSchema = document.getElementById("selectedSchema");
        var schema = selectedSchema.options[selectedSchema.selectedIndex].value;
        
        window.location = 'xplan2iqvoc.php?schema=' + schema;
            }
        </script>
    <title>XPlanung XMI2DB Converter</title>
  </head>
    <body>
    <div class="container">
      <h4>Schemaauswahl</h4>
      <?
      include( dirname(__FILE__) . "/../conf/database_conf.php");
      //Set DB connection
      //$xmi2db->setConn("dbname=moro user=kvwmap password=kv_Map-12:)");
      //$xmi2db->setConn($db_conn);
      //Create Schema and Table structure if needed
      $schema_sql = "SELECT schema_name FROM information_schema.schemata ORDER BY schema_name";
      //echo $schema_sql;
      //echo "<br>";
      $result = pg_query($db_conn, $schema_sql);
      echo '<select class="form-control" id="selectedSchema" size="15">';
      while ($row = pg_fetch_array($result)){      
        echo '<option value="'.$row[0].'">'.$row[0].'</option>';    
      }
      echo '</select>';
      //print_r($new_array);
      ?>
      
      <!--
      <h4>Schemaauswahl/-eingabe</h4>
      <input type="text" id="schema" name="schema" list="schemaName"/>
      <datalist id="schemaName">
        <option value="xplan_eatest">xplan_eatest</option>
        <option value="xplan_argotest">xplan_argotest</option>
        <option value="xplan_model">xplan_model</option>
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
      -->
      <div class="text-center" id="queryButton">
        <!--<button type="submit" class="btn btn-primary btn-sm" id="queryNERC" onclick="document.location.href='use-xmi2db.php?truncate=1&file=xplanerweitert20150609.xmi&schema=xplan_argotest&basepackage=Raumordnungsplan_Kernmodell'"><span class="glyphicon glyphicon-ok"> </span> Suche passende Begriffe</button>-->
        <button type="submit" class="btn btn-primary btn-sm" onclick="exefunction()"><span class="glyphicon glyphicon-ok"> </span> Erzeuge CSV für JSKOSify</button>
      </div>
    </div>
  </body>
</html>

