<?php 
function crea_txt($contenido=''){
// Crea un archivo txt con la fecha de ejecución
	$contenido = date("Y-m-d H:i:s").' -> '.$contenido."\r\n";
	$file=fopen(date("Ymd-His").'.txt',"a") or die("No se creó el archivo");
  	fputs($file,$contenido);
  	fclose($file);
}
sleep(3);
// crea_txt(date("Y-m-d H:i:s"));
echo 'Ejecutado OK: '.date("Y-m-d H:i:s");
?>