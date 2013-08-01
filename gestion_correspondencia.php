<?
	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");

	if ($_SESSION["logged_status"]==0)
		header("location: index.php");
	if ($_SESSION["admin_status"]==0)
		header("location: voir_correspondencia.php");

	$page = 1;
	if (isset($_GET["page"]))
		$page = $_GET["page"];

	if (isset($_GET["filtre"]))
		if (is_numeric($_GET["filtre"]))
			$_SESSION["filtre_adh"]=$_GET["filtre"];

	if (isset($_GET["filtre_2"]))
		if (is_numeric($_GET["filtre_2"]))
			$_SESSION["filtre_adh_2"]=$_GET["filtre_2"];

	// Tri

	if (isset($_GET["tri"]))
		if (is_numeric($_GET["tri"]))
		{
			if ($_SESSION["tri_adh"]==$_GET["tri"])
				$_SESSION["tri_adh_sens"]=($_SESSION["tri_adh_sens"]+1)%2;
			else
			{
				$_SESSION["tri_adh"]=$_GET["tri"];
				$_SESSION["tri_adh_sens"]=0;
			}
		}

	include("header.php");

	if (isset($_GET["sup"]))
	{
		if (is_numeric($_GET["sup"]))
		{
			$requetesup = "SELECT nom_adh, prenom_adh FROM ".PREFIX_DB."correspondencia WHERE id_adh=".$DB->qstr($_GET["sup"]);
			$resultat = $DB->Execute($requetesup);
			if (!$resultat->EOF)
			{
				// supression record adh�rent
				$requetesup = "DELETE FROM ".PREFIX_DB."correspondencia
						WHERE id_adh=".$DB->qstr($_GET["sup"]);
				$DB->Execute($requetesup);




			}
			$resultat->Close();
 		}
	}

?>
<br>

<?php
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

	// filtre d'affichage des correspondencia activ�s/desactiv�s
	if ($_SESSION["filtre_adh_2"]=="")
	{
		$requete[0] .= "WHERE ".PREFIX_DB."correspondencia.activite_adh='' ";
		$requete[1] .= "AND ".PREFIX_DB."correspondencia.activite_adh='' ";
	}

	if ($_SESSION["filtre_adh_2"]=="2")
	{
		$requete[0] .= "WHERE ".PREFIX_DB."correspondencia.activite_adh='0' ";
		$requete[1] .= "AND ".PREFIX_DB."correspondencia.activite_adh='0' ";
	}
	if ($_SESSION["filtre_adh_2"]=="3")
	{
		$requete[0] .= "WHERE ".PREFIX_DB."correspondencia.activite_adh='2' ";
		$requete[1] .= "AND ".PREFIX_DB."correspondencia.activite_adh='2' ";
	}
	elseif ($_SESSION["filtre_adh_2"]=="1")
	{
		$requete[0] .= "WHERE ".PREFIX_DB."correspondencia.activite_adh='' ";
		$requete[1] .= "AND ".PREFIX_DB."correspondencia.activite_adh='' ";
	}


	// phase de tri

	if ($_SESSION["tri_adh_sens"]=="0")
		$tri_adh_sens_txt="ASC";
	else
		$tri_adh_sens_txt="DESC";

	$requete[0] .= "ORDER BY ";

	// tri par pseudo
	if ($_SESSION["tri_adh"]=="1")
		$requete[0] .= "nom_adh ".$tri_adh_sens_txt.",";

	// tri par statut
	elseif ($_SESSION["tri_adh"]=="2")
		$requete[0] .= "date_crea_adh ".$tri_adh_sens_txt.",";

	// tri par echeance
	elseif ($_SESSION["tri_adh"]=="3")
		$requete[0] .= "date_crea_adh ".$tri_adh_sens_txt.",";
	// tri par echeance
	elseif ($_SESSION["tri_adh"]=="4")
		$requete[0] .= "nombre_pais ".$tri_adh_sens_txt.",";
	// defaut : tri par nom, prenom
	$requete[0] .= "nombre_pais ".$tri_adh_sens_txt;

	$resultat = &$DB->SelectLimit($requete[0],PREF_NUMROWS,($page-1)*PREF_NUMROWS);
	$nbadh = &$DB->Execute($requete[1]);

	if ($nbadh->fields[0]%PREF_NUMROWS==0)
		$nbpages = intval($nbadh->fields[0]/PREF_NUMROWS);
	else
		$nbpages = intval($nbadh->fields[0]/PREF_NUMROWS)+1;
	$pagestring = "";
        if ($nbpages==0)
		$pagestring = "<b>1</b>";
	else for ($i=1;$i<=$nbpages;$i++)
	{
		if ($i!=$page)
			$pagestring .= "<A href=\"gestion_correspondencia.php?page=".$i."\">".$i."</A> ";
		else
			$pagestring .= $i." ";
	}

