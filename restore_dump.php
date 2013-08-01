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
	$fields = &$DB->MetaColumns(PREFIX_DB."correspondencia");
	while (list($champ, $proprietes) = each($fields))
	{
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,
		// auto_increment et binary

		$fieldname = $proprietes_arr["name"];

		// on ne met jamais a jour id_adh
		if ($fieldname!="id_adh" && $fieldname!="date_echeance")
			$$fieldname= "";

	  $fieldlen = $fieldname."_len";
	  $fieldreq = $fieldname."_req";

	  // definissons  aussi la longueur des input text
	  $max_tmp = $proprietes_arr["max_length"];
	  if ($max_tmp == "-1")
	  	$max_tmp = 88;
	  $fieldlen = $fieldname."_len";
	  $$fieldlen=$max_tmp;

	  // et s'ils sont obligatoires (à partir de la base)
	  if ($proprietes_arr["not_null"]==1)
		  $$fieldreq = "style=\"color: #FF0000;\"";
		else
		  $$fieldreq = "";
	}
	reset($fields);

	// et les valeurs par defaut
	$id_statut = "4";
	$titre_adh = "1";

	  //
	 // FIN parametrage des champs
	//

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
							      && $fieldname!="date_echeance"
							      && $fieldname!="ter_adh"
							      && $fieldname!="co_adh"
							      && $fieldname!="pro_adh"
							      && $fieldname!="pays_adh"
							      && $fieldname!="ville_adh") ||
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
			    				      && $fieldname!="info_adh"
			    				      && $fieldname!="ter_adh")
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
				if ($$fieldreq!="" && $post_value=="")
				  $error_detected .= "<LI>".("- Campo vacio.")."</LI>";
				else
				{
					// validation des dates
					if($proprietes_arr["type"]=="date" && $post_value!="")
					{
					  	if (ereg("^([0-9]{2})/([0-9]{2})/([0-9]{4})$", $post_value, $array_jours) || $post_value=="")
					  	{
							if (checkdate($array_jours[2],$array_jours[1],$array_jours[3]) || $post_value=="")
//								$value=$DB->DBDate(mktime(0,0,0,$array_jours[2],$array_jours[1],$array_jours[3]));
								$value = $DB->DBDate($array_jours[3].'/'.$array_jours[2].'/'.$array_jours[1]);
							else
								$error_detected .= "<LI>"._T("- Date non valide !")."</LI>";
						}
					  	else
					  		$error_detected .= "<LI>"._T("- Mauvais format de date (jj/mm/aaaa) !")."</LI>";
					}
 					elseif ($fieldname=="email_adh")
 					{
 						$post_value=strtolower($post_value);
						if (!is_valid_email($post_value) && $post_value!="")
					  	$error_detected .= "<LI>"._T("- Adresse E-mail non valide !")."</LI>";
						else
		 					$value = $DB->qstr($post_value, true);

		 				if ($post_value=="" && isset($_POST["mail_confirm"]))
		 					$error_detected .= "<LI>"._T("- Vous ne pouvez pas envoyer de confirmation par mail si l'adhérent n'a pas d'adresse !")."</LI>";
					}
 					elseif ($fieldname=="url_adh")
 					{
 						if (!is_valid_web_url($post_value) && $post_value!="" && $post_value!="http://")
					  	$error_detected .= "<LI>"._T("- Adresse web non valide ! Oubli du http:// ?")."</LI>";
						else
						{
							if ($post_value=="http://")
								$post_value="";
		 					$value = $DB->qstr($post_value, true);
						}
					}

 					else
 					{
 						// on se contente d'escaper le html et les caracteres speciaux
							$value = $DB->qstr($post_value, true);
					}

					// mise à jour des chaines d'insertion/update
					if ($value=="''")
						$value="NULL";
					$update_string .= ",".$fieldname."=".$value;
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

				$requete = "UPDATE ".PREFIX_DB."correspondencia
 		 			    SET " . substr($update_string,1) . "
 		 			    WHERE id_adh=" . $id_adh;
				$DB->Execute($requete);
				dblog(_T("Mise à jour de la fiche adhérent :")." ".strtoupper($_POST["nom_adh"])." ".$_POST["prenom_adh"], $requete);

				$date_fin = get_echeance($DB, $id_adh);
				if ($date_fin!="")
//					$date_fin_update = $DB->DBDate(mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]));
					$date_fin_update = $DB->DBDate($date_fin[2].'/'.$date_fin[1].'/'.$date_fin[0]);
				else
					$date_fin_update = "NULL";
				$requete = "UPDATE ".PREFIX_DB."correspondencia
					    SET date_echeance=".$date_fin_update."
					    WHERE id_adh=" . $id_adh;
  			}
 		 	else
 		 	{
  			// ajout
 			$insert_string_fields = substr($insert_string_fields,1);
			$insert_string_values = substr($insert_string_values,1);
  			$requete = "INSERT INTO ".PREFIX_DB."correspondencia
  				    (" . $insert_string_fields . ")
  				    VALUES (" . $insert_string_values . ")";
			dblog(_T("Ajout de la fiche adhérent :")." ".strtoupper($_POST["nom_adh"])." ".$_POST["prenom_adh"], $requete);

  		}
			$DB->Execute($requete);

			// il est temps d'envoyer un mail

			// récupération du max pour insertion photo
			// ou passage en mode modif apres insertion
// retour à la liste ou passage à la contribution
			if ($warning_detected=="" && $id_adh=="")
			{
				header("location: ajouter_archivo.php?id_adh=".$id_adh_new);
				die();

			}
			elseif ($warning_detected=="" && !isset($_FILES["photo"]))
			{
				header("location: voir_adherent.php?id_adh=".$id_adh);
				die();
			}
			elseif ($warning_detected=="" && ($_FILES["photo"]["tmp_name"]=="none" || $_FILES["photo"]["tmp_name"]==""))
			{
				header("location: gestion_correspondencia.php");
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
								FROM ".PREFIX_DB."correspondencia
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
<div class="form-block">
<H1> </H1><ul><b>Restaurar</ul>
<FORM action="restaurar.php" method="post" enctype="multipart/form-data" >

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



				<TABLE border="0" id="input-table">
	
				<tr> 
					<TH id="libelle" align="center" <? echo $userfile_req?>>Restaurar</TH> 
				         <td><input  type="file" name="userfile" ></td> 
				</tr>   
				<tr> 
					<TH align="center" colspan="4"><BR>

					<input class="button" type="submit" name="valid" value="<? echo "Enviar"; ?>" onclick = "return showMessage()"></TH> 
					
				</tr> 
				</table>							
				</div>
				<br> 
				</form> 


<?php
  include("footer.php")
?>
