<?php
	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
 	if(isset($_GET['id_dos']))
 	{
 // if id is set then get the file with the id from database

 	$id_dos = $_GET['id_dos'];
 	$query = "SELECT name, type, size, content " .
          "FROM gac_archivo WHERE id_dos = ".$id_dos."";
 	$result = mysql_query($query) or die('Error, query failed');
 	list($name, $type, $size, $content) = mysql_fetch_array($result);
	header("Content-length: $size");
 	header("Content-type: $type");
 	header("Content-Disposition: attachment; filename=$name");
 	echo $content;
 	exit;
 	}
 	?>
<?php

	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");

	$id_adh = "";
	if ($_SESSION["logged_status"]==0)
		header("location: index.php");
	if ($_SESSION["admin_status"]==0)
		$id_adh = $_SESSION["logged_id_adh"];

	// On v�?ifie si on a une r�?�?ence => modif ou cr�?tion
	if (isset($_GET["id_adh"]))
		if (is_numeric($_GET["id_adh"]))
			$id_adh = $_GET["id_adh"];

	if ($_SESSION["admin_status"]==0)
		$id_adh = $_SESSION["logged_id_adh"];
	if ($id_adh=="")
		header("location: index.php");

     //
    // Pr�?remplissage des champs
   //  avec des valeurs issues de la base
  //

	$requete = "SELECT *
							FROM ".PREFIX_DB."correspondencia
							WHERE id_adh=$id_adh";
	$result = &$DB->Execute($requete);
        if ($result->EOF)
		header("location: index.php");

	// recuperation de la liste de champs de la table
  $fields = &$DB->MetaColumns(PREFIX_DB."correspondencia");
	while (list($champ, $proprietes) = each($fields))
	{
		$val="";
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,

		// d�?laration des variables correspondant aux champs
		// et reformatage des dates.

		// on doit faire cette verif pour une enventuelle valeur "NULL"
		// non renvoy�? -> ex: pas de tel
		// sinon on obtient un warning
		if (isset($result->fields[$proprietes_arr["name"]]))
			$val = $result->fields[$proprietes_arr["name"]];

		if($proprietes_arr["type"]=="date" && $val!="")
		{
			list($a,$m,$j)=split("-",$val);
			$val="$j/$m/$a";
		}

		$$proprietes_arr["name"] = htmlentities(stripslashes(addslashes($val)), ENT_QUOTES);
	}
	reset($fields);
	include("header.php");
?>
<br><br>
<div class="form-block">
<?
			if ($_SESSION["admin_status"]==1)
			{
?>


<?
			}
?>
	       <div align="right">

	<A href="ver_pdf.php?id_adh=<? echo $id_adh ?>"><IMG src="images/pdf4545.jpg" alt="
		<? echo "crear PDF"; ?>" border="0" width="20" height="20"></A>
	&nbsp;&nbsp;&nbsp;
<?
			if ($_SESSION["admin_status"]==1)
			{
?>
	<A onClick="return confirm('<? echo str_replace("\n","\\n",addslashes("� Est� a punto de borrar este Acuerdo:" .
				" ".$nom_adh." ?.\n\n Quiere seguir con la operaci�n ?")); ?>')" href="gestion_correspondencia.php?sup=
				<? echo $id_adh ?>"><IMG src="images/trash4848.jpg" alt="
				<? echo _T("[sup]"); ?>" border="0" width="22" height="22"></A>
<?
			}
?>
			</div>

    <TABLE border="0" id="input-table" width="755">





      <tr>
        <td id="libelle" width="190"><b>Asunto</b></td>
        <?php
        	$nom_adh_ext .= " ".htmlentities(custom_html_entity_decode($nom_adh), ENT_QUOTES) ;
		?>
        <td  colspan="3" style="color: red" id="mentions" ; ><b><? echo $nom_adh_ext ; ?></b></td>
      </tr>
      <tr>
        <td id="libelle""><b>Remitente</b></td>
        <td id="mentions"><? echo $ie_adh; ?></td>
        <td id="libelle"" width="130"><b>Pa�s/Embajada</b></td>
        <?php
	$requete = "SELECT nombre_pais
								FROM ".PREFIX_DB."pais
								WHERE id_pais=".$id_pais."
								ORDER BY nombre_pais";
	$result = &$DB->Execute($requete);
	if (!$result->EOF)
		$nombre_pais = $result->fields["nombre_pais"] ;
	$result->Close();
