<?php /*O3M*/
/**
* Descripción:	Script para ejecutar cronjobs en servidor linux a traves de BD
* @author		Oscar Maldonado - O3M
* Creación:		2015-04-14
* Modificación:	2015-04-15
*/
require_once("libs/o3m.functions.php");
require_once("libs/inc.mysqli.php");
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

/**
* Funciones
*/

function sql_select_cron_tareas($data=array()){
// Listado de tabla cron_tareas
	$id_cronjob = $data[id_cronjob];
	$filtro .= ($id_cronjob)?" AND id_cronjob='$id_cronjob'":'';
	$sql = "SELECT * FROM cron_tareas WHERE 1 AND activo=1 $filtro;";
	$resultado = SQLQuery($sql);
	$resultado = (count($resultado)) ? $resultado : false ;
	return $resultado;
}

function table_cron_tareas(){
// Muestra tabla con cronjobs activos en la tabla cron_tareas
	$datos = sql_select_cron_tareas();
	$tbl_resultados .= '<table border="1">';
	$tbl_resultados .= '
		<thead>
			<th>id_cronjob</th>
			<th>id_sistema</th>
			<th>cron_nombre</th>
			<th>cron_descripcion</th>
			<th>inicio_fecha</th>
			<th>inicio_hora</th>
			<th>fin_fecha</th>
			<th>fin_hora</th>
			<th>cada_dias</th>
			<th>cada_horas</th>
			<th>cada_minutos</th>
			<th>tipo</th>
			<th>ejecuta</th>
			<th>activo</th>
		</thead>';
	foreach($datos as $registro){
		$tbl_resultados .= '<tr>';
		$soloUno = (!is_array($registro))?true:false; 
		$data = (!$soloUno)?$registro:$datos; 
		// Dibuja tabla
		for($i=0; $i<count($data)/2; $i++){
			$tbl_resultados .= '<td>'.$data[$i].'</td>';
		}
		// $exe_cron = execute_cronjob($data[0]);	
		if($soloUno) break;
		$tbl_resultados .= '</tr>';				
	}
	$tbl_resultados .= '</table>';
	$tbl_resultados .= '<a href="#" onclick="location.reload();">Actualizar</a>';
	return $tbl_resultados;
}

function execute_cron_tareas(){
// Recorre  tabla con cronjobs en la tabla cron_tareas y ejecuta los activos
	$datos = sql_select_cron_tareas();
	foreach($datos as $registro){
		$soloUno = (!is_array($registro))?true:false; 
		$data = (!$soloUno)?$registro:$datos; 		
		$exe_cron = execute_cronjob($data[0]);
		if($soloUno) break;		
	}
	$respuesta = utf8_decode("Modo de ejecución").date("Y-m-d H:i:s");
	return $respuesta;
}

function execute_cronjob($id_cronjob=false){
// Ejecuta una tarea de la tabla cron_tareas
	global $parms;
	$path = $parms[cron_paths];
	$sqlData = array(id_cronjob => $id_cronjob);
	$data = sql_select_cron_tareas($sqlData);
	$data[ejecuta] = $path.$data[ejecuta];
	switch ($data[tipo]) {
		case 'PHP':	$ejecuta = 'php '.$data[ejecuta];
					break;
		case 'LINUX': $ejecuta = 'sh '.$data[ejecuta];
					break;		
		default: unset($ejecuta); break;
	}		
	// Comienza ejecución
	$t_ini = date("Y-m-d H:i:s");
	ob_start();
	passthru($ejecuta, $err);
	$success = (!$err)?ob_get_contents():'Error al ejecutar comando: <'.$ejecuta.'>';
	ob_end_clean();
	// Termina ejecución
	$t_fin = date("Y-m-d H:i:s");
	// Log en txt
	if($parms[log_txt] || !$data[id_cronjob]){
		$s = "\r\n";
		$logtxt .= 'ID_CRONJOB: '.$data[id_cronjob];
		$logtxt .= ' | NOMBRE: '.$data[cron_nombre];
		$logtxt .= ' | ARCHIVO: '.$data[ejecuta];
		$logtxt .= $s.'Tarea iniciada: '.$t_ini.$s.'Respuesta: '.$success.$s.'Tarea finalizada: '.$t_fin;
		$logs = logs_txt($logtxt);
	}
	// Log en BD
	if($parms[log_db]){
		$sqlData = array(
			 id_cronjob => $data[id_cronjob]
			,inicio 	=> $t_ini
			,fin 		=> $t_fin
			,respuesta  => $success
		);
		$success = (sql_insert_cron_logs($sqlData))?true:false;
	}
	return $success;
}

function sql_insert_cron_logs($data=array()){
// Inserta un registro con el detalle de la ejecucion de un cronjob
	$id_cronjob = $data[id_cronjob];
	$inicio		= $data[inicio];
	$fin 		= $data[fin];
	$respuesta  = $data[respuesta];
	$timestamp 	= date("Y-m-d H:i:s");
	$sql = "INSERT INTO cron_logs SET 
			id_cronjob	= '$id_cronjob',
			inicio 		= '$inicio',
			fin 		= '$fin',
			respuesta 	= '$respuesta',
			timestamp 	= '$timestamp'
			;";
	$resultado = SQLDo($sql);
	$resultado = (count($resultado)) ? $resultado : false ;
	return $resultado;
}

function logs_txt($contenido=''){
// Crea un archivo txt con la fecha de ejecución
	$ruta = 'logs/';
	$contenido = date("Y-m-d H:i:s").' -> '.$contenido."\r\n";
	$file=fopen($ruta."logs_cronjobs_".date("Ymd").".txt","a+") or die("No se creó el archivo");
  	fputs($file,$contenido);
  	fputs($file,"-------\r\n");
  	fclose($file);
  	return true;
}

/*O3M*/
?>