<?php
	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");



	require('fpdf16/fpdf.php');

class PDF extends FPDF
{

}
//Crea un nuevo pdf
$pdf=new PDF();

//Disable automatic page break
$pdf->SetAutoPageBreak(true);

//Añade primera página
$pdf->AddPage();

//seteo inicial de margenes y position axis pr pagina
$y_axis_initial = 0;
$x_axis = 10;
$y_axis = 20;

//imprime los titulos de columna para la pagina (quitar comentarios para activar)
$pdf->SetFillColor(232,232,232);
$pdf->SetFont('Arial','B',10);
$pdf->SetY($y_axis_initial);

//$pdf->Cell(30,6,'CODI',1,0,'L',1);


$y_axis = $y_axis + $row_height;

//Hago una query a mi bd
//$result=@mysql_query('select CODI, NOMB, LIBRE from vells',$conexion);
		$requete = "SELECT * FROM ".PREFIX_DB."adherents
			       				WHERE ";

//inicializo el contador
$i = 0;

//Seto el maximo de filas por pagina
$max = 25;

//Seteo la altuira de la fila
$row_height = 6;

$resultat = &$DB->Execute($requete);

while (!$resultat->EOF)

{
//Si la fila actual es la ultima, creo una nueva página e imprimo el titulo (quitar comentarios para activar)
if ($i == $max)
{
$pdf->AddPage();

//print column titles for the current page
//$pdf->SetY($y_axis_initial);
//$pdf->SetX(25);
//$pdf->Cell(30,6,'CODI',1,0,'L',1);


//Go to next row
$y_axis = $y_axis + $row_height;

//Set $i variable to 0 (first row)
$i = 0;
}

$CODI = $resultat->fields['id_adh'];
$NOMB = $resultat->fields['nom_adh'];
$LIBRE = $resultat->fields['region_adh'];

$pdf->SetY($y_axis);
$pdf->SetX($x_axis);
$linea=$CODI.$NOMB.$LIBRE;
$pdf->MultiCell(0,6,$linea,0,1,'L',10);
//$pdf->MultiCell(30,6,$CODI,0,0,'L',0);
//$pdf->MultiCell(90,6,$NOMB,0,0,'Ln',0);
//$pdf->MultiCell(120,6,$LIBRE,0,0,'Ln',0);

//Go to next row
$y_axis = $y_axis + $row_height;
$i = $i + 1;
}

mysql_close($conexion);

//Create file
$pdf->Output();
?>
