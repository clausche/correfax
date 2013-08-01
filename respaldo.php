<?php

//------------------------------------------------------------------------------------------
//  Definiciones


	//  Conexión con la Base de Datos.
	
	$db_server = "localhost"; 
	$db_name = "correfax"; 
	$db_username = "root"; 
	$db_password = "zz"; 


	//  Acceso al script.
	
	$auth_user = "clausche";
	$auth_password = "zz";


	//  Nombre del archivo.

	$filename = $db_name.'-'.date("Ymd-His", time()).'.sql';
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

//------------------------------------------------------------------------------------------
//  No tocar
	error_reporting( E_ALL & ~E_NOTICE );
	define( 'Str_VERS', "1.1.1" );
	define( 'Str_DATE', "18 de Marzo de 2012" );
//------------------------------------------------------------------------------------------
?>
<?php 
	// Check to see if $PHP_AUTH_USER already contains info
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		// If empty, send header causing dialog box to appear
		header('WWW-Authenticate: Basic realm="Acceso al Dump y Download la Base de Datos"');
		header('HTTP/1.0 401 Unauthorized');
    // Defines the charset to be used
    header('Content-Type: text/html; charset=iso-8859-1');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
 <HEAD>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Acceso Denegado</title>
	<!-- no cache headers -->
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="no-cache">
	<meta http-equiv="Expires" content="-1">
	<meta http-equiv="Cache-Control" content="no-store">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Cache-Control" content="must-revalidate">
	<!-- end no cache headers --> 
 </HEAD>
<BODY 
	bgcolor="#D5D5D5" 
	text="#000000" 
	id="all" 
	leftmargin="25" 
	topmargin="25" 
	marginwidth="25" 
	marginheight="25" 
	link="#000020" 
	vlink="#000020" 
	alink="#000020">
	<center><h1>Dump y Download la Base de Datos</h1></center><br>
	<strong><center><p>Usuario/contraseña equivocado. Acceso denegado.</p></center>
