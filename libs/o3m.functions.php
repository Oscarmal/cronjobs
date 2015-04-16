<?php /*O3M*/
/**
* Descripción:	Funciones globales
* Creación:		2015-04-14
* Modificación:	2015-04-15
* @author		Oscar Maldonado - O3M
*/
require_once($parms[root_path]."libs/inc.mysqli.php");
##################
#Funciones comunes
##################

function dump_var($variable,$tipo=0){
	echo "<pre>";
	if(!$tipo){ print_r($variable); }else{var_dump($variable);}
	echo "</pre>";
	die();
}
#-FIN Comunes-#

##################
#Funciones Negocio
##################

function table_cronjob_estatus(){
// Muestra tabla con cronjobs activos en la tabla cron_tareas
	$datos = sql_select_verifica_cron();
	$tbl_resultados .= '<table border="1">';
	$tbl_resultados .= '
		<thead>
			<th>id_cron_log</th>
			<th>id_cronjob</th>
			<th>cron_nombre</th>
			<th>ejecuta</th>
			<th>inicio_fecha</th>
			<th>inicio_hora</th>
			<th>inicio_tiempo</th>
			<th>fin_fecha</th>
			<th>fin_hora</th>
			<th>fin_tiempo</th>
			<th>ejecucion_tipo</th>
			<th>ejecucion_valor</th>
			<th>ejecucion_segundos</th>
			<th>ejecucion_minutos</th>
			<th>ejecucion_tiempo</th>
			<th>estatus</th>
			<th>inicio</th>
			<th>fin</th>
			<th>respuesta</th>
			<th>timestamp</th>
			<th>proxima_ejecucion</th>
			<th>ahora</th>
			<th>excedido_tiempo</th>
			<th>excedido_minutos</th>
			<th>cron_iniciado</th>
			<th>cron_vigente</th>
			<th>cron_activo</th>
		</thead>';
	foreach($datos as $registro){
		$tbl_resultados .= '<tr>';
		$soloUno = (!is_array($registro))?true:false; 
		$data = (!$soloUno)?$registro:$datos; 
		// Dibuja tabla
		for($i=0; $i<count($data)/2; $i++){
			$tbl_resultados .= '<td>'.$data[$i].'</td>';
		}	
		if($soloUno) break;
		$tbl_resultados .= '</tr>';				
	}
	$tbl_resultados .= '</table>';
	$tbl_resultados .= '<a href="#" onclick="location.reload();">Actualizar</a>';
	return $tbl_resultados;
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
	// Verifica vigencia del cronjob
	$sqlData = array(id_cronjob => $data[id_cronjob]);
	$dataCron = sql_select_verifica_cron($sqlData);
	if($dataCron[cron_iniciado] && $dataCron[cron_vigente] && $dataCron[excedido_minutos]>=$parms[cron_excede]){
		// Comienza ejecución
		$t_ini = date("Y-m-d H:i:s");
		// Log en BD - Inserta registro en logs
		if($parms[log_db]){
			$sqlData = array(
				 id_cronjob => $data[id_cronjob]
				,inicio 	=> $t_ini				
			);
			$id_cron_log = sql_insert_cron_logs($sqlData);
		}
		ob_start();
		passthru($ejecuta, $err); #Ejecuta script
		$respuesta = (!$err)?ob_get_contents():'ERROR al ejecutar comando: <'.$ejecuta.'>';
		ob_end_clean();
		// Termina ejecución
		$t_fin = date("Y-m-d H:i:s");
		// Log en txt
		if($parms[log_txt] || !$data[id_cronjob]){
			$s = "\r\n";
			$logtxt .= 'ID_CRONJOB: '.$data[id_cronjob];
			$logtxt .= ' | NOMBRE: '.$data[cron_nombre];
			$logtxt .= ' | ARCHIVO: '.$data[ejecuta];
			$logtxt .= $s.'Tarea iniciada: '.$t_ini.$s.'Respuesta: '.$respuesta.$s.'Tarea finalizada: '.$t_fin;
			$logs = logs_txt($logtxt);
		}
		// Log en BD - Actualiza log con datos de ejecución
		if($parms[log_db]){
			$sqlData = array(
				 id_cron_log=> $id_cron_log
				,fin 		=> $t_fin
				,respuesta  => $respuesta
			);
			$success = (sql_update_cron_logs($sqlData))?true:false;
		}
	}
	return $success;
}

