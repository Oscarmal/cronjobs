<?php /*O3M*/
/**
* Descripción:	Script para ejecutar cronjobs en servidor linux a traves de BD
* @author		Oscar Maldonado - O3M
* Creación:		2015-04-14
* Modificación:	2015-04-15
*/
require_once("libs/o3m.functions.php");
extract($_GET, EXTR_PREFIX_ALL, "v");
extract($_POST, EXTR_PREFIX_ALL, "v"); 

// Parametros
$parms[log_txt] = true; # Activar logs en archivos txt
$parms[log_db]  = true; # Activar logs en base de datos
$parms[cron_paths]  = 'cron/'; # Ruta de los archivos cronjob

// Tiempo de inicio del script
$t_entrada = date("Y-m-d H:i:s");

// 
switch (strtolower($v_a)) {
	case 'tbl':
		echo "<h3>Cronjobs: ".$t_entrada.'</h3>'."<hr/>";
		echo table_cron_tareas();
		break;
	case 'exe':
		execute_cron_tareas();
		break;	
	default:
		echo utf8_decode("Sin autorización");
		break;
}
/*O3M*/
?>