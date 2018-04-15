<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cliente
 * Implementa el CRUD para Cliente
 * @authors Juliana Ramirez Corrales, Luis Daniel Murillo Zuluaga, Mateo Castro González, Jose Fernando Giraldo Giraldo
 */
class OperacionProduccion {

    function add($param) {
//       error_log(print_r($param, TRUE)); //El metodo add, inserta los valores en la base de datos correspondientes ala entidad cliente.
     extract($param);
     $fk_tipo_operacion = substr($fk_tipo_operacion,0,2);

     $sqlHoraInicio = "select hora_fin+interval '1 minute' hora_inicio from operacion_produccion where fk_operario = '$fk_operario' and current_timestamp-hora_fin <= interval '8 hours'";
     if (($rs = $conexion->getPDO()->query($sqlHoraInicio))) {
        $fila = $rs->fetch(PDO::FETCH_ASSOC);
        $hora_inicio = $fila['hora_inicio'];
    }
    if($hora_inicio==""){
        $sqlHoraInicio="select to_char(current_timestamp, 'YYYY-MM-DD ') || hora_inicio hora_inicio from turno
        where extract(hour from current_timestamp)-extract(hour from hora_inicio) > 0 and extract(hour from current_timestamp)-extract(hour from hora_inicio) < 8";
    }
    if (($rs = $conexion->getPDO()->query($sqlHoraInicio))) {
        $fila = $rs->fetch(PDO::FETCH_ASSOC);
        $hora_inicio = $fila['hora_inicio'];
    }
       //$HORAACTUAL= "SELECT CURRENT_TIMESTAMP";
    $sqlINSERT ="INSERT INTO operacion_produccion(fk_tipo_operacion, hora_inicio, hora_fin, fk_maquina,orden_produccion, fk_operario)VALUES ('$fk_tipo_operacion',to_timestamp('$hora_inicio','YYYY-MM-DD HH24:MI:SS'),to_timestamp('$hora_inicio','YYYY-MM-DD HH24:MI:SS') + interval '$duracion minutes', '$maquina','$orden_produccion', '$fk_operario')";
    $conexion->getPDO()->exec($sqlINSERT);
    echo $conexion->getEstado();
}

function edit($param) {
        //El metodo edit, actualiza los valores en la base de datos correspondientes ala entidad cliente.
//        error_log(print_r($param, TRUE));
    extract($param);
    $fk_tipo_operacion = substr($fk_tipo_operacion,0,2);

        $sql = "UPDATE operacion_produccion SET id_operacion=$id, fk_tipo_operacion='$fk_tipo_operacion', hora_inicio='$hora_inicio', hora_fin='$hora_fin', fk_operario='$fk_operario',fk_maquina='$maquina', orden_produccion='$orden_produccion' WHERE id_operacion = $id;";  // <-- el ID de la fila asignado en el SELECT permite construir la condición de búsqueda del registro a modificar

        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function del($param) {
        //El metodo del, elimina los valores en la base de datos correspondientes ala entidad cliente.
        extract($param);
        $conexion->getPDO()->exec("DELETE FROM operacion_produccion WHERE id_operacion = $id");
        
        echo $conexion->getEstado();

//        error_log($sql);
    }

    /**
     * Procesa las filas que son enviadas a un objeto jqGrid
     * @param type $param un array asociativo con los datos que se reciben de la capa de presentación
     */
    function select($param) {
        //El metodo select, seleciona de forma ordenada todos los vaolres que tiene la base de datos en la entidad 
        //cliente
        extract($param);
//        error_log(print_r($param, TRUE));

        $where = $conexion->getWhere($param);
        $sql = " SELECT id_operacion, fk_tipo_operacion, to_char(hora_inicio,'YYYY-MM-DD HH24:MI') hora_inicio,to_char(hora_fin,'YYYY-MM-DD HH24:MI') hora_fin, fk_maquina,orden_produccion, hora_fin-hora_inicio duracion, fk_operario,tipo_operacion_produccion.descripcion_operacion actividad, operario.nombres, (fk_maquina||' - '||maquina.descripcion) hola FROM public.operacion_produccion JOIN tipo_operacion_produccion ON tipo_operacion_produccion.id_tipo_operacion = operacion_produccion.fk_tipo_operacion JOIN maquina ON maquina.id_maquina = operacion_produccion.fk_maquina JOIN operario ON operario.fk_usuario = operacion_produccion.fk_operario";


        // crear un objeto con los datos que se envían a jqGrid para mostrar la información de la tabla
        $respuesta = $conexion->getPaginacion($sql, $rows, $page, $sidx, $sord); // $rows = filas * página


        
        // puede examinar aquí con error_log(..) el contenido de las variables 
        // agregar al objeto que se envía las filas de la página requerida
        if (($rs = $conexion->getPDO()->query($sql))) {
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {

                $respuesta['rows'][] = [
                    'id' => $fila['id_operacion'], // <-- debe identificar de manera única una fila del grid, por eso se usa la PK
                    'cell' => [ // los campos que se muestra en las columnas del grid
                    $fila['orden_produccion'], 
                    $fila['nombres'],
                    $fila['hora_inicio'],
                    $fila['hora_fin'],
                    $fila['duracion'],
                    $fila['hola'],
                    $fila['actividad']
                    ]
                    ];
                }
            }
            echo json_encode($respuesta);
        }

        public function getSelect($param) {
        //El metodo getSelect, retorna dos parametros(fk_usuario, nombre_cliente) para crear un select de la entidad 
        //cliente y poder seleccionar el cliente que va ser foranea en otra tabla
            $json = FALSE;
            extract($param);
            $select = "";
            $select .= "<option value='0'>Seleccione un Cliente</option>";
            foreach ($conexion->getPDO()->query("SELECT id_operacion, fk_tipo_operacion FROM operacion_produccion ORDER BY id_operacion") as $fila) {
                $select .= "<option value='{$fila['id_operacion']}'>{$fila['fk_tipo_operacion']}</option>";
            }
            echo $json ? json_encode($select) : ("<select id='$id'>$select</select>");
        }

    }