function logs_txt($contenido=''){
// Crea un archivo txt con la fecha de ejecución
	global $parms;
	$ruta = $parms[root_path].'logs/';
	$contenido = date("Y-m-d H:i:s").' -> '.$contenido."\r\n";
	$file=fopen($ruta."logs_cronjobs_".date("Ymd").".log","a+") or die("No se creó el archivo");
  	fputs($file,$contenido);
  	fputs($file,"-------\r\n");
  	fclose($file);
  	return true;
}
#-FIN Comunes-#

##################
#Funciones DAO
##################
function sql_select_cron_tareas($data=array()){
// Listado de tabla cron_tareas
	$id_cronjob = $data[id_cronjob];
	$filtro .= ($id_cronjob)?" AND id_cronjob='$id_cronjob'":'';
	$sql = "SELECT * FROM cron_tareas WHERE 1 AND activo=1 $filtro;";
	$resultado = SQLQuery($sql);
	$resultado = (count($resultado)) ? $resultado : false ;
	return $resultado;
}

function sql_insert_cron_logs($data=array()){
// Inserta un registro con el detalle de la ejecucion de un cronjob
	$id_cronjob = $data[id_cronjob];
	$inicio		= $data[inicio];
	$fin 		= $data[fin];
	$respuesta  = $data[respuesta];
	$estatus 	= 'EJECUTANDO';
	$timestamp 	= date("Y-m-d H:i:s");
	$sql = "INSERT INTO cron_logs SET 
			id_cronjob	= '$id_cronjob',
			estatus 	= '$estatus',
			inicio 		= '$inicio',
			/*fin 		= '$fin',
			respuesta 	= '$respuesta',*/			
			timestamp 	= '$timestamp'
			;";
	$resultado = SQLDo($sql);
	$resultado = (count($resultado)) ? $resultado : false ;	
	return $resultado;
}

function sql_update_cron_logs($data=array()){
// Inserta un registro con el detalle de la ejecucion de un cronjob
	$id_cron_log= $data[id_cron_log];
	$id_cronjob = $data[id_cronjob];
	$fin 		= $data[fin];
	$respuesta  = $data[respuesta];
	$estatus 	= 'TERMINADO';
	$sql = "UPDATE cron_logs SET 	
			estatus 	= '$estatus',
			fin 		= '$fin',
			respuesta 	= '$respuesta'
			WHERE id_cron_log='$id_cron_log'
			;";
	$resultado = SQLDo($sql);
	$resultado = (count($resultado)) ? $resultado : false ;
	return $resultado;
}

