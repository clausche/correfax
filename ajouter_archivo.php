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
	$id_dos = "";
  if (isset($_GET["id_dos"])) 
	 	if (is_numeric($_GET["id_dos"]))
	    $id_dos = $_GET["id_dos"];
  if (isset($_POST["id_dos"]))
 		if (is_numeric($_POST["id_dos"])) 
	    $id_dos = $_POST["id_dos"];

	// variables d'erreur (pour affichage)	    
 	$error_detected = "";
	
	  //
	 // DEBUT parametrage des champs
  //  On recupere de la base la longueur et les flags des champs
  //  et on initialise des valeurs par defaut 
    
 	// recuperation de la liste de champs de la table
  $fields = &$DB->MetaColumns(PREFIX_DB."archivo"); 
  while (list($champ, $proprietes) = each($fields))
	{
		$proprietes_arr = get_object_vars($proprietes);
		// on obtient name, max_length, type, not_null, has_default, primary_key,
		// auto_increment et binary		
		
		$fieldname = $proprietes_arr["name"]; 
		$fieldreq = $fieldname."_req";
		$fieldlen = $fieldname."_len";
	
		// on ne met jamais a jour id_cotis -> on le saute
		if ($fieldname!="id_dos")
			$$fieldname = ""; 

	  // definissons  aussi la longueur des input text 
	  $max_tmp = $proprietes_arr["max_length"];
	  if ($max_tmp == "-1")
	  	$max_tmp = 30;
	  $$fieldlen = $max_tmp; 

	  // et s'ils sont obligatoires (� partir de la base)
	  if ($proprietes_arr["not_null"]==1)
	    $$fieldreq = " style=\"color: #FF0000;\"";
  	  else
	    $$fieldreq = ""; 
	}
	reset($fields); 

	// et les valeurs par defaut
	$id_type_dos = "1";
	$duree_mois_dos = "12";   

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
  	
   $fileName = $_FILES['userfile']['name'];
   $tmpName  = $_FILES['userfile']['tmp_name']; 
   $fileSize = $_FILES['userfile']['size'];
    $fileType = $_FILES['userfile']['type'];  
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
			if ($fieldname!="id_dos")
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
/*					if($proprietes_arr["type"]=="date")
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
*/					
 					if(strstr($proprietes_arr["type"],"int"))
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
 		 	if ($id_dos!="")
 		 	{
 		 		// modif
 		 		
 		 		$requete = "UPDATE ".PREFIX_DB."archivo
 		 								SET " . substr($update_string,1) . " 
 		 								WHERE id_dos=" . $DB->qstr($id_dos);
				dblog(_T("Mise � jour d'une contribution :")." ".strtoupper($nom_adh)." ".$prenom_adh, $requete);							
  			}
 		 	else
 		 	{
  			// ajout
 				
   			$requete = "INSERT INTO ".PREFIX_DB."archivo 
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
			header("location: gestion_archivo.php?id_adh=".$id_adh); 

			// r�cup�ration du max pour passage en mode modif apres insertion
			if ($id_dos =="")
			{
				$requete = "SELECT max(id_dos)
										AS max
										FROM gac_archivo";
				$max = &$DB->Execute($requete);
				$id_dos_new = $max->fields["max"];
			}	
			else
				$id_dos_new = $id_dos;
//		  if (!isset($_POST["valid"]) || (isset($_POST["valid"]) && $error_detected==""))
//		  if ($id_adh != "")
// 		  {        
        $fp = fopen($tmpName, 'r');
        $content = fread($fp, $fileSize);
        $content = addslashes($content);  
        fclose($fp);
        
        if(!get_magic_quotes_gpc())
        {
            $fileName = addslashes($fileName);
        }
			
        include 'config.php'; 
        include 'opendb.php';     
//		  if($date_dos !="" || $info_dos != "")
//		  						{   
		  //							$query = "INSERT INTO gac_archivo (id_adh, id_type_dos, info_dos, date_dos, name, size, type, content ) ".
        //        				 "VALUES ('$id_adh','$id_type_dos','$info_dos','$date_dos','$fileName', '$fileSize', '$fileType', '$content')";
			$query = "UPDATE gac_archivo SET name = '$fileName',size = '$fileSize',type = '$fileType',content = '$content' ".
 		 				"WHERE id_dos = '$id_dos_new'";
        	mysql_query($query) or die('Error, query failed');                     
        	echo "<br>File $date_dos uploaded<br>"; 
//		  						}
//  		  }
	    	
  	} 
