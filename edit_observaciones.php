<?php

	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");

	if ($_SESSION["logged_status"]==0)
		header("location: index.php");

	// On vérifie si on a une référence => modif ou création
	$id_adh = "";
	$date_crea_adh = "";
	if (isset($_GET["id_adh"]))
		if (is_numeric($_GET["id_adh"]))
			$id_adh = $_GET["id_adh"];
	if (isset($_POST["id_adh"]))
		if (is_numeric($_POST["id_adh"]))
			$id_adh = $_POST["id_adh"];

	// Si c'est un user qui est loggé, on va à sa fiche
	if ($_SESSION["admin_status"]!=1)
		$id_adh = $_SESSION["logged_id_adh"];

	// variables d'erreur (pour affichage)
 	$error_detected = "";
 	$warning_detected = "";
 	$confirm_detected = "";

	 //
	// DEBUT parametrage des champs
	//  On recupere de la base la longueur et les flags des champs
	//   et on initialise des valeurs par defaut

	// recuperation de la liste de champs de la table
	$fields = &$DB->MetaColumns(PREFIX_DB."adherents");
	while (list($champ, $proprietes) = each($fields))
	{
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,
		// auto_increment et binary

		$fieldname = $proprietes_arr["name"];

		// on ne met jamais a jour id_adh
		if ($fieldname!="id_adh")
			$$fieldname= "";

	}
	reset($fields);

    //
   // Validation du formulaire
  //

  if (isset($_POST["valid"]))
  {
  	// verification de champs
  	$update_string = "";
  	$insert_string_fields = "";
  	$insert_string_values = "";

  	// recuperation de la liste de champs de la table
	  while (list($champ, $proprietes) = each($fields))
		{
			$proprietes_arr = get_object_vars($proprietes);
			// on obtient name, max_length, type, not_null, has_default, primary_key,
			// auto_increment et binary

			$fieldname = $proprietes_arr["name"];

			// on précise les champs non modifiables
			if (
				($_SESSION["admin_status"]==1 && $fieldname!="id_adh"
							      && $fieldname!="date_echeance") ||
			    	($_SESSION["admin_status"]==0 && $fieldname!="date_crea_adh"
			    				      && $fieldname!="id_adh"
			    				      && $fieldname!="titre_adh"
			    				      && $fieldname!="id_statut"
			    				      && $fieldname!="nom_adh"
			    				      && $fieldname!="prenom_adh"
			    				      && $fieldname!="activite_adh"
			    				      && $fieldname!="bool_exempt_adh"
			    				      && $fieldname!="bool_admin_adh"
			    				      && $fieldname!="date_echeance"
			    				      && $fieldname!="info_adh")
			   )
			{
				if (isset($_POST[$fieldname]))
				  $post_value=trim($_POST[$fieldname]);
				else
					$post_value="";

				// on declare les variables pour la présaisie en cas d'erreur
				$$fieldname = htmlentities(stripslashes($post_value),ENT_QUOTES);
				$fieldreq = $fieldname."_req";

				// vérification de la présence des champs obligatoires
				if ($post_value!=="")
				 // $error_detected .= "<LI>".("- Campo vacio.").$fieldname."</LI>";
				//else
				{
						// on se contente d'escaper le html et les caracteres speciaux
							$value = $DB->qstr($post_value, true);


					// mise à jour des chaines d'insertion/update
					if ($value=="''")
						$value="NULL";
					$update_string .= ",".$fieldname."=".$value; //echo substr($update_string,1);exit;
					$insert_string_fields .= ",".$fieldname;
					$insert_string_values .= ",".$value;
				}
			}
		}
		reset($fields);

  	// modif ou ajout
  	if ($error_detected=="")
  	{
 		 	if ($id_adh!="")
 		 	{
 		 		// modif

				$requete = "UPDATE ".PREFIX_DB."adherents
 		 			    SET " . substr($update_string,1) . "
 		 			    WHERE id_adh=" . $id_adh;
				$DB->Execute($requete);

  			}
/* 		 	else
 		 	{
  			// ajout
 			$insert_string_fields = substr($insert_string_fields,1);
			$insert_string_values = substr($insert_string_values,1);
  			$requete = "INSERT INTO ".PREFIX_DB."adherents
  				    (" . $insert_string_fields . ")
  				    VALUES (" . $insert_string_values . ")";


  		}
*/
					$DB->Execute($requete);

			// il est temps d'envoyer un mail

			// récupération du max pour insertion photo
			// ou passage en mode modif apres insertion
// retour à la liste ou passage à la contribution
/*			if ($warning_detected=="" && $id_adh=="")
			{
				header("location: terminos.php?id_adh=".$id_adh_new);
				die();

			}
*/
			if ($warning_detected=="")
			{
				header("location: voir_adherent.php?id_adh=".$id_adh);
				die();
			}

			$id_adh=$id_adh_new;
  	}

  }

 	// suppression photo

	  //
	 // Pré-remplissage des champs
	//  avec des valeurs issues de la base
	//  -> donc uniquement si l'enregistrement existe et que le formulaire
	//     n'a pas déja été posté avec des erreurs (pour pouvoir corriger)

	if (!isset($_POST["valid"]) || (isset($_POST["valid"]) && $error_detected==""))
	if ($id_adh != "")


	{
		// recup des données
		$requete = "SELECT *
								FROM ".PREFIX_DB."adherents
			  				WHERE id_adh=$id_adh";
		$result = &$DB->Execute($requete);
        	if ($result->EOF)
	                header("location: index.php");



		// recuperation de la liste de champs de la table
	  //$fields = &$DB->MetaColumns(PREFIX_DB."cotisations");
	  while (list($champ, $proprietes) = each($fields))
		{
			//echo $proprietes_arr["name"]." -- (".$result->fields[$proprietes_arr["name"]].")<br>";


			$val="";
			$proprietes_arr = get_object_vars($proprietes);
			// on obtient name, max_length, type, not_null, has_default, primary_key,
			// auto_increment et binary

		  // déclaration des variables correspondant aux champs
		  // et reformatage des dates.

			// on doit faire cette verif pour une enventuelle valeur "NULL"
			// non renvoyée -> ex: pas de societe membre
			// sinon on obtient un warning
			if (isset($result->fields[$proprietes_arr["name"]]))
				$val = $result->fields[$proprietes_arr["name"]];

			if($proprietes_arr["type"]=="date" && $val!="")
			{
			  list($a,$m,$j)=split("-",$val);
			  $val="$j/$m/$a";
			}
		  	$$proprietes_arr["name"]=htmlentities(stripslashes(addslashes($val)), ENT_QUOTES);
		}
		reset($fields);
	}
	else
	{
		// initialisation des champs

	}

	// la date de creation de fiche, ici vide si nouvelle fiche
