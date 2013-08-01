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
		
	// On v�rifie si on a une r�f�rence => modif ou cr�ation
	$id_cotis = "";
  if (isset($_GET["id_cotis"]))
	 	if (is_numeric($_GET["id_cotis"]))
	    $id_cotis = $_GET["id_cotis"];
  if (isset($_POST["id_cotis"]))
 		if (is_numeric($_POST["id_cotis"]))
	    $id_cotis = $_POST["id_cotis"];

	// variables d'erreur (pour affichage)	    
 	$error_detected = "";
	
	  //
	 // DEBUT parametrage des champs
  //  On recupere de la base la longueur et les flags des champs
  //  et on initialise des valeurs par defaut
    
 	// recuperation de la liste de champs de la table
  $fields = &$DB->MetaColumns(PREFIX_DB."cotisations");
  while (list($champ, $proprietes) = each($fields))
	{
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,
		// auto_increment et binary		
		
		$fieldname = $proprietes_arr["name"];
		$fieldreq = $fieldname."_req";
		$fieldlen = $fieldname."_len";
	
		// on ne met jamais a jour id_cotis -> on le saute
		if ($fieldname!="id_cotis")
			$$fieldname = "";

	  // definissons  aussi la longueur des input text
	  $max_tmp = $proprietes_arr["max_length"];
	  if ($max_tmp == "-1")
	  	$max_tmp = 10;
	  $$fieldlen = $max_tmp;

	  // et s'ils sont obligatoires (� partir de la base)
	  if ($proprietes_arr["not_null"]==1)
	    $$fieldreq = " style=\"color: #FF0000;\"";
  	  else
	    $$fieldreq = "";
	}
	reset($fields);

	// et les valeurs par defaut
	$id_type_cotis = "1";
	$duree_mois_cotis = "12";

	  //
	 // FIN parametrage des champs
	// 

  $id_adh = "";
  if (isset($_GET["id_adh"]))
  	$id_adh = $_GET["id_adh"];
  elseif (isset($_POST["id_adh"]))
  	$id_adh = $_POST["id_adh"];
  	if ($id_adh!="")
  	{
  		$requete = "SELECT nom_adh, prenom_adh FROM ".PREFIX_DB."correspondencia WHERE id_adh=".$DB->qstr($id_adh);
		$resultat = $DB->Execute($requete);
		if (!$resultat->EOF)
		{
			$nom_adh = $resultat->fields[0];
			$prenom_adh = $resultat->fields[1];
			$resultat->Close();
		}
  	}
    
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
	  //$fields = &$DB->MetaColumns(PREFIX_DB."cotisations");
	  while (list($champ, $proprietes) = each($fields))
		{
			$proprietes_arr = get_object_vars($proprietes);
			// on obtient name, max_length, type, not_null, has_default, primary_key,
			// auto_increment et binary		
		
			$fieldname = $proprietes_arr["name"];
			$fieldreq = $fieldname."_req";

			// on ne met jamais a jour id_cotis -> on le saute
			if ($fieldname!="id_cotis")
			{			
				if (isset($_POST[$fieldname]))
				  $post_value=trim($_POST[$fieldname]);
				else			
					$post_value="";
					
				// on declare les variables pour la pr�saisie en cas d'erreur
				$$fieldname = htmlentities(stripslashes($post_value),ENT_QUOTES);

				// v�rification de la pr�sence des champs obligatoires
				if ($$fieldreq!="" && $post_value=="")
				  $error_detected = "<LI>"._T("- V�rifiez que tous les champs obligatoires sont renseign�s.")."</LI>";
				else
				{
					$value = "";
					// validation des dates			
					if($proprietes_arr["type"]=="date")
					{
					  if (ereg("^([0-9]{2})/([0-9]{2})/([0-9]{4})$", $post_value, $array_jours))
					  {
						  if (checkdate($array_jours[2],$array_jours[1],$array_jours[3]))
								$value=$DB->DBDate(mktime(0,0,0,$array_jours[2],$array_jours[1],$array_jours[3]));
							else
								$error_detected .= "<LI>"._T("- Date non valide !")."</LI>";
					  }
					  else
					  	$error_detected .= "<LI>"._T("- Mauvais format de date (jj/mm/aaaa) !")."</LI>";
					}
 					elseif(strstr($proprietes_arr["type"],"int"))
 					{
 						if (is_numeric($post_value) || $post_value=="")
						  $value=$DB->qstr($post_value,ENT_QUOTES);
						else
							$error_detected .= "<LI>"._T("- La dur�e doit �tre un entier !")."</LI>";
 					}
 					elseif(strstr($proprietes_arr["type"],"float"))
 					{
 						$us_value = strtr($post_value, ",", ".");
 						if (is_numeric($us_value) || $us_value=="")
						  $value=$DB->qstr($us_value,ENT_QUOTES);
						else
							$error_detected .= "<LI>"._T("- Le montant doit �tre un chiffre !")."</LI>";
 					}
 					else
 					{
 						// on se contente d'escaper le html et les caracteres speciaux
							$value = $DB->qstr($post_value,ENT_QUOTES);
					}
					
					// mise � jour des chaines d'insertion/update
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
 		 	if ($id_cotis!="")
 		 	{
 		 		// modif
 		 		
 		 		$requete = "UPDATE ".PREFIX_DB."cotisations
 		 								SET " . substr($update_string,1) . " 
 		 								WHERE id_cotis=" . $DB->qstr($id_cotis);
				dblog(_T("Mise � jour d'une contribution :")." ".strtoupper($nom_adh)." ".$prenom_adh, $requete);							
  			}
 		 	else
 		 	{
  			// ajout
 				
   			$requete = "INSERT INTO ".PREFIX_DB."cotisations
  									(" . substr($insert_string_fields,1) . ") 
  									VALUES (" . substr($insert_string_values,1) . ")";
  									
 				dblog(_T("Ajout d'une contribution :")." ".strtoupper($nom_adh)." ".$prenom_adh, $requete);							
  			}
			$DB->Execute($requete);
			
			// mise a jour de l'�ch�ance
			$date_fin = get_echeance($DB, $id_adh);
			if ($date_fin!="")
				$date_fin_update = $DB->DBDate(mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]));
			else
				$date_fin_update = "'NULL'";

			$requete = "UPDATE ".PREFIX_DB."correspondencia
				    SET date_echeance=".$date_fin_update."
				    WHERE id_adh='".$id_adh."'";
			$DB->Execute($requete);
			
			// retour � la liste
			header("location: gestion_contributions.php?id_adh=".$id_adh);

			// r�cup�ration du max pour passage en mode modif apres insertion
			if ($id_cotis=="")
			{
				$requete = "SELECT max(id_cotis)
						AS max
						FROM ".PREFIX_DB."cotisations";
				$max = &$DB->Execute($requete);
				$id_cotis = $max->fields["max"];
			}	
  	}  	
  }
	
	  //	
	 // Pr�-remplissage des champs
	//  avec des valeurs issues de la base
	//  -> donc uniquement si l'enregistrement existe et que le formulaire
	//     n'a pas d�ja �t� post� avec des erreurs (pour pouvoir corriger)
	
	if (!isset($_POST["valid"]) || (isset($_POST["valid"]) && $error_detected==""))
	if ($id_cotis != "") 
	{ 
		// recup des donn�es
		$requete = "SELECT * 
								FROM ".PREFIX_DB."cotisations 
			  				WHERE id_cotis=$id_cotis";
		$result = &$DB->Execute($requete);
        	if ($result->EOF)
	                header("location: index.php");
			                                                                                                                    

			
		// recuperation de la liste de champs de la table
	  //$fields = &$DB->MetaColumns(PREFIX_DB."cotisations");
	  while (list($champ, $proprietes) = each($fields))
		{
			$proprietes_arr = get_object_vars($proprietes);
			// on obtient name, max_length, type, not_null, has_default, primary_key,
			// auto_increment et binary		
		
		  // d�claration des variables correspondant aux champs
		  // et reformatage des dates.
			
			$val = $result->fields[$proprietes_arr["name"]];

			if($proprietes_arr["type"]=="date" && $val!="")
			{
			  list($a,$m,$j)=split("-",$val);
			  $val="$j/$m/$a";
			}
		  $$proprietes_arr["name"] = htmlentities(stripslashes(addslashes($val)), ENT_QUOTES);
		}
	}
	else
	{
		// initialisation des champs
			
	}

	// la date de creation de fiche, ici vide si nouvelle fiche
	if ($date_cotis=="")
		$date_cotis = date("d/m/Y");

	include("header.php");

