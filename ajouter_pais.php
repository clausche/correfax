<?php
 
	include("includes/config.inc.php"); 
	include(WEB_ROOT."includes/database.inc.php"); 
	include(WEB_ROOT."includes/functions.inc.php"); 
	include(WEB_ROOT."includes/lang.inc.php"); 
	include(WEB_ROOT."includes/session.inc.php"); 
	
	if ($_SESSION["logged_status"]==0) 
		header("location: index.php");
	if ($_SESSION["admin_status"]==0) 
		header("location: voir_pais.php");
		
	// On v�ifie si on a une r��ence => modif ou cr�tion
	$id_paises = "";
  if (isset($_GET["id_paises"]))
	 	if (is_numeric($_GET["id_paises"]))
	    $id_paises = $_GET["id_paises"];
  if (isset($_POST["id_paises"]))
 		if (is_numeric($_POST["id_paises"]))
	    $id_paises = $_POST["id_paises"];

	// variables d'erreur (pour affichage)	    
 	$error_detected = "";
	
	  //
	 // DEBUT parametrage des champs
  //  On recupere de la base la longueur et les flags des champs
  //  et on initialise des valeurs par defaut
    
 	// recuperation de la liste de champs de la table
  $fields = &$DB->MetaColumns(PREFIX_DB."paises");
  while (list($champ, $proprietes) = each($fields))
	{
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,
		// auto_increment et binary		
		
		$fieldname = $proprietes_arr["name"];
		$fieldreq = $fieldname."_req";
		$fieldlen = $fieldname."_len";
	
		// on ne met jamais a jour id_cotis -> on le saute
		if ($fieldname!="id_paises")
			$$fieldname = "";

	  // definissons  aussi la longueur des input text
	  $max_tmp = $proprietes_arr["max_length"];
	  if ($max_tmp == "-1")
	  	$max_tmp = 10;
	  $$fieldlen = $max_tmp;

	  // et s'ils sont obligatoires (�partir de la base)
	  if ($proprietes_arr["not_null"]==1)
	    $$fieldreq = " style=\"color: #FF0000;\"";
  	  else
	    $$fieldreq = "";
	}
	reset($fields);

	// et les valeurs par defaut
	//$id_type_cotis = "1";
	//$duree_mois_cotis = "12";

	  //
	 // FIN parametrage des champs
	// 
