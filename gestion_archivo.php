<?php
 
	include("includes/config.inc.php"); 
	include(WEB_ROOT."includes/database.inc.php"); 
	include(WEB_ROOT."includes/functions.inc.php"); 
	include(WEB_ROOT."includes/lang.inc.php"); 
	include(WEB_ROOT."includes/session.inc.php"); 
	
	$filtre_id_adh = "";
	
	if ($_SESSION["logged_status"]==0) 
		header("location: index.php");
	if ($_SESSION["admin_status"]==0) 
		$_SESSION["filtre_cotis_adh"] = $_SESSION["logged_id_adh"];
	else
	{
		if (isset($_GET["id_adh"]))
		{
			if (is_numeric($_GET["id_adh"]))
				$_SESSION["filtre_cotis_adh"]=$_GET["id_adh"];
			else
				$_SESSION["filtre_cotis_adh"]="";
		}
		else
			$_SESSION["filtre_cotis_adh"]="";
	}		

	
	$page = 1; 
	if (isset($_GET["page"]))
		$page = $_GET["page"];


	// Tri
	
	if (isset($_GET["tri"])) 
	{
		if ($_SESSION["tri_cotis"]==$_GET["tri"])
			$_SESSION["tri_cotis_sens"]=($_SESSION["tri_cotis_sens"]+1)%2;
		else
		{
			$_SESSION["tri_cotis"]=$_GET["tri"];
			$_SESSION["tri_cotis_sens"]=0;
		}
	}
 	if(isset($_GET['id_dos']))  
 	{  
 // if id is set then get the file with the id from database
 
 	$id    = $_GET['id_dos'];  
 	$query = "SELECT name, type, size, content " .
          "FROM gac_archivo WHERE id_dos = '$id'";
 	$result = mysql_query($query) or die('Error, query failed');
 	list($name, $type, $size, $content) = mysql_fetch_array($result);
 	header("Content-length: $size"); 
 	header("Content-type: $type"); 
 	header("Content-Disposition: attachment; filename=$name");
 	echo $content;   
 	exit;
 	}
	include("header.php");

	if ($_SESSION["admin_status"]==1) 
	if (isset($_GET["sup"]))
	{
		// recherche adherent
		$requetesel = "SELECT id_adh
			    FROM ".PREFIX_DB."archivo 
			    WHERE id_dos=".$DB->qstr($_GET["sup"]); 
		$result_adh = &$DB->Execute($requetesel);
		if (!$result_adh->EOF)
		{			
			$id_adh = $result_adh->fields["id_adh"];

			$requetesup = "SELECT nom_adh FROM ".PREFIX_DB."correspondencia WHERE id_adh=".$DB->qstr($id_adh);
			$resultat = $DB->Execute($requetesup);
			if (!$resultat->EOF) 
			{			
				// supression record cotisation
				$requetesup = "DELETE FROM ".PREFIX_DB."archivo 
				    	    WHERE id_dos=".$DB->qstr($_GET["sup"]); 
				$DB->Execute($requetesup);
			
				// mise a jour de l'�ch�ance
				$date_fin = get_echeance($DB, $id_adh); 
				if ($date_fin!="")
					$date_fin_update = $DB->DBDate(mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]));
				else
					$date_fin_update = "NULL";	
				$requeteup = "UPDATE ".PREFIX_DB."correspondencia
					    SET date_echeance=".$date_fin_update."
					    WHERE id_adh=".$DB->qstr($id_adh); 
				$DB->Execute($requeteup);
 				dblog("Suppression d'une contribution :"." ".strtoupper($resultat->fields[0])." ".$resultat->fields[1], $requetesup);							
 			}
 			$resultat->Close();
 		}
 		$result_adh->Close(); 
	}

?> 

