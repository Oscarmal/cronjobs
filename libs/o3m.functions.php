<?php /*O3M*/
/**
* Descripción:	Funciones globales
* Creación:		2014-04-25
* Modificación:	2014-08-25
* @author		Oscar Maldonado - O3M
*/

##################
#Funciones críticas - ¡Cuidado al editar!
##################

function parseFormSanitizer($g,$p){
#Load form information ($_GET/$_POST) into array $ins[], $cmd[] with sanitizer
#ejem: parse_form($_GET, $_POST);
	global $ins, $cmd;
	if(!empty($g)){
		$tvars = count($g);
		$vnames = array_keys($g);
		$vvalues = array_values($g);
	}elseif(!empty($p)){
		$tvars = count($p);
		$vnames = array_keys($p);
		$vvalues = array_values($p);
	}
	for($i=0;$i<$tvars;$i++){
		if($vnames[$i]=='cmd'){$cmd=$vvalues[$i];}
		$ins[$vnames[$i]]=sanitizerUrl($vvalues[$i]);
	}
}

function parseForm($g,$p){
#Load form information ($_GET/$_POST) into array $in[], $cmd[] without sanitizer
#ejem: parse_form($_GET, $_POST);
	global $in, $cmd;
	if(!empty($g)){
		$tvars = count($g);
		$vnames = array_keys($g);
		$vvalues = array_values($g);
	}elseif(!empty($p)){
		$tvars = count($p);
		$vnames = array_keys($p);
		$vvalues = array_values($p);
	}
	for($i=0;$i<$tvars;$i++){
		if($vnames[$i]=='cmd'){$cmd=$vvalues[$i];}
		$in[$vnames[$i]]=$vvalues[$i];
	}
}

function sanitizerUrl($param) {
#Sanitizes a url param
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "+", "[", "{", "]","}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;","â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($param)));
	return $clean;
}

function limpiarTmp($dir='',$extension=array(), $segundos=120){
// Borra archivos con las extension indeicada
	$t=time();
    $h=opendir($dir);
    while($file=readdir($h)){
    	$f = explode('.',$file);
		$ext = $f[count($f)-1];	
    	if(in_array($ext, $extension)){
        // if(substr($file,-4)=='.'.$extension){
            $path=$dir.$file;
            if($t-filemtime($path)>$segundos)
                @unlink($path);
        }
    }
    closedir($h);
}

