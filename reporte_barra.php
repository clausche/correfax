<? 

 
	include("includes/config.inc.php"); 
	include(WEB_ROOT."includes/database.inc.php"); 
	include(WEB_ROOT."includes/functions.inc.php"); 
	include(WEB_ROOT."includes/lang.inc.php"); 
	include(WEB_ROOT."includes/session.inc.php"); 
	
include ("jpgraph-2.2/src/jpgraph.php");
include ("jpgraph-2.2/src/jpgraph_bar.php");
include ("jpgraph-2.2/src/jpgraph_pie.php");
include ("jpgraph-2.2/src/jpgraph_utils.inc.php");
include ("jpgraph-2.2/src/jpgraph_mgraph.php");

$db = mysql_connect("localhost", "root","zz") ;

mysql_select_db("correfax",$db);

$sql = mysql_query("SELECT ar_adh,count(*) as total_es, count(*) as total 
	FROM correfax.gac_correspondencia WHERE 
ar_adh not like '' group by ar_adh");
$sql2 =mysql_query("SELECT COUNT(*) as inti FROM gac_correspondencia");
$tot = mysql_fetch_array($sql2);

while($row = mysql_fetch_array($sql))
{
$data[] = $row['total'];
$leg[] = $row['ar_adh'];
}

$graph = new Graph(650,150,"auto");

// Set A title for the plot
// Set A title for the plot
// Set title

$graph->title->SetFont(FF_FONT1,FS_BOLD,10);
$graph->title->SetColor('darkred');
$graph->title->Set("Total carga por Despacho (".$tot['inti'].")");

$graph->SetScale("textint");
$graph->img->SetMargin(50,30,50,50);
//$graph->AdjBackgroundImage(0.4,0.7,-1); //setting BG type
//$graph->SetBackgroundImage("images/bolivar.JPG",BGIMG_FILLFRAME); //adding image
$graph->SetShadow();

$graph->xaxis->SetTickLabels($leg);

$bplot = new BarPlot($data);
$bplot->SetFillColor("red"); // Fill color
$bplot->value->Show();
$bplot->value->SetFont(FF_ARIAL,FS_BOLD);
$bplot->value->SetFormat("%d ");
$bplot->value->SetAngle(0);
$bplot->value->SetColor("blue","navy");

$graph->Add($bplot);
$graph->Stroke("graphico_01.jpg");


	if ($_SESSION["logged_status"]==0) 
		header("location: index.php");
	if ($_SESSION["admin_status"]==0) 
		header("location: voir_adherent.php");
		
	$mailing_adh = array();
	$nomail_adh = array();
	if (isset($_POST["mailing_adh"]))
		while (list($key,$value)=each($_POST["mailing_adh"]))
			$mailing_adh[]=$value;

	$mailing_corps = "";
	if (isset($_POST["mailing_corps"]))
		$mailing_corps = stripslashes($_POST["mailing_corps"]);

	$mailing_objet = "";
	if (isset($_POST["mailing_objet"]))
		$mailing_objet = stripslashes($_POST["mailing_objet"]);

	$error_detected = "";
	
	$etape = 0;
	
	include("header.php");

	if ($etape==0)
	{
		
		if (isset($_GET["etiquettes"]))
		{
?> 
			<H1 class="titre"><? echo "Generar PDF"; ?></H1>
<?
		}
		
		// Affichage des erreurs
		if ($error_detected!="")
		{
?>
		  	<DIV id="errorbox">
		  		<H1><? echo _T("- ERREUR -"); ?></H1>
		  		<UL>
		  			<? echo $error_detected; ?>
		  		</UL>
		  	</DIV>
<?
		}

		// selection des correspondencia et application filtre / tri
			
		$requete[0] = "SELECT id_adh, nom_adh, ar_adh, rr_adh, activite_adh,
		       ie_adh, date_crea_adh, nombre_pais, region_adh,".PREFIX_DB."paises.id_paises,gsm_adh,date_crea_adh
		       FROM ".PREFIX_DB."correspondencia INNER JOIN ".PREFIX_DB."pais ON 
		       ".PREFIX_DB."correspondencia.id_pais " .
		       "= ".PREFIX_DB."pais.id_pais LEFT JOIN ".PREFIX_DB."paises ON 
		       ".PREFIX_DB."paises.id_pais = ".PREFIX_DB."pais.id_pais " ;
		$requete[1] = "SELECT count(id_adh)
			       FROM ".PREFIX_DB."correspondencia
			       WHERE 1=1 ";
		
		$resultat = &$DB->Execute($requete[0]);
		$nbadh = &$DB->Execute($requete[1]);
?>
		<SCRIPT LANGUAGE="JavaScript">
		<!--
		var checked = 1; 	
		function check()
		{ 
			for (var i=0;i<document.mailing_form.elements.length;i++)
			{
				var e = document.mailing_form.elements[i];
				if(e.type == "checkbox")
				{
					e.checked = checked;
				}
			}
			checked = !checked;
		}
		-->
		</SCRIPT>
		<div class="form-block">
		<TABLE id="infoline" width="100%">
			<TR>
				<TD class="left"><? echo $nbadh->fields[0]." "; if ($nbadh->fields[0]!=1) echo "correo"; else echo "correos"; ?></TD>
				
			</TR>
		</TABLE>
<?
		if (isset($_GET["etiquettes"]))
		{
?>
						<FORM action="eti_correspondencia.php" method="post" name="mailing_form" target="_blank">
<?
		}

?>
						<table width="100%"> 
							<TR> 
							<TH class="listing" width="15">#</TH> 
				  			<TH width="170" class="listing left"> 
									<A href="mailing_correspondencia.php?tri=0" class="listing"><? echo _T("Nom"); ?></A>
									
								</TH> 
								<TH width="60" class="listing left"> 
									<A href="mailing_correspondencia.php?tri=3" class="listing"><? echo "Fechas"; ?></A>
									
								
<? 
		if ($resultat->EOF)
		{
?>	
							<tr>
								<td colspan="6" class="emptylist"><? echo _T("aucun adhérent"); ?></td>
							</tr>
<?
		}
		else while (!$resultat->EOF) 
		{ 

?>							 
							<TR> 
								<TD width="15" class="<? echo $row_class; ?>" nowrap> 
									<INPUT type="checkbox" name="mailing_adh[]" value="<? echo $resultat->fields[0] ?>" <? if (in_array($resultat->fields[0],$mailing_adh)) echo "CHECKED"; ?>> 
								</TD> 
								<TD class="<? echo $row_class ?>" width="600">


									<A href="voir_adherent.php?id_adh=<? echo $resultat->fields["id_adh"] ?>"><? echo htmlentities(strtoupper($resultat->fields[1]), ENT_QUOTES); ?></A>
								</TD> 
								
								<TD class="<? echo $row_class; ?>" nowrap><? echo $resultat->fields[11]; ?></TD>
								
							</TR> 
<? 
			$resultat->MoveNext();
		} 
		$resultat->Close();
?>							 
						</TABLE>
						<A href="#" onClick="check()"><? echo "[Seleccionar todo][Ninguna]"; ?></A>
						<BR>
						<BR>
<?
		if (isset($_GET["etiquettes"]))
		{
?>
							<DIV align="center"><INPUT type="submit" value="Generar"></DIV>
<?
		}
?>
						<INPUT type="hidden" name="mailing_go" value="1">
						</FORM>
<? 
	}
	else
	{
		$confirm_detected="";
		
		// $mailing_corps = $_POST["mailing_corps"];
		// adhérents avec email
		$requete = "SELECT id_adh, nom_adh, prenom_adh, pseudo_adh, activite_adh,
				libelle_statut, bool_exempt_adh, titre_adh, email_adh, bool_admin_adh, date_echeance
				FROM ".PREFIX_DB."correspondencia, ".PREFIX_DB."statuts
	  				WHERE ".PREFIX_DB."correspondencia.id_statut=".PREFIX_DB."statuts.id_statut AND (";
		$where_clause = "";
		while(list($key,$value)=each($mailing_adh))
		{
			if ($where_clause!="")
				$where_clause .= " OR ";
			$where_clause .= "id_adh='".$value."'";
		}
		$requete .= $where_clause.") AND email_adh IS NOT NULL ORDER by nom_adh, prenom_adh;";
		// echo $requete;
		$resultat = &$DB->Execute($requete);

?>



			<TABLE width="100%"> 
							
<?		
		$num_mails = 0;
		$concatmail = "";
		if ($resultat->EOF)
		{
?>	
				<tr>
					<td colspan="4" bgcolor="#EEEEEE" align="center"><i><? echo _T("aucun adhérent"); ?></i></td>
				</tr>
<?
		}
		else while (!$resultat->EOF) 
		{

?>							 
				<tr> 
					<td bgcolor="<? echo $color ?>">

						<a href="voir_adherent.php?id_adh=<? echo $resultat->fields["id_adh"] ?>"><? echo htmlentities(strtoupper($resultat->fields[1]), ENT_QUOTES)." ".htmlentities($resultat->fields[2], ENT_QUOTES) ?></a>
					</td> 
					<td bgcolor="<? echo $color ?>"<? echo $activity_class ?>> 
						<? if ($resultat->fields[8]!="") echo "<A href=\"mailto:".htmlentities($resultat->fields[8], ENT_QUOTES)."\">".htmlentities($resultat->fields[8], ENT_QUOTES)."</A>"; ?>&nbsp; 
					</td> 
					<td bgcolor="<? echo $color ?>"<? echo $activity_class ?>>
						<? echo _T($resultat->fields[5]) ?> 
					</td> 
					<td bgcolor="<? echo $color ?>"<? echo $activity_class ?>> 
						<? echo $statut_cotis ?>
					</td>
				</TR>

<?	
			$resultat->MoveNext();
		}
		
		$resultat->Close();
?>


			<TABLE width="100%"> 
							
<?
		// adhérents sans email
		$requete = "SELECT id_adh, nom_adh, prenom_adh, adresse_adh, activite_adh,
				libelle_statut, bool_exempt_adh, titre_adh, cp_adh, bool_admin_adh, date_echeance,
				ville_adh, tel_adh, gsm_adh, msn_adh, icq_adh, pays_adh, jabber_adh, adresse2_adh
				FROM ".PREFIX_DB."correspondencia, ".PREFIX_DB."statuts
			       	WHERE ".PREFIX_DB."correspondencia.id_statut=".PREFIX_DB."statuts.id_statut AND (";
		$requete .= $where_clause.")  ORDER by nom_adh, prenom_adh;";
		// echo $requete;
		$resultat = &$DB->Execute($requete);
		
		
?>								
								<TD>
									<FORM action="eti_correspondencia.php" method="post" target="_blank">
<?
		reset($nomail_adh);
		while(list($key,$value)=each($nomail_adh))
		{
			echo "<INPUT type=\"hidden\" name=\"mailing_adh[]\" value=\"".$value."\">";
		}

?>
										&nbsp;&nbsp;&nbsp;<INPUT type="submit" value="<? echo _T("Génération d'étiquettes"); ?>">
									</FORM>
								</TD>
							<TR>
						</TABLE>
						</DIV>
<?
	}
?>
<table id="infoline" width="100%">

<TR><TD><div><IMG src="graphico_01.jpg" width="650" height="150" align="left" border="0"></div></TD></TR>

</table>
	</div>
		<FORM><INPUT Type="button" VALUE="Volver" onClick="history.go(-1);return true;"></FORM>

<?
	include("footer.php"); 
?>
