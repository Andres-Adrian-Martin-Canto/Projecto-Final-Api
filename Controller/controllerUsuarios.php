<?php
require_once 'Models/Usuario.php';
require('Utilidad/PDF.php');

class controllerUsuarios
{

    /**
     * TODO: Método que trae un usuario o todos los usuarios.
     * @throws ExcepcionApi
     */
    public function index($id): array
    {
        // Si el id no es null entones retornara un dato.
        if (!is_null($id)) {
            // Retorno el resultado.
            return Usuario::getOne($id);
            // De lo contrario retornara todos.
        } else {
            // Retorno todos los datos.
            return Usuario::getAll();
        }
    }

    /**
     * @throws ExcepcionApi
     */
    public function store()
    {
        $accion = $_GET['accion'] ?? "";
        if ($accion === 'login') {
            return $this->login();
        } else if ($accion === 'registro') {
            return $this->register();
        } else {
            throw new ExcepcionApi(METHOD_NOT_ALLOWED, "URL no valida:");
        }
    }

    /**
     * Todo: Método para loguear.
     * @throws ExcepcionApi
     */
    public function login(): string
    {
        $cuerpo = file_get_contents('php://input');
        $params = json_decode($cuerpo);
        return Usuario::login($params);
    }

    /**
     * TODO: Método para agregar un usuario.
     * @return string
     * @throws ExcepcionApi
     */
    public function register()
    {
        $cuerpo = file_get_contents('php://input');
        $params = json_decode($cuerpo);
        return Usuario::insertOne($params);
    }

    /**
     * TODO: Método que manda actualizar un usuario.
     * @throws ExcepcionApi
     */
    public function edit($id): string
    {
        if (!is_null($id)) {
            $cuerpo = file_get_contents('php://input');
            $params = json_decode($cuerpo);
            return Usuario::update($params, $id);
        } else {
            throw new ExcepcionApi(400, "Se requiere el id");
        }
    }

    /**
     * TODO: Método que creara el pdf
     * @throws ExcepcionApi
     */
    public function pdf(): void
    {
        // Creo la instancia
        $pdf = new PDF();
        // Creación de titulo
        $pdf->titulo("Lista de usuarios");
        // Creo la pagina.
        $pdf->AddPage();
        // Obtengo los usuarios
        $data = Usuario::pdf();
        // Le asigno la letra, tipo y numero letra.
        $pdf->SetFont('Arial', '', 14);
        // Le asigno el tamaño que tendrá cada columna
        $pdf->SetWidths(array(10, 50, 80, 40));
        // Digo que estarán centrados
        $pdf->SetAligns(array("C", "C", "C", "C"));
        // Le asigno la cabeceras
        $pdf->Row(array("No", 'Nombre', 'Correo electrónico',"Contraseña"));
        // Contador para que me diga numero 1 tal y asi.
        $contador = 1;
        // Le digo como estarán posicionados
        $pdf->SetAligns(array("C", "L", "L", "C"));
        // For each para enviarle los valores al método Row y los acomode bien
        foreach ($data as $row) {
            // Agrego una fila con los valores correspondientes
            $pdf->Row(array($contador++, $row['nombre'], $row['correo_electronico'],$row['contrasenia']));
        }
        // Muestro el PDF.
        $pdf->Output();
    }

    /**
     * TODO: Método para eliminar un usuario.
     * @throws ExcepcionApi
     */
    public function delete($id): string
    {
        if (!is_null($id)) {
            return Usuario::destroy($id);
        } else {
            throw new ExcepcionApi(400, "Se requiere el id");
        }
    }
}