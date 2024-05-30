<?php
// Importamos la base de datos.
require_once 'BD/ConexionBD.php';
require_once 'View/ExceptionApi.php';
require_once 'View/VistaJson.php';
require_once 'Controller/controllerUsuarios.php';
require_once 'Models/Usuario.php';

$vista = new VistaJson();
const ESTADO_RUTA_NO_VALIDA = 403;
const METHOD_NOT_ALLOWED = 405;
// Las rutas permitidas.
$rutas = [
    'actores' => 'controllerActores',
    'generos' => 'controllerGeneros',
    'peliculas' => 'controllerPeliculas',
    'usuarios' => 'controllerUsuarios'
];
// Si hay una excepción esto la obtiene
set_exception_handler(function ($exception) use ($vista) {
    $cuerpo = array(
        "estado" => $exception->estado,
        "mensaje" => $exception->getMessage()
    );
    if (!$exception->estado) {
        $vista->estado = 500;
    } else {
        $vista->estado = $exception->estado;
    }
    $vista->imprimir($cuerpo);
}
);
// Obtengo el modelo.
$Model = $_GET['model'] ?? 'peliculas';

// Valido que si no esta entonces manda un error
if (!array_key_exists($Model, $rutas)) {
    throw new ExcepcionApi(ESTADO_RUTA_NO_VALIDA, "No se reconoce el recurso al que intentas acceder: " . $Model);
} else {
    // Importamos la clase dependiendo de cuál se cumpla en la ruta.
    require_once 'Controller/' . $rutas[$Model] . '.php';
    // Creación de un objeto el cual me ayudara a llamar al controller.
    $objetoController = new $rutas[$Model];
    // Obtener el método.
    $metodo = strtolower($_SERVER['REQUEST_METHOD']);
    $id = $_GET['id'] ?? null;
    if (empty ($id)) $id = null;
    $respuesta = "";
    // Retorno mi respuesta en un array Asociativo.
    $arrayDevolver = [
        'estado' => '',
        'cuerpo' => '',
    ];
    // Validación de pdf
    $pdf = $_GET['pdf'] ?? null;
    if (empty ($pdf)) $pdf = null;
    if (!is_null($pdf)) {
        if ($metodo === 'get'){
            Usuario::autenticar();
            $objetoController->pdf();
        }else{
            throw new ExcepcionApi(METHOD_NOT_ALLOWED, "URL no valida: ");
        }
        // Fin del pdf
    } else {
        switch ($metodo) {
            case 'get':
                // Válido que me haya enviado el token
                Usuario::autenticar();
                // Mando a llamar al método index para que haga lo siguiente.
                $respuesta = $objetoController->index($id);
                $vista->estado = 200;
                break;
            case 'post':
                $respuesta = $objetoController->store();
                // Mandamos que se ha creado correctamente.
                $vista->estado = 201;
                break;
            case 'put':
                // Válido que me haya enviado el token
                Usuario::autenticar();
                // Mando a llamar al método edit para modificar dependiendo.
                $respuesta = $objetoController->edit($id);
                $vista->estado = 200;
                break;
            case 'delete':
                // Válido que me haya enviado el token
                Usuario::autenticar();
                // Mando a llamar al método delete para eliminar dependiendo.
                $respuesta = $objetoController->delete($id);
                $vista->estado = 200;
                break;
            default:
                throw new ExcepcionApi(METHOD_NOT_ALLOWED, "URL no valida: " . $rutas[$Model]);
                break;
        }
        // Si todo salio bien le envió todo ok
        $arrayDevolver['estado'] = $vista->estado;
        // Le asigno la respuesta al arreglo asociativo
        $arrayDevolver['cuerpo'] = $respuesta;
        // Crear el excel de las peticiones y todo el proceso.

        // Imprimir mi respuesta.
        $vista->imprimir($arrayDevolver);
    }

}