?>
        <td id="mentions"><? echo $nombre_pais ?></td>

      </tr>
      <tr>
        <td id="libelle"><b>Fecha</b></td>
        <td id="mentions"  colspan="3"><? echo $date_crea_adh; ?></td>
      </tr>
      <tr>
        <td id="libelle" valign="top"><b>Asignado</b></td>
        <td  id="mentions" colspan="3"><? echo html_entity_decode($ar_adh); ?></td>
      </tr>

      <tr>
        <td id="libelle" valign="top"><b>Observaciones</b></td>
        <td id="mentions"  colspan="3"><? echo html_entity_decode($ae_adh); ?></td>
      </tr>

      <tr>
        <td id="libelle"><b>Copia</b></td>
        <td  id="mentions" colspan="3"><? echo html_entity_decode($rr_adh); ?></td>
      </tr>


      <?php
      if(!$re_adh==""){ ?>
      <tr>
        <td id="libelle"><b>Copia</b></td>
        <td  id="mentions" colspan="3"><? echo html_entity_decode($re_adh); ?></td>
      </tr>
      <?php
      }
      if(!$dr_adh==""){ ?>
      <tr>
        <td id="libelle"><b>Copia</b></td>
        <td  id="mentions" colspan="3"><? echo html_entity_decode($dr_adh); ?></td>
      </tr>
	  <?php
      }
      if(!$de_adh==""){ ?>
      <tr>
        <td id="libelle"><b>Copia</b></td>
        <td  id="mentions" colspan="3"><? echo html_entity_decode($de_adh); ?></td>
      </tr>
      <?php
      }
      ?>
      <tr>
        <td colspan="4"></td>
      </tr>
<?
			if ($_SESSION["admin_status"]==1)
			{
?>
      <tr>
        <td colspan="4"></td>
      </tr>

<?

	$querys = "SELECT id_dos, libelle_type_dos,name FROM ".PREFIX_DB."archivo,".PREFIX_DB."types_archivo".
				" WHERE id_adh = '$id_adh' AND ".PREFIX_DB."archivo.id_type_dos = ".PREFIX_DB."types_archivo.id_type_dos";

	$resultado = &$DB->Execute($querys);

	if($resultado->EOF)
	{
?>
	<tr>
        <td colspan="2" align="center"><b>No tiene archivos guardados a�n</b></td></tr>
<?
	}
	else {
?>
	<tr>
        <td><b>Archivos relacionados</b></td></tr>
<?	while(!$resultado->EOF)
		{
?>

			<tr><td id="libelle" ><b><i><? echo $resultado->fields[1] ?><i></b></td>
			<td  ><a href="voir_adherent.php?id_dos=<? echo $resultado->fields[0];?>"><? echo $resultado->fields[2]; ?></a></td></tr>

<?
		$resultado->MoveNext();
		}
	}

	$resultado->Close();
?>


      <tr>
        <td colspan="4"></td>
      </tr>
      <?php


				$ajout_archivo = "<A href=\"ajouter_archivo.php?id_adh=".$id_adh."\">".("[ Agregar archivo ]")."</A>";
	//		}
?>

      <tr>
        <td colspan="4" align="center"><br> <a href="ajouter_adherent.php?id_adh=<? echo $id_adh; ?>"><? echo ("[ Editar ]"); ?></a>


        &nbsp;&nbsp;&nbsp;<? echo $ajout_archivo; ?></td>
      </tr>
<?
			}
?>
    </table>
  </DIV>
			<BR>

<?php

  include("footer.php")
?>
