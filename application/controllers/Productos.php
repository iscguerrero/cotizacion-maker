<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends Base_Controller {

	public function __construct(){
		parent::__construct();
		$firebird = $this->load->database('firebird');
	}

	# Metodo para obtener producto por nombre(autocomplete)
	public function ObtenerProductoPorNombre(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros de la peticion ajax
		$term = $this->input->get('term');
		# Cargamos los modelos necesarios
		$this->load->model('inve03');
		$this->load->model('precio_x_prod03');
		# Obtenemos la informacion del producto
		$productos = $this->inve03->xnombre($term);
		# Obtenemos el precio de cada uno de los productos devueltos
		foreach ($productos as $producto) {
			# Obtenemos y seteamos el precio del producto
			$precio = $this->precio_x_prod03->obtener($producto->CVE_ART, 1);
			$producto->PRECIO = isset($precio) ? $precio->PRECIO : 0;
			$producto->value = $producto->DESCR;
		}
		exit(json_encode($productos));
	}

	# Este metodo se encarga de recibir la cotizacion en formato pdf, leerlo y reenviar la informacion a la vista
	public function RecibirExcel(){
		# Comprobamos que se trate de una peticion ajax
		if(!$this->input->is_ajax_request()) show_404();

		# Comprobamos que el nombre del cliente haya sido proporcionado
		if($this->input->post('id_cliente') == null || $this->input->post('id_cliente') == '') die(json_encode(array('bandera'=>false, 'msj'=>'Es necesario proporcionar la información del cliente antes de iniciar una nueva cotizacion')));

		# Guardamos el archivo en la carpeta de uploads para futuras referencias
			$nombre = $_FILES['cotizacion']['name'];
			$file = 'uploads/' . $nombre;
			if(!move_uploaded_file($_FILES['cotizacion']['tmp_name'], $file)) exit(json_encode(array('flag'=>false, 'msj'=>'Se presento un error al copiar el archivo de cotización al servidor')));

		# Cargamos la libreria necesaria para leer el archivo de excel cargado al servidor
			$this->load->library('excel');
			$objPHPExcel = PHPExcel_IOFactory::load($file);
			$worksheet = $objPHPExcel->getSheet(0);
			$highestRow = $worksheet->getHighestRow();

		# Iteramos en el contenido del archivo para obtener los items y la descripcion de la cotizacion
			$items = array();
			for ($row = 2; $row <= $highestRow; $row++) {
				if(!is_null($worksheet->getCell('A'.$row)->getValue())){
					array_push($items, array(
						'cve_art'=>$worksheet->getCell('A'.$row)->getValue(),
						'piezas'=>$worksheet->getCell('B'.$row)->getValue(),
						'precioPiezaAD'=>$worksheet->getCell('C'.$row)->getValue(),
					));
				}
			}
			$desc_armada = $worksheet->getCell('D2')->getValue();

		# En caso de que se trate de una cotización armada y no se proporcione la descripcion se finaliza el proceso
			if($this->input->post('tipo') == 'A' && ($desc_armada == null || $desc_armada == '')) die(json_encode(array('bandera'=>false, 'msj'=>'Es necesario proporcionar el nombre del ensamble para poder generar una cotización armada')));

		# Cargamos los modelos necesarios para la transaccion
			$this->load->model('inve03');
			$this->load->model('precio_x_prod03');

		# En caso de que se trate de una cotizacion armada se agregar las partidas por defecto
			$replica = $this->input->post('replica');
			$existentes = $faltantes = array();
			if($this->input->post('tipo') == 'A') {
				array_push($existentes,
					array('cve_art'=>'TUB', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>1, 'partida_armado'=>'S'),
					array('cve_art'=>'RIE', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>2, 'partida_armado'=>'S'),
					array('cve_art'=>'GUI', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>3, 'partida_armado'=>'S'),
					array('cve_art'=>'SUP', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>4, 'partida_armado'=>'S'),
					array('cve_art'=>'TOR', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>5, 'partida_armado'=>'S'),
					array('cve_art'=>'OTR', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>6, 'partida_armado'=>'S')
				);
				# Obtenemos los costos y precios por defecto del diseño, la mano de obra y el flete
				$precio = $this->precio_x_prod03->obtener('ZPROYECTOS02', 2);
				$costo = $this->inve03->obtener('ZPROYECTOS02');
				array_push($existentes, array('cve_art'=>'ZPROYECTOS02', 'piezas'=>0, 'precioPiezaAD'=>count($precio) > 0 ? $precio->PRECIO : 0, 'descripcion'=>'Diseño', 'ult_costo'=>$costo->ULT_COSTO, 'no_partida'=>7, 'partida_armado'=>'S'));
				$precio = $this->precio_x_prod03->obtener('ZPROYECTOS01', 2);
				$costo = $this->inve03->obtener('ZPROYECTOS01');
				array_push($existentes, array('cve_art'=>'ZPROYECTOS01', 'piezas'=>0, 'precioPiezaAD'=>count($precio) > 0 ? $precio->PRECIO : 0, 'descripcion'=>'Mano de Obra', 'ult_costo'=>$costo->ULT_COSTO, 'no_partida'=>8, 'partida_armado'=>'S'));
				$precio = $this->precio_x_prod03->obtener('ZPROYECTOS04', 2);
				$costo = $this->inve03->obtener('ZPROYECTOS04');
				array_push($existentes, array('cve_art'=>'ZPROYECTOS04', 'piezas'=>0, 'precioPiezaAD'=>count($precio) > 0 ? $precio->PRECIO : 0, 'descripcion'=>'Fletes', 'ult_costo'=>$costo->ULT_COSTO, 'no_partida'=>9, 'partida_armado'=>'S'));
			}	

		# Filtramos los productos existentes en la base de datos de los que no y agregamos el ultimo costo y descripcion registrados
			$no = 10;
			foreach ($items as $key => $item) {
				$producto = $this->inve03->obtener($item['cve_art']);
				if(count($producto) > 0){
					array_push($existentes, array(
						'no_partida' => $no,
						'cve_art' => $item['cve_art'],
						'piezas' => $item['piezas'],
						'precioPiezaAD' => $item['precioPiezaAD'],
						'descripcion' => $producto->DESCR,
						'ult_costo' => $producto->ULT_COSTO,
						'partida_armado'=>'N'
					));
					$no++;
				} else{
					array_push($faltantes, $item['cve_art']);
				}
			}

		# Se agregan los campos calculados a las partidas de la precotizacion
			foreach ($existentes as $key => $existente) {
				$existentes[$key]['folio'] = 0;
				$existentes[$key]['descuento'] = 0;
				$existentes[$key]['clasificador'] = '';
				$existentes[$key]['aparece_en_armada'] = 'No';
				$existentes[$key]['precioParteDD'] = $existentes[$key]['precioPiezaAD'] * $existentes[$key]['piezas'];
				$existentes[$key]['replicas'] = $existentes[$key]['piezas'] * $replica;
				$existentes[$key]['precioReplicaAD'] = $existentes[$key]['precioPiezaAD'] * $existentes[$key]['replicas'];
				$existentes[$key]['precioReplicaDD'] = $existentes[$key]['precioReplicaAD'];
				$existentes[$key]['utilidad'] = $existentes[$key]['precioReplicaDD'] > 0 ? 100 * ( $existentes[$key]['precioReplicaDD'] - ($existentes[$key]['ult_costo'] * $existentes[$key]['replicas']) ) / $existentes[$key]['precioReplicaDD'] : 0;
			}

		# Cerramos la conexion a Firebird para abrir la conexion mysql y los modelos necesarios
			$this->db->close();
			$mysql = $this->load->database('mysql');
			$this->load->model('preencabezado');
			$this->load->model('prepartidas');

		# Formamos el data para insertar el preencabezado de la precotizacion
			#$folio = $this->preencabezado->folio()->folio + 1;
			$folio = count($this->preencabezado->folio()) == 0 ? 1 : $this->preencabezado->folio()->folio + 1;
			$data = array(
				'folio' => $folio,
				'id_cliente' => $this->input->post('id_cliente'),
				'nombre_cliente' => $this->input->post('nombre_empresa'),
				'nombre_empresa' => $this->input->post('nombre_empresa'),
				'rfc' => $this->input->post('rfc'),
				'direccion' => $this->input->post('direccion'),
				'colonia' => $this->input->post('colonia'),
				'municipio' => $this->input->post('municipio'),
				'estado' => $this->input->post('estado'),
				'codigo_postal' => $this->input->post('codigo_postal'),
				'nombre_contacto' => $this->input->post('nombre_contacto'),
				'telefono' => $this->input->post('telefono'),
				'correo' => $this->input->post('correo'),
				'representante_ventas' => $this->input->post('representante_ventas'),
				'terminos_y_condiciones' => $this->input->post('terminos_y_condiciones'),
				'observaciones' => $this->input->post('observaciones'),
				'tipo_impresion' => $this->input->post('tipo'),
				'tq' => $this->input->post('tq'),
				'estatus' => 'A',
				'tipo_cambios' => $this->input->post('tc'),
				'replicas' => $replica,
				'descuentost' => $this->input->post('descuento'),
				'tasa_impuesto' => $this->input->post('impuestos'),
				'descripcion_armado' => $desc_armada,
				'created_user' => $this->created_user,
				'updated_user' => $this->updated_user,
				'created_at' => date('Y-m-j H:i:s'),
				'updated_at' => date('Y-m-j H:i:s')
			);
		
		# Iniciamos la transaccion para la operacion del guardado de la cotizacion e insertamos el encabezado de la precotizacion
			$this->db->trans_start();
			$this->preencabezado->alta($data);

		# Insertamos las partidas de la cotizacion
			$stUsdPrecioPDD = $stUsdPrecioRAD = $stUsdPrecioRDD = $costo_total = $utilidad = 0;
			foreach ($existentes as $key => $existente) {
				$existentes[$key]['folio_encabezado'] = $folio;
				$existentes[$key]['estatus'] = 'A';
				$this->prepartidas->alta($existentes[$key]);
				# Calculamos los totales de la precotizacion
				$stUsdPrecioPDD += $existente['precioParteDD'];
				$stUsdPrecioRAD += $existente['precioReplicaAD'];
				$stUsdPrecioRDD += $existente['precioReplicaDD'];
				$costo_total += ($existente['ult_costo'] * $existente['replicas']);
			}

		# Calculamos el resto de totales de la precotizacion
			$descuentoPrecioPDD = $stUsdPrecioPDD * $this->input->post('descuento') / 100;
			$descuentoPrecioRAD = $stUsdPrecioRAD * $this->input->post('descuento') / 100;
			$descuentoPrecioRDD = $stUsdPrecioRDD * $this->input->post('descuento') / 100;
			$stPrecioPDD = $stUsdPrecioPDD - $descuentoPrecioPDD;
			$stPrecioRAD = $stUsdPrecioRAD - $descuentoPrecioRAD;
			$stPrecioRDD = $stUsdPrecioRDD -$descuentoPrecioRDD;
			$ivaPrecioPDD = $stPrecioPDD * $this->input->post('impuestos') / 100;
			$ivaPrecioRAD = $stPrecioRAD * $this->input->post('impuestos') / 100;
			$ivaPrecioRDD = $stPrecioRDD * $this->input->post('impuestos') / 100;
			$totalPrecioPDD = $stPrecioPDD + $ivaPrecioPDD;
			$totalPrecioRAD = $stPrecioRAD + $ivaPrecioRAD;
			$totalPrecioRDD = $stPrecioRDD + $ivaPrecioRDD;
			$utilidad = 100 * ($totalPrecioRDD - $costo_total) / $totalPrecioRDD;

		# Formamos un nuevo data para actualizar los campos de totales de la precotizacion y finalizamos la transaccion
			$data = array(
				'folio' => $folio,
				'stUsdPrecioPDD' => $stUsdPrecioPDD,
				'stUsdPrecioRAD' => $stUsdPrecioRAD,
				'stUsdPrecioRDD' => $stUsdPrecioRDD,
				'descuentoPrecioPDD' => $descuentoPrecioPDD,
				'descuentoPrecioRAD' => $descuentoPrecioRAD,
				'descuentoPrecioRDD' => $descuentoPrecioRDD,
				'stPrecioPDD' => $stPrecioPDD,
				'stPrecioRAD' => $stPrecioRAD,
				'stPrecioRDD' => $stPrecioRDD,
				'ivaPrecioPDD' => $ivaPrecioPDD,
				'ivaPrecioRAD' => $ivaPrecioRAD,
				'ivaPrecioRDD' => $ivaPrecioRDD,
				'totalPrecioPDD' => $totalPrecioPDD,
				'totalPrecioRAD' => $totalPrecioRAD,
				'totalPrecioRDD' => $totalPrecioRDD,
				'utilidad' => $utilidad
			);
			$this->preencabezado->editar($data);
			$this->db->trans_complete();

		# Finalizamos el proceso
		$this->db->trans_status() === FALSE ? exit(json_encode(array('bandera'=>false, 'msj'=>'Se presento un error al generar la precotización'))): exit(json_encode(array('flag'=>true, 'msj'=>'Archivo recibido con éxito', 'data'=>$existentes, 'faltantes'=>$faltantes, 'pre_folio'=>$folio, 'desc_armada'=>$desc_armada)));
	}

	# Metodo para obtener la lista de productos segun su clasificacion
	public function Combo(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros de la peticion ajax
		$clasificador = $this->input->post('clasificador');
		# Cargamos los modelos necesarios
		$this->load->model('inve03');
		# Obtenemos los productos que pertenezcan al clasificador seleccionado
		exit(json_encode($this->inve03->xclasificador($clasificador)));
	}

	# Metodo para obtener una lista de productos seleccionados de un clasificador
	public function Seleccion(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros de la peticion ajax
		$clasificador = $this->input->post('clasificador');
		$valores = $this->input->post('valores');
		# Cargamos los modelos necesarios
		$this->load->model('inve03');
		$this->load->model('precio_x_prod03');
		$productos = $this->inve03->xvalores($clasificador, $valores);
		foreach($productos as $producto) {
			$precio = $this->precio_x_prod03->obtener($producto->CVE_ART, 1);
			$producto->precioPiezaAD = count($precio) > 0 ? $precio->PRECIO : 0;
		}
		# Obtenemos los productos que pertenezcan al clasificador seleccionado
		exit(json_encode($productos));
	}

}
