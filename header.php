<?php header('Content-type: text/html;charset=ISO-8859-1') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
	<TITLE>CORREFAX <? echo LAUTARO_VERSION ?></TITLE>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
	<LINK rel="stylesheet" type="text/css" href="galette2.css" >
	<link rel="stylesheet" type="text/css" href="index35.css" />

<!--<link rel="stylesheet" href="theme_wp.css" type="text/css" media="screen" />
<!--[if IE]><link rel="stylesheet" href="http://s.wordpress.com/wp-content/themes/default/ie.css?m=1177376074a" type="text/css" media="screen" /><![endif]-->
<link rel='stylesheet' href='global.css' type='text/css' />
<link rel="stylesheet" href="wp-login_files/login.css" type="text/css" media="all">

<!-- menu gay -->
<script type="text/javascript" language="javascript" src="jquery.js"></script>
<script>
function greeting()
{
alert("Welcome " + document.forms["frm1"]["fname"].value + "!")
}
</script>
<script type = "text/javascript">
					function showMessage() {
					alert ("Esta acci�n tardar� unos minutos en completarse...");
					return true;
					}
</script>
<script type="text/javascript">
<!--//---------------------------------+
//  Developed by Roshan Bhattarai 
//  Visit http://roshanbh.com.np for this script and more.
//  This notice MUST stay intact for legal use
// --------------------------------->
$(document).ready(function()
{
	//slides the element with class "menu_body" when paragraph with class "menu_head" is clicked 
	$("#firstpane p.menu_head").click(function()
    {
		$(this).css({backgroundImage:"url(images/down.png)"}).next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
       	$(this).siblings().css({backgroundImage:"url(images/left.png)"});
	});
	//slides the element with class "menu_body" when mouse is over the paragraph
	$("#secondpane p.menu_head").mouseover(function()
    {
	     $(this).css({backgroundImage:"url(images/down.png)"}).next("div.menu_body").slideDown(500).siblings("div.menu_body").slideUp("slow");
         $(this).siblings().css({backgroundImage:"url(images/left.png)"});
	});
});
</script>
<style type="text/css">
body {
	margin: 10px auto;
	font: 75%/120% Verdana,Arial, Helvetica, sans-serif;
}
.menu_list {	
	width: 150px;
}
.menu_head {
	padding: 5px 10px;
	cursor: pointer;
	position: relative;
	margin:1px;
    font-weight:bold;
    background: #eef4d3 url(left.png) center right no-repeat;
}
.menu_body {
	display:none;
}
.menu_body a{
  display:block;
  color:#006699;
  background-color:#EFEFEF;
  padding-left:10px;
  font-weight:bold;
  text-decoration:none;
}
.menu_body a:hover{
  color: #000000;
  text-decoration:underline;
  }
</style>
<script language="Javascript">
// ==================
//	Activations - D�sactivations
// ==================
function GereControle(Controleur, Controle, Masquer) {
var objControleur = document.getElementById(Controleur);
var objControle = document.getElementById(Controle);
	if (Masquer=='1')
		objControle.style.visibility=(objControleur.checked==true)?'visible':'hidden';
	else
		objControle.disabled=(objControleur.checked==true)?false:true;
	return true;
}
</script>
<script type="text/javascript" src="jquery.js"></script>
<style type="text/css">
@import url(css.css);
</style>
<script type="text/javascript" src="js.js"></script>
</HEAD>
<BODY BGCOLOR="#FFFFFF" onload="GereControle('chkbox_pro', 'sel_pro', '1');">
	<div class="posi_logo">
	</div>

<br><br>
	<DIV id="content" ><div class="login-session" align="center" width="290" style="bold">
	<b>Sistema de Gesti&oacute;n de archivos y correspondencia<br> de la Oficina de Criptograf&iacute;a 
	del Ministerio del Poder Popular para Relaciones Exteriores </b></div><i>Usuario conectado: <?php 
	if ($_SESSION["logged_status"]==1)  echo strtoupper($_SESSION["logged_username"]) ; ?> </i>
