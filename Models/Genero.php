<?php
require_once 'BD/ConexionBD.php';
require_once 'View/ExceptionApi.php';

class Genero
{
    const  ESTADO_CREACION_EXITOSA = "Se ha creado el genero";
    const  ESTADO_CREACION_FALLIDA = "No se ha creado el genero";
    const  ESTADO_MODIFICACO_EXITOSA = "Se ha modificado el genero";
    const  ESTADO_MODIFICACO_FALLIDA = "No se ha modificado el genero";
    const  ESTADO_DELETE_EXITOSA = "Se ha borrado el genero";
    const  ESTADO_DELETE_FALLIDA = "No se ha borrado el genero";
    private static $table = 'generos';
    const ERROR_DB = 500;
    protected static $columnasTabla = [
        'nombre',
        'descripcion'
    ];

    /**
     * TODO: Método que traer un genero.
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
            if (!$respuesta) {
                return [];
            }
            return $respuesta;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que me trae todos los géneros.
     * @return array
     * @throws ExcepcionApi
     */
    public static function getAll(): array
    {
        try {
            $comando = "SELECT * FROM " . self::$table;
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            // Ejecutar el query.
            $sentencia->execute();
            // Retorno el resultado.
            $respuesta = $sentencia->fetchAll(PDO::FETCH_ASSOC);
            if (!$respuesta) {
                return [];
            }
            return $respuesta;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Metodo para insertar.
     * @throws ExcepcionApi
     */
    public static function insertOne($params): string
    {
        // Validación de token.
        Usuario::autenticar();
        self::validacionParams($params);
        try {
            // Obtenemos el pdo para poder hacer el insert.
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "INSERT INTO " . self::$table . " (" . self::$columnasTabla[0] . " , " . self::$columnasTabla[1] . ") VALUES (?, ?)";
            // Mandamos a validar si la sintaxis esta bien escrita.
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindValue(1, $params->nombre, PDO::PARAM_STR);
            $sentencia->bindValue(2, $params->descripcion, PDO::PARAM_STR);
            $sentencia->execute();
            if ($sentencia->rowCount() > 0) {
                return self::ESTADO_CREACION_EXITOSA;
            } else {
                return self::ESTADO_CREACION_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que modifica un genero.
     * @throws ExcepcionApi
     */
    public static function update($params, $id): string
    {
        self::validacionParams($params);
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "UPDATE " . self::$table . " SET " . self::$columnasTabla[0] . " = ? ," . self::$columnasTabla[1] . " = ? WHERE id = ?";
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindValue(1, $params->nombre, PDO::PARAM_STR);
            $sentencia->bindValue(2, $params->descripcion, PDO::PARAM_STR);
            $sentencia->bindValue(3, $id, PDO::PARAM_INT);
            $sentencia->execute();

            if ($sentencia->rowCount() > 0) {
                return self::ESTADO_MODIFICACO_EXITOSA;
            } else {
                return self::ESTADO_MODIFICACO_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }

    /**
     * TODO: Método que elimina un genero.
     * @return string
     * @throws ExcepcionApi
     */
    public static function destroy($id): string
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            $comando = "DELETE FROM " . self::$table . " WHERE id =?";
            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $id, PDO::PARAM_INT);
            $sentencia->execute();
            if ($sentencia->rowCount() > 0) {
                return self::ESTADO_DELETE_EXITOSA;
            } else {
                return self::ESTADO_DELETE_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ERROR_DB, $e->getMessage());
        }
    }


    /**
     * TODO: Método que valida que tengan todos los parámetros.
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
            $comando = "SELECT " . self::$columnasTabla[0] . " , " . self::$columnasTabla[1] ." FROM " . self::$table;
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            // Ejecutar el query.
            $sentencia->execute();
            // Retorno el resultado.
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