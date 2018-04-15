<?php

/**
 * Description of TipoOperacion
 * Implementa el CRUD para Tipo Operacion
 * @authors Juliana Ramirez Corrales, Luis Daniel Murillo Zuluaga, Mateo Castro González, Jose Fernando Giraldo Giraldo
 */
class TipoOperacion {

    function add($param) {
        //El metodo add, inserta los valores en la base de datos correspondientes ala entidad tipo_operacion.
        // error_log(print_r($param, TRUE));  // cómo ver el contenido de una estructura de datos
        extract($param);
        // nunca suponga el orden de las columnas: INSERT INTO tabla VALUES (v1, v2,v3, ...); tómese el trabajo de indicar los nombres de columnas
        // los nombres de los elementos del array asociativo corresponden a los atributos name de las columnas del jqGrid
        $sql = "INSERT INTO tipo_operacion_produccion(id_tipo_operacion, descripcion_operacion, categoria)VALUES ('$id_tipo_operacion','$descripcion_operacion', $categoria)";
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function edit($param) {
        //El metodo edit, actualiza los valores en la base de datos correspondientes ala entidad tipo_operacion.
        extract($param);
        $sql = "UPDATE tipo_operacion_produccion SET id_tipo_operacion='$id_tipo_operacion',descripcion_operacion='$descripcion_operacion', categoria=$categoria WHERE tipo_operacion_produccion.id_tipo_operacion = '$id'";  // <-- el ID de la fila asignado en el SELECT permite construir la condición de búsqueda del registro a modificar
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function del($param) {
        //El metodo del, elimina los valores en la base de datos correspondientes ala entidad tipo_operacion.
        extract($param);
        $conexion->getPDO()->exec("DELETE FROM tipo_operacion_produccion WHERE id_tipo_operacion = '$id'");
        echo $conexion->getEstado();
    }
    /**
     * Procesa las filas que son enviadas a un objeto jqGrid
     * @param type $param un array asociativo con los datos que se reciben de la capa de presentación
     */
    function select($param) {
        //El metodo select, seleciona de forma ordenada todos los vaolres que tiene la base de datos en la entidad 
        //tipo_operacion
        extract($param);
        $where = $conexion->getWhere($param);
        // conserve siempre esta sintaxis para enviar filas al grid:
        $sql = "SELECT id_tipo_operacion,
                descripcion_operacion, 
                categoria 
                FROM tipo_operacion_produccion $where";
        // crear un objeto con los datos que se envían a jqGrid para mostrar la información de la tabla
        $respuesta = $conexion->getPaginacion($sql, $rows, $page, $sidx, $sord); // $rows = filas * página
        // puede examinar aquí con error_log(..) el contenido de las variables 
        // agregar al objeto que se envía las filas de la página requerida
//        error_log($sql);
        if (($rs = $conexion->getPDO()->query($sql))) {
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {

                $respuesta['rows'][] = [
                    'id' => $fila['id_tipo_operacion'], // <-- debe identificar de manera única una fila del grid, por eso se usa la PK
                    'cell' => [ // los campos que se muestra en las columnas del grid
                        $fila['id_tipo_operacion'],
                        $fila['descripcion_operacion'],
                        $fila['categoria']
                    ]
                ];
            }
        }
        echo json_encode($respuesta);
    }

    public function getSelect($param) {
        //El metodo getSelect, retorna dos parametros(id_tipo_operacion, categoria) para crear un select de la entidad 
        //tipo_operacion y poder seleccionar el tipo de operacion que va ser foranea en otra tabla
        $json = FALSE;
        extract($param);
        $select = "";
        $select .= "<option value='0'>Seleccione un Tipo Operacion Produccion</option>";
        foreach ($conexion->getPDO()->query("SELECT id_tipo_operacion, categoria FROM tipo_operacion_produccion ORDER BY descripcion_operacion") as $fila) {
            $select .= "<option value='{$fila['id_tipo_operacion']}'>{$fila['categoria']}</option>";
        }
        echo $json ? json_encode($select) : ("<select id=$id>$select</select>");
    }

    public function getArray($param) {
        extract($param);
        foreach ($conexion->getPDO()->query("SELECT id_tipo_operacion, descripcion_operacion FROM tipo_operacion_produccion ORDER BY descripcion_operacion") as $fila) {
            $array[] = $fila['id_tipo_operacion'] . ' - ' . $fila['descripcion_operacion'];
        }
        echo json_encode($array);
    }

}
