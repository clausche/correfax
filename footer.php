<?php

	$end = utime(); $run = $end - $start;
?>

	<DIV id="copyright">
		<A href="http://localhost/"><? echo LAUTARO_VERSION ?></A> - Autor :  <A href="mailto:clausche@gmail.com"><? echo AUTOR ?></A> - <? echo _T("Page affich�e en")." ".substr($run, 0, 5)." "._T("secondes."); ?>
	</DIV>
	</DIV>
</DIV>

			

				


<?
	if ($_SESSION["admin_status"]==1)
	{
?>
				
<div style="float:left; margin-left:0px;"> <!--This is the second division of right-->
  <p><strong>Operaciones </strong></p>
  <div class="menu_list" id="secondpane"> <!--Code for menu starts here-->
		<p class="menu_head">Gesti�n Correos</p>
		<div class="menu_body">
		<a href="gestion_correspondencia.php?filtre_2=0">Lista de correos</a>
         <a href="ajouter_adherent.php">Agregar Correo</a>
         	
		</div>
		<p class="menu_head">Gesti�n de archivos</p>
		<div class="menu_body">
			<a href="gestion_archivo.php"><? echo ("Lista de Archivos"); ?></a>
         <a href="ajouter_archivo.php"><? echo ("Agregar archivo"); ?></a>

		</div>
		<p class="menu_head">Reportes</p>
		<div class="menu_body">
          <a href="pdform.php?etiquettes=1"><? echo "Generar PDF"; ?></a>
          <a href="reporte_torta.php"><? echo "Generar Gr�fico Torta"; ?></a>
          <a href="reporte_barra.php"><? echo "Generar Gr�fico Barra"; ?></a>
			
       	</div>
		<p class="menu_head">Bade de Datos</p>
		<div class="menu_body">
          <a href="respaldo.php"><? echo "Respaldo"; ?></a>
          <a href="restore_dump.php"><? echo "Restaurar"; ?></a>
       	</div>
  </div>      



<?
	}
?>


			
		
		
		
<?
	if (basename($_SERVER["SCRIPT_NAME"])=="gestion_correspondencia.php" || 
		basename($_SERVER["SCRIPT_NAME"])=="mailing_correspondencia.php")
	{
?>
<div style="float:left; margin-left:0px;"> <!--This is the second division of right-->
  <p><strong>Leyenda </strong></p>
		<DIV class="menu_list">

<?
		if (basename($_SERVER["SCRIPT_NAME"])=="gestion_correspondencia.php")
		{

		}
?>
<?
	if ($_SESSION["admin_status"]==1)
	{
?>
				
					<p width="30" class="back"><IMG src="images/icon-edit.png" alt="Editar" border="0" width="12" height="13">
					 <? echo ("Editar"); ?></p>
				
				
					<p width="30" class="back"><IMG src="images/icon-money.png" alt="Modificaciones" border="0" width="13" height="13">
					<? echo ("Modificaciones"); ?>
				
					<p width="30" class="back"><IMG src="images/icon-trash.png" alt="Eliminar" border="0" width="11" height="13">
					<? echo ("Eliminar"); ?></p>
					
				
<?
	}
?>
				
					
				
			
		</DIV>

</div>

<?
	}
	elseif (basename($_SERVER["SCRIPT_NAME"])=="gestion_contributions.php")
	{
?>
		<DIV id="legende">
			<H1><? echo _T("L�gende"); ?></H1>
			<TABLE>
<?
		if ($_SESSION["admin_status"]==1)
		{
?>
				
<?
		}
?>

			</TABLE>
		</DIV>
<?
	}
?>

	


</div>
<DIV id="logout">
				<A href="index.php?logout=1"><? echo _T("D�connexion"); ?></A>
	</DIV>
</BODY>
</HTML>
