<?php 
  
	session_start();   
	if (!isset($_SESSION["logged_status"]) ||
			isset($_POST["logout"]) ||
			isset($_GET["logout"]))
	{
		if (isset($_POST["logout"]) ||
			isset($_GET["logout"]))
			dblog("Desconexion");

		$_SESSION["admin_status"]=0; 
		$_SESSION["logged_status"]=0;
		$_SESSION["logged_id_adh"]=0;
		$_SESSION["logged_nom_adh"]="";
		$_SESSION["filtre_adh"]=0;
		$_SESSION["filtre_adh_2"]=1;
		$_SESSION["filtre_date_cotis_1"]=""; 
		$_SESSION["filtre_date_cotis_2"]="";
		$_SESSION["tri_adh"]=0;
		$_SESSION["tri_adh_sens"]=0;
		$_SESSION["tri_log"]=0;
		$_SESSION["tri_log_sens"]=0;
		$_SESSION["filtre_cotis"]=0;
		$_SESSION["tri_cotis"]=0; 
		$_SESSION["tri_cotis_sens"]=1;
		$_SESSION["filtre_cotis_adh"]="";
/*		$_SESSION["filtre_dos"]=0;
		$_SESSION["tri_dos"]=0;
		$_SESSION["tri_dos_sens"]=1;
		$_SESSION["filtre_dos_adh"]="";
		$_SESSION["filtre_date_dos_1"]=""; 
		$_SESSION["filtre_date_dos_2"]="";		
*/
	}

?>
