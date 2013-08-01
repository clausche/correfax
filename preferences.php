<?php


	include("includes/config.inc.php");
	include(WEB_ROOT."includes/database.inc.php");
	include(WEB_ROOT."includes/functions.inc.php");
	include(WEB_ROOT."includes/lang.inc.php");
	include(WEB_ROOT."includes/session.inc.php");

	if ($_SESSION["logged_status"]==0)
		header("location: index.php");
	if ($_SESSION["admin_status"]==0)
		header("location: voir_adherent.php");

	// On v�ifie si on a une r��ence => modif ou cr�tion

	// variables d'erreur (pour affichage)
 	$error_detected = "";
 	$warning_detected = "";

	  //
	 // DEBUT parametrage des champs
	//  on initialise des valeurs par defaut

	// recup des donnees
        $requete = "SELECT nom_pref
                    FROM ".PREFIX_DB."preferences";
        $result = &$DB->Execute($requete);
        while (!$result->EOF)
        {
		$fieldname = $result->fields["nom_pref"];
                $$fieldname = "";

		// declaration des champs obligatoires
		$fieldreq = $fieldname."_req";
		if ($fieldname=="pref_nom" ||
		    $fieldname=="pref_lang" ||
		    $fieldname=="pref_numrows" ||
		    $fieldname=="pref_log" ||
		    $fieldname=="pref_email_nom" ||
		    $fieldname=="pref_email" ||
		    $fieldname=="pref_etiq_marges" ||
		    $fieldname=="pref_etiq_hspace" ||
		    $fieldname=="pref_etiq_vspace" ||
		    $fieldname=="pref_etiq_hsize" ||
		    $fieldname=="pref_etiq_vsize" ||
		    $fieldname=="pref_etiq_cols" ||
		    $fieldname=="pref_etiq_rows" ||
		    $fieldname=="pref_etiq_corps" ||
		    $fieldname=="pref_admin_login" ||
		    $fieldname=="pref_admin_pass")
			$$fieldreq = " style=\"color: #FF0000;\"";
		else
			$$fieldreq = "";

		 $result->MoveNext();
        }
        $result->Close();

	  //
	 // FIN parametrage des champs
	//

	  //
	 // Validation du formulaire
	//

	if (isset($_POST["valid"]))
	{
  		// verification de champs
	  	$insert_values = array();

		// recuperation de la liste de champs de la table
		$requete = "SELECT nom_pref
			    FROM ".PREFIX_DB."preferences";
		$result=&$DB->Execute($requete);
		while (!$result->EOF)
		{
			$fieldname = $result->fields["nom_pref"];
			$fieldreq = $fieldname."_req";

			if (isset($_POST[$fieldname]))
				$post_value=trim($_POST[$fieldname]);
			else
				$post_value="";

			// on declare les variables pour la pr�aisie en cas d'erreur
			$$fieldname=htmlentities(stripslashes($post_value),ENT_QUOTES);

			// v�ification de la pr�ence des champs obligatoires
			$req = $$fieldreq;
			if ($req!="" && $post_value=="")
				$error_detected .= "<LI>"._T("- Champ obligatoire non renseign�")."</LI>";
			else
			{
				// validation de la langue
				if ($fieldname=="pref_lang")
 				{
 					if (file_exists(WEB_ROOT . "lang/lang_" . $post_value . ".php"))
		 				$value = $DB->qstr($post_value, true);
		 			else
				  		$error_detected .= "<LI>"._T("- Langue non valide !")."</LI>";
				}
				// validation des adresses mail
				elseif ($fieldname=="pref_email")
 				{
 					$post_value=strtolower($post_value);
					if (!is_valid_email($post_value) && $post_value!="")
				  	$error_detected .= "<LI>"._T("- Adresse E-mail non valide !")."</LI>";
					else
		 				$value = $DB->qstr($post_value, true);
				}
				// validation login
  				elseif ($fieldname=="pref_admin_login")
 				{
 					if (strlen($post_value)<4)
 						$error_detected .= "<LI>"._T("- L'identifiant doit �re compos�d'au moins 4 caract�es !")."</LI>";
 					else
 					{
 						// on v�ifie que le login n'est pas d��utilis�
 						$requete = "SELECT id_adh FROM ".PREFIX_DB."correspondencia WHERE login_adh=". $DB->qstr($post_value, true);
 						if ($id_adh!="")
 							$requete .= " AND id_adh!=" . $DB->qstr($id_adh, true);

 						$result2 = &$DB->Execute($requete);
						if (!$result2->EOF)
	 						$error_detected .= "<LI>"._T("- Cet identifiant est d��utilis�par un adh�ent !")."</LI>";
						else
	 						$value = $DB->qstr($post_value, true);
					}
 				}
				// validation des entiers
				elseif ($fieldname=="pref_numrows" ||
				        $fieldname=="pref_etiq_marges" ||
		                        $fieldname=="pref_etiq_hspace" ||
					$fieldname=="pref_etiq_vspace" ||
					$fieldname=="pref_etiq_hsize" ||
					$fieldname=="pref_etiq_vsize" ||
					$fieldname=="pref_etiq_cols" ||
					$fieldname=="pref_etiq_rows" ||
					$fieldname=="pref_etiq_corps")
 				{
 					// �itons la divison par zero
 					if ($fieldname=="pref_numrows" && $post_value=="0")
 						$post_value="1";

 					if ((is_numeric($post_value) && $post_value >=0) || $post_value=="")
						$value=$DB->qstr($post_value,ENT_QUOTES);
					else
						$error_detected .= "<LI>"._T("- Les nombres et mesures doivent �re des entiers !")."</LI>";
 				}
				// validation mot de passe
 				elseif ($fieldname=="pref_admin_pass")
 				{
 					if (strlen($post_value)<4)
 						$error_detected .= "<LI>"._T("- Le mot de passe doit �re compos�d'au moins 4 caract�es !")."</LI>";
 					else
 						$value = $DB->qstr($post_value, true);
 				}
 				else
 				{
 					// on se contente d'escaper le html et les caracteres speciaux
					$value = $DB->qstr($post_value, true);
				}

				// mise a jour des chaines d'insertion
				if ($value=="''")
					$value="NULL";
				$insert_values[$fieldname] = $value;
			}
			$result->MoveNext();
		}
		$result->Close();

  		// modif ou ajout
  		if ($error_detected=="")
  		{
			// vidage des preferences
			$requete = "DELETE FROM ".PREFIX_DB."preferences";
			$DB->Execute($requete);

			// insertion des nouvelles preferences
			while (list($champ,$valeur)=each($insert_values))
			{
				$requete = "INSERT INTO ".PREFIX_DB."preferences
					    (nom_pref, val_pref)
					    VALUES (".$DB->qstr($champ).",".$valeur.");";
				$DB->Execute($requete);
			}

			// ajout photo
			if (isset($_FILES["photo"]["tmp_name"]))
                        if ($_FILES["photo"]["tmp_name"]!="none" &&
                            $_FILES["photo"]["tmp_name"]!="")
			{

				if ($_FILES['photo']['type']=="image/jpeg" ||
				    (function_exists("ImageCreateFromGif") && $_FILES['photo']['type']=="image/gif") ||
				    $_FILES['photo']['type']=="image/png" ||
				    $_FILES['photo']['type']=="image/x-png")
				{
					$tmp_name = $HTTP_POST_FILES["photo"]["tmp_name"];

					// extension du fichier (en fonction du type mime)
					if ($_FILES['photo']['type']=="image/jpeg")
						$ext_image = ".jpg";
					if ($_FILES['photo']['type']=="image/png" || $_FILES['photo']['type']=="image/x-png")
						$ext_image = ".png";
					if ($_FILES['photo']['type']=="image/gif")
						$ext_image = ".gif";

					// suppression ancienne photo
					// NB : une verification sur le type de $id_adh permet d'eviter une faille
					//      du style $id_adh = "../../../image"
					@unlink(WEB_ROOT . "photos/logo.jpg");
					@unlink(WEB_ROOT . "photos/logo.gif");
					@unlink(WEB_ROOT . "photos/logo.jpg");
					@unlink(WEB_ROOT . "photos/tn_logo.jpg");
					@unlink(WEB_ROOT . "photos/tn_logo.gif");
					@unlink(WEB_ROOT . "photos/tn_logo.jpg");

					// copie fichier temporaire
					if (!@move_uploaded_file($tmp_name,WEB_ROOT . "photos/logo".$ext_image))
						$warning_detected .= "<LI>"._T("- La photo semble ne pas avoir ��transmise correstement. L'enregistrement a cependant ��effectu�")."</LI>";
				 	else
						resizeimage(WEB_ROOT . "photos/logo".$ext_image,WEB_ROOT . "photos/tn_logo".$ext_image,130,130);

			 	}
			 	else
				{
					if (function_exists("ImageCreateFromGif"))
			 			$warning_detected .= "<LI>"._T("- Le fichier transmis n'est pas une image valide (GIF, PNG ou JPEG). L'enregistrement a cependant ��effectu�")."</LI>";
					else
			 			$warning_detected .= "<LI>"._T("- Le fichier transmis n'est pas une image valide (PNG ou JPEG). L'enregistrement a cependant ��effectu�")."</LI>";
				}
			}

			// retour �l'accueil
			if ($warning_detected=="")
			{
				header("location: index.php");
				die();
			}
		}
	}

 	// suppression photo
	if (isset($_POST["del_photo"]))
  {
 		@unlink(WEB_ROOT . "photos/logo.jpg");
 		@unlink(WEB_ROOT . "photos/logo.png");
 		@unlink(WEB_ROOT . "photos/logo.gif");
 		@unlink(WEB_ROOT . "photos/tn_logo.jpg");
 		@unlink(WEB_ROOT . "photos/tn_logo.png");
 		@unlink(WEB_ROOT . "photos/tn_logo.gif");
 	}

	  //
	 // Pr�remplissage des champs
	//  avec des valeurs issues de la base
	//  -> donc uniquement si l'enregistrement existe et que le formulaire
	//     n'a pas d�a ��post�avec des erreurs (pour pouvoir corriger)

	if (!isset($_POST["valid"]) || (isset($_POST["valid"]) && $error_detected==""))
	{
		// recup des donnees
		$requete = "SELECT *
		  	    FROM ".PREFIX_DB."preferences";
		$result = &$DB->Execute($requete);
        	if ($result->EOF)
	                header("location: index.php");
		else
		{
			while (!$result->EOF)
			{
				$fieldname=$result->fields["nom_pref"];
				$$fieldname = htmlentities(stripslashes(addslashes($result->fields["val_pref"])), ENT_QUOTES);
				$result->MoveNext();
			}
		}
		$result->Close();
	}
	else
	{
		// initialisation des champs
	}

	include("header.php");

