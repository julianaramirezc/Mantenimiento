<?php

/**
 * Librería de funciones varias que requiere la aplicación
 * @author Carlos Cuesta
 */
class Utilidades {
    // Utilidades para el manejo de fechas

    /**
     * Analiza una cadena que representa una fecha y la convierte en un objeto DateTime
     * opcionalmente con zona horaria
     * @param type $string La Cadena que representa la fecha
     * @param type $timezone Opcionalmente la zona horaria
     * @return \DateTime
     */
    public static function stringComoDateTime($string, $timezone = null) {
        $date = new DateTime($string, $timezone ? $timezone : new DateTimeZone('UTC'));  // UTC = Universal Time Coordinate
        if ($timezone) {
            // Forzar la zona horaria si fue ignorada
            $date->setTimezone($timezone);
        }
        return $date;
    }

    /**
     * Toma los valores año/mes/día del DateTime dado y los convierte a un nuevo DateTime, pero como UTC
     * @param type $datetime
     * @return \DateTime
     */
    public static function fechaUTC($datetime) {
        return new DateTime($datetime->format('Y-m-d'));
    }

    /**
     * Permite descargar un archivo
     * @param type $param un array asociativo con el elemento 'archivo' que contiene el nombre del archivo a descargar
     * @throws Exception Se lanza un error si el archivo no se encuentra disponible.
     */
    public static function descargar($param) {
        extract($param);
        $totalArchivos=$param;
        
        try {
            if ($totalArchivos) {
                $totalArchivos = count($archivos);
                if ($totalArchivos == 1) {
                    $nombreArchivo = $archivos[0];
                } else if ($totalArchivos > 1) {  // Crear un ZIP para enviar todos los archivos
                    $nombreArchivoZIP = $descarga['carpeta'] . $descarga['nombreZIP'] . '.zip';
                    $zip = new ZipArchive;
                    if ($zip->open($nombreArchivoZIP, ZIPARCHIVE::OVERWRITE) === TRUE) {
                        foreach ($archivos as $archivo) {
                            if (file_exists($archivo)) {
                                $zip->addFile($archivo, $descarga['nombreZIP'] . '/' . 
                                                       pathinfo($archivo, PATHINFO_BASENAME));
                            } else {
                                error_log("Intentando agregar <<" . pathinfo($archivo,
                       PATHINFO_BASENAME) . 
                       ">> a " . $descarga['nombreZIP'] . " pero no existe.", 0);
                            }
                        }
                        $zip->close();
                        $nombreArchivo = $nombreArchivoZIP;
                    } else {
                        throw new Exception('Falló la creación de ' .
                                                           $descarga['nombreZIP'] . '.zip');
                    }
                } else {
                    throw new Exception("No se encontraron archivos");
                }
            } else {
                throw new Exception("No se encontró....");
            }
            if (!is_file($nombreArchivo)) {
                $nombreArchivo = pathinfo($nombreArchivo,
                                               PATHINFO_BASENAME);
                throw new Exception("El archivo $nombreArchivo no ….");
            } else {
                $rutaArchivo = $nombreArchivo;
                $nombreArchivo = pathinfo($nombreArchivo,
                                               PATHINFO_BASENAME);
                // Se inicia la descarga
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, 
                              pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Disposition: attachment;
                         filename=\"$nombreArchivo\"\n");  // Oculta la ruta de descarga
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: " . filesize($rutaArchivo));
                @readfile($rutaArchivo);
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => 0, 'mensaje' => $e->getMessage()]);         }
    }

    public static function send_download($file) {
        $basename = basename($file);
        $length = sprintf("%u", filesize($file));
        header('Set-Cookie: fileDownload=true; path=/');
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $basename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Content-Length:' . $length);
        header("Content-Range: 0-" . ($length - 1) . "/" . $length);
        set_time_limit(0);
        readfile($file);
    }

    public static function redirect($file) {
        header('Set-Cookie: fileDownload=true; path=/');
        header('Location:' . $file);
    }
    
    public static function enviar($envio) {
        set_time_limit(1800);
        header('Set-Cookie: fileDownload=true; path=/'); 
        extract($envio);

        try {
            $transport = Swift_SmtpTransport::newInstance(
                                                 $origen["smtp"], 
                                                 $origen["puerto"], 
                                                 $origen["encriptado"]);
            $transport->setUsername(
                    $origen["usuario"])->setPassword($origen["contrasena"]
             )->start();

            if ($transport->isStarted()) {
                $mailer = Swift_Mailer::newInstance($transport);
                $message = Swift_Message::newInstance();
                $message->setSubject($contenido['asunto']);
                $message->setFrom([$origen['usuario'] => $origen['alias']]);

                $message->setBody($contenido['mensaje'], 'text/html');
                $message->setTo($destino);

                foreach ($contenido['adjuntos'] as $adjunto) {
                    $message->attach(Swift_Attachment::fromPath($adjunto));
                }

                if ($mailer->send($message)) {
                    return TRUE;
                } else {
                    error_log('problemas enviando: ');
                    error_log(print_r($envio, 1));
                    return FALSE;
                }
            } else {
                error_log('problemas intentando enviar: ');
                error_log(print_r($envio, 1));
                return FALSE;
            }
        } catch (Exception $e) {
            $mensaje = utf8_encode($e->getMessage());
            error_log($mensaje);
            return FALSE;
        }
    }

    public static function subirArchivo() {   //
        
        $archivoDestino = isset($_REQUEST["name"]) ? $_REQUEST["name"] : ('basura_' . substr(md5(rand()), 0, 10) . '.tmp');
        $archivoTemporal = $_FILES['file']['tmp_name'];

        error_log("$archivoDestino - $archivoTemporal");

        if (is_file($archivoTemporal)) {
            if (is_dir(PATH_TMP)) {
                if (!move_uploaded_file($archivoTemporal, PATH_TMP . $archivoDestino)) {
                    echo('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Falló la carga de <b>' .
                    $archivoDestino . '</b>."}, "id" : "id"}');
                }
            } else {
                echo('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "No se encontró la carpeta 
                          destino"}, "id" : "id"}');
            }
        } else {
            echo('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "No se pudo abrir el archivo 
                      <b>' . $archivoDestino . '</b>."}, "id" : "id"}');
        }
        
        return $archivoDestino;
    }

    

}
