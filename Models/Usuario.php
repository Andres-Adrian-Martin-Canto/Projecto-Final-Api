<?php
require_once 'BD/ConexionBD.php';
require_once 'View/ExceptionApi.php';

class Usuario
{
    protected static $columnasTable = [
        'nombre',
        'correo_electronico',
        'contrasenia'
    ];
    const  ESTADO_CREACION_EXITOSA = "Se ha creado el usuario";
    const  ESTADO_CREACION_FALLIDA = "No se ha creado el usuario";
    const  ESTADO_MODIFICADO_EXITOSA = "Se ha modificado el usuario";
    const  ESTADO_MODIFICACO_FALLIDA = "No se ha modificado el usuario";
    const  ESTADO_DELETE_EXITOSA = "Se ha borrado el usuario";
    const  ESTADO_DELETE_FALLIDA = "No se ha borrado el usuario";
    private static $table = "usuarios";
    const ERROR_DB = 500;

    /**
     * TODO: Método para crear un nuevo usuario.
     * @throws ExcepcionApi
     */
    public static function insertOne($params)
    {
        self::validacionParams($params);
        // Si existe el correo envía la respuesta siguiente
        if (self::existeCorreo($params->correo_electronico)) {
            return "El correo ya existe";
        }
        // Encrypto la contraseña texto que me envió el usuario.
        $contrasenia = self::encryptarPassword($params->contrasenia);
        $token = self::encryptarPassword($params->contrasenia . date('Y-m-d H:i:s'));
        try {
            // Obtenemos el pdo para poder hacer el insert.
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "INSERT INTO " . self::$table . " (" .
                self::$columnasTable[0] . "," .
                self::$columnasTable[1] . "," .
                self::$columnasTable[2] . "," .
                "token". ")" .
                "VALUES(?,?,?,?)";
            // Mandamos a validar si la sintaxis esta bien escrita.
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $params->nombre, PDO::PARAM_STR);
            $sentencia->bindParam(2, $params->correo_electronico, PDO::PARAM_STR);
            $sentencia->bindParam(3, $contrasenia, PDO::PARAM_STR);
            $sentencia->bindParam(4, $token, PDO::PARAM_STR);
            // Agregar el token.
            // Ejecutamos el script
            $resultado = $sentencia->execute();
            // Si se ejecuto entonces envía un mensaje de todo correcto.
            if ($resultado) {
                // Le retorno suu correo y su token.
                return ["correo" => $params->correo_electronico, "token" => $token];
                // De lo contrario mandara que no fue exitosa.
            } else {
                return self::ESTADO_CREACION_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Metodo que va verificar si existe el correo.
     * @throws ExcepcionApi
     */
    private static function existeCorreo($correoParams): bool
    {
        try {
            $comando = "SELECT * FROM " . self::$table . " WHERE " . self::$columnasTable[1] . " = ?";
            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->bindParam(1, $correoParams, PDO::PARAM_STR);
            $sentencia->execute();
            $respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
            if (!$respuesta) {
                return false;
            }
            return true;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Metodo que valida los parametros sean corrector al arreglo columnas.
     * @param $params
     * @return void
     * @throws ExcepcionApi
     */
    public static function validacionParams($params): void
    {
        // Verificar si las columnasDeLaTabla las enviaron como parámetros.
        foreach (self::$columnasTable as $columna) {
            // Si no esta definido dentro del arreglo que me enviaron entonces marco el error y lo envió.
            if (!isset($params->$columna)) {
                // Paso el arreglo a una cadena
                $mensajeError = "Las columnas son las siguientes: " . implode(', ', self::$columnasTable);
                throw new ExcepcionApi(400, $mensajeError);
            }
        }
    }

    /**
     * TODO: Método que encripta la contrasenia que me da el usuario.
     * @param $contrasenia
     * @return string
     */
    private static function encryptarPassword($contrasenia): string
    {
        return password_hash($contrasenia, PASSWORD_BCRYPT);
    }


    /**
     * TODO: Método para loguearse.
     * @throws ExcepcionApi
     */
    public static function login($params): string
    {
        // Validar parámetros que son correo y contrasenia.
        if (!isset($params->correo_electronico) || !isset($params->contrasenia)) {
            // Si no me los enviaron entonces envió un error.
            throw new ExcepcionApi(400, "Correo o contrasenia no valida");
        }
        // Buscar el usuario por su correo.
        if (!self::existeCorreo($params->correo_electronico)) {
            // Si no existe se lo informo.
            return "No existe su correo.";
        }
        // Obtengo el usuario.
        $usuario = self::obtenerUsuario($params->correo_electronico);
        // Si existe entonces valido, mando a desencriptar la contrasenia
        if (self::descryptarPassword($params->contrasenia, $usuario['contrasenia'])) {
            return "Se ha logueado";
        }
        return "Contraseña incorrecta o correo electronico incorrecto";
    }

    /**
     * TODO: Método que decrypt y me dice si es correo o no.
     * @param $contrasenia_ingresada
     * @param $hash_guardado
     * @return bool
     */
    private static function descryptarPassword($contrasenia_ingresada, $hash_guardado): bool
    {
        if (password_verify($contrasenia_ingresada, $hash_guardado)) {
            return true;
        }
        return false;
    }

    /**
     * TODO: Método para obtener el usuario.
     * @throws ExcepcionApi
     */
    private static function obtenerUsuario($correoParams)
    {
        try {
            $comando = "SELECT * FROM " . self::$table . " WHERE " . self::$columnasTable[1] . " = ?";
            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->bindParam(1, $correoParams, PDO::PARAM_STR);
            $sentencia->execute();
            $respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
            if (!$respuesta) {
                return [];
            }
            return $respuesta;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método para autenticar.
     * @throws ExcepcionApi
     */
    public static function autenticar(): void
    {
        $cabeceras = apache_request_headers();
        // Si no me enviaron el token entonces mando error.
        if (!isset($cabeceras["Authorization"])) {
            throw new ExcepcionApi(
                400, "Se necesita el token.");
        }
        // Obtengo el token.
        $token = $cabeceras["Authorization"];
        // Si no hay con ese valor entonces mando un error.
        if (!self::tokenExiste($token)) {
            throw new ExcepcionApi(
                410, "Clave de API no autorizada");
        }
    }

    /**
     * TODO: Método que valida que haya un usuario con ese mismo token.
     * @throws ExcepcionApi
     */
    public static function tokenExiste($token): bool
    {
        try{
            $comando = "SELECT COUNT( id ) FROM " . self::$table . " WHERE token = ?";
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->bindParam(1, $token, PDO::PARAM_STR);
            $sentencia->execute();
            return $sentencia->fetchColumn(0) > 0;
        }catch (PDOException $e){
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método para obtener los usuarios.
     * @return array
     * @throws ExcepcionApi
     */
    public static function getAll(): array
    {
        try{
            $comando = "SELECT * FROM " . self::$table;
            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            // Ejecutar el query.
            $sentencia->execute();
            // Retorno el resultado.
            $respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            if(!$respuesta){
                return [];
            }
            return $respuesta;
        }catch (PDOException $e){
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que me trae un id.
     * @param $id
     * @return array
     * @throws ExcepcionApi
     */
    public static function getOne($id): array
    {
        try {
            $comando = "SELECT * FROM " . self::$table . " WHERE id =?";
            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->bindParam(1, $id, PDO::PARAM_INT);
            // Ejecutar el query.
            $sentencia->execute();
            // Retorno el resultado.
            $respuesta = $sentencia->fetch(PDO::FETCH_ASSOC);
            // En caso de que no traiga datos.
            if (!$respuesta) {
                return [];
            }
            // Si tiene datos trae
            return $respuesta;
        }catch (PDOException $e){
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que actualiza un usuario.
     * @throws ExcepcionApi
     */
    public static function update($params, $id): string
    {
        // Valido los parámetros
        self::validacionParams($params);
        // Encriptar contrasenia.
        // Encripto la contrasenia texto que me envió el usuario.
        $contrasenia = self::encryptarPassword($params->contrasenia);
        $token = self::encryptarPassword($params->contrasenia . date('Y-m-d H:i:s'));
        try {
            // Obtenemos el pdo para poder hacer el insert.
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "UPDATE " . self::$table . " SET " .
            self::$columnasTable[0] ." = ?, " .
            self::$columnasTable[1] ." = ?, " .
            self::$columnasTable[2] ." = ?, " . " token = ? WHERE id = ?";
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $params->nombre, PDO::PARAM_STR);
            $sentencia->bindParam(2, $params->correo_electronico, PDO::PARAM_STR);
            $sentencia->bindParam(3, $contrasenia, PDO::PARAM_STR);
            $sentencia->bindParam(4, $token, PDO::PARAM_STR);
            $sentencia->bindParam(5, $id, PDO::PARAM_INT);
            $sentencia->execute();
            if ($sentencia->rowCount() > 0) {
                return self::ESTADO_MODIFICADO_EXITOSA;
            } else {
                return self::ESTADO_MODIFICACO_FALLIDA;
            }
        }catch (PDOException $e){
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método para eliminar un usuario.
     * @param $id
     * @return string
     * @throws ExcepcionApi
     */
    public static function destroy($id): string
    {
        try{
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "DELETE FROM " . self::$table . " WHERE id =?";
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $id, PDO::PARAM_INT);
            $sentencia->execute();
            if ($sentencia->rowCount() > 0) {
                return self::ESTADO_DELETE_EXITOSA;
            }else{
                return self::ESTADO_DELETE_FALLIDA;
            }
        }catch (PDOException $e){
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que devuelve el campo nombre, correo electronico y contraseña
     * @return array|mixed
     * @throws ExcepcionApi
     */
    public static function pdf()
    {
        try {
            $comando = "SELECT nombre, correo_electronico, contrasenia FROM " . self::$table ;

            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->execute();
            $respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            if (!$respuesta) {
                return [];
            }
            return $respuesta;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }
}