function sql_select_verifica_cron($data=array()){
// Listado de tabla cron_tareas
	$id_cronjob = $data[id_cronjob];
	$filtro .= ($id_cronjob)?" AND b.id_cronjob='$id_cronjob'":'';
	$sql = "SELECT 
				 a.id_cron_log
				,b.id_cronjob
				,b.cron_nombre
				,b.ejecuta
				,b.inicio_fecha
				,b.inicio_hora
				,CONCAT(b.inicio_fecha,' ',b.inicio_hora) as inicio_tiempo
				,b.fin_fecha
				,b.fin_hora
				,CONCAT(b.fin_fecha,' ',b.fin_hora) as fin_tiempo
				,b.ejecucion_tipo
				,b.ejecucion_valor
				,IF(b.ejecucion_tipo='MINUTOS',IFNULL(b.ejecucion_valor,0)*60,0) 
				+IF(b.ejecucion_tipo='HORAS',IFNULL(b.ejecucion_valor,0)*60*60,0) 
				+IF(b.ejecucion_tipo='DIAS',IFNULL(b.ejecucion_valor,0)*60*60*24,0) AS ejecucion_segundos
				,TRUNCATE((IF(b.ejecucion_tipo='MINUTOS',IFNULL(b.ejecucion_valor,0)*60,0) 
				+IF(b.ejecucion_tipo='HORAS',IFNULL(b.ejecucion_valor,0)*60*60,0) 
				+IF(b.ejecucion_tipo='DIAS',IFNULL(b.ejecucion_valor,0)*60*60*24,0))/60,0) AS ejecucion_minutos
				,TIME_FORMAT(SEC_TO_TIME(IF(b.ejecucion_tipo='MINUTOS',IFNULL(b.ejecucion_valor,0)*60,0) 
				+IF(b.ejecucion_tipo='HORAS',IFNULL(b.ejecucion_valor,0)*60*60,0) 
				+IF(b.ejecucion_tipo='DIAS',IFNULL(b.ejecucion_valor,0)*60*60*24,0)),'%H:%i') AS ejecucion_tiempo
				,a.estatus
				,IFNULL(a.inicio,NOW()) as inicio
				,IFNULL(a.fin,NOW()) as fin
				,a.respuesta
				,a.timestamp
				,(IFNULL(a.inicio,NOW())
					+
					INTERVAL (
						TRUNCATE(
							(IF(b.ejecucion_tipo='MINUTOS',IFNULL(b.ejecucion_valor,0)*60,0) 
							+IF(b.ejecucion_tipo='HORAS',IFNULL(b.ejecucion_valor,0)*60*60,0) 
							+IF(b.ejecucion_tipo='DIAS',IFNULL(b.ejecucion_valor,0)*60*60*24,0))/60,0
						)
					) MINUTE
				) as proxima_ejecucion
				,NOW() as ahora
				,IF(a.inicio IS NOT NULL,
					TIMEDIFF(NOW(),
					(a.inicio
						+
						INTERVAL (
							TRUNCATE(
								(IF(b.ejecucion_tipo='MINUTOS',IFNULL(b.ejecucion_valor,0)*60,0) 
								+IF(b.ejecucion_tipo='HORAS',IFNULL(b.ejecucion_valor,0)*60*60,0) 
								+IF(b.ejecucion_tipo='DIAS',IFNULL(b.ejecucion_valor,0)*60*60*24,0))/60,0
							)
						) MINUTE
					)
				),1) as excedido_tiempo
				,IF(a.inicio IS NOT NULL,
					TIME_TO_SEC(TIMEDIFF(NOW(),
					(a.inicio
						+
						INTERVAL (
							TRUNCATE(
								(IF(b.ejecucion_tipo='MINUTOS',IFNULL(b.ejecucion_valor,0)*60,0) 
								+IF(b.ejecucion_tipo='HORAS',IFNULL(b.ejecucion_valor,0)*60*60,0) 
								+IF(b.ejecucion_tipo='DIAS',IFNULL(b.ejecucion_valor,0)*60*60*24,0))/60,0
							)
						) MINUTE
					)
				))/60,1) as excedido_minutos
				,IF(NOW()>=CONCAT(b.inicio_fecha,' ',b.inicio_hora),1,0) as cron_iniciado
				,IF(IFNULL(a.fin,NOW())<=CONCAT(b.fin_fecha,' ',b.fin_hora),1,0) as cron_vigente
				,b.activo as cron_activo
			FROM cron_tareas b
			LEFT JOIN (SELECT * FROM (SELECT * FROM cron_logs ORDER BY id_cronjob ASC, inicio DESC) AS tbl_unicos GROUP BY tbl_unicos.id_cronjob) a ON a.id_cronjob=b.id_cronjob
			WHERE 1 AND IFNULL(a.estatus,'TERMINADO')='TERMINADO' $filtro
			;";
	$resultado = SQLQuery($sql);
	$resultado = (count($resultado)) ? $resultado : false ;
	return $resultado;
}

#-FIN DAO-#

/*O3M*/
?>
