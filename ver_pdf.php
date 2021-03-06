<?php header('Content-type: text/html;charset=UTF-8') ?>
<?php
 	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");

	// On vï¿?ifie si on a une rï¿?ï¿?ence => modif ou crï¿?tion
	if (isset($_GET["id_adh"]))
		if (is_numeric($_GET["id_adh"]))
			$id_adh = $_GET["id_adh"];
     //
    // Prï¿?remplissage des champs
   //  avec des valeurs issues de la base
  //

	$requete = "SELECT * FROM ".PREFIX_DB."correspondencia WHERE id_adh=$id_adh";
	$result = &$DB->Execute($requete);

	// recuperation de la liste de champs de la table
  $fields = &$DB->MetaColumns(PREFIX_DB."correspondencia");
	while (list($champ, $proprietes) = each($fields))
	{
		$val="";
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,

		// dï¿?laration des variables correspondant aux champs
		// et reformatage des dates.

		// on doit faire cette verif pour une enventuelle valeur "NULL"
		// non renvoyï¿? -> ex: pas de tel
		// sinon on obtient un warning
		if (isset($result->fields[$proprietes_arr["name"]]))
			$val = $result->fields[$proprietes_arr["name"]];

		if($proprietes_arr["type"]=="date" && $val!="")
		{
			list($a,$m,$j)=split("-",$val);
			$val="$j/$m/$a";
		}

		$$proprietes_arr["name"] = html_entity_decode(stripslashes(addslashes($val)), ENT_QUOTES);
	}
	reset($fields);
$ter_adh= html_entity_decode(Strip_tags($result->fields["ter_adh"]));
$ofe_adh= html_entity_decode(Strip_tags($result->fields["ofe_adh"]));
$ville_adh= html_entity_decode(Strip_tags($result->fields["ville_adh"]));
$pays_adh= html_entity_decode(Strip_tags($result->fields["pays_adh"]));
$pro_adh= html_entity_decode(Strip_tags($result->fields["pro_adh"]));
$co_adh= html_entity_decode(Strip_tags($result->fields["co_adh"]));

define('FPDF_FONTPATH','font/');
require('fpdf17/fpdf.php');
//Fecha de la Impresión

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
	$this->Image('./images/bannerprincipal.jpg',10,8,180,12,'','http://localhost/prueba07022007/');
	$this->Ln(15);
	$this->SetFont('Arial','B',12);
	$w=$this->GetStringWidth($title)+6;
	$this->SetX((210-$w)/2);
	$this->SetDrawColor(0,80,180);
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
$html='<A  href="http://192.168.40.91/">Lautaro versi&oacute;n 0.01</A> - Autor :
  <A href="mailto:cscheuermann@mes.gov.ve">Claudio Scheuermann </A> - Fecha actual : '.$fecha.'';
global $fecha;
	//Pie de página
	$this->SetY(-15);
	$this->SetFont('Arial','I',8);
	$this->SetTextColor(128);
//	$this->Cell(0,8,'Página '.$this->PageNo(),0,0,'C');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,'Lautaro versión 0.01','http://localhost/prueba07022007/');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,'- Autor : ');
	$this->SetTextColor(128);
	$this->SetFont('Arial','I',8);
	$this->Write(5,'Claudio Scheuermann','mailto:cscheuermann@mes.gov.ve');
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
   
	$this->ChapterTitle($title);
   
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
$html='<A  href="http://192.168.40.91/">Lautaro versión 0.01</A> - Autor :
  <A href="mailto:cscheuermann@mes.gov.ve">Claudio Scheuermann </A> - Fecha actual : '.$fecha.'';

$pdf=new PDF();
html_entity_decode($pdf);

$title_decode = "Criptografía";
$title=html_entity_decode("Criptograf&iacute;a");
$pdf->SetTitle($title);
$pdf->AddPage();
$pdf->SetFont('Arial',I,'',10);
$pdf->PrintChapter('Nombre completo del Acuerdo');
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(190,6,html_entity_decode($nom_adh),1);
$pdf->Cell(95,6,"Regi&oacute;n:"." ".$region_adh."",1);
	$requete = "SELECT nombre_pais
								FROM ".PREFIX_DB."pais
								WHERE id_pais=".$id_pais."
								ORDER BY nombre_pais";
	$result = &$DB->Execute($requete);
	if (!$result->EOF)
		$nombre_pais = $result->fields["nombre_pais"] ; 
	$result->Close();
$pdf->Cell(95,6,"País:"." ".$nombre_pais."",1);
$pdf->Ln();
$pdf->Cell(95,6,"Organización Multilateral:"." ".$multi_adh."",1);
	$requete = "SELECT libelle_statut
								FROM ".PREFIX_DB."statuts
								WHERE id_statut=".$id_statut."
								ORDER BY priorite_statut";
	$result = &$DB->Execute($requete);
	if (!$result->EOF)
		$libelle_statut = $result->fields["libelle_statut"]; 
$pdf->Cell(95,6,"Tipo:"." ".$libelle_statut."",1);
$pdf->Ln();
$pdf->Cell(60,6,"Fecha suscripción:"." ".$date_crea_adh."",1);
if ($vigor_adh=="") $vigor_adh="Sin Gaceta";
$pdf->Cell(65,6,"Fecha entrada en vigor:"." ".$vigor_adh."",1);
$pdf->Cell(65,6,"Fecha de termino:"." ".$term_adh."",1);
$pdf->Ln();
if ($activite_adh=="1") $activite_adh="Activo";
if ($activite_adh=="0") $activite_adh="Inactivo"; 
if ($activite_adh=="2") $activite_adh="Pendientes"; 
$pdf->Cell(95,6,"Status:"." ".$activite_adh."",1);
$pdf->Cell(95,6,"Prorroga:"." ".strtolower($sel_pro)."",1);
$pdf->Ln(10);
$pdf->PrintChapter('Contraparte');
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(190,6,$ofe_adh,1);
$pdf->Ln(10);
$pdf->PrintChapter('Terminos');
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(190,5,$ter_adh,1);
$pdf->Ln(4);
$pdf->PrintChapter('Últimas actividades');
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(190,6,$ville_adh,1);
$pdf->Ln(4);
$pdf->PrintChapter('Historial');
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(190,6,$pays_adh,1);
$pdf->Ln(4);
$pdf->PrintChapter('Propuestas');
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(190,6,$pro_adh,1);
$pdf->Ln(4);
$pdf->PrintChapter('Observaciones');
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(190,6,$co_adh,1);
$pdf->AliasNbPages();
$pdf->Output();
?>