<?php
		echo( "</strong><br><br><hr><center><small>" );
		setlocale( LC_TIME,"spanish" );
		echo strftime( "%A %d %B %Y&nbsp;-&nbsp;%H:%M:%S", time() );
		echo( "<br>&copy;2005 <a href=\"mailto:clausche@gmail.com\">Inside PHP</a><br>" );
		echo( "vers." . Str_VERS . "<br>" );
		echo( "</small></center>" );
		echo( "</BODY>" );
		echo( "</HTML>" );
    exit();
	}
	else {
		if (($_SERVER['PHP_AUTH_USER'] != $auth_user ) || ($_SERVER['PHP_AUTH_PW'] != $auth_password )) {
			header('WWW-Authenticate: Basic realm="Acceso al Dump y Download la Base de Datos"');
			header('HTTP/1.0 401 Unauthorized');
    	// Defines the charset to be used
    	header('Content-Type: text/html; charset=iso-8859-1');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
 <HEAD>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Acceso Denegado</title>
	<!-- no cache headers -->
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="no-cache">
	<meta http-equiv="Expires" content="-1">
	<meta http-equiv="Cache-Control" content="no-store">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Cache-Control" content="must-revalidate">
	<!-- end no cache headers --> 
 </HEAD>
<BODY 
	bgcolor="#D5D5D5" 
	text="#000000" 
	id="all" 
	leftmargin="25" 
	topmargin="25" 
	marginwidth="25" 
	marginheight="25" 
	link="#000020" 
	vlink="#000020" 
	alink="#000020">
	<center><h1>Dump y Download la Base de Datos</h1></center><br>
	<strong><center><p>Usuario/contraseña equivocado. Acceso denegado.</p></center>
<?php
			echo( "</strong><br><br><hr><center><small>" );
			setlocale( LC_TIME,"spanish" );
			echo strftime( "%A %d %B %Y&nbsp;-&nbsp;%H:%M:%S", time() );
			echo( "<br>&copy;2005 <a href=\"mailto:insidephp@gmail.com\">Inside PHP</a><br>" );
			echo( "vers." . Str_VERS . "<br>" );
			echo( "</small></center>" );
			echo( "</BODY>" );
			echo( "</HTML>" );
    	exit();
		}
		else {
///////  El área protegida empieza DESPUÉS de la SIGUIENTE línea  /////
?>


<?php
//------------------------------------------------------------------------------------------
//  Funciones

	error_reporting( E_ALL & ~E_NOTICE );

	function fetch_table_dump_sql($table, $fp = 0) {
		$tabledump = "--\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	
		$tabledump = "-- Table structure for table `$table`\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	
		$tabledump = "--\n\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	

		$tabledump = query_first("SHOW CREATE TABLE $table");
		strip_backticks($tabledump['Create Table']);
		$tabledump = "DROP TABLE IF EXISTS $table;\n" . $tabledump['Create Table'] . ";\n\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	

		$tabledump = "--\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	
		$tabledump = "-- Dumping data for table `$table`\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	
		$tabledump = "--\n\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	

		$tabledump = "LOCK TABLES $table WRITE;\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	

		$rows = query("SELECT * FROM $table");
		$numfields=mysql_num_fields($rows);
		while ($row = fetch_array($rows, DBARRAY_NUM)) {
			$tabledump = "INSERT INTO $table VALUES(";
			$fieldcounter = -1;
			$firstfield = 1;
			// campos
			while (++$fieldcounter < $numfields) {
				if( !$firstfield) {
					$tabledump .= ', ';
				}
				else {
					$firstfield = 0;
				}
				if( !isset($row["$fieldcounter"])) {
					$tabledump .= 'NULL';
				}
				else {
					$tabledump .= "'" . mysql_escape_string($row["$fieldcounter"]) . "'";
				}
			}
			$tabledump .= ");\n";
			if( !$hay_Zlib ) 
				fwrite($fp, $tabledump);
			else
				gzwrite($fp, $tabledump);	
		}
		free_result($rows);
		$tabledump = "UNLOCK TABLES;\n";
		if( !$hay_Zlib ) 
			fwrite($fp, $tabledump);
		else
			gzwrite($fp, $tabledump);	
	}

	function strip_backticks(&$text) {
		return $text;
	}

	function fetch_array($query_id=-1) {
		if( $query_id!=-1) {
			$query_id=$query_id;
		}
		$record = mysql_fetch_array($query_id);
		return $record;
	}

	function problemas($msg) {
		$errdesc = mysql_error();
    $errno = mysql_errno();
    $message  = "<br>";
    $message .= "- Ha habido un problema accediendo a la Base de Datos<br>";
    $message .= "- Error $appname: $msg<br>";
    $message .= "- Error mysql: $errdesc<br>";
    $message .= "- Error número mysql: $errno<br>";
    $message .= "- Script: ".getenv("REQUEST_URI")."<br>";
    $message .= "- Referer: ".getenv("HTTP_REFERER")."<br>";

		echo( "</strong><br><br><hr><center><small>" );
		setlocale( LC_TIME,"spanish" );
		echo strftime( "%A %d %B %Y&nbsp;-&nbsp;%H:%M:%S", time() );
		echo( "<br>&copy;2005 <a href=\"mailto:insidephp@gmail.com\">Inside PHP</a><br>" );
		echo( "vers." . Str_VERS . "<br>" );
		echo( "</small></center>" );
		echo( "</BODY>" );
		echo( "</HTML>" );
		die("");
  }

	function free_result($query_id=-1) {
    if( $query_id!=-1) {
      $query_id=$query_id;
    }
    return @mysql_free_result($query_id);
  }

  function query_first($query_string) {
    $res = query($query_string);
    $returnarray = fetch_array($res);
    free_result($res);
    return $returnarray;
  }

	function query($query_string) {
    $query_id = mysql_query($query_string);
    if( !$query_id) {
      problemas("Invalid SQL: ".$query_string);
    }
    return $query_id;
  }


//------------------------------------------------------------------------------------------
//  Main

//	include("header.php");

?>

<center><h1>Dump y Download la Base de Datos</h1></center>
<br>
<strong>
<?php
	@set_time_limit( 0 );

	echo( "- Base de Datos: '$db_name' en '$db_server'.<br>" );
	$error = false;
	$tablas = 0;

	if( !@function_exists( 'gzopen' ) ) {
		$hay_Zlib = false;
		echo( "- Ya que no está disponible Zlib, salvaré la Base de Datos sin comprimir, como '$filename'<br>" );
	}
	else {
		$filename = $filename . ".gz";
		$hay_Zlib = true;
		echo( "- Ya que está disponible Zlib, salvaré la Base de Datos comprimida, como '$filename'<br>" );
	}
	
	if( !$error ) { 
	    $dbconnection = @mysql_connect( $db_server, $db_username, $db_password ); 
	    if( $dbconnection) 
	        $db = mysql_select_db( $db_name );
	    if( !$dbconnection || !$db ) { 
	        echo( "<br>" );
	        echo( "- La conexion con la Base de datos ha fallado: ".mysql_error()."<br>" );
	        $error = true;
	    }
	    else {
	        echo( "<br>" );
	        echo( "- He establecido conexion con la Base de datos.<br>" );
	    }
	}

	if( !$error ) { 
		//  MySQL versión
		$result = mysql_query( 'SELECT VERSION() AS version' );
		if( $result != FALSE && @mysql_num_rows($result) > 0 ) {
			$row   = mysql_fetch_array($result);
		} else {
			$result = @mysql_query( 'SHOW VARIABLES LIKE \'version\'' );
			if( $result != FALSE && @mysql_num_rows($result) > 0 ){
				$row   = mysql_fetch_row( $result );
			}
		}
		if(! isset($row) ) {
			$row['version'] = '3.21.0';
		}
	}

	if( !$error ) { 
		$el_path = getenv("REQUEST_URI");
		$el_path = substr($el_path, strpos($el_path, "/"), strrpos($el_path, "/"));

		$result = mysql_list_tables( $db_name );
		if( !$result ) {
			print "- Error, no puedo obtener la lista de las tablas.<br>";
			print '- MySQL Error: ' . mysql_error(). '<br><br>';
			$error = true;
		}
		else {
			$t_start = time();
			
			if( !$hay_Zlib ) 
				$filehandle = fopen( $filename, 'w' );
			else
				$filehandle = gzopen( $filename, 'w6' );	//  nivel de compresión
				
			if( !$filehandle ) {
				$el_path = getenv("REQUEST_URI");
				$el_path = substr($el_path, strpos($el_path, "/"), strrpos($el_path, "/"));
				echo( "<br>" );
				echo( "- No he podido crear '$filename' en '$el_path/'. Por favor, asegúrese de<br>" );
				echo( "&nbsp;&nbsp;que dispone de privilegios de escritura.<br>" );
			}
			else {					
				$tabledump = "-- Dump de la Base de Datos\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				setlocale( LC_TIME,"spanish" );
				$tabledump = "-- Fecha: " . strftime( "%A %d %B %Y - %H:%M:%S", time() ) . "\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				$tabledump = "--\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				$tabledump = "-- Version: " . Str_VERS . ", del " . Str_DATE . ", insidephp@gmail.com\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				$tabledump = "-- Soporte y Updaters: http://insidephp.sytes.net\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				$tabledump = "--\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				$tabledump = "-- Host: `$db_server`    Database: `$db_name`\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				$tabledump = "-- ------------------------------------------------------\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				$tabledump = "-- Server version	". $row['version'] . "\n\n";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	

				$result = query( 'SHOW tables' );
				while( $currow = fetch_array($result, DBARRAY_NUM) ) {
					fetch_table_dump_sql( $currow[0], $filehandle );
					fwrite( $filehandle, "\n" );
					if( !$hay_Zlib ) 
						fwrite( $filehandle, "\n" );
					else
						gzwrite( $filehandle, "\n" );
						$tablas++;
				}
				$tabledump = "\n-- Dump de la Base de Datos Completo.";
				if( !$hay_Zlib ) 
					fwrite( $filehandle, $tabledump );
				else
					gzwrite( $filehandle, $tabledump );	
				if( !$hay_Zlib ) 
					fclose( $filehandle );
				else
					gzclose( $filehandle );
	
				$t_now = time();
				$t_delta = $t_now - $t_start;
				if( !$t_delta )
					$t_delta = 1;
				$t_delta = floor(($t_delta-(floor($t_delta/3600)*3600))/60)." minutos y "
				.floor($t_delta-(floor($t_delta/60))*60)." segundos.";
				echo( "- He salvado las $tablas tablas en $t_delta<br>" );
				echo( "<br>" );
				echo( "- El Dump de la Base de Datos está completo.<br>" );
				echo( "- He salvado la Base de Datos en: $el_path/$filename<br>" );
				echo( "<br>" );
				echo( "- Puede bajársela directamente: </strong><a href=\"$filename\">$filename</a>" );
				$size = filesize($filename);
				$size = number_format( $size );
				$size = str_replace( ",",".",$size );
				echo( "&nbsp;&nbsp;&nbsp;<small>($size bytes)</small><br>" );
			}
		}
	}

	if( $dbconnection )
	    mysql_close();

	echo( "</strong><br><br><hr><center><small>" );
	setlocale( LC_TIME,"spanish" );
	echo strftime( "%A %d %B %Y&nbsp;-&nbsp;%H:%M:%S", time() );
	echo( "<br>&copy;GAC-CRIP <a href=\"mailto:clausche@gmail.com\">Sistema de Gestión de archivos y correspondencia<br> de la Oficina de Criptografía del Ministerio del Poder Popular para Relaciones Exteriores</a><br>" );
	echo( "vers." . Str_VERS . "<br>" );
	echo( "</small></center>" );	

	?>
<FORM>
<INPUT Type="BUTTON" VALUE="Home Page" ONCLICK="window.location.href='index.php'">
</FORM>
<?php

	echo( "</BODY>" );
	echo( "</HTML>" );


//------------------------------------------------------------------------------------------
//  END
?>


<?php
///////  El área protegida acaba ANTES de la ANTERIOR línea  /////
		}
	}

?>
