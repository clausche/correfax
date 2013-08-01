<?php

	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");

	$id_adh = "";
	if ($_SESSION["logged_status"]==0)
		header("location: index.php");
	if ($_SESSION["admin_status"]==0)
		$id_adh = $_SESSION["logged_id_adh"];

	// On vï¿?ifie si on a une rï¿?ï¿?ence => modif ou crï¿?tion
	if (isset($_GET["id_adh"]))
		if (is_numeric($_GET["id_adh"]))
			$id_adh = $_GET["id_adh"];

	if ($_SESSION["admin_status"]==0)
		$id_adh = $_SESSION["logged_id_adh"];
	if ($id_adh=="")
		header("location: index.php");

     //
    // Prï¿?remplissage des champs
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

		$$proprietes_arr["name"] = htmlentities(stripslashes(addslashes($val)), ENT_QUOTES);
	}
	reset($fields);
	include("header.php");


?>
<br><br>
  <div class="form-block">

    <table border="0" id="input-table" width="755">
		<tr><td colspan="4" id="mentions"><? echo $nom_adh; ?><td><tr>
      <tr>
        <td id="libelle" width="190"><b>Asunto</b></td>
        <?php
        	$nom_adh_ext .= " ".htmlentities(custom_html_entity_decode($nom_adh), ENT_QUOTES) ;
		?>
        <td  colspan="3" style="color: red" id="mentions" ; ><b><? echo $nom_adh_ext ; ?></b></td>
      </tr>
      <tr>
        <td id="libelle" width="190"><b>Número</b></td>

        <td ><b><? echo $ir_adh ; ?></b></td>
                <td id="libelle"><b>Asignado</b></td>

        <td id="mentions" ><? echo $ar_adh ; ?></td>
      </tr>

      <tr>
        <td id="libelle"><b>Fecha Recepci&oacute;n</b></td>
        <td  ><? echo $date_crea_adh; ?></td>
        <td id="libelle" width="130"><b>País/Embajada</b></td>
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


      </tr>

      <tr>
        <td id="libelle"><b>Remitente Recepci&oacute;n</b></td>
        <td  > <? echo $ie_adh; ?>
          <?php /* if ($url_adh!="") { ?>
          <a href="<? echo $url_adh; ?>"><? echo $url_adh; ?></a>
          <? }  */ ?>
          </td>
        <td id="libelle"><b>Copia</b></td>
        <td  ><? echo $rr_adh; ?></td>
      </tr>

      <tr>
        <td id="libelle"><b>Observaciones</b></td>
        <td colspan="3" ><? echo $ae_adh; ?></td>


      </tr>



      <tr>
        <td colspan="4" align="center"><br> <a href="ajouter_adherent.php?id_adh=<? echo $id_adh; ?>">
        <? echo ("[ Editar ]"); ?></a></td>
      </tr>

    </table>
  </DIV>
			<BR>

<?php

  include("footer.php")
?>