$numberOfRows = $resultat->RecordCount();


?>
<br>

	<div class="form-block">
	<TABLE id="infoline" width="790">
		<TR>

			<TD class="right"><? echo _T("Pages :"); ?> <SPAN class="pagelink"><? echo $pagestring; ?></SPAN></TD>
		</TR>
	</TABLE>

	<TABLE width="790">
		<TR>
			<TH width="15" class="listing">#</TH>
			<TH width="80" class="listing left">
				<A href="gestion_correspondencia.php?tri=4" class="listing"><? echo "Fecha"; ?></A>
<?php
	if ($_SESSION["tri_adh"]=="4")
	{
		if ($_SESSION["tri_adh_sens"]=="4")
			$img_sens = "asc.png";
		else
			$img_sens = "desc.png";
	}
	else
		$img_sens = "icon-empty.png";
?>
				<IMG src="images/<? echo $img_sens; ?>" width="7" height="7" alt="">
			</TH>
  			<TH width="170" class="listing left">
				<A href="gestion_correspondencia.php?tri=0" class="listing"><? echo ("Asunto"); ?></A>
<?php
	if ($_SESSION["tri_adh"]=="0")
	{
		if ($_SESSION["tri_adh_sens"]=="0")
			$img_sens = "asc.png";
		else
			$img_sens = "desc.png";
	}
	else
		$img_sens = "icon-empty.png";
?>
				<IMG src="images/<? echo $img_sens; ?>" width="7" height="7" alt="">
			</TH>
			<TH width="100" class="listing left" nowrap>
				<A href="gestion_correspondencia.php?tri=1" class="listing"><? echo ("Asignado"); ?></A>
<?php
	if ($_SESSION["tri_adh"]=="1")
	{
		if ($_SESSION["tri_adh_sens"]=="0")
			$img_sens = "asc.png";
		else
			$img_sens = "desc.png";
	}
	else
		$img_sens = "icon-empty.png";
?>
				<IMG src="images/<? echo $img_sens; ?>" width="7" height="7" alt="">
			</TH>
			<TH width="100" class="listing left">
				<A href="gestion_correspondencia.php?tri=2" class="listing"><? echo ("Remitente"); ?></A>
<?php
	if ($_SESSION["tri_adh"]=="2")
	{
		if ($_SESSION["tri_adh_sens"]=="0")
			$img_sens = "asc.png";
		else
			$img_sens = "desc.png";
	}
	else
		$img_sens = "icon-empty.png";
?>
				<IMG src="images/<? echo $img_sens; ?>" width="7" height="7" alt="">
			</TH>
			<TH  width="75" class="listing left">
				<A href="gestion_correspondencia.php?tri=3" class="listing"><? echo ("Pa�s"); ?></A>
<?php
	if ($_SESSION["tri_adh"]=="3")
	{
		if ($_SESSION["tri_adh_sens"]=="0")
			$img_sens = "asc.png";
		else
			$img_sens = "desc.png";
	}
	else
		$img_sens = "icon-empty.png";
?>
				<IMG src="images/<? echo $img_sens; ?>" width="7" height="7" alt="">
			</TH>
<?
			if ($_SESSION["admin_status"]==1)
			{
?>
			<TH width="55" class="listing"><? echo ("Acci�n"); ?></TH>

<?
			}
?>
		</TR>

