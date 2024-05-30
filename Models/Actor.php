<?php
require_once 'BD/ConexionBD.php';
require_once 'View/ExceptionApi.php';
require_once 'Models/Usuario.php';
class Actor
{
    const  ESTADO_CREACION_EXITOSA = "Se ha creado el actor";
    const  ESTADO_CREACION_FALLIDA = "No se ha creado el actor";
    const  ESTADO_MODIFICACO_EXITOSA = "Se ha modificado el actor";
    const  ESTADO_MODIFICACO_FALLIDA = "No se ha modificado el actor";
    const  ESTADO_DELETE_EXITOSA = "Se ha borrado el actor";
    const  ESTADO_DELETE_FALLIDA = "No se ha borrado el actor";
    private static $table = 'actores';
    const ERROR_DB = 500;
    protected static $columnasTabla = [
        'nombre',
        'nacionalidad',
        'edad',
        'pelicula_id'
    ];

    /**
     * TODO: Método que regresa todos los actores.
     * @throws ExcepcionApi
     */
    public static function getAll(): array
    {
        try {
            // Traigo todos los datos de mi tabla
            $comando = "SELECT * FROM " . self::$table;
            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            // Ejecutar el query.
            $sentencia->execute();
            // Retorno el resultado.
            // Si quiero traer varios datos uso fetchAll
            $respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            if(!$respuesta){
                return [];
            }
            return $respuesta;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que regresa un actor por su ID.
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
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método para insertar un actor.
     * @throws ExcepcionApi
     */
    public static function insertOne($params): string
    {
        // Validación de token.
        Usuario::autenticar();
        // Método que valida que envié los parámetros correctos.
        self::validacionParams($params);
        // Hace la inserción de un nuevo actor.
        try {
            // Obtenemos el pdo para poder hacer el insert.
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Sentencia INSERT
            $comando = "INSERT INTO " . self::$table . " ( " .
                self::$columnasTabla[0] . "," .
                self::$columnasTabla[1] . "," .
                self::$columnasTabla[2] . "," .
                self::$columnasTabla[3] . ")" .
                " VALUES(?,?,?,?)";
            // Mandamos a validar si la sintaxis esta bien escrita.
            $sentencia = $pdo->prepare($comando);
            // Le asignamos los parámetros que se enviaran a la vista.
            $sentencia->bindParam(1, $params->nombre, PDO::PARAM_STR);
            $sentencia->bindParam(2, $params->nacionalidad, PDO::PARAM_STR);
            $sentencia->bindParam(3, $params->edad, PDO::PARAM_INT);
            $sentencia->bindParam(4, $params->pelicula_id, PDO::PARAM_INT);
            // Ejecutamos el script
            $resultado = $sentencia->execute();
            // Si se ejecuto entonces envía un mensaje de todo correcto.
            if ($resultado) {
                return self::ESTADO_CREACION_EXITOSA;
                // De lo contrario mandara que no fue exitosa.
            } else {
                return self::ESTADO_CREACION_FALLIDA;
            }
        } catch (PdoException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método para actualizar un actor.
     * @throws ExcepcionApi
     */
    public static function update($params, $id): string
    {
        // Método que valida que envié los parámetros correctos.
        self::validacionParams($params);
        // Procedimiento de actualizar un dato.
        try {
            // Obtenemos el pdo para poder hacer el insert.
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "UPDATE " . self::$table . " SET " . self::$columnasTabla[0] . " =?, " . self::$columnasTabla[1] . " =?, " .
                self::$columnasTabla[2] . " =?, " . self::$columnasTabla[3] . " =? WHERE id =?";
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $params->nombre, PDO::PARAM_STR);
            $sentencia->bindParam(2, $params->nacionalidad, PDO::PARAM_STR);
            $sentencia->bindParam(3, $params->edad, PDO::PARAM_INT);
            $sentencia->bindParam(4, $params->pelicula_id, PDO::PARAM_INT);
            $sentencia->bindParam(5, $id, PDO::PARAM_INT);
            $sentencia->execute();
            if ($sentencia->rowCount() > 0) {
                return self::ESTADO_MODIFICACO_EXITOSA;
            } else {
                return self::ESTADO_MODIFICACO_FALLIDA;
            }
        } catch (PdoException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método para eliminar un Actor.
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
     * TODO: Método que valida los parámetros sean corrector al arreglo columnas.
     * @param $params
     * @return void
     * @throws ExcepcionApi
     */
    public static function validacionParams($params): void
    {
        // Verificar si las columnasDeLaTabla las enviaron como parámetros.
        foreach (self::$columnasTabla as $columna) {
            // Si no está definido dentro del arreglo que me enviaron entonces marco el error y lo envió.
            if (!isset($params->$columna)) {
                // Paso el arreglo a una cadena
                $mensajeError = "Las columnas son las siguientes: " . implode(', ', self::$columnasTabla);
                throw new ExcepcionApi(400, $mensajeError);
            }
        }
    }

    public static function pdf(): array
    {
        try {
            // Traigo todos los datos de mi tabla nombre, Actores.nacionalidad, Actores.edad,
            $comando = "SELECT  " . self::$columnasTabla[0] . ", ". self::$columnasTabla[1] . ", " . self::$columnasTabla[2] .
                " , peliculas.titulo FROM " .self::$table ." JOIN  peliculas ON actores.pelicula_id = peliculas.id";
            // Preparar sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            // Ejecutar el query.
            $sentencia->execute();
            // Retorno el resultado.
            // Si quiero traer varios datos uso fetchAll
            $respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            if(!$respuesta){
                return [];
            }
            return $respuesta;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }

    }
}