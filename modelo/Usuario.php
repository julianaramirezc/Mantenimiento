<?php

/**
 * Description of Usuario
 * Implementa el CRUD para Usuario
 * @authors Juliana Ramirez Corrales, Luis Daniel Murillo Zuluaga, Mateo Castro González, Jose Fernando Giraldo Giraldorador
 */
class Usuario {

    function add($param) {
        //El metodo add, inserta los valores en la base de datos correspondientes ala entidad usuario.
        // error_log(print_r($param, TRUE));  // cómo ver el contenido de una estructura de datos
        extract($param);
        // nunca suponga el orden de las columnas: INSERT INTO tabla VALUES (v1, v2,v3, ...); tómese el trabajo de indicar los nombres de columnas
        // los nombres de los elementos del array asociativo corresponden a los atributos name de las columnas del jqGrid
        $sql = "INSERT INTO usuario(id_usuario, direccion, telefonos, correos, rol) VALUES ('$id_usuario', '$direccion', '$telefonos', '$correos', '$rol')";
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function edit($param) {
        //El metodo edit, actualiza los valores en la base de datos correspondientes ala entidad usuario.
        extract($param);
        $sql = "UPDATE usuario SET id_usuario='$id_usuario', direccion='$direccion', telefonos='$telefonos', correos='$correos', contrasena='$contrasena', rol='$rol' WHERE id_usuario = '$id'";  // <-- el ID de la fila asignado en el SELECT permite construir la condición de búsqueda del registro a modificar
        $conexion->getPDO()->exec($sql);
        echo $conexion->getEstado();
    }

    function del($param) {
        //El metodo del, elimina los valores en la base de datos correspondientes ala entidad usuario.
        extract($param);
        $conexion->getPDO()->exec("DELETE FROM usuario WHERE id_usuario = '$id'");
        echo $conexion->getEstado();
    }

    /**
     * Procesa las filas que son enviadas a un objeto jqGrid
     * @param type $param un array asociativo con los datos que se reciben de la capa de presentación
     */
    function select($param) {
        //El metodo select, seleciona de forma ordenada todos los vaolres que tiene la base de datos en la entidad 
        //usuario
        extract($param);
        $where = $conexion->getWhere($param);
        // conserve siempre esta sintaxis para enviar filas al grid:
        $sql = "SELECT id_usuario, direccion, telefonos, correos, rol FROM usuario $where";
//        error_log($sql);
        // crear un objeto con los datos que se envían a jqGrid para mostrar la información de la tabla
        $respuesta = $conexion->getPaginacion($sql, $rows, $page, $sidx, $sord); // $rows = filas * página
        // puede examinar aquí con error_log(..) el contenido de las variables 
        // agregar al objeto que se envía las filas de la página requerida
        if (($rs = $conexion->getPDO()->query($sql))) {
            while ($fila = $rs->fetch(PDO::FETCH_ASSOC)) {

                $respuesta['rows'][] = [
                    'id' => $fila['id_usuario'], // <-- debe identificar de manera única una fila del grid, por eso se usa la PK
                    'cell' => [ // los campos que se muestra en las columnas del grid
                    $fila['id_usuario'],
                    $fila['direccion'],
                    $fila['telefonos'],
                    $fila['correos'],
                    $fila['rol']
                    ]
                    ];
                }
            }
            echo json_encode($respuesta);
        }

        public function getSelect($param) {
        //El metodo getSelect, retorna un parametro(id_usuario) para crear un select de la entidad 
        //usuario y poder seleccionar el usuario que va ser foranea en otra tabla
            $json = FALSE;
            extract($param);
            $select = "";
            $select .= "<option value='0'>Seleccione un Usuario</option>";
            foreach ($conexion->getPDO()->query("SELECT id_usuario FROM usuario ORDER BY id_usuario") as $fila) {
                $select .= "<option value='{$fila['id_usuario']}'>{$fila['id_usuario']}</option>";
            }
            echo $json ? json_encode($select) : ("<select id='$id'>$select</select>");
        }

    // ********* prototipos de los métodos para manejo de sesiones ***********

    /**
     * Verificar si la sesión está activa (versión completa)
     */
    public function verificar() {
        $ok = 'ERROR';
        if (isset($_SESSION['autenticado'])) {
            $ok = $_SESSION['autenticado'] ? 'OK' : 'ERROR';
        }
        echo $ok;
    }

    /**
     * Verifica si el usuario y la contraseña ingresados son correctos (versión incompleta)
     * @param type $param
     */
    public function autenticar($param) {

        //echo json_encode(['nombre' => 'id_usuario', 'tipo' => 'administrador', 'autenticado' => TRUE]);
        //return;

        $_SESSION['autenticado'] = FALSE;

        extract($param);
        // implementar de acuerdo a las siguientes instrucciones:
        // 1. Guardar en variables de sesión el nombre, el tipo de usuario y autenticado = TRUE
        // 2. si el usuario y la contraseña son correctos, devolver el nombre del usuario, el tipo de usuario y autenticado = TRUE,
        //    si no, guardar en variables de sesión autenticado = FALSE y devolver autenticado = FALSE
        $sql = "SELECT id_usuario, contrasena, rol FROM usuario where contrasena = '$contrasena' AND id_usuario = '$usuario'";
//        error_log($sql);
        if (($rs = $conexion->getPDO()->query($sql))) {
            $fila = $rs->fetch(PDO::FETCH_ASSOC);

            if ($fila['id_usuario'] === $usuario && $fila['contrasena'] === $contrasena) {
                $_SESSION['autenticado'] = TRUE;
                echo json_encode(['nombre' => 'id_usuario', 'tipo' => $fila['rol'], 'autenticado' => TRUE]);
            }  else {
             $_SESSION['autenticado'] = TRUE;
         }
     }
     $_SESSION['autenticado'] = FALSE;
 }

 public function enviarPassword($contrasena, $correo_usuario) {
        // falta implementar lo necesario para enviar una nueva contraseña al correo
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
            $correo_usuario => "Usuario"
            ],
            "contenido" => [
            "asunto" => "Control de cuentas",
            "mensaje" => "
            <html>
            <body>
                Buen día:
                <br><br>
                Su nueva contraseña de accceso es la siguiente:
                $contrasena
                <br>
                Atentamente,
                <br><br>
                Sistema de Producción
            </body>
            </html>"
            ]
            ];
            Utilidades::enviar($envio);
            echo json_encode(['ok' => TRUE, 'mensaje' => 'Nueva contraseña enviada a su correo']);
        }

    /**
     * Sólo para los usuarios que al menos han ingresado una vez al sistema, éste les genera un nuevo password y lo envía al correo.
     * (versión incompleta)
     * @param type $param
     */
    public function nuevoPassword($param){
        extract($param);
        $sqlCorreo = "SELECT correos FROM Usuario WHERE id_usuario = '$id_usuario'";
        if (($rs = $conexion->getPDO()->query($sqlCorreo))) {
            $fila = $rs->fetch(PDO::FETCH_ASSOC);
            $correo_usuario = $fila['correos'];
        }
        $nuevoPassword = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
        $contrasenaMD5 = md5($nuevoPassword);
        $sqlUpdatePassword = "UPDATE usuario SET contrasena = '$contrasenaMD5' WHERE id_usuario = '$id_usuario'";
        $conexion->getPDO()->query($sqlUpdatePassword);
        self::enviarPassword($nuevoPassword, $correo_usuario);
    }

    /**
     * Cambia la contraseña actual por una nueva (versión incompleta)
     * @param type $param
     */
    public function cambiarPassword($param) {
        extract($param);
        // falta implementar lo necesario para permitir al usuario cambiar su contraseña
        $sqlUpdatePassword = "UPDATE usuario SET contrasena = '$nuevoPassword' WHERE id_usuario = '$id_usuario' and contrasena = '$actualPassword'";
        if(($rs=$conexion->getPDO()->query($sqlUpdatePassword))){
            if($rs->rowCount()==1){
                echo json_encode(['ok' => TRUE, 'mensaje' => 'Cambio de contraseña exitoso']);
            }else{
                echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló el cambio de contraseña']);
            }
        }else{
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló el cambio de contraseña']);
        }
    }

    /**
     * Sólo para los usuarios que nunca han ingresado al sistema, éste les genera una contraseña
     * y envía el nombre de usuario y la contraseña al correo. (versión incompleta)
     * @param type $param
     */
    public function nuevoUsuario($param) {
        extract($param);
        // falta implementar lo necesario para permitir al usuario cambiar su contraseña
        if(strlen($contrasena)>=8){
            $sqlCorreo = "SELECT correos FROM Usuario WHERE id_usuario = '$id_usuario'";
            if (($rs = $conexion->getPDO()->query($sqlCorreo))) {
                $fila = $rs->fetch(PDO::FETCH_ASSOC);
                $correo_usuario = $fila['correos'];
            }
            $contrasenaMD5 = md5($contrasena);
            $sqlUpdatePassword = "UPDATE usuario SET contrasena = '$contrasenaMD5' WHERE id_usuario = '$id_usuario' AND contrasena IS NULL";
//                        error_log(print_r($sqlUpdatePassword,TRUE));
            $rs = $conexion->getPDO()->query($sqlUpdatePassword);
            if($rs->rowCount()==1){
                self::enviarPassword($contrasena, $correo_usuario);
            }else{
                echo json_encode(['ok' => FALSE, 'mensaje' => 'Esta cuenta ya ha sido activada antes.']);    
            }
        }else{
            echo json_encode(['ok' => FALSE, 'mensaje' => 'La contraseña es demasiado corta, minimo 8 caracteres']);
        }
    }

    /**
     * Cierra la sesión actual y elimina cualquier rastro de ella. (versión completa)
     * @param type $param
     */
    public function cerrarSesion($param) {
        extract($param);
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params["httponly"]);
        }
        session_destroy();
    }

}
