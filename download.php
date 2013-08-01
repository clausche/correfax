<?php
 	if(isset($_GET['id']))  
 	{  
 // if id is set then get the file with the id from database
 
 	include 'config.php';
 	include 'opendb.php'; 
 	$id    = $_GET['id'];  
 	$query = "SELECT name, type, size, content " .
          "FROM gac_archivo WHERE id_dos = '$id'";
 	$result = mysql_query($query) or die('Error, query failed');
 	list($name, $type, $size, $content) = mysql_fetch_array($result);
 	header("Content-length: $size"); 
 	header("Content-type: $type"); 
 	header("Content-Disposition: attachment; filename=$name");
 	echo $content;   
 	exit;
 	}
?>
<html>
<head>
<title>Download File From MySQL</title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" >
</head>
<body>
<?php
include 'config.php';
include 'opendb.php';

	$query = "SELECT id_dos, name FROM gac_archivo";
	$result = mysql_query($query) or die('Error, query failed');
	if(mysql_num_rows($result) == 0)
	{
	echo "Database is empty <br>";
	}
	else
	{
		while(list($id_dos, $name) = mysql_fetch_array($result))
		{
?>
			<a href="download.php?id_dos=<? echo $id_dos;?>"><? echo $name;?></a><br>
<?php
		}
	}
?>

</body>
</html>
