<?php header('Content-type: text/html;charset=ISO-8859-1') ?>
<?php 
	include("includes/config.inc.php"); 
	include(WEB_ROOT."includes/database.inc.php"); 
	include(WEB_ROOT."includes/functions.inc.php"); 
	include(WEB_ROOT."includes/lang.inc.php"); 
	include(WEB_ROOT."includes/session.inc.php"); 
	 
	if (isset($_POST["ident"]) or isset($_GET["login"]) & isset($_GET["password"])) 
	{ 
		if ($_POST["login"]==PREF_ADMIN_LOGIN && $_POST["password"]==PREF_ADMIN_PASS)
		{
			$_SESSION["logged_status"]=1;
			$_SESSION["admin_status"]=1;
			$_SESSION["logged_username"]=$_POST["login"];
			$_SESSION["logged_nom_adh"]=_T("Administrateur");
			dblog(_T("Identification"));
		}
		if ($_POST["login"]==PREF_TRANS_LOGIN && $_POST["password"]==PREF_TRANS_PASS)
		{
			$_SESSION["logged_status"]=1;
			$_SESSION["admin_status"]=1;
			$_SESSION["logged_username"]=$_POST["login"];
			$_SESSION["logged_nom_adh"]=_T("Administrateur");
			dblog(_T("Identification"));
		}
		if ($_GET["login"]=="invitado" && $_GET["password"]=="invitado")
		{
			$_SESSION["logged_status"]=2;
			$_SESSION["admin_status"]=2;
			$_SESSION["logged_username"]="invitado";
			$_SESSION["logged_nom_adh"]="Invitado";
			dblog(_T("Identification"));
		}
		else
		{
			$requete = "SELECT id_adh, bool_admin_adh, nom_adh, prenom_adh
					FROM ".PREFIX_DB."correspondencia
									WHERE login_adh=" . txt_sqls($_POST["login"]) . "
									AND activite_adh='1'
									AND mdp_adh=" . txt_sqls($_POST["password"]);
			$resultat = &$DB->Execute($requete);
			if (!$resultat->EOF)
			{
				if ($resultat->fields[1]=="1")
					$_SESSION["admin_status"]=1;
				$_SESSION["logged_id_adh"]=$resultat->fields[0];
				$_SESSION["logged_status"]=1;
				$_SESSION["logged_nom_adh"]=strtoupper($resultat->fields[2]) . " " . strtolower($resultat->fields[3]);
				dblog(_T("Identification"));
			}
			else
				dblog(_T("Echec authentification. Login :")." \"" . $_POST["login"] . "\"");
		}
	} 
	 
	if ($_SESSION["logged_status"]!=0)
		header("location: gestion_correspondencia.php");
	else
	{ 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="es">

<head profile="">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title>Convenios Internacionales</title>

<meta name="generator" content="WordPress MU"/> <!-- leave this for stats -->

<link rel="stylesheet" href="theme_wp.css" type="text/css" media="screen" />
<!--[if IE]><link rel="stylesheet" href="http://s.wordpress.com/wp-content/themes/default/ie.css?m=1177376074a" type="text/css" media="screen" /><![endif]-->
<link rel='stylesheet' href='global.css' type='text/css' />
<link rel="stylesheet" href="wp-login_files/login.css" type="text/css" media="all">
<link rel="stylesheet" href="wp-login_files/colors-fresh.css" type="text/css" media="all">

	<style type='text/css'>
body { background: url("http://localhost/gale/images/kubrickbgcolor.gif"); }
#page { background: url("http://localhost/gale/images/kubrickbgwide.gif") repeat-y top !important; border: none; }
#header { background: url("http://localhost/gale/images/kubrickheader.gif") no-repeat bottom center; }
#footer { background: url("http://localhost/gale/images/kubrickfooter.gif") no-repeat bottom; border: none;}
#header 	{ margin: 0 !important; margin: 0 0 0 1px; padding: 1px; height: 198px; width: 758px; }
#headerimg 	{ margin: 7px 9px 0; height: 192px; width: 740px; }
#headerimg h1 a, #headerimg h1 a:visited, #headerimg .description { color: ; }
#headerimg h1 a, #headerimg .description { display:  }

	</style></head>
<body>
	<div class="posi_logo">	
	</div>
<div id="page">

<div id="header">

	<div id="headerimg" onclick=" location.href='www.mes.gov.ve/gale';" style="cursor: pointer;">
		<h1><a href="www.mes.gov.ve/gale">Convenios Internacionales</a></h1>
		<div class="description"></div>

	</div>
</div>

	<div id="content" class="widecolumn">


<div class="login">

<div id="login">

<form name="loginform" id="loginform" action="login.php" method="post">
	
		<label>Usuario<br>
		<input name="login" id="user_login" class="input" value="" size="20" tabindex="10" type="text" ></label>
	
	<p>
		<label>Clave<br>
		<input name="password" id="user_pass" class="input" value="" size="20" tabindex="20" type="password"></label>
	</p>
	<p class="forgetmenot"><label><input name="rememberme" id="rememberme" value="forever" tabindex="90" type="checkbox"> Recordar</label></p>
	<p class="submit">
		<input name="ident" id="wp-submit" value="Log In" tabindex="100" type="submit">
		<input name="redirect_to" value="wp-admin/" type="hidden">
		<input name="testcookie" value="1" type="hidden">
	</p>
			
</form>
<p id="nav">
<a>El público puede ingresar usando los siguentes datos de acceso.
</p>
<p align="center">Usuario : <b>invitado</b> <br /> Clave:<b>invitado</b></p>


</div>

</div>

								
				<p class="postmetadata alt">
					<small>Esta aplicación tiene como propósito gestionar en conjunto con una base de datos los acuerdos y convenios internacionales, del estado venezolano. Llevar un seguimiento de los convenios, a fin de visualizar su estatus cuando sea necesario.						
					</small>
				</p>

			

	

	
<!-- You can start editing here. -->


			<!-- If comments are closed. -->
		<p class="nocomments">Ministerio del Poder Popular para la Educaci&oacute;n Superior</p>
		<p class="nocomments">Oficina de Convenios & Cooperaci&oacute;n</p>
		<p class="nocomments">webmaster: Claudio Scheuermann</p>
	


	
	</div>


<hr />
<div id="footer">
	<p>
		<a href='http://www.mes.gov.ve/' rel='generator'>MPPES</a>.
		<br /><a href="http://www.mes.gov.ve/gale/">Convenios</a> & <a href="http://www.mes.gov.ve/gale/">Cooperaci&oacute;n</a>.	</p>
</div>

</body>
</html>
<?
	}
?>
