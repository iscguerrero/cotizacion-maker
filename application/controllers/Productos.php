<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends Base_Controller {
	public function __construct(){
		parent::__construct();
		# Conexion a la base de datos para interactuar con las cotizaciones
			$this->load->database('firebird');
	}
	# Metodo para obtener producto por nombre(autocomplete)
	public function ObtenerProductoPorNombre(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros de la peticion ajax
		$term = $this->input->get('term');
		# Cargamos los modelos necesarios
		$this->load->model('Producto');
		$this->load->model('Precio');
		# Obtenemos la informacion del producto
		$productos = $this->Producto->obtenerProductoPorNombre($term);
		# Obtenemos el precio de cada uno de los productos devueltos
		foreach ($productos as $producto) {
			# Obtenemos y seteamos el precio del producto
			$precio = $this->Precio->obtenerPrecio($producto->CVE_ART, 2);
			$producto->PRECIO = isset($precio[0]) ? $precio[0]->PRECIO : 0;
			$producto->value = $producto->DESCR;
		}

		exit(json_encode($productos));
	}
	# Este metodo se encarga de recibir la cotizacion en formato pdf, leerlo y reenviar la informacion a la vista
	public function RecibirExcel(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros adicionales con los que se calculara el precio final del producto
		$tc = $this->input->post('tc');
		$replica = $this->input->post('replica');
		$std = $this->input->post('std');
		# Guardamos el archivo en la carpeta de uploads para futuras referencias
		$nombre = $_FILES['cotizacion']['name'];
		$file = 'uploads/' . $nombre;
		if(!move_uploaded_file($_FILES['cotizacion']['tmp_name'], $file)) exit(json_encode(array('flag'=>false, 'msj'=>'SE PRESENTO UN ERROR AL CARGAR EL ARCHIVO AL SISTEMA')));
		# Cargamos la libreria necesaria para leer el archivo de excel cargado al servidor
		$this->load->library('excel');
		$objPHPExcel = PHPExcel_IOFactory::load($file);
		# Obtenemos la pagina uno del archivo y leemos su contenido
		$worksheet = $objPHPExcel->getSheet(0);
		# Obtenemos el total de filas de la cotizacion
		$highestRow = $worksheet->getHighestRow();
		# Iteramos sobre el contenido de los productos de la cotizacion y obtenemos la informacion de los catalogos
		$items = array();
		for ($row = 2; $row <= $highestRow; $row++) {
			if(!is_null($worksheet->getCell('A'.$row)->getValue())){
				array_push($items, array(
					'cve_art'=>$worksheet->getCell('A'.$row)->getValue(),
					'piezas'=>$worksheet->getCell('B'.$row)->getValue(),
				));
			}
		}
		$this->load->model('Producto');
		$this->load->model('Precio');
		$existentes = $faltantes = array();
		# Se agrega la informacion necesaria de la base de datos y se filtran los productos existentes en el catalogo
		foreach ($items as $key => $item) {
			$producto = json_decode(json_encode($this->Producto->obtenerProducto($item['cve_art'])), TRUE);

			if(count($producto) > 0){
				$precio = $this->Precio->obtenerPrecio($item['cve_art'], 2);
				array_push($existentes, array(
					'cve_art' => $item['cve_art'],
					'piezas' => $item['piezas'],
					'descripcion' => $producto[0]['DESCR'],
					'precioPiezaAD' => isset($precio[0]) ? $precio[0]->PRECIO : 0
				));
				unset($precio);
			} else{
				array_push($faltantes, $item['cve_art']);
			}

		}
		# Se agregan los campos calculados
		$no = 0;
		foreach ($existentes as $key => $existente) {
			$no++;
			$existentes[$key]['no'] = $no;
			$existentes[$key]['precioPiezaDD'] = $existentes[$key]['precioPiezaAD'] - ( $existentes[$key]['precioPiezaAD'] * $std / 100 );
			$existentes[$key]['descuento'] = $std;
			$existentes[$key]['replicas'] = $existentes[$key]['piezas'] * $replica;
			$existentes[$key]['precioParteDD'] = $existentes[$key]['precioPiezaDD'] * $existentes[$key]['piezas'];
			$existentes[$key]['precioReplicaAD'] = $existentes[$key]['precioPiezaAD'] * $existentes[$key]['replicas'];
			$existentes[$key]['precioReplicaDD'] = $existentes[$key]['precioPiezaDD'] * $existentes[$key]['replicas'];
		}

		exit(json_encode(array('flag'=>true, 'msj'=>'ARCHIVO RECIBIDO CON EXITO', 'data'=>$existentes, 'faltantes'=>$faltantes)));
	}
}
