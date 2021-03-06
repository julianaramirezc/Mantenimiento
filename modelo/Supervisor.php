<?php

/**
 * Description of Supervisor    
 * Implementa el CRUD para Supervisor
 * @authors Juliana Ramirez Corrales, Luis Daniel Murillo Zuluaga, Mateo Castro González, Jose Fernando Giraldo Giraldo
 */
class Supervisor {
    
    function add($param) {
        //El metodo add, inserta los valores en la base de datos correspondientes ala entidad supervisor.
        extract($param);
        // nunca suponga el orden de las columnas: INSERT INTO tabla VALUES (v1, v2,v3, ...); tómese el trabajo de indicar los nombres de columnas
        // los nombres de los elementos del array asociativo corresponden a los atributos name de las columnas del jqGrid
//        $sql = "INSERT INTO supervisor(fk_usuario, nombre, apellido) VALUES ('$supervisor', '$nombre', '$apellido');";
        $sql = "INSERT INTO usuario(id_usuario, direccion, telefonos, correos, contrasena, rol )
        VALUES ('$id_usuario', '$direccion','$telefonos', '$correos', '$contrasena', 'supervisor');
        INSERT INTO supervisor(fk_usuario,nombres, apellidos) 
        VALUES ('$id_usuario','$nombres', '$apellidos');";
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function edit($param) {
        //El metodo edit, actualiza los valores en la base de datos correspondientes ala entidad supervisor.
        extract($param);
        $sql = "UPDATE supervisor SET nombres='$nombres', apellidos='$apellidos' WHERE fk_usuario='$id'; UPDATE usuario SET id_usuario='$id_usuario', direccion='$direccion', telefonos='$telefonos', correos='$correos', contrasena='$contrasena' WHERE id_usuario = '$id';";  // <-- el ID de la fila asignado en el SELECT permite construir la condición de búsqueda del registro a modificar
        $conexion->getPDO()->exec($sql);        echo $conexion->getEstado();
    }

    function del($param) {
        //El metodo del, elimina los valores en la base de datos correspondientes ala entidad supervisor.
        extract($param);
        $conexion->getPDO()->exec("DELETE FROM supervisor WHERE fk_usuario = '$id'; DELETE FROM usuario WHERE id_usuario='$id'");
        echo $conexion->getEstado();
    }

    /**
     * Procesa las filas que son enviadas a un objeto jqGrid
     * @param type $param un array asociativo con los datos que se reciben de la capa de presentación
     */
    function select($param) {
        //El metodo select, seleciona de forma ordenada todos los vaolres que tiene la base de datos en la entidad 
        //supervisor
        extract($param);
        $where = $conexion->getWhere($param);
        // conserve siempre esta sintaxis para enviar filas al grid:
        $sql = "SELECT usuario.id_usuario, nombres, apellidos, usuario.direccion, usuario.telefonos, usuario.correos, usuario.contrasena
                FROM supervisor INNER JOIN usuario ON supervisor.fk_usuario = usuario.id_usuario $where";
        // crear un objeto con los datos que se envían a jqGrid para mostrar la información de la tabla
        $respuesta = $conexion->getPaginacion($sql, $rows, $page, $sidx, $sord); // $rows = filas * página
        // agregar al objeto que se envía las filas de la página requerida
        if (($rs = $conexion->getPDO()->query($sql))) {
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {
                
                $respuesta['rows'][] = [
                    'id' => $fila['id_usuario'], // <-- debe identificar de manera única una fila del grid, por eso se usa la PK
                    'cell' => [ // los campos que se muestra en las columnas del grid
                        $fila['id_usuario'],
                        $fila['nombres'],
                        $fila['apellidos'],
                        $fila['direccion'],
                        $fila['telefonos'],
                        $fila['correos'],
                        $fila['contrasena']
                        
                    ]
                ];
            }
        }
        echo json_encode($respuesta);
    }
    
    public function getSelect($param) {
        //El metodo getSelect, retorna dos parametros(fk_usuario, nombre) para crear un select de la entidad 
        //supervisor y poder seleccionar el supervisor que va ser foranea en otra tabla
        $json = FALSE;
        extract($param);
        $select = "";
        $select .= "<option value='0'>Seleccione un Supervisor</option>";
        foreach ($conexion->getPDO()->query("SELECT fk_usuario, nombres FROM supervisor ORDER BY nombres") as $fila) {
            $select .= "<option value='{$fila['fk_usuario']}'>{$fila['fk_usuario']} - {$fila['nombres']}</option>";
        }
        echo $json ? json_encode($select) : ("<select id='$id'>$select</select>");
    }

}
