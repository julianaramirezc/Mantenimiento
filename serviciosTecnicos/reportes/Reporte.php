<?php

/**
 * Description of Reporte
 * Ejemplo de la manera de crear un libro utilizando PHPExcel
 * @author Carlos Cuesta
 */
class Reporte {

    private $objPHPExcel;
    private $conexion;       // <-- OJO

    /**
     * Crea un objeto PHPExcel y lo deja disponible para los demás metódos.
     * Asigna las propiedades principales de dicho objeto.
     */

    function __construct() {
        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->getProperties()->setCreator("Editorial La Patria")
                ->setLastModifiedBy("Editorial La Patria")
                ->setTitle('Reportes')
                ->setSubject("Uso exclusivo para personal de control de producción")
                ->setKeywords("Reporte, Impresos, Producción")
                ->setCategory("Control de Producción");
    }

    /**
     * Crea las hojas del libro a partir de un array con los nombres de las funciones
     * que deben ejecutarse (una por página). Seguidamente llama a la función encargada
     * de llenar los datos de cada hoja
     * @param array $param una colección de nombres de funciones que deben haber sido 
     * implementadas previamente y que se llaman para registrar los datos de cada hoja 
     * del libro.
     */
    public function generarReporte($param) {
        set_time_limit(1800);
        ini_set('memory_limit', '512M');
        extract($param);
        extract($args);

        print_r($args);
        // $conexion es un objeto de tipo UtilConexion, creado en la fachada e incluido 
        // en el array de argumentos que recibe cada método llamado desde la fachada.
        $this->conexion = $conexion;  // <-- OJO

        $crear = FALSE;
        foreach ($opciones as $metodo) {
            if ($crear) {
                $objWorksheet = $this->objPHPExcel->createSheet();
            } else {
                $objWorksheet = $this->objPHPExcel->getActiveSheet();
                $crear = TRUE;
            }

            if (method_exists($this, $metodo)) {
                if (isset($args[$metodo])) {  // si se requiere parametrización
                    $this->{$metodo}($objWorksheet, $args[$metodo]);
                } else {  // si no se requiere parametrización
                    $this->{$metodo}($objWorksheet);
                }
            } else {
                error_log("Error en Reporte->generarReporte(): el método $metodo no existe.");
            }
        }

        // guardar, recoger la basura generada y permitir descargar el archivo
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $archivo = 'ctrlactividades' . date("YmdHis") . '.xlsx';
        $objWriter->save(RUTA_DESCARGA . $archivo);
        $this->objPHPExcel->disconnectWorksheets();
        unset($objWriter, $this->objPHPExcel);
        //Utilidades::descargar(['archivo' => $archivo]);
        Utilidades::redirect(RUTA_DESCARGA . $archivo);

        $envio = [
            "origen" => [
                "smtp" => "smtp.gmail.com", // ← El servidor smtp
                "puerto" => 587, // o 465 si va a utilizar ssl
                "encriptado" => "tls", //o tls si usa el puerto 587
                "usuario" => "informesyscontrolp@gmail.com",
                "alias" => "Informe La Patria",
                "contrasena" => "JuMaJoMu"
            ],
            "destino" => [  // ojo: se supone que las direcciones de envío son correctas
                "joseefgg21@gmail.com" => "Jose Fernando"
            ],
            "contenido" => [
                "asunto" => "Prueba",
                "mensaje" => "
                        <html>
                            <body>
                                Buen día:
                                <br><br>
                                Adjuntamos los últimos reportes de producción generados.
                                <br>
                                Atentamente,
                                <br><br>
                                Sistema de Producción
                            </body>
                        </html>",
                "adjuntos" => [RUTA_DESCARGA . $archivo]  // [] sin adjuntos
            ]
        ];
        Utilidades::enviar($envio);
    }

