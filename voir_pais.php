<?php

	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");

	$id_paises = "";
	if ($_SESSION["logged_status"]==0)
		header("location: index.php");
	if ($_SESSION["admin_status"]==0)
		$id_paises = $_SESSION["logged_id_adh"];

	// On v�ifie si on a une r��ence => modif ou cr�tion
	if (isset($_GET["id_paises"]))
		if (is_numeric($_GET["id_paises"]))
 			$id_paises = $_GET["id_paises"];

	if ($_SESSION["admin_status"]==0)
		$id_adh = $_SESSION["logged_id_adh"];
	if ($id_paises=="")
		header("location: index.php");

     //
    // Pr�remplissage des champs
   //  avec des valeurs issues de la base
  //

	$requete = "SELECT *
							FROM ".PREFIX_DB."paises
							WHERE id_paises=$id_paises";
	$result = &$DB->Execute($requete);
        if ($result->EOF)
		header("location: index.php");

	// recuperation de la liste de champs de la table
  $fields = &$DB->MetaColumns(PREFIX_DB."paises");
	while (list($champ, $proprietes) = each($fields))
	{
		$val="";
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,

		// d�laration des variables correspondant aux champs
		// et reformatage des dates.

		// on doit faire cette verif pour une enventuelle valeur "NULL"
		// non renvoy� -> ex: pas de tel
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
<br>
        <?php
	$requete = "SELECT nombre_pais FROM ".PREFIX_DB."pais,".PREFIX_DB."paises WHERE id_paises=".$id_paises."
			ORDER BY nombre_pais";
	$result = &$DB->Execute($requete);
	if (!$result->EOF)
		$nombre_pais = $result->fields["nombre_pais"] ;
	$result->Close();


?>


  <DIV align="left"> <H1 class="subtitre" ><? echo "Panorama General : $nombre_pais"; ?></H1>
    <table border="0" id="input-table" width="755">
      <tr>
        <td bgcolor="#DDDDFF"><b>Pa��s</b></td>
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
        <td bgcolor="#EEEEEE"><? echo $nombre_pais ?>&nbsp;</td>
      </tr>
      <tr>
        <td bgcolor="#DDDDFF">Fecha de actualizac�on</td>
        <td bgcolor="#EEEEEE">&nbsp;<? echo $date_paises; ?></td>
      </tr>
      <tr>
        <td colspan="4">&nbsp;</td>
      </tr>
        <tr>
	<td colspan="2" id="header" ><? echo ("Panorama General :"); ?></td></tr>
	<tr><td colspan="2" bgcolor="#EEEEEE"><? echo html_entity_decode($pano_gen) ; ?></td>
      </tr>
    <tr>
	<td colspan="2" id="header"><? echo ("Actualidad :"); ?></td></tr>
	<tr><td colspan="2" bgcolor="#EEEEEE"><? echo html_entity_decode($actu); ?></td>
      </tr>
      <tr>
	<td colspan="2"id="header"><? echo ("Estadisticas :"); ?></td></tr>
	<tr><td colspan="2" bgcolor="#EEEEEE"><? echo html_entity_decode($estatis); ?></td>
      </tr>
      <tr>
	<td colspan="2" id="header"><? echo ("Actividades :"); ?></td></tr>
	<tr><td colspan="2" bgcolor="#EEEEEE"><? echo html_entity_decode($acti); ?></td>
      </tr>
      <tr>
	<td colspan="2" id="header"><? echo ("Marco Jur�dico :"); ?></td></tr>
	<tr><td colspan="2" bgcolor="#EEEEEE"><? echo html_entity_decode($juri); ?></td>
      </tr>
      <tr>
        <td colspan="4">&nbsp;</td>
      </tr>

      <?php

				$ajout_contrib = "&nbsp;&nbsp;&nbsp;<A href=\"ajouter_contribution.php?id_adh=".$id_adh."\">".("[ Agregar una Modificaci&oacute;n ]")."</A>";
				$ajout_archivo = "&nbsp;&nbsp;&nbsp;<A href=\"ajouter_archivo.php?id_adh=".$id_adh."\">".("[ Agregar archivo ]")."</A>";
	//		}

			if ($_SESSION["admin_status"]==1)
			{
?>

      <tr>
        <td colspan="4" align="center"><br> <a href="ajouter_pais.php?id_paises=<? echo $id_paises; ?>"><? echo ("[ Editar ]"); ?></td>
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
