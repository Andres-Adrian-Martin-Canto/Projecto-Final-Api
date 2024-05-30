<?php
require_once 'Models/peliculas.php';
require_once 'View/ExceptionApi.php';
class controllerPeliculas
{

    /**
     * TODO: Método que trae una película por su id o todas las películas.
     * @throws ExcepcionApi
     */
    public function index($id){
        // Si el id no es null entones retornara un dato.
        if (!is_null($id)){
            // Retorno el resultado.
            return Peliculas::getOne($id);
            // De lo contrario retornara todos.
        }else{
            // Retorno todos los datos.
            return Peliculas::getAll();
        }
    }

    /**
     * TODO: Método que me va a servir para insertar películas.
     * @return string
     * @throws ExcepcionApi
     */
    public function store(): string{
        $cuerpo = file_get_contents('php://input');
        $params = json_decode($cuerpo);
        return Peliculas::insertOne($params);
    }

    /**
     * TODO: Método para modificar una película.
     * @param $id
     * @return string
     * @throws ExcepcionApi
     */
    public function edit($id): string{
        if (!is_null($id)){
            $cuerpo = file_get_contents('php://input');
            $params = json_decode($cuerpo);
            return Peliculas::update($params, $id);
        }else{
            throw new ExcepcionApi(400, "Se requiere el id");
        }
    }

    /**
     * TODO: Método para eliminar una película.
     * @param $id
     * @return string
     * @throws ExcepcionApi
     */
    public function delete($id): string{
        if (!is_null($id)){
            return Peliculas::destroy($id);
        }else{
            throw new ExcepcionApi(400, "Se requiere el id");
        }
    }

    /**
     * TODO: Método para creación de pdf las películas
     * @throws ExcepcionApi
     */
    public function pdf(): void
    {
        // Creo la instancia
        $pdf = new PDF();
        $pdf->titulo(utf8_decode("Lista de películas"));
        // Creo la pagina.
        $pdf->AddPage();
        $data = Peliculas::pdf();
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetWidths(array(10, 50, 60, 30,30));
        $pdf->SetAligns(array("C", "C", "C", "C", "C"));
        $pdf->Row(array ("No", 'Titulo', 'Director',utf8_decode("Año estreno"),"Genero"));
        $contador = 1;
        $pdf->SetAligns(array("C", "C", "C", "C","C"));
        foreach ($data as $row) {
            $pdf->Row(array($contador++, utf8_decode($row['titulo']), utf8_decode($row['director']),$row['anio_estreno'], utf8_decode($row['nombre'])));
        }
        // Muestro el PDF.
        $pdf->Output();

    }
}