    /**
     * Un simple ejemplo de la forma en que se pueden registrar datos en una hoja de cálculo
     * @param type $objWorksheet
     */
//    private function infoMensualTurnoMaquinas($objWorksheet, $args) {
//        $objWorksheet->setShowGridlines(true);
//        $objWorksheet->setTitle('Turnos');
//        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
//        $objDrawing = new PHPExcel_Worksheet_Drawing();
//        $objDrawing->setName('La_Patria');
//        $objDrawing->setDescription('La_Patria');
//        $ruta = RUTA_APLICACION . 'vista/imagenes/80.jpg';
//        $objDrawing->setPath($ruta);
//        $objDrawing->setCoordinates('B2');
//        $objDrawing->getShadow()->setVisible(true);
//        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());
//
//
//        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
//        $i = 6;
//        // titulo
//        $objWorksheet->mergeCells('B' . $i . ':' . 'F' . $i);
//        $objWorksheet->getStyle('B' . $i)->applyFromArray($this->estilo1());
//        $objWorksheet->getStyle('B' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//        $objWorksheet->getStyle('B' . $i)->getFont()->setSize(16);
//        $objWorksheet->setCellValue('B' . $i, "TURNOS DE MAQUINAS");
//
//        // ancho de las columnas
//        $objWorksheet->getColumnDimension('A')->setWidth(4);
//        $objWorksheet->getColumnDimension('B')->setWidth(8);
//        $objWorksheet->getColumnDimension('C')->setWidth(25);
//        $objWorksheet->getColumnDimension('D')->setWidth(40);
//        $objWorksheet->getColumnDimension('E')->setWidth(30);
//        $objWorksheet->getColumnDimension('F')->setWidth(30);
//
//        $i = $i + 2;
//        // datos de la cabecera
//        $objWorksheet->setCellValue("B$i", 'Orden')
//                ->setCellValue("C$i", 'Máquina')
//                ->setCellValue("D$i", 'Operario')
//                ->setCellValue("E$i", 'Inicio')
//                ->setCellValue("F$i", 'Fin');
//
//        $objWorksheet->getStyle("B$i:F$i")->applyFromArray($this->estilo1());
//
//        $maquina = $args[maquina];
//        $operario = $args[operario];
//        $fechaI = $args[fecha_inicio];
//        $fechaF = $args[fecha_fin];
//
//        //"hola papi"+ $fechaF + " -- " + $fechaI;
//
//        $where = "";
//        if ($maquina && $operario && $fechaI && $fechaF) {
//            $where = "$where AND maquina.id_maquina = '$maquina' AND id_usuario = '$operario' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaI%' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaF%';";
//        } else if ($fechaI) {
//            $where = "$where AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaI%';";
//        } else if ($maquina && $operario && $fechaI) {
//            $where = "$where AND maquina.id_maquina = '$maquina' AND id_usuario = '$operario' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaI%';";
//        } else if ($maquina && $operario && $fechaF) {
//            $where = "$where AND maquina.id_maquina = '$maquina' AND id_usuario = '$operario' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaF%';";
//        } else if ($maquina && $operario) {
//            $where = "$where AND maquina.id_maquina = '$maquina' AND id_usuario = '$operario';";
//        } else if ($maquina && $fechaI) {
//            $where = "$where AND maquina.id_maquina = '$maquina' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaI%';";
//        } else if ($maquina && $fechaF) {
//            $where = "$where AND maquina.id_maquina = '$maquina' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaF%';";
//        } else if ($operario && $fechaI) {
//            $where = "$where AND id_usuario = '$operario' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaI%';";
//        } else if ($operario && $fechaF) {
//            $where = "$where AND id_usuario = '$operario' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaF%';";
//        } else if ($fechaI && $fechaF) {
//            $where = "$where AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaI%' AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaF%';";
//        } else if ($maquina) {
//            $where = "$where AND maquina.id_maquina = '$maquina';";
//        } else if ($operario) {
//            $where = "$where AND id_usuario = '$operario';";
//        } else if ($fechaF) {
//            $where = "$where AND to_char(turno_produccion.hora_inicio, 'YYYY-MM-DD') like '%$fechaF%';";
//        }
//
//        $sql = "SELECT turno_produccion.hora_inicio, turno_produccion.hora_fin, 
//                    operario_select.nombres || ' ' || operario_select.apellidos AS el_operario, 
//                    fk_orden_produccion, maquina.descripcion, maquina.color
//                FROM turno_produccion 
//                   JOIN maquina ON maquina.id_maquina = turno_produccion.fk_maquina 
//                   JOIN operario_select ON operario_select.id_usuario = turno_produccion.fk_operario $where";
//
//        if (($rs = $this->conexion->getPDO()->query($sql))) {    // <-- OJO
//            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
//                $i++;
//                $inicio = (new DateTime($fila['hora_inicio']))->format('Y-m-d H:i');
//                $fin = (new DateTime($fila['hora_fin']))->format('Y-m-d H:i');
//
//                $objWorksheet->setCellValueByColumnAndRow(1, $i, $fila['fk_orden_produccion'])
//                        ->setCellValueByColumnAndRow(2, $i, $fila['descripcion'])
//                        ->setCellValueByColumnAndRow(3, $i, $fila['el_operario'])
//                        ->setCellValueByColumnAndRow(4, $i, $inicio)
//                        ->setCellValueByColumnAndRow(5, $i, $fin);
//                // vea también: $objWorksheet->getCellByColumnAndRow(j, i)->getValue()|getCalculatedValue();
//            }
//            $objWorksheet->getStyle("B8:B$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//            $objWorksheet->getStyle("B8:F$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//            $objWorksheet->getStyle("B8:F$i")->applyFromArray($this->estilo2());
//        }
//    }

