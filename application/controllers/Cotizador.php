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
	public function ImprimirCotizacion($folio){
		$this->load->library('Pdf');
		$pdf = new Pdf('P', 'mm', 'Letter');

		$pdf->SetMargins(15, 15 , 15);
		$pdf->SetAutoPageBreak(false, 15); 
		$pdf->AliasNbPages();
		$pdf->SetFillColor(192, 192, 192);
		$pdf->SetDrawColor(192, 192, 192);

		# Obtenemos la informacion de la cotizacion
		$this->load->model('encabezado_cotizacion');
		$this->load->model('partidas_cotizacion_bulk');
		$encabezado = $this->encabezado_cotizacion->obtenerEncabezadoPdf($folio);
		$partidas = $this->partidas_cotizacion_bulk->obtenerPartidas($folio);

		$this->nuevaPaginaBulk($pdf, $encabezado, $partidas);

		$pdf->Output();
	}

	# Funcion para agregar una nueva pagina a la cotizacion bulk
	public function nuevaPaginaBulk($pdf, $encabezado, $partidas) {
		$pdf->AddPage();

		# Cuadro superior izquierda
			$pdf->RoundedRect(15, 30, 95, 5, 1, 'DF', '12');
			$pdf->RoundedRect(15, 35, 95, 25, 1, 'D', '');
			$pdf->RoundedRect(15, 60, 95, 5, 1, 'DF', '');
			$pdf->RoundedRect(15, 65, 95, 5, 1, 'D', '34');

		# Cuadro superior derecho
			$pdf->RoundedRect(115, 30, 95, 5, 1, 'DF', '12');
			$pdf->RoundedRect(115, 35, 25, 5, 1, 'D', '');
			$pdf->RoundedRect(140, 35, 35, 5, 1, 'D', '');
			$pdf->RoundedRect(175, 35, 35, 5, 1, 'D', '');
			$pdf->RoundedRect(115, 40, 95, 5, 1, 'DF', '');
			$pdf->RoundedRect(115, 45, 95, 25, 1, 'D', '34');

		# Cuadro inferior donde ira el contenido de la orden de compra
			$pdf->RoundedRect(15, 75, 195, 5, 1, 'DF', '12');
			$pdf->RoundedRect(15, 80, 195, 125, 1, 'D', '34');

		# Pintamos las partidas de la orden de compra
			$pdf->Line(25, 80, 25, 205);
			$pdf->Line(45, 80, 45, 205);
			$pdf->Line(117, 80, 117, 205);
			$pdf->Line(142, 80, 142, 205);
			$pdf->Line(163, 80, 163, 205);
			$pdf->Line(185, 80, 185, 205);

		# Firmas de pie de página
			$pdf->Line(20, 220, 80, 220);
			$pdf->Line(90, 220, 150, 220);

		# Cuadro totales
			$pdf->RoundedRect(155, 205, 55, 35, 1, 'D', '34');

		# Leyendas del formato / encabezado
			$pdf->SetFont('Courier', 'B', 14);
			$pdf->setXY(15, 20); $pdf->Cell(0, 5, utf8_decode('COTIZACIÓN'), 0, 0, 'C', false);
			$pdf->SetFont('Courier', 'B', 11);
			$pdf->setXY(15, 30); $pdf->Cell(95, 5, utf8_decode('Información del cliente'), 0, 0, 'L', false);
			$pdf->setXY(15, 60); $pdf->Cell(95, 5, 'Representante de ventas', 0, 0, 'L', false);
			$pdf->setXY(115, 30); $pdf->Cell(25, 5, 'T. C.', 0, 0, 'C', false);
			$pdf->setXY(140, 30); $pdf->Cell(35, 5, 'Folio', 0, 0, 'C', false);
			$pdf->setXY(175, 30); $pdf->Cell(35, 5, 'Fecha', 0, 0, 'C', false);
			$pdf->setXY(115, 40); $pdf->Cell(95, 5, utf8_decode('Términos y condiciones de venta'), 0, 0, 'L', false);

		# Leyendas del formato / partidas
			$pdf->SetFont('Courier', 'B', 10);
			$pdf->setXY(15, 75); $pdf->Cell(10, 5, '#', 1, 0, 'C', false);
			$pdf->Cell(20, 5, utf8_decode('Código'), 1, 0, 'C', false);
			$pdf->Cell(72, 5, utf8_decode('Descripción'), 1, 0, 'L', false);
			$pdf->Cell(25, 5, 'Precio U.', 1, 0, 'R', false);
			$pdf->Cell(21, 5, 'Cantidad', 1, 0, 'R', false);
			$pdf->Cell(22, 5, 'Descuento', 1, 0, 'R', false);
			$pdf->Cell(25, 5, 'Total', 1, 1, 'R', false);

		# Leyendas del pie de página
			$pdf->SetFont('Courier', 'B', 9);
			$pdf->setXY(155, 205); $pdf->Cell(22, 7, 'Subtotal USD', 0, 0, 'L', false);
			$pdf->setXY(155, 212); $pdf->Cell(22, 7, 'Subtotal MXN', 0, 0, 'L', false);
			$pdf->setXY(155, 219); $pdf->Cell(22, 7, 'Descuento ST %', 0, 0, 'L', false);
			$pdf->setXY(155, 226); $pdf->Cell(22, 7, 'Impuesto %', 0, 0, 'L', false);
			$pdf->setXY(155, 233); $pdf->Cell(22, 7, 'Total', 0, 0, 'L', false);

		# Se descarga la informacion de la cotizacion
			$pdf->SetFont('Courier', '', 9);
			$pdf->setXY(15, 35); $pdf->Cell(95, 5, utf8_decode($encabezado->nombre_cliente), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('ID: ' . $encabezado->id_cliente . ', RFC: ' . $encabezado->rfc), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('Contacto: ' . $encabezado->nombre_contacto), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('Telelefono: '.$encabezado->telefono), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('Correo: '. $encabezado->correo), 0, 1, 'L', false);
			$pdf->Ln(5);
			$pdf->Cell(95, 5, utf8_decode($encabezado->representante_ventas), 0, 0, 'L', false);

			$pdf->setXY(115, 35); $pdf->Cell(25, 5, utf8_decode($encabezado->tipo_cambios), 0, 0, 'C', false);
			$pdf->Cell(35, 5, str_pad($encabezado->folio, '0', STR_PAD_LEFT), 0, 0, 'C', false);
			$pdf->Cell(35, 5, utf8_decode($encabezado->ffecha), 0, 1, 'C', false);
			$pdf->Ln();
			$pdf->setX(115); $pdf->MultiCell(95, 5, utf8_decode($encabezado->terminos_y_condiciones), 0, 'J', false);

			$pdf->setXY(20, 215); $pdf->Cell(60, 5, utf8_decode($encabezado->representante_ventas), 0, 0, 'C', false);
			$pdf->setXY(20, 220); $pdf->Cell(60, 5, 'Representante de ventas', 0, 0, 'C', false);
			$pdf->setXY(90, 220); $pdf->Cell(60, 5, 'Vo. Bo.', 0, 0, 'C', false);
			$pdf->setXY(20, 225); $pdf->Cell(130, 5, utf8_decode('Observaciones: ' . $encabezado->observaciones), 0, 0, 'J', false);

			$pdf->SetFont('Courier', 'B', 9);
			$pdf->setXY(155, 205); $pdf->Cell(55, 7, number_format($encabezado->stUsdPrecioRDD, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 212); $pdf->Cell(55, 7, number_format($encabezado->stMxpPrecioRDD, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 219); $pdf->Cell(55, 7, number_format($encabezado->descuentost, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 226); $pdf->Cell(55, 7, number_format($encabezado->tasa_impuesto, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 233); $pdf->Cell(55, 7, number_format($encabezado->totalPrecioRDD, 2), 1, 0, 'R', false);

		# Se descarga la informacion de las partidas de la cotizacion
			$pdf->SetFont('Courier', '', 9);
			$pdf->SetWidths(array(10, 20, 72, 25, 21, 22, 25));
			$pdf->SetAligns(array('C', 'C', 'L', 'R', 'R', 'R', 'R'));
			$pdf->setXY(15, 80);
			foreach ($partidas as $key => $partida) {
				$pdf->Row(array(($partida->no_partida)*1, $partida->cve_art, $partida->descripcion, number_format($partida->precioPiezaAD, 2), $partida->replicas, number_format($partida->descuento, 1) . ' %', number_format($partida->precioReplicaDD, 2)));
			}

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
		$folios = $this->input->post('folios');

		# Comprobamos que el nombre del cliente haya sido proporcionado
		if($cliente[2]['value'] == null || $cliente[2]['value'] == '')
			die(json_encode(array('bandera'=>false, 'msj'=>'Es necesario proporcionar la información del cliente antes de iniciar una nueva cotizacion')));

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
				$partida['folio'] == null || $partida['folio'] == '' ? $this->partidas_cotizacion_bulk->altaPartida($data) : $this->partidas_cotizacion_bulk->editarPartida($data);
				foreach ($folios as $folio) {
					$this->partidas_cotizacion_bulk->borrarPartida($folio);
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

	# Obtenemos el encabezado de la cotizacion
	public function ObtenerEncabezado() {
		if(!$this->input->is_ajax_request()) show_404();
		$folio = $this->input->post('folio');
		$this->load->model('encabezado_cotizacion');
		exit(json_encode(array('bandera'=>true, 'encabezado'=>$this->encabezado_cotizacion->obtenerEncabezado($folio))));
	}

}
