<?php
include ("../jpgraph.php");
include ("../jpgraph_pie.php");

$db = mysql_connect("localhost", "root","zz") ;

mysql_select_db("becarios",$db);

// Some data
//$data = array(40,21,17,14,23);

// Create the Pie Graph. 
$graph = new PieGraph(450,300,"auto");
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Estudiantes por Estados");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create
$sql_sexo = mysql_query("SELECT estado_inst,count(*) as total FROM becas_estudiantes WHERE 
estado_inst not like '' group by estado_inst") ;

while($row = mysql_fetch_array($sql_sexo))
{
$data[] = $row['total'];
$tado[] = $row['estado_inst'];
}
//echo $estado['estado_inst'];exit;
$p1 = new PiePlot($data);
$p1->SetLegends($tado);
$graph->Add($p1);
$graph->Stroke("../../../graphico_02.jpg");

?>