<?php
	$i=0;
	$compteur = 1+($page-1)*PREF_NUMROWS;
	if ($resultat->EOF)
	{
?>
		<TR><TD colspan="6" class="emptylist"><? echo _T("aucun adh�rent"); ?></TD></TR>
<?php
	}

	else while (!$resultat->EOF & $i<$numberOfRows)
	{
			if (($i%2)==0) { $bgColor = "#ededcb"; } else { $bgColor = "#e6e6d1"; }

/*		// d�finition CSS pour correspondencia d�sactiv�
		if ($resultat->fields[4]=="1")
			$row_class = "actif";
		else
			$row_class = "inactif";

		// temps d'adh�sion
		if($resultat->fields[13]=="10")
		{
			$statut_cotis = _T("Exempt de cotisation");
			$row_class .= " cotis-exempt";
		}
		else
		{
			if ($resultat->fields[13]=="4")
			{
				$statut_cotis = _T("N'a jamais cotis�");
				$row_class .= " cotis-never";
			}

			else
			{
				$date_fin = split("-",$resultat->fields[10]);
				$ts_date_fin = mktime(0,0,0,$date_fin[1],$date_fin[2],$date_fin[0]);
				$aujourdhui = time();

				$difference = intval(($ts_date_fin - $aujourdhui)/(3600*24));
				if ($difference==0)
				{
					$statut_cotis = _T("Dernier jour !");
					$row_class .= " cotis-lastday";
				}
				elseif ($difference<0)
				{
					$statut_cotis = _T("En retard de ").-$difference." "._T("jours")." ("._T("depuis le")." ".$date_fin[2]."/".$date_fin[1]."/".$date_fin[0].")";
					$row_class .= " cotis-late";
				}
				else
				{
					if ($difference!=1)
						$statut_cotis = $difference." "._T("jours restants")." ("._T("fin le")." ".$date_fin[2]."/".$date_fin[1]."/".$date_fin[0].")";
					else
						$statut_cotis = $difference." "._T("jour restant")." ("._T("fin le")." ".$date_fin[2]."/".$date_fin[1]."/".$date_fin[0].")";
					if ($difference < 30)
						$row_class .= " cotis-soon";
					else
						$row_class .= " cotis-ok";
				}
			}
		}*/

?>
		<TR BGCOLOR="<? echo $bgColor; ?>">

			<TD width="15" ><? echo $compteur ?></TD>

		<TD ><? echo htmlentities($resultat->fields[11], ENT_QUOTES) ?></TD>


			</TD>
			<TD  width="600" ><A href="voir_correspondencia.php?id_adh=<? echo $resultat->fields["id_adh"] ?>">


				<?php echo htmlentities($resultat->fields[1],ENT_QUOTES)."".htmlentities($resultat->fields[21], ENT_QUOTES)."" ; ?></A></TD>

			<TD ><? echo htmlentities($resultat->fields[2], ENT_QUOTES) ?></TD>

			<TD ><? echo $resultat->fields[5] ?></TD>

    <TD ><? echo $resultat->fields[7] ?></TD>
<?
			if ($_SESSION["admin_status"]==1)
			{
?>
			<TD >
				<A href="ajouter_correspondencia.php?id_adh=<? echo $resultat->fields[0] ?>"><IMG src="images/icon-edit.png" alt="<? echo _T("[mod]"); ?>"
				border="0" width="12" height="13"></A>
				<A href="ajouter_contribution.php?id_adh=<? echo $resultat->fields[0] ?>"><IMG src="images/icon-money.png" alt="<? echo _T("[C�]"); ?>"
				border="0" width="13" height="13"></A>
				<A onClick="return confirm('<? echo str_replace("\n","\\n",addslashes
				("Esta seguro de querer eliminar este correo ?")); ?>')"
						href="gestion_correspondencia.php?sup=<? echo $resultat->fields[0] ?>"><IMG src="images/icon-trash.png" alt="<? echo _T("[sup]"); ?>"
						border="0" width="11" height="13"></A>
			</TD>
<?
			}
?>
		</TR>
<?php
		$i++;
		$compteur++;
		$resultat->MoveNext();
	}
	$resultat->Close();
?>
	</TABLE>
	<tr>
	<TD><? echo _T("Pages :"); ?> <SPAN class="pagelink"><? echo $pagestring; ?></SPAN></TD>
	</tr>



<?php
  include("footer.php");
?>