?>
						<H1 class="titre"><? echo _T("Pr��ences"); ?></H1>
						<FORM action="preferences.php" method="post" enctype="multipart/form-data">
<?
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
	if ($warning_detected!="")
	{
?>
	<DIV id="warningbox">
  		<H1><? echo _T("- AVERTISSEMENT -"); ?></H1>
  		<UL>
  			<? echo $warning_detected; ?>
  		</UL>
  	</DIV>
<?
	}
?>
						<BLOCKQUOTE>
						<DIV align="center">

						<TABLE border="0" id="input-table">
							<TR>
								<TH colspan="2" id="header"><? echo _T("Informations g��ales :"); ?></TH>
							</TR>
							<TR>
								<TH <? echo $pref_nom_req ?> id="libelle"><? echo _T("Nom (raison sociale) de l'association :"); ?></TH>
								<TD><INPUT type="text" name="pref_nom" value="<? echo $pref_nom; ?>" maxlength="190"></TD>
							</TR>
							<TR>
								<TH id="libelle"><? echo _T("Logo :"); ?></TH>
								<td>
								<?
									$logo_asso = "";
									if (file_exists(WEB_ROOT . "photos/tn_logo.jpg"))
										$logo_asso = "photos/tn_logo.jpg";
									elseif (file_exists(WEB_ROOT . "photos/tn_logo.gif"))
										$logo_asso = "photos/tn_logo.gif";
									elseif (file_exists(WEB_ROOT . "photos/tn_logo.png"))
										$logo_asso = "photos/tn_logo.png";
									elseif (file_exists(WEB_ROOT . "photos/logo.jpg"))
										$logo_asso = "photos/logo.jpg";
									elseif (file_exists(WEB_ROOT . "photos/logo.gif"))
										$logo_asso = "photos/logo.gif";
									elseif (file_exists(WEB_ROOT . "photos/logo.png"))
										$logo_asso = "photos/logo.png";

									if ($logo_asso != "")
									{
										if (function_exists("ImageCreateFromString"))
											$imagedata = getimagesize($logo_asso);
										else
											$imagedata = array("130","");
								?>
									<img src="<? echo $logo_asso."?nocache=".time(); ?>" border="1" alt="<? echo _T("Photo"); ?>" width="<? echo $imagedata[0]; ?>" height="<? echo $imagedata[1]; ?>"><BR>
									<input type="submit" name="del_photo" value="<? echo _T("Supprimer la photo"); ?>">
								<?
									}
									else
									{
								?>
										<input type="file" name="photo">
								<?
									}
								?>
								</td>
						  </TR>
						  <TR>
								<TH<? echo $pref_adresse_req ?> id="libelle"><? echo _T("Adresse :"); ?></TH>
								<td><input type="text" name="pref_adresse" value="<? echo $pref_adresse; ?>" maxlength="190" size="42"></td>
							</TR>
						  <TR>
								<TH id="libelle">&nbsp;</TH>
								<td><input type="text" name="pref_adresse2" value="<? echo $pref_adresse2; ?>" maxlength="190" size="42"></td>
							</TR>
						  <TR>
								<TH<? echo $pref_cp_req ?> id="libelle"><? echo _T("Code Postal :"); ?></TH>
								<td><input type="text" name="pref_cp" value="<? echo $pref_cp; ?>" maxlength="10"></td>
							</TR>
						  <TR>
								<TH<? echo $pref_ville_req ?> id="libelle"><? echo _T("Ville :"); ?></TH>
								<td><input type="text" name="pref_ville" value="<? echo $pref_ville; ?>" maxlength="100"></td>
							</TR>
						  <TR>
								<TH<? echo $pref_pays_req ?> id="libelle"><? echo _T("Pays :"); ?></TH>
								<td><input type="text" name="pref_pays" value="<? echo $pref_pays; ?>" maxlength="50"></td>
							</TR>
							<TR>
								<TH colspan="2" id="header"><BR><? echo _T("Parámetro GAC :"); ?></TH>
							</TR>
						  <TR>
								<TH<? echo $pref_lang_req ?> id="libelle"><? echo _T("Langue :"); ?></TH>
								<TD>
									<SELECT name="pref_lang">