    private function infoMensualOperarios($objWorksheet, $args) {
        $objWorksheet->setShowGridlines(true);
        $objWorksheet->setTitle('Operarios');


        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('La_Patria');
        $objDrawing->setDescription('La_Patria');
        $ruta = RUTA_APLICACION . '/vista/imagenes/80.jpg';
        $objDrawing->setPath($ruta);
        $objDrawing->setCoordinates('B2');
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());


        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $i = 6;
        // titulo
        $objWorksheet->mergeCells('B' . $i . ':' . 'F' . $i);
        $objWorksheet->getStyle('B' . $i)->applyFromArray($this->estilo1());
        $objWorksheet->getStyle('B' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorksheet->getStyle('B' . $i)->getFont()->setSize(16);
        $objWorksheet->setCellValue('B' . $i, "OPERARIOS LA PATRIA");

        // ancho de las columnas
        $objWorksheet->getColumnDimension('A')->setWidth(4);
        $objWorksheet->getColumnDimension('B')->setWidth(12);
        $objWorksheet->getColumnDimension('C')->setWidth(25);
        $objWorksheet->getColumnDimension('D')->setWidth(25);
        $objWorksheet->getColumnDimension('E')->setWidth(25);
        $objWorksheet->getColumnDimension('F')->setWidth(25);

        $i = $i + 2;
        // datos de la cabecera
        $objWorksheet->setCellValue("B$i", 'Id')
                ->setCellValue("C$i", 'Nombre')
                ->setCellValue("D$i", 'Apellido')
                ->setCellValue("E$i", 'Telefono')
                ->setCellValue("F$i", 'Correo');

        $objWorksheet->getStyle("B$i:F$i")->applyFromArray($this->estilo1());

        $operario = $args[operario];
        $where = "";

        if ($operario) {
            $where = "$where AND usuario.id_usuario = '$operario'";
        }

        $sql = "SELECT usuario.id_usuario, nombres, apellidos, usuario.direccion, usuario.telefonos, usuario.correos, usuario.contrasena
                FROM operario INNER JOIN usuario ON operario.fk_usuario = usuario.id_usuario $where";

        if (($rs = $this->conexion->getPDO()->query($sql))) {    // <-- OJO
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $objWorksheet->setCellValueByColumnAndRow(1, $i, $fila['id_usuario'])
                        ->setCellValueByColumnAndRow(2, $i, $fila['nombres'])
                        ->setCellValueByColumnAndRow(3, $i, $fila['apellidos'])
                        ->setCellValueByColumnAndRow(4, $i, $fila['telefonos'])
                        ->setCellValueByColumnAndRow(5, $i, $fila['correos']);
                // vea también: $objWorksheet->getCellByColumnAndRow(j, i)->getValue()|getCalculatedValue();
            }

            $objWorksheet->getStyle("B8:B$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("B8:F$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("B8:F$i")->applyFromArray($this->estilo2());
        }
    }

