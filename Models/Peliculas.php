<?php
require_once 'BD/ConexionBD.php';
require_once 'View/ExceptionApi.php';

class Peliculas
{
    const  ESTADO_CREACION_EXITOSA = "Se ha creado la película";
    const  ESTADO_CREACION_FALLIDA = "No se ha creado la película";
    const  ESTADO_MODIFICACO_EXITOSA = "Se ha modificado la película";
    const  ESTADO_MODIFICACO_FALLIDA = "No se ha modificado la película";
    const  ESTADO_DELETE_EXITOSA = "Se ha borrado la película";
    const  ESTADO_DELETE_FALLIDA = "No se ha borrado la película";
    private static $table = 'peliculas';
    const ERROR_DB = 500;
    protected static $columnasTabla = [
        'titulo',
        'director',
        'anio_estreno',
        'genero_id'
    ];

    public static function getOne($id): array
    {
        try {
            // Traigo todos los datos de mi tabla
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
     * TODO: Método que trae todos las películas.
     * @return array
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
     * @throws ExcepcionApi
     */
    public static function insertOne($params): string
    {
        // Validación de token.
        Usuario::autenticar();
        // Validación de parámetros.
        self::validacionParams($params);
        try {
            // Obtenemos el pdo para poder hacer el insert.
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "INSERT INTO " . self::$table . " (" .
            self::$columnasTabla[0] . "," .
            self::$columnasTabla[1] . "," .
            self::$columnasTabla[2] . "," .
            self::$columnasTabla[3] . " )".
                "VALUES (?,?,?,?)";
            // Mandamos a validar si la sintaxis esta bien escrita.
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $params->titulo, PDO::PARAM_STR);
            $sentencia->bindParam(2, $params->director, PDO::PARAM_STR);
            $sentencia->bindParam(3, $params->anio_estreno, PDO::PARAM_INT);
            $sentencia->bindParam(4, $params->genero_id, PDO::PARAM_INT);
            // Ejecutamos el script
            $resultado = $sentencia->execute();
            // Si se ejecuto entonces envía un mensaje de todo correcto.
            if ($resultado) {
                return self::ESTADO_CREACION_EXITOSA;
                // De lo contrario mandara que no fue exitosa.
            } else {
                return self::ESTADO_CREACION_FALLIDA;
            }
        }catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que actualiza una película.
     * @param $params
     * @param $id
     * @return string
     * @throws ExcepcionApi
     */
    public static function update($params, $id): string
    {
        self::validacionParams($params);
        try {
            // Obtenemos el pdo para poder hacer el insert.
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "UPDATE " . self::$table . " SET " . self::$columnasTabla[0] . " =?, " . self::$columnasTabla[1] . " =?, " .
                self::$columnasTabla[2] . " =?, " . self::$columnasTabla[3] . " =? WHERE id =?";
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $params->titulo, PDO::PARAM_STR);
            $sentencia->bindParam(2, $params->director, PDO::PARAM_STR);
            $sentencia->bindParam(3, $params->anio_estreno, PDO::PARAM_INT);
            $sentencia->bindParam(4, $params->genero_id, PDO::PARAM_INT);
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
     * TODO: Método para eliminar una película.
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
     * TODO: Método que valida que estén bien las columnas.
     * @param $params
     * @return void
     * @throws ExcepcionApi
     */
    public static function validacionParams($params): void
    {
        // Verificar si las columnasDeLaTabla las enviaron como parámetros.
        foreach (self::$columnasTabla as $columna) {
            // Si no esta definido dentro del arreglo que me enviaron entonces marco el error y lo envió.
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
            // Traigo todos los datos de mi tabla
            $comando = "SELECT ". self::$columnasTabla[0] . " , " . self::$columnasTabla[1] . " , " .
            self::$columnasTabla[2] . ", generos.nombre  FROM " . self::$table . " JOIN generos ON peliculas.genero_id = generos.id";
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
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }


}