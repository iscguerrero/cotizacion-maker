<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cotizador extends Base_Controller {
	public function __construct(){
		parent::__construct();
		# Conexion a la base de datos para interactuar con las cotizaciones
			$this->load->database('mysql');
	}
	# Este metodo simplemente se encarga de retornar la vista principal del controlador en la carpeta views
	public function index() {
		$this->load->view('cotizador');
	}
	# Este método se encarga de consumir un web service para obtener el tipo de cambio del dia
	public function ObtenerTC() {
		if(!$this->input->is_ajax_request()) show_404();
		$resultado='';
		$fecha_tc='';
		$tc = '';
		$client = new SoapClient(null, array(
			'location' => 'http://www.banxico.org.mx:80/DgieWSWeb/DgieWS?WSDL',
			'uri' => 'http://DgieWSWeb/DgieWS?WSDL', 
			'encoding' => 'ISO-8859-1',
			'trace' => 1
		));
		try {
			$resultado = $client->tiposDeCambioBanxico(); 
		} catch (SoapFault $exception) {}
		if(!empty($resultado)) {
			$dom = new DomDocument();
			$dom->loadXML($resultado);
			$xmlDatos = $dom->getElementsByTagName("Obs");
			if($xmlDatos->length>1) {
				$item = $xmlDatos->item(1); 
				$fecha_tc = $item->getAttribute('TIME_PERIOD'); 
				$tc = $item->getAttribute('OBS_VALUE'); 
			}
		}
		exit(json_encode(array('flag'=>true, 'tc'=>$tc, 'fecha_tc'=>$fecha_tc)));
	}
	# Este metodo se encarga de mostrar en pantalla el pdf de la cotizacion
	public function ImprimirCotizacion(){
		$this->load->library('pdf');
		$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(40,10,utf8_decode('¡Hola, Mundo!'));
		$pdf->Output();
	}
	# Metodo para guardar los cambios en la cotizacion
	public function GuardarCotizacion(){
		if(!$this->input->is_ajax_request()) show_404();
		# Guardamos los parametros de la peticion el variables locales
		$prefolio = $this->input->post('pre_folio');
		$folio = $this->input->post('folio');
		$cliente = $this->input->post('cliente');
		$encabezado = $this->input->post('encabezado');
		$partidas = $this->input->post('partidas');

		# Cargamos los modelos necesarios para guardar la cotizacion
		$this->load->model('encabezado_cotizacion');
		$this->load->model('partidas_cotizacion_bulk');

		# Obtenemos los datos del cliente
		$data = array(
			'folio_preencabezado' => $prefolio,
			'id_cliente' => $cliente[2]['value'],
			'nombre_cliente' => $cliente[0]['value'],
			'nombre_empresa' => $cliente[1]['value'],
			'rfc' => $cliente[3]['value'],
			'direccion' => $cliente[4]['value'],
			'colonia' => $cliente[5]['value'],
			'municipio' => $cliente[6]['value'],
			'estado' => $cliente[7]['value'],
			'codigo_postal' => $cliente[8]['value'],
			'nombre_contacto' => $cliente[9]['value'],
			'telefono' => $cliente[10]['value'],
			'correo' => $cliente[11]['value'],
			'descuentoPrecioPDD' => str_replace(',', '', $encabezado['descuentoPrecioPDD']),
			'descuentoPrecioRAD' => str_replace(',', '', $encabezado['descuentoPrecioRAD']),
			'descuentoPrecioRDD' => str_replace(',', '', $encabezado['descuentoPrecioRDD']),
			'ivaPrecioPDD' => str_replace(',', '', $encabezado['ivaPrecioPDD']),
			'ivaPrecioRAD' => str_replace(',', '', $encabezado['ivaPrecioRAD']),
			'ivaPrecioRDD' => str_replace(',', '', $encabezado['ivaPrecioRDD']),
			'observaciones' => $encabezado['observaciones'],
			'replicas' => str_replace(',', '', $encabezado['replica']),
			'representante_ventas' => $encabezado['representante'],
			'stUsdPrecioPDD' => str_replace(',', '', $encabezado['stUsdPrecioPDD']),
			'stUsdPrecioRAD' => str_replace(',', '', $encabezado['stUsdPrecioRAD']),
			'stUsdPrecioRDD' => str_replace(',', '', $encabezado['stUsdPrecioRDD']),
			'stMxpPrecioPDD' => str_replace(',', '', $encabezado['stMxpPrecioPDD']),
			'stMxpPrecioRAD' => str_replace(',', '', $encabezado['stMxpPrecioRAD']),
			'stMxpPrecioRDD' => str_replace(',', '', $encabezado['stMxpPrecioRDD']),
			'stPrecioPDD' => str_replace(',', '', $encabezado['stPrecioPDD']),
			'stPrecioRAD' => str_replace(',', '', $encabezado['stPrecioRAD']),
			'stPrecioRDD' => str_replace(',', '', $encabezado['stPrecioRDD']),
			'descuento_sobre_pieza' => $encabezado['std'],
			'tasa_impuesto' => $encabezado['tasa_impuesto'],
			'tipo_cambios' => $encabezado['tc'],
			'terminos_y_condiciones' => $encabezado['terminos'],
			'totalPrecioPDD' => str_replace(',', '', $encabezado['totalPrecioPDD']),
			'totalPrecioRAD' => str_replace(',', '', $encabezado['totalPrecioRAD']),
			'totalPrecioRDD' => str_replace(',', '', $encabezado['totalPrecioRDD']),
			'utilidad' => str_replace(',', '', $encabezado['utilidad']),
			'descuentost' => str_replace(',', '', $encabezado['descuentost']),
			'tipo_impresion' => 'A',
			'created_user' => $this->created_user,
			'updated_user' => $this->updated_user,
			'created_at' => date('Y-m-j H:i:s'),
			'updated_at' => date('Y-m-j H:i:s'),
			'estatus' => 'A'
		);

		$this->db->trans_start();

		if($folio == ''){
			$ultimo_folio = $this->encabezado_cotizacion->obtenerUltimoFolio();
			$folio_encabezado = $ultimo_folio->folio + 1;
			$data['folio'] = $folio_encabezado;
			$this->encabezado_cotizacion->altaEncabezado($data);
			foreach ($partidas as $key => $partida) {
				$data = array(
					'folio_encabezado' => $folio_encabezado,
					'no_partida' => $partida['no_partida'],
					'ult_costo' => $partida['ult_costo'],
					'cve_art' => $partida['cve_art'],
					'descripcion' => $partida['descripcion'],
					'precioPiezaAD' => $partida['precioPiezaAD'],
					'precioPiezaDD' => $partida['precioPiezaDD'],
					'piezas' => $partida['piezas'],
					'descuento' => $partida['descuento'],
					'precioParteDD' => $partida['precioParteDD'],
					'replicas' => $partida['replicas'],
					'precioReplicaAD' => $partida['precioReplicaAD'],
					'precioReplicaDD' => $partida['precioReplicaDD'],
					'estatus' => 'A'
				);
				$this->partidas_cotizacion_bulk->altaPartida($data);
			}
		} else {
			$data['folio'] = $folio;
			$folio_encabezado = $folio;
			unset($data['created_user']);
			unset($data['created_at']);
			$this->encabezado_cotizacion->editarEncabezado($data);
			# Comprobamos si las partidas se tienen que eliminar o actualizar
			$antPartidas = $this->partidas_cotizacion_bulk->obtenerPartidas($folio);

			foreach ($partidas as $key => $partida) {
				$data = array(
					'folio_encabezado' => $folio_encabezado,
					'folio' => $partida['folio'],
					'no_partida' => $partida['no_partida'],
					'ult_costo' => $partida['ult_costo'],
					'cve_art' => $partida['cve_art'],
					'descripcion' => $partida['descripcion'],
					'precioPiezaAD' => $partida['precioPiezaAD'],
					'precioPiezaDD' => $partida['precioPiezaDD'],
					'piezas' => $partida['piezas'],
					'descuento' => $partida['descuento'],
					'precioParteDD' => $partida['precioParteDD'],
					'replicas' => $partida['replicas'],
					'precioReplicaAD' => $partida['precioReplicaAD'],
					'precioReplicaDD' => $partida['precioReplicaDD'],
					'estatus' => 'A'
				);
				if($partida['folio'] == null || $partida['folio'] == ''){
					$this->partidas_cotizacion_bulk->altaPartida($data);
				} else {
					$this->partidas_cotizacion_bulk->editarPartida($data);
					/*$cont = 0;
					foreach ($antPartidas as $antPartida) {
						if($antPartida->folio == $partida['folio']){
							$cont = 1;
						}
					}
					if($cont == 1) {
						$this->partidas_cotizacion_bulk->editarPartida($data);
					} else {
						$data = array('estatus'=>'X', 'folio'=>$partida['folio']);
						$this->partidas_cotizacion_bulk->borrarPartida($data);
					}*/
				}
			}
		}


		$this->db->trans_complete();

		header('Content-type: text/javascript');
		if($this->db->trans_status() === FALSE) {
			echo json_encode(array('bandera'=>false, 'msj'=>'SE PRESENTO UN ERROR AL GENERAR LA PRECOTIZACIÓN'));
		} else {
			echo json_encode(array('bandera'=>true, 'msj'=>'LA COTIZACIÓN SE GUARDO CON EXITO CON FOLIO <strong>' . $folio_encabezado . '</strong>', 'folio'=>$folio_encabezado));
		}
	}

	# Metodo para obtener las partidas de la cotizacion
	public function ObtenerPartidas(){
		if(!$this->input->is_ajax_request()) show_404();
		$folio = $this->input->post('folio');
		$this->load->model('partidas_cotizacion_bulk');
		exit(json_encode($this->partidas_cotizacion_bulk->obtenerPartidas($folio)));
	}

	# Funcion para obtener la lista de cotizaciones en un periodo de tiempo
	public function ObtenerCotizaciones() {
		# Validamos la fechas de consulta
		$this->form_validation->set_rules('fi', 'Fecha Inicial', 'trim|required', array(
			'required' => 'La fecha inicial de la consulta es necesaria'));
		$this->form_validation->set_rules('ff', 'Fecha Final', 'trim|required', array(
			'required' => 'La fecha final de la consulta es necesaria',
		));
		# Retornamos los errrores de validacion en caso de que estos se presente
		if ($this->form_validation->run() === false) {
			exit(json_encode(array('bandera'=>false, 'msj'=>'Las validaciones del formulario no se completaron, atiende:', 'error'=>validation_errors())));
		} else {
			$fi = $this->str_to_date($this->input->post('fi'));
			$ff = $this->str_to_date($this->input->post('ff'));
			$this->load->model('encabezado_cotizacion');
			exit(json_encode($this->encabezado_cotizacion->obtenerCotizaciones($fi, $ff)));
		}
	}

}