<?php 
	$requete[0] = "SELECT ".PREFIX_DB."archivo.*, ".PREFIX_DB."correspondencia.nom_adh, ".PREFIX_DB."correspondencia.prenom_adh, 
			".PREFIX_DB."types_archivo.libelle_type_dos 
			FROM ".PREFIX_DB."archivo,".PREFIX_DB."correspondencia,".PREFIX_DB."types_archivo
			WHERE ".PREFIX_DB."archivo.id_adh=".PREFIX_DB."correspondencia.id_adh
			AND ".PREFIX_DB."types_archivo.id_type_dos=".PREFIX_DB."archivo.id_type_dos ORDER BY info_dos";
	$requete[1] = "SELECT count(id_dos)
			FROM ".PREFIX_DB."archivo 
			WHERE 1=1 ";
 
	// phase filtre
	
	
	// date filter

	// phase de tri 
	

	// tri par adherent 
	if ($_SESSION["tri_cotis"]=="1")
		$requete[0] .= "nom_adh ".$tri_cotis_sens_txt.", ";
		
	// tri par type
	elseif ($_SESSION["tri_cotis"]=="2")
		$requete[0] .= "libelle_type_cotis ".$tri_cotis_sens_txt.",";
	
	// tri par montant


	// tri par duree


	// defaut : tri par date
	
	// $resultat = &$DB->Execute($requete[0]);  
	$resultat = &$DB->SelectLimit($requete[0],PREF_NUMROWS,($page-1)*PREF_NUMROWS);
	$nbcotis = &$DB->Execute($requete[1]); 
	
	if ($nbcotis->fields[0]%PREF_NUMROWS==0) 
		$nbpages = intval($nbcotis->fields[0]/PREF_NUMROWS);
	else 
		$nbpages = intval($nbcotis->fields[0]/PREF_NUMROWS)+1;
	$pagestring = "";
	if ($nbpages==0)
		$pagestring = "<b>1</b>"; 
	else for ($i=1;$i<=$nbpages;$i++)
	{
		if ($i!=$page)
			$pagestring .= "<a href=\"gestion_archivo.php?page=".$i."\">".$i."</a> ";
		else
			$pagestring .= $i." ";
	}
	$numberOfRows = $resultat->RecordCount();
?>




<br><br>
	<div class="form-block">
		<TABLE id="infoline" width="100%"> 
		<TR>
		<TD class="left"><? echo $nbcotis->fields[0]." "; if ($nbcotis->fields[0]!=1) echo ("documentos"); else echo ("documento"); ?></TD>
		<TD class="left">Archivos</TD>		
		<TD class="right"><? echo _T("Pages :"); ?> <SPAN class="pagelink"><? echo $pagestring; ?></SPAN></TD>
							</TR>
						</TABLE>
						<TABLE width="100%"> 
							<TR> 
								<TH width="15" class="listing">#</TH> 
			  					<TH class="listing left"> 
									<A href="gestion_archivo.php?tri=0&amp;id_adh=<? echo $_SESSION["filtre_cotis_adh"] ?>" class="listing">
									<? echo _T("Date"); ?></A>
									<?
										if ($_SESSION["tri_cotis"]=="0")
											if ($_SESSION["tri_cotis_sens"]=="0")
												echo "<IMG src=\"images/asc.png\" width=\"7\" height=\"7\" alt=\"\">";
											else 
												echo "<IMG src=\"images/desc.png\" width=\"7\" height=\"7\" alt=\"\">";
									?>
								</TH> 

								<TH class="listing left"> 
									 <? echo ("Pa�s"); ?></A><?
										if ($_SESSION["tri_cotis"]=="2")
											if ($_SESSION["tri_cotis_sens"]=="0")
												echo "<IMG src=\"images/asc.png\" width=\"7\" height=\"7\" alt=\"\">";
											else 
												echo "<IMG src=\"images/desc.png\" width=\"7\" height=\"7\" alt=\"\">";
									?>
								</TH> 

								<TH class="listing left">  
									 <? echo ("Acuerdo"); ?>
									<?
										if ($_SESSION["tri_cotis"]=="1")
											if ($_SESSION["tri_cotis_sens"]=="0")
												echo "<IMG src=\"images/asc.png\" width=\"7\" height=\"7\" alt=\"\">";
											else 
												echo "<IMG src=\"images/desc.png\" width=\"7\" height=\"7\" alt=\"\">";
									?>
								</TH> 
								<TH class="listing left">  
									<? echo ("Tipo"); ?>
									<?
										if ($_SESSION["tri_cotis"]=="1")
											if ($_SESSION["tri_cotis_sens"]=="0")
												echo "<IMG src=\"images/asc.png\" width=\"7\" height=\"7\" alt=\"\">";
											else 
												echo "<IMG src=\"images/desc.png\" width=\"7\" height=\"7\" alt=\"\">";
									?>
								</TH>


								<TH class="listing left"> 
									 <? echo ("Documento"); ?></A>

								</TH> 
<?
	if ($_SESSION["admin_status"]==1) 
	{
?>
								<TH width="55" class="listing"> 
									<? echo _T("Actions"); ?> 
								</TH> 
<?
	}