function LogTxt($nom, $u, $n, $g, $ub, $r, $params){
# Función para crear archivo log .txt con movimientos dentro del sistema
# acceso(nombre_archivo, usuario ID, usuario_nombre, usuario, nivel, ruta)
global $cfg;
$fec = date("Y-m-d");
if($_SERVER["HTTP_X_FORWARDED_FOR"])
{
	if($pos=strpos($_SERVER["HTTP_X_FORWARDED_FOR"]," "))
	{
		$ip_loc = substr($_SERVER["HTTP_X_FORWARDED_FOR"],0,$pos);
		$ip_pub = substr($_SERVER["HTTP_X_FORWARDED_FOR"],$pos+1);
	}else{$ip_pub=$_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	if($_SERVER["REMOTE_ADDR"])
		$proxy = $_SERVER["REMOTE_ADDR"];
}else{$ip_pub=$_SERVER["REMOTE_ADDR"];
}
$s = "|";	//separador
$page_ant = $_SERVER['HTTP_REFERER'];
$page = $_SERVER['PHP_SELF'];  
#$hostname=gethostbyaddr($ip_pub);
if($ip_pub!=$hostname){	$host = $hostname; }
$f = date("d-m-Y H:i:s");
$nav = $_SERVER['HTTP_USER_AGENT'];
$archivo = $r.$cfg[path_log].strtolower($nom)."_".$fec.".txt";
$fp = fopen($archivo, "a+");
# FECHA | USUARIO | NOMBRE | GRUPO | UBICACION | IP PUBLICA | IP LOCAL | NOMBRE DE PC | NAVEGADOR | URL ANTERIOR | URL ACTUAL
#$texto = $f."$s".$u."$s".$n."$s".$e."$s".$ub."$s".$ip_pub."$s".$ip_loc."$s".$host."$s".$nav."$s".$page_ant."$s".$page."\r\n";
$texto = $f."$s".$u."$s".$n."$s".$g."$s".$ub."$s".$ip_pub."$s".$ip_loc."$s".$host."$s".$nav."$s".$page_ant."$s".$page."$s".$params."\r\n";
$write = fputs($fp, $texto);
fclose($fp);
return ;
}

function breadcrumbs($home = 'Inicio', $separator = '>') {
## Crea la barra de navegación para el usuario
## @params $home String, $separatos String (puede ser un caracter o la ruta de una imagen)
	global $Raiz;
	$home=(!$Raiz['sitefolder']?ucwords($home):ucwords($Raiz['sitefolder']));
	#$path = array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
	$rootPath = explode($Raiz['sitefolder'].'/', $_SERVER['REQUEST_URI']);
	$path = array_filter(explode('/', $rootPath[1]));
	$base = $Raiz['url'];
	$breadcrumbs = array(':: <a rel="nofollow" href="'. $base .'">'. $home .'</a>');	
	$last = end(array_keys($path));	 
	foreach ($path as $x => $crumb) {
		$base.=$crumb.'/';
		$title = ucwords(str_replace(array('.php', '_', '-'), array('', ' ', ' '), $crumb));	 
		if ($x != $last) {
			$breadcrumbs[] = '<a rel="nofollow" href="'. $base .'">'. $title .'</a>';
		} else {
			$breadcrumbs[] = $title;
		}
	}
	if(strlen($separator)>3){
		$separator="&nbsp;<img src='$separator' border='0' align='middle'>&nbsp;";
	}
	return implode($separator, $breadcrumbs);
}

function plantillaRtf($Plantilla,$Ruta,$NuevoDoc,$Variables,$CharAbre,$CharCierra,$Valores){
	$NuevoDoc=$Ruta.$NuevoDoc;
	$txtplantilla=file_get_contents($Plantilla);
	$matriz=explode("sectd",$txtplantilla);
	$cabecera=$matriz[0]."sectd";
	$inicio=strlen($cabecera);
	$final=strrpos($txtplantilla,"}");
	$largo=$final-$inicio;
	$cuerpo=substr($txtplantilla,$inicio,$largo);
	$punt=fopen($NuevoDoc,"wb");
	fputs($punt,$cabecera);
	$Registros = count($Valores);
	for($i=1; $i<=$Registros; $i++){			
		$row=$Valores[$i];
		$despues=$cuerpo;
		for($x=0; $x<count($Variables); $x++){$nvariables[$x][0]=$CharAbre.$Variables[$x].$CharCierra;}
		$n=0;
		foreach($nvariables as $dato){
			$datosql=utf8_decode(str_replace('\\','Ñ',utf8_encode(utf8_decode($row[$n]))));
			$datortf=$dato[0];
			$despues=str_replace($datortf,$datosql,$despues);
			$despues=str_replace(strtoupper($datortf),$datosql,$despues);
			$despues=str_replace(strtolower($datortf),$datosql,$despues);
			$n++;
		}
		fputs($punt,$despues);
	}	
	fputs($punt,"}");
	fclose($punt);
	return $NuevoDoc;
}

function encrypt($input,$opt=false){
	global $cfg;
	$Key = $cfg[encrypt_key]; 
	if($opt){
		$output = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($Key), $input, MCRYPT_MODE_CBC, md5(md5($Key))));
    	// $output = urlencode($output);
    	$output = md5($output);
	}else{
		// $input = urldecode($input);
		$output = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($Key), base64_decode($input), MCRYPT_MODE_CBC, md5(md5($Key))), "\0");
    }
	return ($output);	
}

function siExiste($texto, $txtmd5){
// Compara una cadena sin codificar contra una en MD5
	// $encrypt = md5(encrypt($texto,1));
	$encrypt = encrypt($texto,1);
	$resultado = ($encrypt == $txtmd5)?true:false;
	return $resultado;
}

