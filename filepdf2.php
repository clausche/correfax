<?php
      $conexion = mysql_connect("localhost", "root", "zz");
      mysql_select_db("demo", $conexion);

      $queEmp = "SELECT * FROM empresa ORDER BY nombre ASC";
      $resEmp = mysql_query($queEmp, $conexion) or die(mysql_error());
      $totEmp = mysql_num_rows($resEmp);


      if ($totEmp> 0) {

         while ($rowEmp = mysql_fetch_assoc($resEmp)) {

            echo "<strong>".$rowEmp['nombre']."</strong><br>";

            echo "Direccion: ".$rowEmp['direccion']."<br>";

            echo "Telefono: ".$rowEmp['telefono']."<br><br>";

         }

      }




?>
