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
		$this->load->model('Producto');
		$this->load->model('Precio');
		# Obtenemos la informacion del producto
		$productos = $this->Producto->obtenerProductoPorNombre($term);
		# Obtenemos el precio de cada uno de los productos devueltos
		foreach ($productos as $producto) {
			# Obtenemos y seteamos el precio del producto
			$precio = $this->Precio->obtenerPrecio($producto->CVE_ART, 2);
			$producto->PRECIO = isset($precio) ? $precio->PRECIO : 0;
			$producto->value = $producto->DESCR;
		}

		exit(json_encode($productos));
	}

	# Este metodo se encarga de recibir la cotizacion en formato pdf, leerlo y reenviar la informacion a la vista
	public function RecibirExcel(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros adicionales con los que se calculara el precio final del producto
		$id_cliente = $this->input->post('id_cliente');
		$nombre_cliente = $this->input->post('nombre_cliente');
		$nombre_empresa = $this->input->post('nombre_empresa');
		$rfc = $this->input->post('rfc');
		$direccion = $this->input->post('direccion');
		$colonia = $this->input->post('colonia');
		$municipio = $this->input->post('municipio');
		$estado = $this->input->post('estado');
		$codigo_postal = $this->input->post('codigo_postal');
		$nombre_contacto = $this->input->post('nombre_contacto');
		$telefono = $this->input->post('telefono');
		$correo = $this->input->post('correo');
		$representante_ventas = $this->input->post('representante_ventas');
		$terminos_y_condiciones = $this->input->post('terminos_y_condiciones');
		$observaciones = $this->input->post('observaciones');
		$estatus  = 'A';
		$tc = $this->input->post('tc');
		$replica = $this->input->post('replica');
		$std = 0;
		$impuestos = $this->input->post('impuestos');
		$descuentost = $this->input->post('descuento');
		$tipo_impresion = $this->input->post('tipo') == 0 ? 'B' : 'A';

		# Comprobamos que el nombre del cliente haya sido proporcionado
		if($id_cliente == null || $id_cliente == '')
			die(json_encode(array('bandera'=>false, 'msj'=>'Es necesario proporcionar la información del cliente antes de iniciar una nueva cotizacion')));

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
					'precioPiezaAD'=>$worksheet->getCell('C'.$row)->getValue(),
				));
			}
		}
		$descripcionArmada = $worksheet->getCell('D2')->getValue();

		if($tipo_impresion == 'A' && ($descripcionArmada == null || $descripcionArmada == ''))
			die(json_encode(array('bandera'=>false, 'msj'=>'Es necesario proporcionar el nombre del ensamble para poder generar una cotización armada')));

		$this->load->model('Producto');
		$existentes = $faltantes = array();

		# Partidas por defecto en la cotizacion armada
		if($tipo_impresion == 'A') {
			array_push($existentes,
				array('folio' =>'', 'cve_art'=>'TUB', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>1, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'),

				array('folio' =>'', 'cve_art'=>'RIE', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>2, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'),

				array('folio' =>'', 'cve_art'=>'GUI', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>3, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'),

				array('folio' =>'', 'cve_art'=>'SUP', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>4, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'),

				array('folio' =>'', 'cve_art'=>'TOR', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>5, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'),

				array('folio' =>'', 'cve_art'=>'OTR', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Vacío', 'ult_costo'=>0, 'no_partida'=>6, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S')
			);
			# Obtenemos los precios por defecto del diseño, la mano de obra y el flete
			$this->load->model('Precio');
			$precio = $this->Precio->obtenerPrecio('ZPROYECTOS02', 2);
			$precio2 = count($precio) > 0 ? $precio->PRECIO : 0;
			$costo = $this->Producto->obtenerProducto('ZPROYECTOS02');
			$costo2 = $costo->ULT_COSTO;
			array_push($existentes, array('folio' =>'', 'cve_art'=>'ZPROYECTOS02', 'piezas'=>0, 'precioPiezaAD'=>$precio2, 'descripcion'=>'Diseño', 'ult_costo'=>$costo2, 'no_partida'=>7, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'));

			$precio = $this->Precio->obtenerPrecio('ZPROYECTOS01', 2);
			$precio2 = count($precio) > 0 ? $precio->PRECIO : 0;
			$costo = $this->Producto->obtenerProducto('ZPROYECTOS01');
			$costo2 = $costo->ULT_COSTO;
			array_push($existentes, array('folio' =>'', 'cve_art'=>'ZPROYECTOS01', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Mano de Obra', 'ult_costo'=>$costo2, 'no_partida'=>8, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'));

			$precio = $this->Precio->obtenerPrecio('ZPROYECTOS04', 2);
			$precio2 = count($precio) > 0 ? $precio->PRECIO : 0;
			$costo = $this->Producto->obtenerProducto('ZPROYECTOS04');
			$costo2 = $costo->ULT_COSTO;
			array_push($existentes, array('folio' =>'', 'cve_art'=>'ZPROYECTOS04', 'piezas'=>0, 'precioPiezaAD'=>0, 'descripcion'=>'Fletes', 'ult_costo'=>$costo2, 'no_partida'=>9, 'precioPiezaDD'=>0, 'descuento'=>$std, 'replicas'=>$replica, 'precioParteDD'=>0, 'precioReplicaAD'=>0, 'precioReplicaDD'=>0, 'utilidad'=>0, 'partida_armado'=>'S'));
		}



		# Se agrega la informacion necesaria de la base de datos y se filtran los productos existentes en el catalogo
		$no = 10;
		foreach ($items as $key => $item) {
			$producto = json_decode(json_encode($this->Producto->obtenerProducto($item['cve_art'])), TRUE);
			if(count($producto) > 0){
				array_push($existentes, array(
					'folio' =>'',
					'no_partida' => $no,
					'cve_art' => $item['cve_art'],
					'piezas' => $item['piezas'],
					'precioPiezaAD' => $item['precioPiezaAD'],
					'descripcion' => $producto['DESCR'],
					'ult_costo' => $producto['ULT_COSTO'],
					'partida_armado'=>'N'
				));
				$no++;
			} else{
				array_push($faltantes, $item['cve_art']);
			}
		}
		# Se agregan los campos calculados
		
		foreach ($existentes as $key => $existente) {
			$existentes[$key]['precioPiezaDD'] = $existentes[$key]['precioPiezaAD'] - ( $existentes[$key]['precioPiezaAD'] * $std / 100 );
			$existentes[$key]['descuento'] = $std;
			$existentes[$key]['replicas'] = $existentes[$key]['piezas'] * $replica;
			$existentes[$key]['precioParteDD'] = $existentes[$key]['precioPiezaDD'] * $existentes[$key]['piezas'];
			$existentes[$key]['precioReplicaAD'] = $existentes[$key]['precioPiezaAD'] * $existentes[$key]['replicas'];
			$existentes[$key]['precioReplicaDD'] = $existentes[$key]['precioPiezaDD'] * $existentes[$key]['replicas'];
			$existentes[$key]['utilidad'] = $existentes[$key]['precioReplicaDD'] - ($existentes[$key]['ult_costo'] * $existentes[$key]['replicas']);
		}

		$this->db->close();
		$mysql = $this->load->database('mysql');
		$this->load->model('preencabezado_cotizacion');
		$this->load->model('prepartidas_cotizacion_bulk');

		$ultimo_folio = $this->preencabezado_cotizacion->obtenerUltimoFolio();
		$folio_encabezado = $ultimo_folio->folio + 1;

		$data = array(
			'folio' => $folio_encabezado,
			'id_cliente' => $id_cliente,
			'nombre_cliente' => $nombre_cliente,
			'nombre_empresa' => $nombre_empresa,
			'rfc' => $rfc,
			'direccion' => $direccion,
			'colonia' => $colonia,
			'municipio' => $municipio,
			'estado' => $estado,
			'codigo_postal' => $codigo_postal,
			'nombre_contacto' => $nombre_contacto,
			'telefono' => $telefono,
			'correo' => $correo,
			'representante_ventas' => $representante_ventas,
			'terminos_y_condiciones' => $terminos_y_condiciones,
			'observaciones' => $observaciones,
			'tipo_impresion' => $tipo_impresion,
			'estatus' => $estatus,
			'tipo_cambios' => $tc,
			'replicas' => $replica,
			'descuento_sobre_pieza' => $std,
			'descuentost' => $descuentost,
			'descripcion_armado' => $descripcionArmada,
			'created_user' => $this->created_user,
			'updated_user' => $this->updated_user,
			'created_at' => date('Y-m-j H:i:s'),
			'updated_at' => date('Y-m-j H:i:s')
		);

		$no_partida = 1;
		$stUsdPrecioPDD = 0;
		$stUsdPrecioRAD = 0;
		$stUsdPrecioRDD = 0;
		$costo_total = 0;
		$utilidad = 0;
		$this->db->trans_start();
		$this->preencabezado_cotizacion->altaPreencabezado($data);
		foreach ($existentes as $key => $existente) {
			$data = array(
				'folio_encabezado' => $folio_encabezado,
				'no_partida' => $existente['no_partida'],
				'ult_costo' => $existente['ult_costo'],
				'cve_art' => $existente['cve_art'],
				'descripcion' => $existente['descripcion'],
				'precioPiezaAD' => $existente['precioPiezaAD'],
				'precioPiezaDD' => $existente['precioPiezaDD'],
				'piezas' => $existente['piezas'],
				'descuento' => $existente['descuento'],
				'precioParteDD' => $existente['precioParteDD'],
				'replicas' => $existente['replicas'],
				'precioReplicaAD' => $existente['precioReplicaAD'],
				'precioReplicaDD' => $existente['precioReplicaDD'],
				'utilidad' => $existente['utilidad'],
				'partida_armado' => $existente['partida_armado'],
				'estatus' => 'A'
			);
			$this->prepartidas_cotizacion_bulk->altaPrePartida($data);
			$no_partida++;
			$stUsdPrecioPDD += $existente['precioParteDD'];
			$stUsdPrecioRAD += $existente['precioReplicaAD'];
			$stUsdPrecioRDD += $existente['precioReplicaDD'];
			$costo_total += ($existente['ult_costo'] * $existente['replicas']);
		}

		$descuentoPrecioPDD = $stUsdPrecioPDD * $descuentost / 100;
		$descuentoPrecioRAD = $stUsdPrecioRAD * $descuentost / 100;
		$descuentoPrecioRDD = $stUsdPrecioRDD * $descuentost / 100;

		$stPrecioPDD = $stUsdPrecioPDD - $descuentoPrecioPDD;
		$stPrecioRAD = $stUsdPrecioRAD - $descuentoPrecioRAD;
		$stPrecioRDD = $stUsdPrecioRDD -$descuentoPrecioRDD;

		$ivaPrecioPDD = $stPrecioPDD * $impuestos / 100;
		$ivaPrecioRAD = $stPrecioRAD * $impuestos / 100;
		$ivaPrecioRDD = $stPrecioRDD * $impuestos / 100;

		$totalPrecioPDD = $stPrecioPDD + $ivaPrecioPDD;
		$totalPrecioRAD = $stPrecioRAD + $ivaPrecioRAD;
		$totalPrecioRDD = $stPrecioRDD + $ivaPrecioRDD;

		$utilidad = ($totalPrecioRDD * 100 / $costo_total) - 100;

		$data = array(
			'folio' => $folio_encabezado,
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
			'utilidad' => $utilidad,
			'tasa_impuesto' => $impuestos
		);
		$this->preencabezado_cotizacion->editarPreencabezado($data);

		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE)
			exit(json_encode(array('bandera'=>false, 'msj'=>'SE PRESENTO UN ERROR AL GENERAR LA PRECOTIZACIÓN')));

		exit(json_encode(array('flag'=>true, 'msj'=>'ARCHIVO RECIBIDO CON EXITO', 'data'=>$existentes, 'faltantes'=>$faltantes, 'pre_folio'=>$folio_encabezado)));
	}

	# Metodo para obtener la lista de productos segun su clasificacion
	public function Combo(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros de la peticion ajax
		$clasificador = $this->input->post('clasificador');
		# Cargamos los modelos necesarios
		$this->load->model('Producto');
		# Obtenemos los productos que pertenezcan al clasificador seleccionado
		exit(json_encode($this->Producto->obtenerPorAgrupador($clasificador)));
	}

	# Metodo para obtener una lista de productos seleccionados de un clasificador
	public function Seleccion(){
		if(!$this->input->is_ajax_request()) show_404();
		# Obtenemos los parametros de la peticion ajax
		$agrupador = $this->input->post('agrupador');
		$valores = $this->input->post('valores');
		# Cargamos los modelos necesarios
		$this->load->model('Producto');
		$this->load->model('Precio');
		$productos = $this->Producto->obtenerSeleccion($agrupador, $valores);
		foreach($productos as $producto) {
			$precio = $this->Precio->obtenerPrecio($producto->CVE_ART, 2);
			if(count($precio) > 0)
				$producto->precioPiezaAD = $precio->PRECIO;
			else
				$producto->precioPiezaAD = 0;
		}
		# Obtenemos los productos que pertenezcan al clasificador seleccionado
		exit(json_encode($productos));
	}

}