    private function infoMensualSupervisores($objWorksheet, $args) {
        $objWorksheet->setShowGridlines(true);
        $objWorksheet->setTitle('Supervisores');

        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('La_Patria');
        $objDrawing->setDescription('La_Patria');
        $ruta = RUTA_APLICACION . 'vista/imagenes/80.jpg';
        $objDrawing->setPath($ruta);
        $objDrawing->setCoordinates('B2');
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());


        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $i = 6;
        // titulo
        $objWorksheet->mergeCells('B' . $i . ':' . 'F' . $i);
        $objWorksheet->getStyle('B' . $i)->applyFromArray($this->estilo1());
        $objWorksheet->getStyle('B' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorksheet->getStyle('B' . $i)->getFont()->setSize(16);
        $objWorksheet->setCellValue('B' . $i, "INFORME SUPERVISORES LA PATRIA");

        // ancho de las columnas
        $objWorksheet->getColumnDimension('A')->setWidth(4);
        $objWorksheet->getColumnDimension('B')->setWidth(12);
        $objWorksheet->getColumnDimension('C')->setWidth(25);
        $objWorksheet->getColumnDimension('D')->setWidth(25);
        $objWorksheet->getColumnDimension('E')->setWidth(25);
        $objWorksheet->getColumnDimension('F')->setWidth(25);

        $i = $i + 2;
        // datos de la cabecera
        $objWorksheet->setCellValue("B$i", 'Id')
                ->setCellValue("C$i", 'Nombre')
                ->setCellValue("D$i", 'Apellido')
                ->setCellValue("E$i", 'Telefono')
                ->setCellValue("F$i", 'Correo');

        $objWorksheet->getStyle("B$i:F$i")->applyFromArray($this->estilo1());

        $supervisor = $args[supervisor];
        $where = "";
        if ($supervisor) {
            $where = "$where AND usuario.id_usuario = '$supervisor'";
        }

        $sql = "SELECT usuario.id_usuario, nombres, apellidos, usuario.direccion, usuario.telefonos, usuario.correos, usuario.contrasena
                FROM supervisor INNER JOIN usuario ON supervisor.fk_usuario = usuario.id_usuario $where";

        if (($rs = $this->conexion->getPDO()->query($sql))) {    // <-- OJO
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $objWorksheet->setCellValueByColumnAndRow(1, $i, $fila['id_usuario'])
                        ->setCellValueByColumnAndRow(2, $i, $fila['nombres'])
                        ->setCellValueByColumnAndRow(3, $i, $fila['apellidos'])
                        ->setCellValueByColumnAndRow(4, $i, $fila['telefonos'])
                        ->setCellValueByColumnAndRow(5, $i, $fila['correos']);
                // vea también: $objWorksheet->getCellByColumnAndRow(j, i)->getValue()|getCalculatedValue();
            }
            $objWorksheet->getStyle("B8:B$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("B8:F$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("B8:F$i")->applyFromArray($this->estilo2());
        }
    }

