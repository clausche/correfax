<?php
 
	function makeRandomPassword()
	{
		$pass = "";
  		$salt = "abcdefghjkmnpqrstuvwxyz0123456789";
    		srand((double)microtime()*1000000);
          	$i = 0;
	        while ($i <= 6) 
		{
	        	$num = rand() % 33;
	        	$tmp = substr($salt, $num, 1);
	        	$pass = $pass . $tmp;
	        	$i++;
	      	}
	     	return $pass;
	}

	function isSelected($champ1, $champ2) { 
	  if ($champ1 == $champ2) { 
	    echo " selected"; 
	  } 
	} 
 
	function isChecked($champ1, $champ2) { 
	  if ($champ1 == $champ2) { 
	    echo " checked"; 
	  } 
	} 

	function txt_sqls($champ) { 
		return "'".str_replace("'", "\'", str_replace('\\', '', $champ))."'"; 
	}
	
	function is_valid_web_url($url) {
	  return (preg_match(
	  		'/^(http|https):\/\/'.
	  		'.*/i', $url, $m
	  		));
	}
	

	function is_valid_email ($address) {
    return (preg_match(
        '/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+'.   // the user name
        '@'.                                     // the ubiquitous at-sign
        '([-0-9A-Z]+\.)+' .                      // host, sub-, and domain names
        '([0-9A-Z]){2,4}$/i',                    // top-level domain (TLD)
        trim($address))); 
        	} 
	
	function dblog($text, $query="")
	{
		if (PREF_LOG=="2")
		{
			$requete = "INSERT INTO ".PREFIX_DB."logs (date_log, ip_log, adh_log, text_log) VALUES (" . $GLOBALS["DB"]->DBTimeStamp(time()) . ", " . $GLOBALS["DB"]->qstr($_SERVER["REMOTE_ADDR"]) . ", " . $GLOBALS["DB"]->qstr($_SESSION["logged_nom_adh"]) . ", " . $GLOBALS["DB"]->qstr($text."\n".$query) . ");";
			$GLOBALS["DB"]->Execute($requete);
		}
		elseif (PREF_LOG=="1")
		{
			$requete = "INSERT INTO ".PREFIX_DB."logs (date_log, ip_log, adh_log, text_log) VALUES (" . $GLOBALS["DB"]->DBTimeStamp(time()) . ", " . $GLOBALS["DB"]->qstr($_SERVER["REMOTE_ADDR"]) . ", " . $GLOBALS["DB"]->qstr($_SESSION["logged_nom_adh"]) . ", " . $GLOBALS["DB"]->qstr($text) . ");";
			$GLOBALS["DB"]->Execute($requete);
		}
	}
	
	
	
	
	function resizeimage($img,$img2,$w,$h)
	{
		if (function_exists("imagecreate"))
		{
			$ext = substr($img,-4);
			$imagedata = getimagesize($img);
			$ratio = $imagedata[0]/$imagedata[1];
			if ($imagedata[0]>$imagedata[1])
				$h = $w/$ratio;
			else
				$w = $h*$ratio;
			$thumb = imagecreate ($w, $h);
			switch($ext)
			{
				case ".jpg":
					$image = ImageCreateFromJpeg($img);
					imagecopyresized ($thumb, $image, 0, 0, 0, 0, $w, $h, $imagedata[0], $imagedata[1]);
					imagejpeg($thumb, $img2);
					break;
				case ".png":
					$image = ImageCreateFromPng($img);
					imagecopyresized ($thumb, $image, 0, 0, 0, 0, $w, $h, $imagedata[0], $imagedata[1]);
					imagepng($thumb, $img2);
					break;
				case ".gif":
					if (function_exists("imagegif"))
					{
						$image = ImageCreateFromGif($img);
						imagecopyresized ($thumb, $image, 0, 0, 0, 0, $w, $h, $imagedata[0], $imagedata[1]);
						imagegif($thumb, $img2);
					}
					break;					
			}
		}
	}
	
	function custom_html_entity_decode( $given_html, $quote_style = ENT_QUOTES )
	{
   	$trans_table = array_flip(get_html_translation_table( HTML_ENTITIES, $quote_style ));
   	$trans_table['&#39;'] = "'";
   	return ( strtr( $given_html, $trans_table ) );
	}

	
	
	
	
?>