/*	if ($date_crea_adh=="")
		$date_crea_adh = date("d/m/Y");

*/
	if ($url_adh=="")
		$url_adh = "http://";
	if ($mdp_adh=="")
		$mdp_adh = makeRandomPassword();

	// variable pour la desactivation de champs
	if ($_SESSION["admin_status"]==0)
		$disabled_field = "disabled";
	else
		$disabled_field = "";


	include("header.php");
	include("./fckeditor/fckeditor.php") ;

?>
 <br>
<div class="form-block">
<H1 class="subtitre"> Observaciones (<? if ($id_adh!="") echo ("edición"); else echo ("creación"); ?>)</H1>
<FORM action="edit_observaciones.php" method="post" enctype="multipart/form-data">

<?php
	// Affichage des erreurs
	if ($error_detected!="")
	{
?>
  	<DIV id="errorbox">
  		<H1><? echo ("- ERROR -"); ?></H1>
  		<UL>
  			<? echo $error_detected; ?>
  		</UL>
  	</DIV>
<?php
	}
	if ($warning_detected!="")
	{
?>
	<DIV id="warningbox">
  		<H1><? echo _T("- AVERTISSEMENT -"); ?></H1>
  		<UL>
  			<? echo $warning_detected; ?>
  		</UL>
  	</DIV>
<?php
	}
?>


	<TABLE border="0" id="input-table" width="800">

        <TR>
          <TH colspan="4" class="espacio">Observaciones</TH>
        </TR>
        <TR>

          <TD colspan="4" ><?php
$oFCKeditor = new FCKeditor('co_adh') ;
$oFCKeditor->BasePath = 'fckeditor/';
$output = $oFCKeditor->CreateHtml() ;
$oFCKeditor->ToolbarSet = 'MitoolBar';
$oFCKeditor->Value = html_entity_decode($co_adh);
$oFCKeditor->Create() ;
?>
            <BR> <DIV class="exemple">Agrege un Comentario si lo desea</DIV></TD>
        </TR>


          <TH colspan="4" id="espacio">&nbsp;</TH>
          </TR>
        <TR>
          <TH align="center" colspan="4"><BR> <INPUT type="submit" name="valid" value="GRABAR" class="button" ></TH>
        </TR>
      </TABLE>
						</DIV>
						<BR>

						<INPUT type="hidden" name="id_adh" value="<? echo $id_adh ?>">
						</FORM>
<?php
  include("footer.php")
?>