?>
							</TR> 
<? 
	$i=0;
	$compteur = 1+($page-1)*PREF_NUMROWS;
	$activity_class = "";
	if ($resultat->EOF)
	{
		if ($_SESSION["admin_status"]==1)
			$colspan = 7;
		else
			$colspan = 5;
?>
							<TR>
								<TD colspan="<? echo $colspan; ?>" class="emptylist"><? echo ("Sin archivos a�n"); ?></TD>
							</TR>
<?	
	}
	else while(!$resultat->EOF & $i<$numberOfRows)  
	{ 
		if (($i%2)==0) { $bgColor = "#e6e6f1"; } else { $bgColor = "#e1e1e5"; } 
	 
		
?>							 
							<TR BGCOLOR="<? echo $bgColor; ?>">
								<TD width="15" class="<? echo $row_class; ?> center" nowrap><? echo $compteur ?></TD> 
								<TD width="50" class="<? echo $row_class; ?>" nowrap> 
									<?
										list($a,$m,$j)=split("-",$resultat->fields["date_dos"]);
										echo "$j/$m/$a"; 
									?> 
								</TD> 
								<TD class="<? echo $row_class; ?>" nowrap><?
										echo htmlentities($resultat->fields["info_dos"], ENT_QUOTES)." "; ?></TD> 

								<TD class="<? echo $row_class; ?>" width="600"> 
									<A href="voir_adherent.php?id_adh=<? echo $resultat->fields["id_adh"] ?>">
									<?	echo htmlentities($resultat->fields["nom_adh"], ENT_QUOTES)." ";
									?></A> 
								</TD> 

								<TD class="<? echo $row_class; ?>" nowrap><?
										echo htmlentities($resultat->fields["libelle_type_dos"], ENT_QUOTES)." "; ?></TD> 


										
								<TD class="<? echo $row_class; ?>" nowrap><A href="gestion_archivo.php?id_dos=<? echo $resultat->fields["id_dos"] ?>"><?
										echo htmlentities($resultat->fields["name"], ENT_QUOTES)." "; ?></a></TD> 
<?
			if ($_SESSION["admin_status"]==1)
			{
?>
								<TD width="55" class="<? echo $row_class; ?> center" nowrap>  
									<A href="ajouter_archivo.php?id_dos=<? echo $resultat->fields["id_dos"] ?>"><IMG src="images/icon-edit.png" alt="
									<? echo _T("[mod]"); ?>" border="0" width="12" height="13"></A>
									<A onClick="return confirm('<? echo str_replace("\n","\\n",addslashes("
									Desea usted suprimir este documento ?")); ?>')" href="gestion_archivo.php?sup=<? echo $resultat->fields["id_dos"] ?>"><IMG src="images/icon-trash.png" alt="<? echo _T("[sup]"); ?>" border="0" width="11" height="13"></A>
								</TD> 
<?
			}

	
		$i++;
		$compteur++;
		$resultat->MoveNext();
	}
	$resultat->Close();
?>
						</TABLE>
						<DIV id="infoline2" class="right"><? echo _T("Pages :"); ?> <SPAN class="pagelink"><? echo $pagestring; ?></SPAN></DIV>
<?	
	// affichage du temps d'ah�sion restant si on est en train de visualiser
	// les cotisations d'un membre unique
	
	if ($_SESSION["filtre_cotis_adh"]!="")
	{
		$requete = "SELECT date_echeance, bool_exempt_adh
			    FROM ".PREFIX_DB."correspondencia
			    WHERE id_adh='".$_SESSION["filtre_cotis_adh"]."'";
		$resultat = $DB->Execute($requete);
		 
		
?>	
		<BR>
		<DIV align="center">

<?
		if ($_SESSION["admin_status"]==1)
	        {
?>
	<BR>
	<A href="voir_adherent.php?id_adh=<? echo $_SESSION["filtre_cotis_adh"]; ?>"><? echo _T("[ Voir la fiche adh�rent ]"); ?></A>
	&nbsp;&nbsp;&nbsp;
	<A href="ajouter_archivo.php?id_adh=<? echo $_SESSION["filtre_cotis_adh"]; ?>"><? echo ("[ Agregar un Documento ]"); ?></A>
<?
		}	
?>
		</DIV>
<?
	}	
?>							 
</div>
<? 
  include("footer.php"); 
?>
