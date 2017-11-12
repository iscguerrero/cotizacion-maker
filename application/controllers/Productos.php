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
			$producto->PRECIO = isset($precio[0]) ? $precio[0]->PRECIO : 0;
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
		$tipo_impresion = 'A';
		$estatus  = 'A';
		$tc = $this->input->post('tc');
		$replica = $this->input->post('replica');
		$std = $this->input->post('std');
		$impuestos = $this->input->post('impuestos');
		$descuentost = $this->input->post('descuento');

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
					'ult_costo' => $producto[0]['ULT_COSTO'],
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
			$existentes[$key]['no_partida'] = $no;
			$existentes[$key]['precioPiezaDD'] = $existentes[$key]['precioPiezaAD'] - ( $existentes[$key]['precioPiezaAD'] * $std / 100 );
			$existentes[$key]['descuento'] = $std;
			$existentes[$key]['replicas'] = $existentes[$key]['piezas'] * $replica;
			$existentes[$key]['precioParteDD'] = $existentes[$key]['precioPiezaDD'] * $existentes[$key]['piezas'];
			$existentes[$key]['precioReplicaAD'] = $existentes[$key]['precioPiezaAD'] * $existentes[$key]['replicas'];
			$existentes[$key]['precioReplicaDD'] = $existentes[$key]['precioPiezaDD'] * $existentes[$key]['replicas'];
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
		#$folio_encabezado = $this->db->insert_id();
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

		$stMxpPrecioPDD = $stUsdPrecioPDD * $tc;
		$stMxpPrecioRAD = $stUsdPrecioRAD * $tc;
		$stMxpPrecioRDD = $stUsdPrecioRDD * $tc;

		$utilidad = ($totalPrecioRDD * 100 / $costo_total) - 100;

		$data = array(
			'folio' => $folio_encabezado,
			'stUsdPrecioPDD' => $stUsdPrecioPDD,
			'stUsdPrecioRAD' => $stUsdPrecioRAD,
			'stUsdPrecioRDD' => $stUsdPrecioRDD,
			'stMxpPrecioPDD' => $stMxpPrecioPDD,
			'stMxpPrecioRAD' => $stMxpPrecioRAD,
			'stMxpPrecioRDD' => $stMxpPrecioRDD,
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

		foreach ($existentes as $key => $existente) {
			$existentes[$key]['partida_armado'] = 'N';
		}

		exit(json_encode(array('flag'=>true, 'msj'=>'ARCHIVO RECIBIDO CON EXITO', 'data'=>$existentes, 'faltantes'=>$faltantes, 'pre_folio'=>$folio_encabezado)));
	}
}
