
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
			$_SESSION["logged_nom_adh"]="Analista";
			dblog(_T("Identification"));
		}
		if ($_POST["login"]==PREF_USER_LOGIN && $_POST["password"]==PREF_USER_PASS)
		{
			$_SESSION["logged_status"]=1;
			$_SESSION["admin_status"]=1;
			$_SESSION["logged_username"]=$_POST["login"];
			$_SESSION["logged_nom_adh"]="invitado";
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
		header("location: gestion_correspondencia.php?filtre_2=0");
	else
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="es">

<head>
<meta http-equiv="content-type" content="text/htm" charset="ISO-8859-1"  >

<title>Correspondencia y Fax</title>

<link rel="stylesheet" href="inicio.css" type="text/css" />

</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="joomla">Ministerio del Poder Popular para Relaciones Exteriores</div>
	</div>
</div>
<div id="ctr">
	<div class="posi_logo">
			
  			<div class="clr"></div>		
	</div>
	<div class="install">
		
		
		
	  		<div id="step_licencia">Gesti&oacute;n de Correspondencia y FAX para Criptograf&iacute;a</div>

  			<div class="clr"></div>
  			
	  		<div class="install-text">
  				<p>Esta Aplicaci&oacute;n esta desarrollada en Software Libre bajo licencia <a href="licencia.php">GNU/GPL</a>...</p>
  				<p>Inicia tu secci&oacute;n, ingresando tu login y contrase&ntilde;a.</p>
				
  			</div>
			<div class="install-form">
  				<div class="form-block">
				<fieldset class="fieldset">
				<legend>Iniciar sesi&oacute;n</legend>
				<form action="index.php" method="post">
				<table cellpadding="0" cellspacing="3" border="0" align="center">
				<tbody>
				<tr>
					<td>Nombre:<br><input class="bginput" name="login" size="50" accesskey="u" tabindex="1" type="text"></td>
				</tr>
				<tr>
					<td>Contrase&ntildea:<br><input class="bginput" name="password" size="50" tabindex="1" type="password"></td>
				</tr>

				<tr>

					<td>
						<span style="float: right;"><a href="mailto:cscheuermann@mes.gov.ve">Olvidaste tu contrase&ntildea?</a></span>
						<label for="cb_cookieuser"><input name="cookieuser" value="1" id="cb_cookieuser" tabindex="1" type="checkbox">Iniciar sesi&oacute;n autom&aacute;ticamente</label>
					</td>
				</tr>
				<tr>
					<td align="right">
						<input class="button" value="Iniciar sesi&oacute;n" name="ident" accesskey="s" tabindex="1" type="submit">

						<input class="button" value="Restablecer" accesskey="r" tabindex="1" type="reset">
					</td>
				</tr>
				</tbody>
				</TABLE>
		  		</form>
				</fieldset>
				</div>
			</div>
		
		<div class="clr"></div>
	</div>
	
</div>

<div class="ctr">
	<a href="mailto:clausche@gmail.com" target="_blank">GAC-CRIP</a> es un software libre bajo licencia GNU/GPL.
</div>
</body>
</html>
<?
	}
?>