function enArray($valor,$array){
// Busca que exista el indice de un array y devuelve en nombre del indice
	foreach(array_keys($array) as $n){
		if(siExiste($n,$valor) && !$ok){
			$ok = true;
			$find = $n;
			break;
		}else{$find = false;}	
	}
	return $find;
}

function diccionario($filename='es.dic') {
#Load config information from config.ini file.		
	try {
		global $cfg, $dic;
		if (file_exists($filename)) {
	        if ($handle = fopen($filename, 'r')) {
	        	$varsList = explode(',',$cfg[modulos]);
	        	// $varsList = array('gral','captura','consultas','reportes','admin');
	            while (!feof($handle)) {
	                list($type, $name, $value) = preg_split("/\||=/", fgets($handle), 3);
					if (trim($type)!='#') { 
					#DIC vars
						$dic[trim($type)][trim($name)] = trim($value);
					}	
					if (in_array(trim($type),$varsList)) { 
					#Print for Debug
					 	$val.=$type.' | '.$name.' = '.$value."<br/>\n\r";
					}
	            }	            
	        }	        
			return $val;
		}else{
			$msj = "¡ERROR CRÍTICO!<br/> No se ha logrado cargar el archivo diccionario, por favor, contacte al administrador del sistema.<br/>";
	    	throw new Exception($msj, 1);    	
	    }	
	} catch (Exception $e) {		
		print($e->getMessage());
		return false;
	}	   
}

function pais_params($filename='pais_params.cfg') {
#Load config information from config.ini file.		
	try {
		global $cfg, $dic;
		if (file_exists($filename)) {
	        if ($handle = fopen($filename, 'r')) {
	            while (!feof($handle)) {
	                list($type, $name, $value) = preg_split("/\||=/", fgets($handle), 3);
					if (trim($type)!='#') { 
					#PARAMS $pais[mexico][variable]
						$pais_params[trim($type)][trim($name)] = trim($value);
						$val.=$type.' | '.$name.' = '.$value."<br/>\n\r";
					}	
	            }	            
	        }	        
			return $val;
		}else{
			$msj = "¡ERROR CRÍTICO!<br/> No se ha logrado cargar el archivo diccionario, por favor, contacte al administrador del sistema.<br/>";
	    	throw new Exception($msj, 1);    	
	    }	
	} catch (Exception $e) {		
		print($e->getMessage());
		return false;
	}	   
}

function filtro_grupo($grupos=array()){
// Validación de grupos de acceso
	global $usuario;
	switch ($usuario[id_grupo]){	
		#Roor - Desarrollo:
		case 0 : $filtro = $grupos[0]; break;
		#Administradores:
		case 10 : $filtro = $grupos[10]; break;
		#Super Usuarios - Inplant:
		case 20 : $filtro = $grupos[20]; break;
		#Super Usuarios - Cliente:
		case 21 : $filtro = $grupos[21]; break;
		#Grupo 1 - Nivel1:
		case 30 : $filtro = $grupos[30]; break;
		#Grupo 1 - Nivel2:
		case 34 : $filtro = $grupos[34]; break;
		#Grupo 1 - Nivel3:
		case 35 : $filtro = $grupos[35]; break;
		#Grupo 2 - Nivel2:	
		case 40 : $filtro = $grupos[40]; break;
		#Grupo 3 - Nivel1:
		case 50 : $filtro = $grupos[50]; break;
		#Grupo 4 - Empleados:
		case 60 : $filtro = $grupos[60]; break;
		#Grupo 5 - Extra:
		case 70 : $filtro = $grupos[70]; break;
		default : $filtro = $grupos[70]; break;
	}
	$filtro = (!$filtro)?'':$filtro;
	return $filtro;
}

function horas_int($horas){
	$h = explode(':', $horas);
	$horas = (!$h[1]) ? $h[0].':00' : $h[0].':'.$h[1];
	return $horas;
}

function semana_iso8601($fecha=''){
	$fecha = (empty($fecha))?date('Y-m-d'):$fecha;
	$semana_iso8601 = date('o-W', strtotime($fecha));
	return $semana_iso8601;
}
#-FIN Críticas-#

##################
#Funciones de apoyo
##################

