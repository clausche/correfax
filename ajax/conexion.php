<?php
function conectar()
{
	mysql_connect("localhost", "root", "");
	mysql_select_db("ajax");
}

function desconectar()
{
	mysql_close();
}
?>