    private function infoOrden($objWorksheet, $args) {
        $objWorksheet->setShowGridlines(true);
        $objWorksheet->setTitle('Ordenes');
        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('La_Patria');
        $objDrawing->setDescription('La_Patria');
        $ruta = RUTA_APLICACION . 'vista/imagenes/80.jpg';
        $objDrawing->setPath($ruta);
        $objDrawing->setCoordinates('B2');
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());



        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $i = 6;        // titulo
        $objWorksheet->mergeCells('C' . $i . ':' . 'D' . $i);
        $objWorksheet->getStyle('C' . $i)->applyFromArray($this->estilo1());
        $objWorksheet->getStyle('C' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorksheet->getStyle('C' . $i)->getFont()->setSize(12);
        $objWorksheet->setCellValue('C' . $i, "Orden");

        // ancho de las columnas

        $i = $i + 2;
        // datos de la cabecera
        $objWorksheet->setCellValue("C$i", 'Orden')
                ->setCellValue("D$i", 'Maquina')
                ->setCellValue("E$i", 'Operario')
                ->setCellValue("F$i", 'Tiempo');

        $objWorksheet->getStyle("C$i:F$i")->applyFromArray($this->estilo1());
        $orden = $args[orden];

        $sql = "SELECT distinct fk_maquina, maquina.descripcion ,operario.nombres FROM operacion_produccion JOIN operario ON operacion_produccion.fk_operario = operario.fk_usuario JOIN maquina ON operacion_produccion.fk_maquina = maquina.id_maquina WHERE orden_produccion = '$orden'";

        if (($rs = $this->conexion->getPDO()->query($sql))) {    // <-- OJO
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $i++;

                $objWorksheet->setCellValueByColumnAndRow(2, $i, $orden)
                        ->setCellValueByColumnAndRow(3, $i, $fila['descripcion'])
                        ->setCellValueByColumnAndRow(4, $i, $fila['nombres']);
            }
        }

        $sql2 = "select sum(hora_fin - hora_inicio)suma_tiempo from operacion_produccion WHERE orden_produccion = '$orden'";