function ceros($num=0, $digitos=1){
	if(strlen($num)<$digitos){
	    for($i=1; $i<=$digitos-strlen($num); $i++){
	        $cero .= '0';
	    }
	    $num = $cero.$num;
	}
    return $num;
}

function fechaHoy(){
	$dia=date("l");
	if ($dia=="Monday") $dia="Lunes";
	if ($dia=="Tuesday") $dia="Martes";
	if ($dia=="Wednesday") $dia="Miércoles";
	if ($dia=="Thursday") $dia="Jueves";
	if ($dia=="Friday") $dia="Viernes";
	if ($dia=="Saturday") $dia="Sábado";
	if ($dia=="Sunday") $dia="Domingo";
	$dia2=date("d");
	$mes=date("F");
	if ($mes=="January") $mes="Enero";
	if ($mes=="February") $mes="Febrero";
	if ($mes=="March") $mes="Marzo";
	if ($mes=="April") $mes="Abril";
	if ($mes=="May") $mes="Mayo";
	if ($mes=="June") $mes="Junio";
	if ($mes=="July") $mes="Julio";
	if ($mes=="August") $mes="Agosto";
	if ($mes=="September")$mes="Septiembre";
	if ($mes=="October") $mes="Octubre";
	if ($mes=="November") $mes="Noviembre";
	if ($mes=="December") $mes="Diciembre";
	$anio=date("Y");
	return "$dia $dia2 de $mes del $anio";
}

function zipFile($File='', $Path='', $Delete=false){
##Compress in Zip file in the same directory
	chdir($Path);
	$f = explode('.',$File);
	for($i=0; $i<count($f)-1; $i++){
		$zipFile .= $f[$i];
	}
	$zipFile .= '.zip';
	$Zip = new ZipArchive;
	$Zip->open($zipFile, ZipArchive::CREATE);	
	$Zip->addFile($File);
	$Zip->close();
	if($Delete){
		unlink($File);
	}
	return $zipFile;
}

function incCss($filename){
	$cadena = '<link href="'.$filename.'" rel="stylesheet" type="text/css">';
	return $cadena;
}
function incJs($filename){
	$cadena = '<script type="text/javascript" src="'.$filename.'"></script>';
	return $cadena;
}
function incImg($filename,$w='100%',$h='100%'){
	$cadena = '<img src="'.$filename.'" width="'.$w.'" height="'.$h.'" border="0">';
	return $cadena;
}

function fecha_form($fecha,$hora=0){
	switch ($hora) {
		case 0: $hora = ' H:i:s'; break;
		case 1: $hora = ''; break;		
		default: $hora = ' H:i:s'; break;
	}
	$fecha = date_create_from_format('d/m/Y', $fecha);
	$fecha = date_format($fecha, 'Y-m-d'.$hora);
	return $fecha;
}