/*
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
*/    
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
			if ($fieldname!="id_paises")
			{			
				if (isset($_POST[$fieldname]))
				  $post_value=trim($_POST[$fieldname]);
				else			
					$post_value="";
					
				// on declare les variables pour la pr�aisie en cas d'erreur
				$$fieldname = htmlentities(stripslashes($post_value),ENT_QUOTES);

				// v�ification de la pr�ence des champs obligatoires
				if ($$fieldreq!="" && $post_value=="")
				  $error_detected = "<LI>"._T("- V�ifiez que tous les champs obligatoires sont renseign�.")."</LI>";
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
							$error_detected .= "<LI>"._T("- La dur� doit �re un entier !")."</LI>";
 					}
 					elseif(strstr($proprietes_arr["type"],"float"))
 					{
 						$us_value = strtr($post_value, ",", ".");
 						if (is_numeric($us_value) || $us_value=="")
						  $value=$DB->qstr($us_value,ENT_QUOTES);
						else
							$error_detected .= "<LI>"._T("- Le montant doit �re un chiffre !")."</LI>";
 					}
 					else
 					{
 						// on se contente d'escaper le html et les caracteres speciaux
							$value = $DB->qstr($post_value,ENT_QUOTES);
					}
					
					// mise �jour des chaines d'insertion/update
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
  			if ( $id_paises = $id_paises )
  			{
 		 		// modif
 		 		
 		 		$requete = "UPDATE ".PREFIX_DB."paises
 		 								SET " . substr($update_string,1) . " 
 		 								WHERE id_paises=" . $DB->qstr($id_paises);
											
  			}
 		 	if ($id_paises!="")
 		 	{
 		 		// modif
 		 		
 		 		$requete = "UPDATE ".PREFIX_DB."paises
 		 								SET " . substr($update_string,1) . " 
 		 								WHERE id_paises=" . $DB->qstr($id_paises);
											
  			}
 		 	else
 		 	{
  			// ajout
 				
   			$requete = "INSERT INTO ".PREFIX_DB."paises
  									(" . substr($insert_string_fields,1) . ") 
  									VALUES (" . substr($insert_string_values,1) . ")";
  									
 											
  			}
			$DB->Execute($requete);
			
			// mise a jour de l'�h�nce
		/*	$date_fin = get_echeance($DB, $id_adh); 
			if ($date_fin!="")
				$date_fin_update = $DB->DBDate(mktime(0,0,0,$date_fin[1],$date_fin[0],$date_fin[2]));
			else
				$date_fin_update = "'NULL'";

			$requete = "UPDATE ".PREFIX_DB."correspondencia
				    SET date_echeance=".$date_fin_update."
				    WHERE id_adh='".$id_adh."'";
			$DB->Execute($requete);
			*/ 
			// retour �la liste
			header("location: gestion_pais.php?id_paises=".$id_paises);

			// r�up�ation du max pour passage en mode modif apres insertion
			if ($id_paises=="")
			{
				$requete = "SELECT max(id_paises)
						AS max
						FROM ".PREFIX_DB."paises";
				$max = &$DB->Execute($requete);
				$id_paises = $max->fields["max"];
			}	
  	}  	
  }
	
	  //	
	 // Pr�remplissage des champs
	//  avec des valeurs issues de la base
	//  -> donc uniquement si l'enregistrement existe et que le formulaire
	//     n'a pas d�a ��post�avec des erreurs (pour pouvoir corriger)
	
	if (!isset($_POST["valid"]) || (isset($_POST["valid"]) && $error_detected==""))
	if ($id_paises != "") 
	{ 
		// recup des donn�s
		$requete = "SELECT * 
								FROM ".PREFIX_DB."paises 
			  				WHERE id_paises=$id_paises";
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
		
		  // d�laration des variables correspondant aux champs
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
	 if ($date_paises=="")
		$date_paises = date("d/m/Y");
	
	include("header.php");
	include("./fckeditor/fckeditor.php") ;	

?> 
<br>
<div class="form-block"> 
<H1 class="subtitre"><? echo ("Ficha de los paises"); ?> (<? if ($id_paises!="") echo ("Edici\F3n"); else echo ("creaci\F3n"); ?>)</H1>
<FORM action="ajouter_pais.php" method="post"> 
						
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
						
						
						<table width="755" border="0" id="input-table"> 
							<tr> 
								<TH id="libelle" <? echo $id_adh_req ?>><? echo ("Pa\EDs :"); ?></TH> 
								<td>
									<select name="id_pais">
										<option value="" <? isSelected($id_pais,"") ?>><? echo ("-- seleccionar un pais --"); ?></option>
									<?
										$requete = "SELECT id_pais,nombre_pais
		 														FROM ".PREFIX_DB."pais
		 														ORDER BY nombre_pais";
										$result = &$DB->Execute($requete);
										while (!$result->EOF)
										{									
									?>																				
										<option value="<? echo $result->fields[0] ?>"<? isSelected($id_pais,$result->fields[0]) ?>><? echo htmlentities(($result->fields[1]), ENT_QUOTES)." ".htmlentities($result->fields[2], ENT_QUOTES); ?></option>
									<?
											$result->MoveNext();
										}
										$result->Close();
									?>
									</select>
								</td> 
								<TH id="libelle" <? echo $date_paises_req ?>><? echo ("Fecha actual :"); ?><br>&nbsp;</TH> 
								<td><input type="text" name="date_paises" value="<? echo $date_paises; ?>" maxlength="10"><BR><DIV class="exemple"><? echo ("(formato dd/mm/aaaa)"); ?></DIV></td> 
						  </tr> 
								<tr>
								<TH id="libelle" ><? echo ("Panorama General :"); ?></TH>
							</tr>
							   <TR> 
          					<TD colspan="4"> <?php
$oFCKeditor = new FCKeditor('pano_gen') ;
$oFCKeditor->BasePath = 'fckeditor/';
$oFCKeditor->ToolbarSet = 'MitoolBar';
$output = $oFCKeditor->CreateHtml() ;
$oFCKeditor->Value = html_entity_decode($pano_gen);
$oFCKeditor->Create() ;
?> </TD>
        </TR>

							<tr> 
								<TH id="libelle"><? echo ("Actualidad :"); ?></TH> 

						  </tr> 
						  <TR> 
          					<TD colspan="4"> <?php
$oFCKeditor = new FCKeditor('actu') ;
$oFCKeditor->BasePath = 'fckeditor/';
$oFCKeditor->ToolbarSet = 'MitoolBar';
$output = $oFCKeditor->CreateHtml() ;
$oFCKeditor->Value = html_entity_decode($actu);
$oFCKeditor->Create() ;
?> </TD>
        </TR>

						  <tr> 
								<TH id="libelle"><? echo ("Estadisticas :"); ?></TH> 

						  </tr> 
						  <TR> 
          					<TD colspan="4"> <?php
$oFCKeditor = new FCKeditor('estatis') ;
$oFCKeditor->BasePath = 'fckeditor/';
$oFCKeditor->ToolbarSet = 'MitoolBar';
$output = $oFCKeditor->CreateHtml() ;
$oFCKeditor->Value = html_entity_decode($estatis);
$oFCKeditor->Create() ;
?> </TD>
        </TR>

						  <tr> 
								<TH id="libelle"><? echo ("Actividades :"); ?></TH> 

						  </tr> 
						  <TR> 
          					<TD colspan="4"> <?php
$oFCKeditor = new FCKeditor('acti') ;
$oFCKeditor->BasePath = 'fckeditor/';
$oFCKeditor->ToolbarSet = 'MitoolBar';
$output = $oFCKeditor->CreateHtml() ;
$oFCKeditor->Value = html_entity_decode($acti);
$oFCKeditor->Create() ;
?> </TD>
        </TR>

						  <tr> 
								<TH id="libelle"><? echo ("Marco Jur\EDdico :"); ?></TH> 

						  </tr> 
						  <TR> 
          					<TD colspan="4"> <?php
$oFCKeditor = new FCKeditor('juri') ;
$oFCKeditor->BasePath = 'fckeditor/';
$oFCKeditor->ToolbarSet = 'MitoolBar';
$output = $oFCKeditor->CreateHtml() ;
$oFCKeditor->Value = html_entity_decode($juri);
$oFCKeditor->Create() ;
?> </TD>
        </TR>

							<tr> 
								<TH align="center" colspan="4"><BR><input type="submit" name="valid" value="<? echo ("guardar"); ?>"></TH> 
						  </tr> 
						</table> 
						
						<br> 		
						</div> 
						<input type="hidden" name="id_paises" value="<? echo $id_paises ?>"> 
						</form> 
<? 
	// } 
 
 				
  include("footer.php") 
?>