<?
	$path = WEB_ROOT."/lang";
	$dir_handle = @opendir($path);
	while ($file = readdir($dir_handle))
	{
		if (substr($file,0,5)=="lang_" && substr($file,-4)==".php")
		{
        $file = substr(substr($file,5),0,-4);
?>
										<OPTION value="<? echo $file; ?>" <? isSelected($file,$pref_lang) ?>><? echo ucfirst($file); ?></OPTION>
<?
		}
	}
	closedir($dir_handle);
?>
									</SELECT>
								</TD>
							</TR>
						  <TR>
								<TH<? echo $pref_numrows_req ?> id="libelle"><? echo _T("Lignes / Page :"); ?></TH>
								<td><input type="text" name="pref_numrows" value="<? echo $pref_numrows; ?>" maxlength="5"> <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_log_req ?> id="libelle"><? echo _T("Niveau d'historique :"); ?></TH>
								<TD>
									<SELECT name="pref_log">
										<OPTION value="0" <? isSelected("0",$pref_log) ?>><? echo _T("Nul"); ?></OPTION>
										<OPTION value="1" <? isSelected("1",$pref_log) ?>><? echo _T("Normal"); ?></OPTION>
										<OPTION value="2" <? isSelected("2",$pref_log) ?>><? echo _T("D\E9taill\E9"); ?></OPTION>
									</SELECT>
								</TD>
							</TR>
							<TR>
								<TH colspan="2" id="header"><BR><? echo _T("Param�res mail :"); ?></TH>
							</TR>
						  <TR>
								<TH<? echo $pref_email_nom_req ?> id="libelle"><? echo _T("Nom exp�iteur :"); ?></TH>
								<td><input type="text" name="pref_email_nom" value="<? echo $pref_email_nom; ?>" maxlength="50"></td>
							</TR>
						  <TR>
								<TH<? echo $pref_email_req ?> id="libelle"><? echo _T("Email exp�iteur :"); ?></TH>
								<td><input type="text" name="pref_email" value="<? echo $pref_email; ?>" maxlength="100" size="30"></td>
							</TR>
							<TR>
								<TH colspan="2" id="header"><BR><? echo _T("Param�res de g��ation d'�iquettes :"); ?></TH>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_marges_req ?> id="libelle"><? echo _T("Marges :"); ?></TH>
								<td><input type="text" name="pref_etiq_marges" value="<? echo $pref_etiq_marges; ?>" maxlength="4"> mm <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_hspace_req ?> id="libelle"><? echo _T("Espacement horizontal :"); ?></TH>
								<td><input type="text" name="pref_etiq_hspace" value="<? echo $pref_etiq_hspace; ?>" maxlength="4"> mm <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_vspace_req ?> id="libelle"><? echo _T("Espacement vertical :"); ?></TH>
								<td><input type="text" name="pref_etiq_vspace" value="<? echo $pref_etiq_vspace; ?>" maxlength="4"> mm <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_hsize_req ?> id="libelle"><? echo _T("Largeur �iquette :"); ?></TH>
								<td><input type="text" name="pref_etiq_hsize" value="<? echo $pref_etiq_hsize; ?>" maxlength="4"> mm <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_vsize_req ?> id="libelle"><? echo _T("Hauteur �iquette :"); ?></TH>
								<td><input type="text" name="pref_etiq_vsize" value="<? echo $pref_etiq_vsize; ?>" maxlength="4"> mm <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_cols_req ?> id="libelle"><? echo _T("Nombre de colonnes d'�iquettes :"); ?></TH>
								<td><input type="text" name="pref_etiq_cols" value="<? echo $pref_etiq_cols; ?>" maxlength="4"> <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_rows_req ?> id="libelle"><? echo _T("Nombre de lignes d'�iquettes :"); ?></TH>
								<td><input type="text" name="pref_etiq_rows" value="<? echo $pref_etiq_rows; ?>" maxlength="4"> <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
						  <TR>
								<TH<? echo $pref_etiq_corps_req ?> id="libelle"><? echo _T("Corps du texte :"); ?></TH>
								<td><input type="text" name="pref_etiq_corps" value="<? echo $pref_etiq_corps; ?>" maxlength="4"> <SPAN class="exemple"><? echo _T("(Entier)"); ?></SPAN></td>
							</TR>
							<TR>
								<TH colspan="2" id="header"><BR><? echo _T("Compte administrateur (ind�endant des adh�ents) :"); ?></TH>
							</TR>
							<TR>
								<TH <? echo $pref_admin_login_req ?> id="libelle"><? echo _T("Identifiant :"); ?></TH>
								<TD><INPUT type="text" name="pref_admin_login" value="<? echo $pref_admin_login; ?>" maxlength="20"></TD>
							</TR>
							<TR>
								<TH <? echo $pref_admin_pass_req ?> id="libelle"><? echo _T("Mot de passe :"); ?></TH>
								<TD><INPUT type="text" name="pref_admin_pass" value="<? echo $pref_admin_pass; ?>" maxlength="20"></TD>
							</TR>
							<TR>
								<TH align="center" colspan="2"><BR><INPUT type="submit" name="valid" value="<? echo _T("Enregistrer"); ?>"></TH>
						  </TR>
							</TABLE>
						</DIV>
						<BR>
						<? echo _T("NB : Les champs obligatoires apparaissent en"); ?> <font style="color: #FF0000"><? echo _T("rouge"); ?></font>.
						</BLOCKQUOTE>
						</FORM>
<?
  include("footer.php")
?>
