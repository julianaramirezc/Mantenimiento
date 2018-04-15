<?php

// Posibilidades para deshabilitar el caché según varios tipos de navegadores. ¿**Deberían ir en el controlador**?
header('Content-Type: text/html; charset=UTF-8');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

date_default_timezone_set('America/Bogota');

// algunos de los datos del archivo conexion.json difieren entre el servidor y la maquina local
//$conexion = json_decode(file_get_contents("../../conexion.json"),  TRUE);
$conexion = json_decode(file_get_contents("../serviciosTecnicos/varios/conexion.json"),  TRUE);//local

// Constantes propias de la aplicación
define('COMSPEC', filter_input(INPUT_SERVER, 'COMSPEC'));
define('ROOT', filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'));
define('DOCUMENT_ROOT', substr(ROOT, -1) == '/' ? ROOT : ROOT . '/');
define('DIR_BASE', str_replace("\\", "/", dirname(__FILE__)) . '/');  // ruta de este script
define('PATH_TMP', DOCUMENT_ROOT . 'tmp/'); // <<--- OJO <<<<<<
define('RUTA_DESCARGA', '../serviciosTecnicos/varios/');

define('RUTA_APLICACION', DOCUMENT_ROOT . 'syscontrolp/');
define('PHPEXCEL_ROOT', '../../includes/PHPExcel/');
define('PHPWORD_ROOT', '../../includes/PHPWord/');

//-----------------

// Atributos de la conexión a la base de datos
define('BASE_DATOS', $conexion['base_datos']);
define('SERVIDOR', $conexion['servidor']);
define('PUERTO', $conexion['puerto']);
define('USUARIO', $conexion['usuario']);
define('CONTRASENA', $conexion['password']);
require_once '../../includes/swiftMailer/swift_required.php';

//define('CONTRASENA', '1-876-888');

ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', PATH_TMP . 'syscontrolp_log.txt');  // <-- OJO cambié el nombre del archivo log
error_reporting(E_ERROR);

spl_autoload_register('__autoload');
// Para PHP 6 E_STRICT es parte de E_ALL -- error_reporting(E_ALL | E_STRICT); para verificación exhaustivo --

//error_log(print_r($conexion, 1));   // <<--------  OJO (buscar el archivo en la carpeta temporal)

/**
 * Intenta cargar una clase siguiendo la siguiente convención:
 * Si el nombre de la clase comienza con Util, la clase será una clase de utilidades con 
 * métodos estáticos que se cargada desde la carpeta "Utilidades", en caso contrario se
 * cargará desde la carpeta "Modelo" y no definirá métodos estáticos
 * @param type $nombreClase El nombre de la clase a cargar
 */
function __autoload($nombreClase) {

    if (substr($nombreClase, 0, 7) == 'Reporte') {
        $nombreClase = "../serviciosTecnicos/reportes/$nombreClase.php";
    } else if (substr($nombreClase, 0, 4) == 'Util') {
        $nombreClase = "../serviciosTecnicos/utilidades/$nombreClase.php";
    } else if (substr($nombreClase, 0, 8) == 'PHPExcel') {
        $nombreClase = PHPEXCEL_ROOT . str_replace('_', '/', $nombreClase) . '.php';
    } else if (substr($nombreClase, 0, 7) == 'PHPWord') {
        $nombreClase = PHPWORD_ROOT . str_replace('_', '/', $nombreClase) . '.php';
    } else {
        $nombreClase = "../modelo/$nombreClase.php";
    }
    include_once($nombreClase);
}