// function xls($descarga=true, $datos=array(), $colsTitulos=array(), $archivo='tmp', $tituloTabla='TABLA', $hoja='', $directorio=false){
function xls($params=array()){
/*
// Generación de XLS //
	  $tabla = xls_select($sqlData);
      $titulos = array('ID','Nombre Completo','No. Empleado','Fecha','Horas','Estatus','Capturado por','Capturado el');
      $xlsData = array(
                         descarga         => false
                        ,datos            => $tabla
                        ,colsTitulos      => $titulos
                        ,archivo          => 'HE_Horas-Extra'
                        ,tituloTabla      => 'HE - Horas Extra'
                        ,hoja             => 'Hoja1'
                        ,directorio       => $cfg[path_docs].'autorizacion/'
                        ,id_empresa       => $usuario[id_empresa]
                  );
      $xls = xls($xlsData);
*/
	global $Path, $Raiz;
	require_once($Path[php].'class.spreadsheetExcelWriter.php');
	// Parametros recibidos
	$descarga	= (!isset($params[descarga]))?false:$params[descarga];
	$datos 		= (!isset($params[datos]))?array():$params[datos];
	$colsTitulos= (!isset($params[colsTitulos]))?array():$params[colsTitulos]; 
	$archivo 	= (!isset($params[archivo]))?'tmp':$params[archivo];
	$tituloTabla= (!isset($params[tituloTabla]))?'':$params[tituloTabla]; 
	$hoja 		= (!isset($params[hoja]))?'':$params[hoja];
	$directorio = (!isset($params[directorio]))?false:$params[directorio];
	// Parametros inicales
	$x=0; #Filas
	$y=0; #Columnas
	if (file_exists($Raiz[local].$directorio)){
		$dirLocal 	= $Raiz[local].$directorio;
		$dirUrl		= $Raiz[url].$directorio;
	}else{
		$dirLocal 	= $Path[tmp];
		$dirUrl 	= $Path[tmpurl];
	}
	$id_empresa  = str_pad($params[id_empresa], 2, '0', STR_PAD_LEFT);
	$id_empresa .= '_';
	$extensiones = array('/.xlsx/','/.xls/','/.csv/');
	$archivo 	 = preg_replace($extensiones, '', $archivo); #limpia extensiones
	$archivo 	 = $archivo.'_'.$id_empresa.date('Ymd-His');
	$hoja 		 = (empty($hoja))?date('Ymd-His'):$hoja;
	$filename[filename] = $archivo.'.xls';
	if(!$descarga){            
	    /*Para crear en directorio de servidor*/
	    $filename[opc] = 'onserver';
	    $filename[local] = $dirLocal.$filename[filename];
	    $filename[url] = $dirUrl.$filename[filename];
	    $xls = new Spreadsheet_Excel_Writer($filename[local]);            
	}else{
	    /*Para descarga*/
	    $filename[opc] = 'download';
	    $filename[url] = $archivo;
	    $xls = new Spreadsheet_Excel_Writer();
	    $xls->send($filename[url]);     #Envio de headers HTML
	}
	// Cracion de hoja
	$hoja1 = $xls->addWorksheet($hoja);
	// Formatos
	#Colores: setCustomColor(Indice, R,G,B)
	/*
	    Indices Predefinidos:
	          0 -> Error
	          1 -> Blanco
	          2 -> Rojo
	          3 -> Verde
	          4 -> Azul
	          5 -> Amarillo
	          6 -> Magenta
	          7 -> Cyan
	          8 -> Negro
	*/
	$xls->setCustomColor(9, 255, 255, 255);   #Blanco
	$xls->setCustomColor(10, 0, 0, 0);        #Negro      
	$xls->setCustomColor(11, 51, 51, 51);     #333333
	$xls->setCustomColor(12, 221, 221, 221);  #DDDDDD
	$xls->setCustomColor(13, 204, 204, 204);  #CCCCCC   
	$xls->setCustomColor(14, 45, 186, 113);   #2DBA71 : Verde PAE
	$xls->setCustomColor(15, 5, 110, 247);    #056EF7 : Azul
	/*
	$f0=$xls->addFormat(array(
					 'Size' 		=> 10 		# Tamaños de letra
	                      ,'Align' 		=> 'center'		# Alineacion
	                      ,'Color' 		=> 'green'		# Color de letra
	                      ,'Bold'		=> 1 		      # Negrita
	                      ,'Underline'	=> 1 			# Subrrayado
	                      ,'Pattern'  	=> 1       		# Patron de relleno
	                      ,'FgColor' 		=> 'magenta'	# Color de fondo
	                      ,'Border'		=> 1 			# Borde de celda
	                      ,'BorderColor' 	=> 'blue'		# Color de borde
	                      ,'TextRotation'   => ''			# Grados de Rotación de texto: -1, 0, 90, 180	
	                      ,'Locked' 		=> 1 			# Bloquear celda
	                	));
	*/
	#Titulos de tabla
	$fTitulos = $xls->addFormat();
	$fTitulos -> setFgColor(14);        # Fondo
	$fTitulos -> setBgColor('magenta'); # Color de Patron
	$fTitulos -> setPattern(1);         # Patron: 0-18 | 0 = no background
	$fTitulos -> setColor(9);          # Color de fuente
	$fTitulos -> setSize(11);           # Tamaño de fuente
	$fTitulos -> setAlign(vcenter);     # Alineacion V: top, vcenter, bottom, vjustify, vequal_space
	$fTitulos -> setAlign(center);      # Alineacion H: left, center, right, fill, justify, merge, equal_space
	$fTitulos -> setBold(1);            # Negrita: 1=bold, 400=normal, 700=Bold, 1000=extraBold
	$fTitulos -> setUnderline(false);    # Subrrayado
	$fTitulos -> setBorder(1);          # Borde de celda: 1 => thin, 2 => thick
	$fTitulos -> setBorderColor(9);    # Color de borde
	$fTitulos -> setTextRotation(0);    # Grados de Rotación de texto: -1, 0, 90, 180  
	// #Contenido
	$fTxtOdd = $xls->addFormat();
	$fTxtOdd -> setFgColor(9);        # Fondo
	$fTxtOdd -> setColor(10);          # Color de fuente
	$fTxtOdd -> setSize(10);           # Tamaño de fuente
	$fTxtOdd -> setAlign(vcenter);     # Alineacion V: top, vcenter, bottom, vjustify, vequal_space
	$fTxtOdd -> setAlign(center);      # Alineacion H: left, center, right, fill, justify, merge, equal_space
	$fTxtOdd -> setBorder(1);          # Borde de celda: 1 => thin, 2 => thick
	$fTxtOdd -> setBorderColor(13);    # Color de borde
	// --
	$fTxtEven = $xls->addFormat();
	$fTxtEven -> setFgColor(12);        # Fondo
	$fTxtEven -> setColor(10);          # Color de fuente
	$fTxtEven -> setSize(10);           # Tamaño de fuente
	$fTxtEven -> setAlign(vcenter);     # Alineacion V: top, vcenter, bottom, vjustify, vequal_space
	$fTxtEven -> setAlign(center);      # Alineacion H: left, center, right, fill, justify, merge, equal_space
	$fTxtEven -> setBorder(1);          # Borde de celda: 1 => thin, 2 => thick
	$fTxtEven -> setBorderColor(13);    # Color de borde

	// TODO: Funcion para auto-ajustar las columnas al ancho del contenido
	// function autofit_columns($obj) { 
	//     $col = 0;       
	//     // var_dump($obj->_worksheets);
	//     while ($width=$obj->col_sizes){   
	//       if($width){   
	//             $obj->set_column($col, $col, $width);
	//       }
	//       $col++;
	//     }
	// }
	// autofit_columns($xls);

	// Construccion de contenido 
	// write($x,$y,$valor,$formato) ==> $x=>Fila; $y=>Columna
	#Titulos
	if(!empty($tituloTabla)){
		$hoja1->write($x, $y, $tituloTabla, $fTitulos);
		$hoja1->mergeCells($x, $y, $x, count($colsTitulos)-1); # Combinar celdas      
		$x++;
	}
	foreach($colsTitulos as $tCol){
		$hoja1->write($x, $y, $tCol, $fTitulos);
		$y++;
	}
	$x++;       
	#Contenido
	foreach($datos as $registro){	
	    #Formatos            
	    $fRegistro = (++$r%2==1)?($fTxtOdd):($fTxtEven);
	    #Datos
	    $soloUno = (!is_array($registro))?true:false; #Deteccion de total de registros
		$data = (!$soloUno)?$registro:$datos; #Seleccion de arreglo
      	$y=0;
      	for($i=0; $i<count($data)/2; $i++){		
      		$hoja1->write($x, $y, utf8_decode($data[$i]), $fRegistro);
      		$y++;
      	}
      	$x++;
	    if($soloUno) break;
	}
	// Cerrar y crear archivo
	$resultado = $xls->close();
	/*Fin de XLS*/
	return $filename;
}

function dump_var($variable,$tipo=0){
	echo "<pre>";
	if(!$tipo){ print_r($variable); }else{var_dump($variable);}
	echo "</pre>";
	die();
}

function dia($fecha){
	$dias = array("domingo","lunes","martes","miércoles","jueves","viernes","sábado");
	$fecha = date_create_from_format('Y-m-d', $fecha);
	$fecha = date_format($fecha, 'w');	
	return $dias[$fecha];
}

/*O3M*/
?>