?> 
	<div class="form-block"> 
<H1 class="subtitre"><? echo ("Ficha modificaci�n"); ?> (<? if ($id_cotis!="") echo ("Edici�n"); else echo ("creaci�n"); ?>)</H1>
<FORM action="ajouter_contribution.php" method="post"> 
						
<?
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
<?
	}
?>						
						
						
						<table border="0" id="input-table"> 
							<tr> 
								<TH id="libelle" <? echo $id_adh_req ?>><? echo ("Acuerdo :"); ?></TH> 
								<td colspan="4">
									<select name="id_adh">
										<option value="" <? isSelected($id_adh,"") ?>><? echo ("-- seleccionar un acuerdo --"); ?></option>
									<?
										$requete = "SELECT id_adh, nom_adh, prenom_adh
		 														FROM ".PREFIX_DB."correspondencia
		 														ORDER BY nom_adh, prenom_adh";
										$result = &$DB->Execute($requete);
										while (!$result->EOF)
										{									
									?>
										<option value="<? echo $result->fields[0] ?>"<? isSelected($id_adh,$result->fields[0]) ?>><? echo htmlentities(($result->fields[1]), ENT_QUOTES)." ".htmlentities($result->fields[2], ENT_QUOTES); ?></option>
									<?
											$result->MoveNext();
										}
										$result->Close();
									?>
									</select>
								</td> </tr>
								<tr>
								<TH id="libelle" <? echo $id_type_cotis_req ?>><? echo ("Tipo de modificaci�n :"); ?></TH> 
								<td>
									<select name="id_type_cotis">
									<?
										$requete = "SELECT id_type_cotis, libelle_type_cotis
		 														FROM ".PREFIX_DB."types_cotisation
		 														ORDER BY libelle_type_cotis";
										$result = &$DB->Execute($requete);
										while (!$result->EOF)
										{									
									?>
										<option value="<? echo $result->fields["id_type_cotis"] ?>"<? isSelected($id_type_cotis,$result->fields["id_type_cotis"]) ?>><? echo ($result->fields["libelle_type_cotis"]) ?></option>
									<?
											$result->MoveNext();
										}
										$result->Close();
									?>
									</select>
								</td> 
							</tr>

							<tr> 
								<TH id="libelle" <? echo $date_cotis_req ?>><? echo _T("Date contribution :"); ?><br>&nbsp;</TH> 
								<td colspan="3"><input type="text" name="date_cotis" value="<? echo $date_cotis; ?>" maxlength="10"><BR><DIV class="exemple"><? echo ("(formatO dd/mm/aaaa)"); ?></DIV></td> 
						  </tr> 
							<tr> 
								<TH id="libelle" <? echo $info_cotis_req ?>><? echo ("Comentario :"); ?></TH> 
								<td colspan="3"><textarea name="info_cotis" cols="61" rows="6"><? echo $info_cotis; ?></textarea></td> 
						  </tr> 
							<tr> 
								<TH align="center" colspan="4"><BR><input type="submit" name="valid" value="<? echo ("guardar"); ?>"></TH> 
						  </tr> 
							</table> 
						
						<br> 
						<? echo ("Nota : Los campos obligatorios aparecen en"); ?> <font style="color: #FF0000"><? echo ("rojo rojito"); ?></font>. 
						
						</div> 
						<input type="hidden" name="id_cotis" value="<? echo $id_cotis ?>"> 
						</form> 
<? 
	// } 
 
 				
  include("footer.php") 
?>
