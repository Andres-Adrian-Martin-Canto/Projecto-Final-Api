<?php
require_once 'Models/Genero.php';
require_once 'View/ExceptionApi.php';
class controllerGeneros
{
    /**
     * TODO: Método que me retorna un genero o todos los géneros.
     * @throws ExcepcionApi
     */
    public function index($id):array
    {
        // Si no es null el id entonces retorna un genero
        if(!is_null($id)){
            // Retorna un genero
            return Genero::getOne($id);
            // De lo contrario retorna todos los géneros.
        }else{
            // Return todos los géneros.
            return Genero::getAll();
        }
    }

    /**
     * TODO: Método que crear un nuevo genero.
     * @throws ExcepcionApi
     */
    public function store()
    {
        $cuerpo = file_get_contents('php://input');
        $params = json_decode($cuerpo);
        return Genero::insertOne($params);
    }

    /**
     * TODO: Método para editar un genero.
     * @throws ExcepcionApi
     */
    public function edit($id)
    {
        if (!is_null($id)){
            $cuerpo = file_get_contents('php://input');
            $params = json_decode($cuerpo);
            return Genero::update($params, $id);
        }else{
            throw new ExcepcionApi(400, "Se requiere el id");
        }
    }

    /**
     * TODO: Método para eliminar un genero
     * @param $id
     * @return string
     * @throws ExcepcionApi
     */
    public function delete($id)
    {
        if (!is_null($id)){
            return Genero::destroy($id);
        }else{
            throw new ExcepcionApi(400, "Se requiere el id");
        }
    }

    /**
     * TODO: Método para crear el pdf de géneros
     * @return void
     * @throws ExcepcionApi
     */
    public function pdf(): void
    {
        // Creo la instancia
        $pdf = new PDF();
        $pdf->titulo(utf8_decode("Lista de géneros"));
        $pdf->AddPage();
        $data = Genero::pdf();
        // Le asigno la letra, tipo y numero letra.
        $pdf->SetFont('Arial', '', 14);
        // Le asigno el tamaño que tendrá cada columna
        $pdf->SetWidths(array(10, 80, 80));
        $pdf->SetAligns(array("C", "C", "C"));
        // Le asigno la cabeceras
        $pdf->Row(array("No", 'Nombre', utf8_decode('Descripción')));
        $contador = 1;
        $pdf->SetAligns(array("C", "L", "L"));
        foreach ($data as $row) {
            $pdf->Row(array($contador++, utf8_decode($row['nombre']), utf8_decode($row['descripcion'])));
        }
        // Muestro el PDF.
        $pdf->Output();
    }
}