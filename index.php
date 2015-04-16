<?php /*O3M*/
/**
* Descripción:	Script para ejecutar cronjobs en servidor linux a traves de BD
* @author		Oscar Maldonado - O3M
* Creación:		2015-04-14
* Modificación:	2015-04-15
*/

// Parametros
$parms[cronjobs] = true; # Activar servicio
$parms[cron_excede] = 0; # Minutos excedentes de gracia para re-ejecución de una tarea
$parms[log_txt] = true; # Activar logs en archivos txt
$parms[log_db]  = true; # Activar logs en base de datos
$parms[root_path]  	= ''; # Ruta de directorio raiz del sistema
$parms[cron_paths]  = $parms[root_path].'cron/'; # Ruta de los archivos cronjob

if($parms[cronjobs]){
	require_once($parms[root_path]."libs/o3m.functions.php");
	extract($_GET, EXTR_PREFIX_ALL, "v");
	extract($_POST, EXTR_PREFIX_ALL, "v"); 
	// Tiempo de inicio del script
	$t_entrada = date("Y-m-d H:i:s");

	// 
	switch (strtolower($v_a)) {
		case 'tbl':
			echo "<h3>Cronjobs: ".$t_entrada.'</h3>'."<hr/>";
			echo table_cronjob_estatus();
			break;
		case 'exe':
			execute_cron_tareas();
			break;	
		default:
			echo utf8_decode("Sin autorización");
			break;
	}
}
/*O3M*/
?>