<?php  
	include("includes/config.inc.php"); 
	include(WEB_ROOT."includes/database.inc.php"); 
	include(WEB_ROOT."includes/functions.inc.php"); 
	include(WEB_ROOT."includes/lang.inc.php"); 
	include(WEB_ROOT."includes/session.inc.php"); 
	include(WEB_ROOT."includes/phppdflib/phppdflib.class.php");
	
	if ($_SESSION["logged_status"]==0) 
		die();
	if ($_SESSION["admin_status"]==0) 
		die();
		
	$mailing_adh = array();
	if (isset($_POST["mailing_adh"]))
	{
		while (list($key,$value)=each($_POST["mailing_adh"]))
			$mailing_adh[]=$value;
	}
	else
		die();

		$requete = "SELECT id_adh, nom_adh, prenom_adh, adresse_adh,
									titre_adh, cp_adh, ville_adh, pays_adh, adresse2_adh
									FROM ".PREFIX_DB."correspondencia
			       				WHERE ";
		
		$where_clause = "";
		while(list($key,$value)=each($mailing_adh))
		{
			if ($where_clause!="")
				$where_clause .= " OR ";
			$where_clause .= "id_adh=".$DB->qstr($value);
		}
		$requete .= $where_clause." ORDER by nom_adh, prenom_adh;";
		// echo $requete;
		$resultat = &$DB->Execute($requete);
		
		$pdf = new pdffile;
		$pdf->set_default('margin', 0);
		$firstpage = $pdf->new_page("a4");
		$param["height"] = PREF_ETIQ_CORPS;
		$param["fillcolor"] = $pdf->get_color('#000000');
		$param["align"] = "center";
		$param["width"] = 1;
		$param["strokecolor"] = $pdf->get_color('#DDDDDD');

		if ($resultat->EOF)
			die();
			
	   $yorigin=842-round(PREF_ETIQ_MARGES*2.835);
	   $xorigin=round(PREF_ETIQ_MARGES*2.835);
	   $col=1;
	   $row=1;
	   $nb_etiq=0;
	   $concatname = "";
		while (!$resultat->EOF)
		{

			
			$x1 = $xorigin + ($col-1)*(round(PREF_ETIQ_HSIZE*2.835)+round(PREF_ETIQ_HSPACE*2.835));
			$x2 = $x1 + round(PREF_ETIQ_HSIZE*2.835);
			$y1 = $yorigin-($row-1)*(round(PREF_ETIQ_VSIZE*2.835)+round(PREF_ETIQ_VSPACE*2.835));
			$y2 = $y1 - round(PREF_ETIQ_VSIZE*2.835);
			
			$nom_adh_ext = " ".strtoupper($resultat->fields[1])." ".ucfirst(strtolower($resultat->fields[2]));
			$concatname = $concatname . " - " . $nom_adh_ext;
			$param["font"] = "Helvetica-Bold";
			$pdf->draw_paragraph($y1-10, $x1, $y1-10-(round(PREF_ETIQ_VSIZE*2.835)/5)+5, $x2, $nom_adh_ext, $firstpage, $param);
			$param["font"] = "Helvetica";
			$pdf->draw_paragraph ($y1-10-(round(PREF_ETIQ_VSIZE*2.835)/5), $x1, $y1-10-(round(PREF_ETIQ_VSIZE*2.835)/5)-(round(PREF_ETIQ_VSIZE*2.835)*4/5), $x2, $resultat->fields[3]."\n".$resultat->fields[8]."\n".$resultat->fields[5]."  -  ".$resultat->fields[6]."\n".html_entity_decode($resultat->fields[7]), $firstpage, $param);
			$pdf->draw_rectangle ($y1, $x1, $y2, $x2, $firstpage, $param);
			$resultat->MoveNext();

			$col++;
			if ($col>PREF_ETIQ_COLS)
			{
				$col=1;
				$row++;
			}
			if ($row>PREF_ETIQ_ROWS)
			{
				$col=1;
				$row=1;
				$firstpage = $pdf->new_page("a4");
			}
			$nb_etiq++;
		} 
		$resultat->Close();
		dblog(_T("G�n�ration de ")." ".$nb_etiq." "._T("�tiquette(s)"),$concatname);
		
		header("Content-Disposition: filename=example.pdf");
		header("Content-Type: application/pdf");
		$temp = $pdf->generate();
		header('Content-Length: ' . strlen($temp));
		echo $temp;
?>
