<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cliente
 *
 * @authors Juliana Ramirez Corrales, Luis Daniel Murillo Zuluaga, Mateo Castro González, Jose Fernando Giraldo Giraldo
 */
class Maquina {

    function add($param) {
        //El metodo add, inserta los valores en la base de datos correspondientes ala entidad maquina.
        // error_log(print_r($param, TRUE));  // cómo ver el contenido de una estructura de datos
        extract($param);
        // nunca suponga el orden de las columnas: INSERT INTO tabla VALUES (v1, v2,v3, ...); tómese el trabajo de indicar los nombres de columnas
        // los nombres de los elementos del array asociativo corresponden a los atributos name de las columnas del jqGrid
        $sql = "INSERT INTO maquina(id_maquina, descripcion, tipo_maquina, color) VALUES ('$id_maquina', '$descripcion','$tipo_maquina','$color');";
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function edit($param) {
                //El metodo edit, actualiza los valores en la base de datos correspondientes ala entidad maquina.
        extract($param);
        $sql = "UPDATE maquina SET id_maquina='$id_maquina' ,descripcion='$descripcion', tipo_maquina='$tipo_maquina', color='$color' WHERE id_maquina='$id';";  // <-- el ID de la fila asignado en el SELECT permite construir la condición de búsqueda del registro a modificar
        $conexion->getPDO()->exec($sql);        echo $conexion->getEstado();
    }

    function del($param) {
        //El metodo del, elimina los valores en la base de datos correspondientes ala entidad maquina.
        extract($param);
        $conexion->getPDO()->exec("DELETE FROM maquina WHERE id_maquina = '$id'");
        echo $conexion->getEstado();
    }

    /**
     * Procesa las filas que son enviadas a un objeto jqGrid
     * @param type $param un array asociativo con los datos que se reciben de la capa de presentación
     */
    function select($param) {
        //El metodo select, seleciona de forma ordenada todos los vaolres que tiene la base de datos en la entidad 
        //maquina
        extract($param);
        $where = $conexion->getWhere($param);
        // conserve siempre esta sintaxis para enviar filas al grid:
        $sql = "SELECT id_maquina, descripcion, tipo_maquina, color  FROM maquina $where";
        // crear un objeto con los datos que se envían a jqGrid para mostrar la información de la tabla
        $respuesta = $conexion->getPaginacion($sql, $rows, $page, $sidx, $sord); // $rows = filas * página
        
        // puede examinar aquí con error_log(..) el contenido de las variables 
        // agregar al objeto que se envía las filas de la página requerida
        if (($rs = $conexion->getPDO()->query($sql))) {
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                $tipo = UtilConexion::$categoriaTipoMaquina[$fila["tipo_maquina"]];
                
                
                $respuesta['rows'][] = [
                    'id' => $fila['id_maquina'], // <-- debe identificar de manera única una fila del grid, por eso se usa la PK
                    'cell' => [ // los campos que se muestra en las columnas del grid
                        $fila['id_maquina'],
                        $fila['descripcion'],
                        $tipo,
                        $fila['color']
                    ]
                ];
            }
        }
        echo json_encode($respuesta);
    }
    // completar con funcionalidad de cliente

    public function getSelect($param) {
        $json = FALSE;
        extract($param);
        $select = "";
        $select .= "<option value='0'>Seleccione una máquina</option>";
        foreach ($conexion->getPDO()->query("SELECT id_maquina, descripcion, color FROM maquina ORDER BY descripcion") as $fila) {
            $descripcion =  $fila['tipo_maquina'];
            $select .= "<option value='{$fila['id_maquina']}' color='{$fila['color']}'>{$fila['id_maquina']} - {$fila['descripcion']}</option>";
        }
        echo $json ? json_encode($select) : ("<select id='$id'>$select</select>");
    }

}
