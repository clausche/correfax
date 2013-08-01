<?php
	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");
	include(WEB_ROOT."includes/phppdflib/phppdflib.class.php");
	require('fpdf-land/class.fpdf.php');

	if ($_SESSION["logged_status"]==0)
		die();
	if ($_SESSION["admin_status"]==0)
		die();


	$mailing_adh = array();
	if (isset($_POST["mailing_adh"]))
	{
		while (list($key,$value)=each($_POST["mailing_adh"]))
			$mailing_adh[]=$value;
	}
	else
		die();

		$requete = "SELECT * FROM ".PREFIX_DB."correspondencia
			       				WHERE ";


		$where_clause = "";
		while(list($key,$value)=each($mailing_adh))
		{
			if ($where_clause!="")
				$where_clause .= " OR ";
			$where_clause .= "id_adh=".$DB->qstr($value);
		}
		$requete .= $where_clause." ORDER by nom_adh;";
		// echo $requete;
		$resultat = &$DB->Execute($requete);


$fecha = date("d-m-Y");
class PDF extends FPDF
{
//Columna actual
var $col=0;
//Ordenada de comienzo de la columna
var $y0;

function Header()
{	global $title;
	//Cabacera
	$this->Image('images/bannerprincipal.jpg',50,8,180,12);
	$this->Ln(15);
	$this->SetFont('Arial','B',12);
	$w=$this->GetStringWidth($title)+6;
	$this->SetX((290-$w)/2);
	$this->SetDrawColor(0,0,0);
	$this->SetFillColor(230,230,0);
	$this->SetTextColor(220,50,50);
	$this->SetLineWidth(1);
	$this->Cell($w,9,$title,1,1,'C',1);
	$this->Ln(10);
	//Guardar ordenada
	$this->y0=$this->GetY();
}

function Footer()
{
	global $fecha;
$html='<A  href="http://172.27.21.18/correfax/">CorreFax versión 0.01</A> - Autor :
  <A href="mailto:clausche@gmail.com">Claudio Scheuermann </A> - Fecha actual : '.$fecha.'';;
	//Pie de página
	$this->SetY(-15);
	$this->SetFont('Arial','I',8);
	$this->SetTextColor(128);
//	$this->Cell(0,8,'Página '.$this->PageNo(),0,0,'C');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,'Lautaro versión 0.01','http://172.27.21.18/correfax/');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,'- Autor : ');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,'Claudio Scheuermann','mailto:clausche@gmail.com');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,'- Fecha actual : ');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,$fecha);
	$this->SetY(-11);
	$this->SetFont('Arial','I',8);
	$this->SetTextColor(128);
	$this->Write(5,'Página '.$this->PageNo().'/{nb}');
}

function ChapterTitle($label)
{
    //Arial 12
    $this->SetFont('Arial','B',8);
    //Color de fondo
    $this->SetFillColor(21,72,158);
    //Color de título
	$this->SetTextColor(255,255,255);
	//Título
    $this->Cell(0,6,"$label",1,1,'C',1);
    //Salto de línea
    $this->Ln(0);
}

function PrintChapter($title)
{

	$this->Cell(10,6,"N°",1);
	$this->Cell(50,6,"Procedencia",1);
	$this->Cell(50,6,"Asuntos",1);
	$this->Cell(50,6,"Asignado",1);
	$this->Cell(50,6,"Copia",1);
	$this->Cell(50,6,"Observaciones",1);

	$this->ln();

}
function WriteHTML($html)
{
	//Intérprete de HTML
	$html=str_replace("\n",' ',$html);
	$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($a as $i=>$e)
	{
		if($i%2==0)
		{
			//Text
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
			else
				$this->Write(5,$e);
		}
		else
		{
			//Etiqueta
			if($e{0}=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				//Extraer atributos
				$a2=explode(' ',$e);
				$tag=strtoupper(array_shift($a2));
				$attr=array();
				foreach($a2 as $v)
					if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
						$attr[strtoupper($a3[1])]=$a3[2];
				$this->OpenTag($tag,$attr);
			}
		}
	}
}
function OpenTag($tag,$attr)
{
	//Etiqueta de apertura
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,true);
	if($tag=='A')
		$this->HREF=$attr['HREF'];
	if($tag=='BR')
		$this->Ln(5);
}

function CloseTag($tag)
{
	//Etiqueta de cierre
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,false);
	if($tag=='A')
		$this->HREF='';
}

function SetStyle($tag,$enable)
{
	//Modificar estilo y escoger la fuente correspondiente
	$this->$tag+=($enable ? 1 : -1);
	$style='';
	foreach(array('B','I','U') as $s)
		if($this->$s>0)
			$style.=$s;
	$this->SetFont('',$style);
}

function PutLink($URL,$txt)
{
	//Escribir un hiper-enlace
	$this->SetTextColor(0,0,255);
	$this->SetStyle('I',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('I',false);
	$this->SetTextColor(0);
}

}

$html='<A  href="http://172.27.21.18/correfax/">CorreFax versión 0.01</A> - Autor :
  <A href="mailto:clausche@gmail.com">Claudio Scheuermann </A> - Fecha actual : '.$fecha.'';
//	$pdf=new PDF();

	if ($resultat->EOF)
	die();

	$nb_etiq=0;
	$concatname = "";


while (!$resultat->EOF)
{

$pdf=new PDF();

$pdf->AddPage();
$pdf->SetFont('Arial',I,'',10);

$i=0;

	$pdf->Cell(10,6," ",1);
	$req = "SELECT nombre_pais
								FROM ".PREFIX_DB."pais
								WHERE id_pais=".$resultat->fields["id_adh"]."
								ORDER BY nombre_pais";
	$result = &$DB->Execute($req);
	if (!$result->EOF)

	$result->Close();
$pdf->Cell(50,6,"País:"." ".$result->fields["nombre_pais"]."",1);
	$pdf->Cell(50,6," ",1);
	$pdf->Cell(50,6," ",1);
	$pdf->Cell(50,6," ",1);
	$pdf->Cell(50,6," ",1);

	$pdf->ln();

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(190,6,html_entity_decode($resultat->fields["nom_adh"]),1);
$pdf->Cell(95,6,"Región:"." ".$resultat->fields["region_adh"]."",1);
	$req = "SELECT nombre_pais
								FROM ".PREFIX_DB."pais
								WHERE id_pais=".$resultat->fields["id_adh"]."
								ORDER BY nombre_pais";
	$result = &$DB->Execute($req);
	if (!$result->EOF)

	$result->Close();
$pdf->Cell(95,6,"País:"." ".$result->fields["nombre_pais"]."",1);
$pdf->Ln();
$pdf->Cell(95,6,"Organización Multilateral:"." ".$multi_adh."",1);
	$req = "SELECT libelle_statut
								FROM ".PREFIX_DB."statuts
								WHERE id_statut=".$resultat->fields["id_statut"]."
								ORDER BY priorite_statut";
	$result = &$DB->Execute($req);
	if (!$result->EOF)

$pdf->Cell(95,6,"Tipo:"." ".$result->fields["libelle_statut"]."",1);

$pdf->Ln(10);






			$resultat->MoveNext();

			$col++;
			if ($col>1)
			{
				$col=1;
				$row++;
			}
			if ($row>1)
			{
				$col=1;
				$row=1;
				//$firstpage = $pdf->AddPage();
			}
			$nb_etiq++;
		}
		$resultat->Close();
$pdf->Output();
?>