        if (($rs = $this->conexion->getPDO()->query($sql2))) {    // <-- OJO
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $objWorksheet->setCellValueByColumnAndRow(5, $i, $fila['suma_tiempo']);
                // vea también: $objWorksheet->getCellByColumnAndRow(j, i)->getValue()|getCalculatedValue();
            }
            $objWorksheet->getStyle("C8:C$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("C8:F$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("C8:F$i")->applyFromArray($this->estilo2());
        }
    }

    private function infoMensualJefes($objWorksheet, $args) {
        $objWorksheet->setShowGridlines(true);
        $objWorksheet->setTitle('Jefes de Mantenimiento');
        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('La_Patria');
        $objDrawing->setDescription('La_Patria');
        $ruta = RUTA_APLICACION . 'vista/imagenes/80.jpg';
        $objDrawing->setPath($ruta);
        $objDrawing->setCoordinates('B2');
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());


        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $i = 6;        // titulo
        $objWorksheet->mergeCells('B' . $i . ':' . 'F' . $i);
        $objWorksheet->getStyle('B' . $i)->applyFromArray($this->estilo1());
        $objWorksheet->getStyle('B' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorksheet->getStyle('B' . $i)->getFont()->setSize(16);
        $objWorksheet->setCellValue('B' . $i, "JEFES DE MANTENIMIENTO LA PATRIA");

        // ancho de las columnas
        $objWorksheet->getColumnDimension('A')->setWidth(4);
        $objWorksheet->getColumnDimension('B')->setWidth(12);
        $objWorksheet->getColumnDimension('C')->setWidth(25);
        $objWorksheet->getColumnDimension('D')->setWidth(25);
        $objWorksheet->getColumnDimension('E')->setWidth(25);
        $objWorksheet->getColumnDimension('F')->setWidth(25);

        $i = $i + 2;
        // datos de la cabecera
        $objWorksheet->setCellValue("B$i", 'Id')
                ->setCellValue("C$i", 'Nombre')
                ->setCellValue("D$i", 'Apellido')
                ->setCellValue("E$i", 'Telefono')
                ->setCellValue("F$i", 'Correo');

        $objWorksheet->getStyle("B$i:F$i")->applyFromArray($this->estilo1());

        $jefe = $args[jefe];
        $where = "";
        if ($jefe) {
            $where = "$where AND usuario.id_usuario = '$jefe'";
        }

        $sql = "SELECT usuario.id_usuario, nombres, apellidos, usuario.direccion, usuario.telefonos, usuario.correos, usuario.contrasena
                FROM jefe_mantenimiento INNER JOIN usuario ON jefe_mantenimiento.fk_usuario = usuario.id_usuario $where";

        if (($rs = $this->conexion->getPDO()->query($sql))) {    // <-- OJO
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $inicio = (new DateTime($fila['hora_inicio']))->format('Y-m-d H:i');
                $fin = (new DateTime($fila['hora_fin']))->format('Y-m-d H:i');

                $objWorksheet->setCellValueByColumnAndRow(1, $i, $fila['id_usuario'])
                        ->setCellValueByColumnAndRow(2, $i, $fila['nombres'])
                        ->setCellValueByColumnAndRow(3, $i, $fila['apellidos'])
                        ->setCellValueByColumnAndRow(4, $i, $fila['telefonos'])
                        ->setCellValueByColumnAndRow(5, $i, $fila['correos']);
                // vea también: $objWorksheet->getCellByColumnAndRow(j, i)->getValue()|getCalculatedValue();
            }
            $objWorksheet->getStyle("B8:B$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("B8:F$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("B8:F$i")->applyFromArray($this->estilo2());
        }
    }

    private function infoDiarioOperario($objWorksheet, $args) {
        $objWorksheet->setShowGridlines(true);
        $objWorksheet->setTitle('Informe diario operarios');
        $dateTimeNow = time();
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('La_Patria');
        $objDrawing->setDescription('La_Patria');
        $ruta = RUTA_APLICACION . '/vista/imagenes/80.jpg';
        $objDrawing->setPath($ruta);
        $objDrawing->setCoordinates('B2');
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());

        $objWorksheet->mergeCells('D3:H3');
        $objWorksheet->setCellValue('D3', "INFORME DIARIO POR OPERARIO");
        $objWorksheet->getStyle('D3')->applyFromArray($this->estilo1());
        $objWorksheet->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $i = 6;

        $array_operarios;

        $fechaI = $args[fechaI];
        $fechaF = $args[fechaF];



        $objWorksheet->getColumnDimension('B')->setWidth(10);
        $objWorksheet->getColumnDimension('E')->setWidth(12);
        $where = "";
        if ($fechaI && $fechaF) {
            $where = "WHERE (hora_inicio, hora_fin) OVERLAPS ('$fechaI 00:01:00', '$fechaF 23:59:59')";
        }
        $sql = "SELECT count(distinct fk_operario)numero_operarios FROM operacion_produccion $where";
        error_log("1" . $sql);
        if (($rs = $this->conexion->getPDO()->query($sql))) {    // <-- OJO
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $numero_operarios = $fila['numero_operarios'];
            }
        }
        $array_operarios[$numero_operarios];
        $array_operarios_cedula[$numero_operarios];
        $where2 = "";
        if ($fechaI && $fechaF) {
            $where2 = "WHERE (hora_inicio, hora_fin) OVERLAPS ('$fechaI 00:01:00', '$fechaF 23:59:59') and fk_usuario in (SELECT distinct fk_operario FROM operacion_produccion WHERE (hora_inicio, hora_fin) OVERLAPS ('$fechaI 00:01:00', '$fechaF 23:59:59'));";
        }
        $sql2 = "SELECT  distinct operario.fk_usuario, operario.nombres FROM operacion_produccion, operario $where2 ";
        if (($rs = $this->conexion->getPDO()->query($sql2))) {    // <-- OJO
            $x = 0;
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $array_operarios[$x] = $fila['nombres'];
                $array_operarios_cedula[$x] = $fila['fk_usuario'];
                $x++;
            }
        }
        for ($j = 0; $j < $numero_operarios; $j++) {
            $o = $i;
            $objWorksheet->mergeCells('C' . $i . ':' . 'E' . $i);
            $objWorksheet->getStyle('B' . $i)->applyFromArray($this->estilo5());
            $objWorksheet->setCellValue('B' . $i, "OPERARIO");
            $objWorksheet->getStyle('C' . $i)->applyFromArray($this->estilo3());
            $objWorksheet->setCellValue('C' . $i, $array_operarios[$j]);
            $objWorksheet->getStyle('C' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $i++;

            $objWorksheet->mergeCells('B' . $i . ':' . 'D' . $i);
            $objWorksheet->getStyle('B' . $i)->applyFromArray($this->estilo4());
            $objWorksheet->setCellValue('B' . $i, "OPERACIÓN");
            $objWorksheet->getStyle('B' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle('E' . $i)->applyFromArray($this->estilo4());
            $objWorksheet->setCellValue('E' . $i, "DURACIÓN");
            $objWorksheet->getStyle('E' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $i++;

            $suma = $i;
//            $objWorksheet->setCellValue('E16', '=SUM(E14:E15)');
            $where3 = "";
        if ($fechaI && $fechaF) {
            $where3 = "AND (hora_inicio, hora_fin) OVERLAPS ('$fechaI 00:01:00', '$fechaF 23:59:59') ";
        }
            $sql3 = "SELECT operacion_produccion.hora_fin- operacion_produccion.hora_inicio duracion, tipo_operacion_produccion.descripcion_operacion FROM operacion_produccion JOIN tipo_operacion_produccion ON operacion_produccion.fk_tipo_operacion = tipo_operacion_produccion.id_tipo_operacion WHERE operacion_produccion.fk_operario = '$array_operarios_cedula[$j]' $where3 ";

            if (($rs = $this->conexion->getPDO()->query($sql3))) {    // <-- OJO
                while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {

                    $objWorksheet->mergeCells('B' . $i . ':' . 'D' . $i);
                    $objWorksheet->setCellValue('B' . $i, $fila['descripcion_operacion']);
                    $objWorksheet->setCellValue('E' . $i, $fila['duracion']);
                    $i++;
                }
            }
            $objWorksheet->mergeCells('B' . $i . ':' . 'D' . $i);
            $objWorksheet->setCellValue('B' . $i, "DURACIÓN TOTAL");
            //Volver una celda de tipo hora
            $objWorksheet->setCellValue('E' . $i, 'Date/Time')->setCellValue('E' . $i, 'Time')->setCellValue('E' . $i, PHPExcel_Shared_Date::PHPToExcel($dateTimeNow));
            $objWorksheet->getStyle('E' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4);
            $where4 = "";
            if ($fechaI && $fechaF) {
            $where4 = "AND (hora_inicio, hora_fin) OVERLAPS ('$fechaI 00:01:00', '$fechaF 23:59:59')";
            }
            
            $sql4 = "SELECT sum(operacion_produccion.hora_fin- operacion_produccion.hora_inicio) suma FROM operacion_produccion JOIN tipo_operacion_produccion ON operacion_produccion.fk_tipo_operacion = tipo_operacion_produccion.id_tipo_operacion WHERE operacion_produccion.fk_operario = '$array_operarios_cedula[$j]' $where4";
            error_log(print_r($sql4, TRUE));
            if (($rs = $this->conexion->getPDO()->query($sql4))) {    // <-- OJO
                while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                    $objWorksheet->setCellValue('E' . $i, $fila['suma']);
                }
            }
            $objWorksheet->getStyle("B$o:E$i")->applyFromArray($this->estilo2());
            $i = $i + 3;
        }
    }

    private function operProduccionUtilizadas($objWorksheet, $args) {
        $objWorksheet->setShowGridlines(true);
        $objWorksheet->setTitle('Operaciones de Producción');
        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('La_Patria');
        $objDrawing->setDescription('La_Patria');
        $ruta = RUTA_APLICACION . 'vista/imagenes/80.jpg';
        $objDrawing->setPath($ruta);
        $objDrawing->setCoordinates('B2');
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());
        $objWorksheet->getColumnDimension('H')->setWidth(16);


        // Use posiciones relativas a una fila o columna inicial para que en caso de cambios no tenga que revisar todo el código
        $i = 4;        // titulo
        $objWorksheet->mergeCells('D' . $i . ':' . 'H' . $i);
        $objWorksheet->getStyle('D' . $i)->applyFromArray($this->estilo1());
        $objWorksheet->getStyle('D' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objWorksheet->getStyle('D' . $i)->getFont()->setSize(13);
        $objWorksheet->setCellValue('D' . $i, "OPERACIONES DE PRODUCCIÓN");

        $i = $i + 2;
        // datos de la cabecera
        $objWorksheet->setCellValue("D$i", 'Cantidad');
        $objWorksheet->mergeCells('E' . $i . ':' . 'G' . $i);
        $objWorksheet->setCellValue("E$i", 'Descripción');
        $objWorksheet->setCellValue("H$i", 'Tiempo total');

        $objWorksheet->getStyle("D$i:H$i")->applyFromArray($this->estilo5());
        $fechaI = $args[fechaI];
        $fechaF = $args[fechaF];

        $where = "";
        if ($fechaI && $fechaF) {
            $where = "WHERE (hora_inicio, hora_fin) OVERLAPS ('$fechaI 00:01:00', '$fechaF 23:59:59')";
        }

        $sql = "SELECT count(fk_tipo_operacion) veces, descripcion_operacion, sum(hora_fin-hora_inicio) tiempo from operacion_produccion JOIN tipo_operacion_produccion ON tipo_operacion_produccion.id_tipo_operacion = operacion_produccion.fk_tipo_operacion $where group by descripcion_operacion order by tiempo desc";

        if (($rs = $this->conexion->getPDO()->query($sql))) {    // <-- OJO
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $objWorksheet->mergeCells('E' . $i . ':' . 'G' . $i);

                $objWorksheet->setCellValueByColumnAndRow(3, $i, $fila['veces'])
                        ->setCellValueByColumnAndRow(4, $i, $fila['descripcion_operacion'])
                        ->setCellValueByColumnAndRow(7, $i, $fila['tiempo']);

                // vea también: $objWorksheet->getCellByColumnAndRow(j, i)->getValue()|getCalculatedValue();
            }
            $objWorksheet->getStyle("D6:D$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("D6:H$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objWorksheet->getStyle("D6:H$i")->applyFromArray($this->estilo2());
        }
    }

    /**
     * Resalta en fondo gris las celdas
     * @return array La definición de los estilos que se aplican
     */
    protected function estilo1() {
        return [
            'font' => [ 'bold' => true],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['argb' => '0080ff']
            ]
        ];
    }

    /**
     * Aplica bordes a las celdas
     * @return array La definición de los estilos que se aplican
     */
    protected function estilo2() {
        return [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
    }

    protected function estilo3() {
        return [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['argb' => 'cccccc']
            ]
        ];
    }

    protected function estilo4() {
        return [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['argb' => '9999ff']
            ]
        ];
    }

    protected function estilo5() {
        return [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['argb' => 'ff8080']
            ]
        ];
    }

    // como estilo1 y estilo2 defina TODOS los estilos que aplique repetitivamente
}
