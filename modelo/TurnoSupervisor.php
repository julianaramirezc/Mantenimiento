<?php

/**
 * Description of Turno
 * Implementa el CRUD para Turno
 * @authors Juliana Ramirez Corrales, Luis Daniel Murillo Zuluaga, Mateo Castro González, Jose Fernando Giraldo Giraldo
 */
class TurnoSupervisor {
    
    function add($param) {
        //El metodo add, inserta los valores en la base de datos correspondientes ala entidad turno.
        // error_log(print_r($param, TRUE));  // cómo ver el contenido de una estructura de datos
        extract($param);
        // nunca suponga el orden de las columnas: INSERT INTO tabla VALUES (v1, v2,v3, ...); tómese el trabajo de indicar los nombres de columnas
        // los nombres de los elementos del array asociativo corresponden a los atributos name de las columnas del jqGrid
        $sql = "INSERT INTO turno_supervisor(hora_inicio, hora_fin, id_usuario) VALUES ('$hora_inicio', '$hora_fin', '$id_usuario')";
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function edit($param) {
        //El metodo edit, actualiza los valores en la base de datos correspondientes ala entidad turno.
        extract($param);
        $sql = "UPDATE turno_supervisor SET  id_usuario = '$id_usuario' WHERE turno_supervisor.id_turno = $id";  // <-- el ID de la fila asignado en el SELECT permite construir la condición de búsqueda del registro a modificar
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function del($param) {
        //El metodo del, elimina los valores en la base de datos correspondientes ala entidad turno.
        extract($param);
        $conexion->getPDO()->exec("DELETE FROM turno_supervisor WHERE id_turno = $id");
        echo $conexion->getEstado();
    }

    /**
     * Procesa las filas que son enviadas a un objeto jqGrid
     * @param type $param un array asociativo con los datos que se reciben de la capa de presentación
     */
    function select($param) {
        //El metodo select, seleciona de forma ordenada todos los vaolres que tiene la base de datos en la entidad 
        //turno
        extract($param);
        $where = $conexion->getWhere($param);
        // conserve siempre esta sintaxis para enviar filas al grid:
        $sql = "SELECT turno_supervisor.id_turno,
                    turno_supervisor.hora_inicio,
                    turno_supervisor.hora_fin,
                    turno_supervisor.id_usuario,
                    supervisor.nombres
                   
             FROM turno_supervisor inner join supervisor on turno_supervisor.id_usuario = supervisor.fk_usuario $where";
        // crear un objeto con los datos que se envían a jqGrid para mostrar la información de la tabla
        $respuesta = $conexion->getPaginacion($sql, $rows, $page, $sidx, $sord); // $rows = filas * página
        // puede examinar aquí con error_log(..) el contenido de las variables 
        // agregar al objeto que se envía las filas de la página requerida
        if (($rs = $conexion->getPDO()->query($sql))) {
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                
                $respuesta['rows'][] = [
                    'id' => $fila['id_turno'], // <-- debe identificar de manera única una fila del grid, por eso se usa la PK
                    'cell' => [ // los campos que se muestra en las columnas del grid
                        $fila['hora_inicio'],
                        $fila['hora_fin'],
                        $fila['nombres']
                    ]
                ];
            }
        }
        echo json_encode($respuesta);
    }
    
    public function getSelect($param) {
        //El metodo getSelect, retorna dos parametros(id_turno, hora_inicio) para crear un select de la entidad 
        //turno y poder seleccionar el turno que va ser foranea en otra tabla
        $json = FALSE;
        extract($param);
        $select = "";
        $select .= "<option value='0'>Seleccione un turno</option>";
        foreach ($conexion->getPDO()->query("SELECT id_turno, hora_inicio || ' - ' || hora_fin, id_usuarios AS turno FROM turno_supervisor ORDER BY hora_inicio") as $fila) {
            $select .= "<option value='{$fila['id_turno']}'>{$fila['turno']}</option>";
        }
        echo $json ? json_encode($select) : ("<select id=$id>$select</select>");
    }

}
