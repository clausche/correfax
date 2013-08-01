<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$db = mysql_connect("localhost", "root","zz") ;

mysql_select_db("becarios",$db);

$sql = mysql_query("SELECT estado_inst,COUNT(estado_inst) AS total FROM becas_estudiantes WHERE estado_inst NOT LIKE '' GROUP BY estado_inst");

while($row = mysql_fetch_array($sql))
{
$data[] = $row['total'];
$leg[] = $row['estado_inst'];
}

$graph = new Graph(650,150,"auto");

// Set A title for the plot
$graph->title->Set("Carga por Estados");

$graph->title->SetFont(FF_FONT1,FS_BOLD);

$graph->SetScale("textint");
$graph->img->SetMargin(50,30,50,50);
//$graph->AdjBackgroundImage(0.4,0.7,-1); //setting BG type
//$graph->SetBackgroundImage("linux_pez.png",BGIMG_FILLFRAME); //adding image
$graph->SetShadow();

$graph->xaxis->SetTickLabels($leg);

$bplot = new BarPlot($data);
$bplot->SetFillColor("lightgreen"); // Fill color
$bplot->value->Show();
$bplot->value->SetFont(FF_ARIAL,FS_BOLD);
$bplot->value->SetFormat("%d ");
$bplot->value->SetAngle(45);
$bplot->value->SetColor("black","navy");

$graph->Add($bplot);
$graph->Stroke("graphico_01.jpg"); 
?> 