/*	if (!isset($_POST["valid"]) || (isset($_POST["valid"]) && $error_detected==""))
		  if ($id_adh != "")
 		  { 			

        
        $fp = fopen($tmpName, 'r');
        $content = fread($fp, $fileSize);
        $content = addslashes($content);  
        fclose($fp);
        
        if(!get_magic_quotes_gpc())
        {
            $fileName = addslashes($fileName);
        }
        
			
        include 'config.php'; 
        include 'opendb.php';     
		  if($date_dos !="" || $info_dos != "")
		  						{    
		  							$query = "INSERT INTO gac_archivo (id_adh, id_type_dos, info_dos, date_dos,userfile, name, size, type, content ) ".
                				 "VALUES ('$id_adh','$id_type_dos','$info_dos','$date_dos','$userfile','$fileName', '$fileSize', '$fileType', '$content')";
			
        							mysql_query($query) or die('Error, query failed');                     
        							echo "<br>File $date_dos uploaded<br>"; 
		  						}
  		  } */   

  }  
	
	  //	  
	 // Pr�-remplissage des champs   
	//  avec des valeurs issues de la base
	//  -> donc uniquement si l'enregistrement existe et que le formulaire 
	//     n'a pas d�ja �t� post� avec des erreurs (pour pouvoir corriger) 
	 
	if (!isset($_POST["valid"]) || (isset($_POST["valid"]) && $error_detected==""))
	if ($id_dos != "")  
	{  
		// recup des donn�es     
		$requete = "SELECT *  
								FROM ".PREFIX_DB."archivo  
			  				WHERE id_dos=$id_dos"; 
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
	if ($date_dos=="")
		$date_dos = date("d/m/Y");

	

	include("header.php");

?> 
	<div class="form-block">
	<H1 class="subtitre"><? echo ("Carga de documentos"); ?> (<? if ($id_dos!="") echo ("modificaci�n"); else echo ("nuevo"); ?>)</H1>
	<FORM action="" method="post" enctype="multipart/form-data" name="uploadform"> 
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

				<TABLE border="0" id="input-table">
					<tr WIDTH="100"> 
						<TH id="libelle" <? echo $id_adh_req ?> ><? echo ("Acuerdo :"); ?></TH> 
						<td WIDTH="100">
							<select name="id_adh">
							<option value="" <? isSelected($id_adh,"") ?>><? echo ("-- seleccione --"); ?></option>
							<?
										$requete = "SELECT id_adh, nom_adh, prenom_adh
		 														FROM ".PREFIX_DB."correspondencia
		 														ORDER BY nom_adh, prenom_adh";
										$result = &$DB->Execute($requete);
										while (!$result->EOF)
										{									
									?>
										<option value="<? echo $result->fields[0] ?>"<? isSelected($id_adh,$result->fields[0]) ?>>
										<? echo htmlentities(strtoupper($result->fields[1]), ENT_QUOTES)." ".htmlentities($result->fields[2], ENT_QUOTES); ?></option>
									<?
											$result->MoveNext();
										}
										$result->Close();
									?>
									</select>
								</td> 
								<TH id="libelle"  <? echo $id_type_dos_req ?>><? echo ("Tipo de documento :"); ?></TH> 
								<td>
									<select name="id_type_dos">
									<?
										$requete = "SELECT id_type_dos, libelle_type_dos
		 								FROM ".PREFIX_DB."types_archivo ORDER BY libelle_type_dos";
										$result = &$DB->Execute($requete);
										while (!$result->EOF)
										{									
									?>
										<option value="<? echo $result->fields["id_type_dos"] ?>"<? isSelected($id_type_dos,$result->fields["id_type_dos"]) ?>>
										<? echo ($result->fields["libelle_type_dos"]) ?></option>
									<?
											$result->MoveNext();
										}
										$result->Close();
									?>
									</select>
								</td> 
							</tr>

							<tr> 
								<TH id="libelle"  <?  echo $date_dos_req ?>><? echo ("Fecha :"); ?></TH> 
								<td ><input type="text" name="date_dos" id="date_dos" value="" maxlength="10">

   		               <DIV class="exemple"><? echo ("(formato aaaa/mm/dd)"); ?></DIV></TD>
							</tr>
		             	<tr>

								<TH id="libelle"  <? echo $info_dos_req ?>><? echo ("Pa�s :"); ?></TH>
          <TD > <SELECT name="info_dos" >
              <?php
										$requete = "SELECT *
		 									    FROM ".PREFIX_DB."pais
		 									    ORDER BY nombre_pais";
										$result = &$DB->Execute($requete);
										while (!$result->EOF)
										{
									?>
              <OPTION value="<? echo $result->fields["nombre_pais"] ?>"<? isSelected($info_dos,$result->fields["nombre_pais"]) ?>>
              <? echo $result->fields["nombre_pais"]; ?></OPTION>
              <?php
											$result->MoveNext();
										}
										$result->Close();
									?>
            </SELECT> </TD>

						  </tr>
							<tr> 
								<TH id="libelle" align="center" <? echo $userfile_req?>><? echo ("Subir :"); ?></TH> 
								
                    <td><input  type="file" name="userfile" ></td> 
						  </tr>   
							<tr> 
								<TH align="left" colspan="4"><BR><input class="button" type="submit" name="valid" value="<? echo ("Enviar"); ?>"></TH> 
						  </tr> 
							</table>							
						</div>
						<br> 

						<input type="hidden" name="id_dos" value="<? echo $id_dos ?>"> 
						</form> 
<? 
 
  include("footer.php")
?>
