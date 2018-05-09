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
		$data['tipos'] = $this->session->userdata('tipo_usuario') == 'agente' ? array(
			array('value'=>'', 'text'=>'Selecciona...'),
			array('value'=>'B', 'text'=>'Bulk')
		) : array(
			array('value'=>'', 'text'=>'Selecciona...'),
			array('value'=>'B', 'text'=>'Bulk'),
			array('value'=>'A', 'text'=>'Armada')
		);
		$data['tipo_usuario'] = $this->session->userdata('tipo_usuario');
		$this->load->view('cotizador', $data);
	}

	public function Condiciones() {
		$this->load->view('terminos');
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

		# Comprobamos que las partidas tengan los datos suficientes para ser procesados
		foreach ($partidas as $key => $partida) {
			if(in_array($partida['cve_art'], array('TUB', 'RIE', 'GUI', 'SUP', 'TOR', 'OTR'))) {
				if($partida['descripcion'] == 'Vacío') die(json_encode(array('bandera'=>false, 'msj'=>'Debes seleccionar al menos un item en las partidas armadas o en su defecto la opción "Ninguno", partida: ' . $partida['no_partida']*1)));
			} else {
				if($partida['precioPiezaAD'] == 0) die(json_encode(array('bandera'=>false, 'msj'=>'El precio de venta debe ser mayor a cero en cada una de las partidas, partida: ' . $partida['no_partida']*1)));
				if($partida['piezas'] == 0) die(json_encode(array('bandera'=>false, 'msj'=>'El número de piezas debe ser mayor a cero en cada una de las partidas, partida: ' . $partida['no_partida']*1)));
			}
		}

		# Comprobamos que el nombre del cliente haya sido proporcionado
		if($cliente[1]['value'] == null || $cliente[1]['value'] == '')
			die(json_encode(array('bandera'=>false, 'msj'=>'Es necesario proporcionar la información del cliente antes de iniciar una nueva cotizacion')));

		# Cargamos los modelos necesarios para guardar la cotizacion
		$this->load->model('encabezado');
		$this->load->model('partidas');

		# Obtenemos los datos del cliente
		$data = array(
			'folio_preencabezado' => $prefolio,
			'id_cliente' => $cliente[1]['value'],
			'nombre_cliente' => $cliente[2]['value'],
			'nombre_empresa' => $cliente[2]['value'],
			'rfc' => $cliente[3]['value'],
			'estado' => $cliente[4]['value'],
			'municipio' => $cliente[5]['value'],
			'colonia' => $cliente[6]['value'],
			'codigo_postal' => $cliente[7]['value'],
			'direccion' => $cliente[8]['value'],
			'tq' => $cliente[9]['value'],
			'nombre_contacto' => $cliente[10]['value'],
			'telefono' => $cliente[11]['value'],
			'correo' => $cliente[12]['value'],
			'area' => $cliente[13]['value'],
			'descuentoPrecioPDD' => str_replace(',', '', $encabezado['descuentoPrecioPDD']),
			'descuentoPrecioRAD' => str_replace(',', '', $encabezado['descuentoPrecioRAD']),
			'descuentoPrecioRDD' => str_replace(',', '', $encabezado['descuentoPrecioRDD']),
			'ivaPrecioPDD' => str_replace(',', '', $encabezado['ivaPrecioPDD']),
			'ivaPrecioRAD' => str_replace(',', '', $encabezado['ivaPrecioRAD']),
			'ivaPrecioRDD' => str_replace(',', '', $encabezado['ivaPrecioRDD']),
			'observaciones' => $encabezado['observaciones'],
			'replicas' => str_replace(',', '', $encabezado['replica']),
			'representante_ventas' => $encabezado['representante'],
			'descripcion_armado' => $encabezado['descArmada'],
			'stUsdPrecioPDD' => str_replace(',', '', $encabezado['stUsdPrecioPDD']),
			'stUsdPrecioRAD' => str_replace(',', '', $encabezado['stUsdPrecioRAD']),
			'stUsdPrecioRDD' => str_replace(',', '', $encabezado['stUsdPrecioRDD']),
			'stPrecioPDD' => str_replace(',', '', $encabezado['stPrecioPDD']),
			'stPrecioRAD' => str_replace(',', '', $encabezado['stPrecioRAD']),
			'stPrecioRDD' => str_replace(',', '', $encabezado['stPrecioRDD']),
			'tasa_impuesto' => $encabezado['tasa_impuesto'],
			'tipo_cambios' => $encabezado['tc'],
			'terminos_y_condiciones' => $encabezado['terminos'],
			'totalPrecioPDD' => str_replace(',', '', $encabezado['totalPrecioPDD']),
			'totalPrecioRAD' => str_replace(',', '', $encabezado['totalPrecioRAD']),
			'totalPrecioRDD' => str_replace(',', '', $encabezado['totalPrecioRDD']),
			'descuento_total' => ((str_replace(',', '', $encabezado['stPrecioRDD']) / str_replace(',', '', $encabezado['stUsdPrecioRAD']) * 100) - 100) * -1,
			'utilidad' => str_replace(',', '', $encabezado['utilidad']),
			'descuentost' => str_replace(',', '', $encabezado['descuentost']),
			'tipo_impresion' => $encabezado['tipo'],
			'created_user' => $this->created_user,
			'updated_user' => $this->updated_user,
			'created_at' => date('Y-m-j H:i:s'),
			'updated_at' => date('Y-m-j H:i:s'),
			'estatus' => 'A'
		);

		$this->db->trans_start();

		if($folio == ''){
			# Damos de alta el encabezado de la cotizacion
			$folio = count($this->encabezado->folio()) == 0 ? 1: $this->encabezado->folio()->folio + 1;
			$data['folio'] = $folio;
			$this->encabezado->alta($data);
			# Damos de al ta las partidas de la cotizacion
			foreach ($partidas as $key => $partida) {
				$data = array(
					'folio_encabezado' => $folio,
					'no_partida' => $partida['no_partida'],
					'ult_costo' => $partida['ult_costo'],
					'cve_art' => $partida['cve_art'],
					'descripcion' => $partida['descripcion'],
					'precioPiezaAD' => $partida['precioPiezaAD'],
					'piezas' => $partida['piezas'],
					'descuento' => $partida['descuento'],
					'precioParteDD' => $partida['precioParteDD'],
					'replicas' => $partida['replicas'],
					'precioReplicaAD' => $partida['precioReplicaAD'],
					'precioReplicaDD' => $partida['precioReplicaDD'],
					'estatus' => 'A',
					'clasificador' => $partida['clasificador'],
					'partida_armado' => $partida['partida_armado'],
					'utilidad' => $partida['utilidad'],
					'aparece_en_armada' => $partida['aparece_en_armada']
				);
				$this->partidas->alta($data);
			}
		} else {
			# Actualizamos el encabezado de la cotizacion
			$data['folio'] = $folio;
			$data['estatus'] = $encabezado['estatus'];
			unset($data['created_user']);
			unset($data['created_at']);

			$this->encabezado->editar($data);
			# Editamos, damos de alta y cancelamos las partidas correspondientes de la cotizacion
			foreach ($partidas as $key => $partida) {
				$data = array(
					'folio_encabezado' => $folio,
					'folio' => $partida['folio'],
					'no_partida' => $partida['no_partida'],
					'ult_costo' => $partida['ult_costo'],
					'cve_art' => $partida['cve_art'],
					'descripcion' => $partida['descripcion'],
					'precioPiezaAD' => $partida['precioPiezaAD'],
					'piezas' => $partida['piezas'],
					'descuento' => $partida['descuento'],
					'precioParteDD' => $partida['precioParteDD'],
					'replicas' => $partida['replicas'],
					'precioReplicaAD' => $partida['precioReplicaAD'],
					'precioReplicaDD' => $partida['precioReplicaDD'],
					'estatus' => 'A',
					'clasificador' => $partida['clasificador'],
					'partida_armado' => $partida['partida_armado'],
					'utilidad' => $partida['utilidad'],
					'aparece_en_armada' => $partida['aparece_en_armada']
				);
				$partida['folio'] == null || $partida['folio'] == '' ? $this->partidas->alta($data) : $this->partidas->editar($data);
				if(count($folios) > 0) {
					foreach ($folios as $xfolio) {
						$this->partidas->borrar($xfolio);
					}
				}
			}
		}
		$this->db->trans_complete();

		$this->db->trans_status() === FALSE ? exit(json_encode(array('bandera'=>false, 'msj'=>'SE PRESENTO UN ERROR AL GENERAR LA PRECOTIZACIÓN'))): exit(json_encode(array('bandera'=>true, 'msj'=>'LA COTIZACIÓN SE GUARDO CON EXITO CON FOLIO <strong>' . $folio . '</strong>', 'folio'=>$folio)));
	}

	# Obtenemos el encabezado de la cotizacion
	public function ObtenerEncabezado() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('encabezado');
		exit(json_encode(array('bandera'=>true, 'encabezado'=>$this->encabezado->obtener($this->input->post('folio')), 'tipo_usuario'=>$this->session->userdata('tipo_usuario'))));
	}

	# Metodo para obtener las partidas de la cotizacion
	public function ObtenerPartidas(){
		if(!$this->input->is_ajax_request()) show_404();
		$folio = $this->input->post('folio');
		$this->load->model('partidas');
		$partidas = $this->partidas->obtener($folio);
		$tubos = $rieles = $guias = $superficies = $tornillos = $otros = array();
		foreach ($partidas as $partida) {
			if( $partida->clasificador == 'TUB' ) array_push($tubos, $partida->cve_art);
			if( $partida->clasificador == 'RIE' ) array_push($rieles, $partida->cve_art);
			if( $partida->clasificador == 'GUI' ) array_push($guias, $partida->cve_art);
			if( $partida->clasificador == 'SUP' ) array_push($superficies, $partida->cve_art);
			if( $partida->clasificador == 'TOR' ) array_push($tornillos, $partida->cve_art);
			if( $partida->clasificador == 'OTR' ) array_push($otros, $partida->cve_art);
		}
		exit(json_encode(array('partidas' => $partidas, 'tubos'=>$tubos, 'rieles'=>$rieles, 'guias'=>$guias, 'superficies'=>$superficies, 'tornillos'=>$tornillos, 'otros'=>$otros)));
	}

	# Metodo para obtener la url de las imagenes asociadas a la cotizacion
	public function ObtenerImagenes() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('imagenes');
		$imagenes = $this->imagenes->listar($this->input->post('folio'));
		foreach ($imagenes as $imagen)
			$imagen->url = base_url('uploads/' . $imagen->nombre_unico);
		exit(json_encode($imagenes));
	}

	# Metodo para cargar una nueva imagen a la cotizacion
	public function RecibirImagen(){
		if(!$this->input->is_ajax_request()) show_404();

		if($this->input->post('folio') == '' && $this->input->post('pre_folio') == '') exit(json_encode(array('bandera'=>false, 'msj'=>'Abre o crea una nueva cotización para poder cargar imágenes')));

		# Guardamos el archivo en la carpeta de uploads para futuras referencias
		$nombre = $_FILES['imagen']['name'];
		$ext = pathinfo($nombre, PATHINFO_EXTENSION);
		$nombre_unico = date('ljSFYhisA') . '.' . $ext;
		$file = 'uploads/' . $nombre_unico;
		if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $file)) exit(json_encode(array('bandera'=>false, 'msj'=>'Se presento un error al cargar la imagen a la cotizacion')));

		$data = array(
			'folio_encabezado' => $this->input->post('folio'),
			'folio_preencabezado' => $this->input->post('pre_folio'),
			'nombre_original' => $nombre,
			'nombre_unico' => $nombre_unico,
			'estatus' => 'A'
		);
		$this->load->model('imagenes');
		$imagenes = $this->imagenes->alta($data);

		exit(json_encode(array('bandera'=>true, 'msj'=>'Imagen cargada con éxito')));
	}

	# Metodo para borrar una imagen de la cotizacion
	public function BorrarImagen() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('imagenes');
		$this->imagenes->borrar($folio = $this->input->post('folio')) ? exit(json_encode(array('bandera' => true))): exit(json_encode(array('bandera' => false)));
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
			$estatus = $this->input->post('estatus');
			$this->load->model('encabezado');
			exit(json_encode($this->encabezado->listar($fi, $ff, $estatus)));
		}
	}

	# Metodo para cerrar una cotizacion
	public function CerrarCotizacion() {
		if(!$this->input->is_ajax_request()) show_404();
		$folio = $this->input->post('folio');
		$this->load->model('encabezado');
		if( $this->encabezado->editar(array('folio'=>$folio, 'estatus'=>'H')) ) {
			exit(json_encode(array('bandera' => true)));
		} else {
			exit(json_encode(array('bandera' => false, 'msj'=>'Se presento un error al cerrar la cotización')));
		}
	}

	# Metodo para autorizar una cotizacion
	public function CambiarEstado() {
		if(!$this->input->is_ajax_request()) show_404();
		/*if($this->session->userdata('tipo_usuario') != 'diseñadores') {
			exit(json_encode(array('bandera' => false, 'msj'=>'No cuentas con los permisos necesarios para realizar esta acción')));
		}*/
		$folio = $this->input->post('folio');
		$estatus = $this->input->post('estatus');

		$data = array(
			'folio'=>$folio,
			'estatus'=>$estatus
		);

		if($data['estatus'] == 'D') {
			$data['fase_uno_usuario_autorizacion'] = $this->created_user;
			$data['fase_uno_fecha_autorizacion'] = date('Y-m-d');
		}
		if($data['estatus'] == 'G') {
			$data['fase_dos_usuario_autorizacion'] = $this->created_user;
			$data['fase_dos_fecha_autorizacion'] = date('Y-m-d');
		}


		$this->load->model('encabezado');
		$this->encabezado->editar($data) ? exit(json_encode(array('bandera' => true))): exit(json_encode(array('bandera' => false, 'msj'=>'Se presento un error al cambiar el estatus de la cotización')));
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

		# Instanciamos los modelos necesarios para obtener la información de la cotizacion
		$this->load->model('encabezado');
		$this->load->model('partidas');
		$this->load->model('imagenes');
		$this->load->model('terminosycondiciones');
		$encabezado = $this->encabezado->obtener($folio);
		$partidas = $this->partidas->obtener($folio);
		$imagenes = $this->imagenes->listar($encabezado->folio, $encabezado->folio_preencabezado);

		$precio_pieza = $precio_armada = 0;
		if($encabezado->tipo_impresion == 'A') {
			$i = 0; $no = 1;
			foreach($partidas as $partida) {
				if($partida->aparece_en_armada == 'No') {
					$precio_armada += $partida->precioReplicaDD;
					$precio_pieza += $partida->precioPiezaAD;
					unset($partidas[$i]);
				} else {
					$partida->no_partida = $no;
					$no++;
				}
				$i++;
			}
			array_unshift($partidas, (object) array('precioPiezaAD'=>$precio_pieza, 'descripcion'=>$encabezado->descripcion_armado, 'cve_art'=>'', 'no_partida'=>$no, 'precioReplicaDD'=>$precio_armada, 'descuento'=>0, 'replicas'=>0, 'es_armada'=>'X'));
			$partidas = array_values($partidas);
		}

		$this->nuevaPagina($pdf, $encabezado, $partidas);
		if(count($imagenes) > 0) {
			$this->paginaImagenes($pdf, $encabezado, $imagenes, 0);
		}

		# Se incluye la página de términos y condiciones
			$terminos = $this->terminosycondiciones->obtener('A', 'tyc');
			$observaciones = $this->terminosycondiciones->obtener('A', 'obs');
			$this->paginaTerminos($pdf, $encabezado, $terminos, $observaciones);

		$pdf->Output();
	}

	# Funcion para agregar una nueva pagina a la cotizacion
	public function nuevaPagina($pdf, $encabezado, $partidas) {
		$pdf->AddPage();

			if( in_array($encabezado->estatus, array('A', 'B', 'C','E', 'F', 'I')) ) {
				$pdf->Image(base_url('public/images/marca.jpg'), 80, 50, 70);
			}

		# Cuadro superior izquierda
			$pdf->RoundedRect(15, 30, 95, 5, 1, 'DF', '12');
			$pdf->RoundedRect(15, 35, 95, 25, 1, 'D', '');

		# Cuadro superior derecho
			$pdf->RoundedRect(115, 30, 95, 5, 1, 'DF', '12');
			$pdf->RoundedRect(115, 35, 25, 5, 1, 'D', '');
			$pdf->RoundedRect(140, 35, 35, 5, 1, 'D', '');
			$pdf->RoundedRect(175, 35, 35, 5, 1, 'D', '');

			$pdf->RoundedRect(115, 40, 95, 5, 1, 'DF', '');
			$pdf->RoundedRect(115, 45, 95, 5, 1, 'D', '');
			$pdf->RoundedRect(115, 50, 95, 5, 1, 'DF', '');
			$pdf->RoundedRect(115, 55, 95, 5, 1, 'D', '34');


		# Cuadro inferior donde ira el contenido de la cotizacion
			$pdf->RoundedRect(15, 65, 195, 5, 1, 'DF', '12');
			$pdf->RoundedRect(15, 70, 195, 135, 1, 'D', '34');

		# Pintamos las partidas de la orden de compra
			$pdf->Line(28, 70, 28, 205);
			$pdf->Line(48, 70, 48, 205);
			$pdf->Line(120, 70, 120, 205);
			$pdf->Line(145, 70, 145, 205);
			$pdf->Line(166, 70, 166, 205);
			$pdf->Line(188, 70, 188, 205);

		# Firmas de pie de página
			$pdf->Line(20, 240, 80, 240);
			$pdf->Line(90, 240, 150, 240);

		# Cuadro totales
			$pdf->RoundedRect(155, 205, 55, 35, 1, 'D', '');

		# Leyendas del formato / encabezado
			$pdf->SetFont('Courier', 'B', 14);
			$pdf->setXY(15, 20); $pdf->Cell(0, 5, utf8_decode('COTIZACIÓN TQ'.$encabezado->tq), 0, 1, 'C', false);
			$pdf->SetFont('Courier', 'B', 12);
			$pdf->SetTextColor(255, 0, 0);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('Courier', 'B', 11);
			$pdf->setXY(15, 30); $pdf->Cell(95, 5, utf8_decode('Información del cliente'), 0, 0, 'L', false);
			$pdf->setXY(115, 30); $pdf->Cell(25, 5, 'T. C.', 0, 0, 'C', false);
			$pdf->setXY(140, 30); $pdf->Cell(35, 5, 'Folio', 0, 0, 'C', false);
			$pdf->setXY(175, 30); $pdf->Cell(35, 5, 'Fecha', 0, 0, 'C', false);
			$pdf->setXY(115, 40); $pdf->Cell(95, 5, utf8_decode('Folio TQ'), 0, 0, 'L', false);
			$pdf->setXY(115, 50); $pdf->Cell(95, 5, utf8_decode('Representante de Ventas'), 0, 0, 'L', false);

		# Leyendas del formato / partidas
			$pdf->SetFont('Courier', 'B', 10);
			$pdf->setXY(15, 65); $pdf->Cell(10, 5, '#', 0, 0, 'C', false);
			$pdf->Cell(20, 5, utf8_decode('Código'), 0, 0, 'C', false);
			$pdf->Cell(72, 5, utf8_decode('Descripción'), 0, 0, 'L', false);
			$pdf->Cell(25, 5, 'Precio U.', 0, 0, 'R', false);
			$pdf->Cell(21, 5, 'Cantidad', 0, 0, 'R', false);
			$pdf->Cell(22, 5, 'Descuento', 0, 0, 'R', false);
			$pdf->Cell(25, 5, 'Total', 0, 0, 'R', false);

		# Leyendas del pie de página
			$pdf->SetFont('Courier', 'B', 9);
			$pdf->setXY(155, 205); $pdf->Cell(22, 7, 'Subtotal', 0, 0, 'L', false);
			$pdf->setXY(155, 212); $pdf->Cell(22, 7, 'Descuento ST %', 0, 0, 'L', false);
			$pdf->setXY(155, 219); $pdf->Cell(22, 7, 'Impuesto %', 0, 0, 'L', false);
			$pdf->setXY(155, 226); $pdf->Cell(22, 7, 'Total', 0, 0, 'L', false);
			$pdf->setXY(155, 233); $pdf->Cell(77, 7, utf8_decode('COTIZACION EN DÓLARES'), 0, 0, 'L', false);

		# Se descarga la informacion de la cotizacion
			$pdf->SetFont('Courier', '', 9);
			$pdf->setXY(15, 35); $pdf->Cell(95, 5, utf8_decode($encabezado->nombre_cliente), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('ID: ' . $encabezado->id_cliente . ', RFC: ' . $encabezado->rfc), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('Contacto: ' . $encabezado->nombre_contacto), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('Telelefono: '.$encabezado->telefono), 0, 1, 'L', false);
			$pdf->Cell(95, 5, utf8_decode('Correo: '. $encabezado->correo), 0, 1, 'L', false);

			$pdf->setXY(115, 35); $pdf->Cell(25, 5, utf8_decode($encabezado->tipo_cambios), 0, 0, 'C', false);
			$pdf->Cell(35, 5, str_pad($encabezado->folio, '0', STR_PAD_LEFT), 0, 0, 'C', false);
			$pdf->Cell(35, 5, utf8_decode($encabezado->ffecha), 0, 1, 'C', false);
			$pdf->setXY(115, 46); $pdf->MultiCell(95, 3, utf8_decode('TQ'.$encabezado->tq), 0, 'J', false);
			$pdf->setXY(115, 56); $pdf->MultiCell(95, 3, utf8_decode($encabezado->representante_ventas == 'Representante de ventas' ? '' : utf8_decode($encabezado->representante_ventas)), 0, 'J', false);

			$pdf->setXY(20, 235); $pdf->Cell(60, 5, $encabezado->representante_ventas == 'Representante de ventas' ? '' : utf8_decode($encabezado->representante_ventas), 0, 0, 'C', false);
			$pdf->setXY(20, 240); $pdf->Cell(60, 5, 'Representante de ventas', 0, 0, 'C', false);
			$pdf->setXY(90, 240); $pdf->Cell(60, 5, 'Vo. Bo.', 0, 0, 'C', false);

			# Totales de la cotizacion
			$pdf->SetFont('Courier', 'B', 9);
			$pdf->setXY(155, 205); $pdf->Cell(55, 7, number_format($encabezado->stUsdPrecioRDD, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 212); $pdf->Cell(55, 7, number_format($encabezado->descuentost, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 219); $pdf->Cell(55, 7, number_format($encabezado->tasa_impuesto, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 226); $pdf->Cell(55, 7, number_format($encabezado->totalPrecioRDD, 2), 1, 0, 'R', false);

		# Se descarga la informacion de las partidas de la cotizacion
			$pdf->SetFont('Courier', '', 9);
			$pdf->SetWidths(array(13, 20, 72, 22, 21, 22, 25));
			$pdf->SetAligns(array('R', 'C', 'L', 'R', 'R', 'R', 'R'));
			$pdf->setXY(15, 70);
			foreach ($partidas as $key => $partida) {
				if(isset($partida->es_armada)) {
					$pdf->Row(array('-', '---', utf8_decode($partida->descripcion), '---', '---', '---', number_format($partida->precioReplicaDD, 2)));
				} else{
					$pdf->Row(array(($partida->no_partida)*1, utf8_decode($partida->cve_art), utf8_decode($partida->descripcion), number_format($partida->precioPiezaAD, 2), $partida->replicas, number_format($partida->descuento, 1) . ' %', number_format($partida->precioReplicaDD, 2)));
				}
				unset($partidas[$key]);
				if($pdf->getY() > 195) break;
			}
			if(count($partidas) > 0) {
				$this->nuevaPagina($pdf, $encabezado, $partidas);
			}
	}

	# Funcion para agregar las imagenes de la cotizacion
	public function paginaImagenes($pdf, $encabezado, $imagenes, $key) {
		$pdf->AddPage();
		if( in_array($encabezado->estatus, array('A', 'B', 'C','E', 'F', 'I')) ) {
			$pdf->Image(base_url('public/images/marca.jpg'), 80, 50, 70);
		}
		$pdf->SetFont('Courier', 'B', 12);
		$pdf->RoundedRect(15, 30, 195, 10, 1, 'DF', '1234');
		$pdf->setXY(16, 30); $pdf->Cell(95, 5, utf8_decode('Ilustraciones de referencia, cotización con folio ' . $encabezado->folio), 0, 0, 'L', false);
		$pdf->RoundedRect(15, 43, 195, 210, 1, 'D', '1234');
		$x = 17;
		$y = 45;
		$break = 0;
		$i = 0;
		foreach ($imagenes as $imagen) {
			# Se escalan las imágenes con un ancho de 90px
			$size = getimagesize(base_url('uploads/' . $imagen->nombre_unico));
			$yl = (100 * 90 / $size[0]) * $size[1] / 100;
			$pdf->Image(base_url('uploads/' . $imagen->nombre_unico), $x, $y, 90, $yl);
			$break += 1;
			if($break % 2 == 0) {
				$x = 17;
				$y += 64;
			} else{
				$x = 113;
			}
			unset($imagenes[$key]);
			$key++; $i++;
			if($i == 6) break;
		}
		if(count($imagenes) > 0) {
			$this->paginaImagenes($pdf, $encabezado, $imagenes, $key);
		}
	}

	# Funcion para cargar la pagina de terminos y condiciones de venta
	public function paginaTerminos($pdf, $encabezado, $terminos, $observaciones) {
		$pdf->AddPage();
		if( in_array($encabezado->estatus, array('A', 'B', 'C','E', 'F', 'I')) ) {
			$pdf->Image(base_url('public/images/marca.jpg'), 80, 50, 70);
		}
		$pdf->SetAutoPageBreak(true, 17);
		$pdf->SetMargins(15, 35 , 15);

		$pdf->SetFont('Courier', 'B', 12);
		$pdf->setXY(16, 30); $pdf->Cell(0, 5, utf8_decode('Términos y condiciones de venta'), 0, 1, 'C', false);
		$tipo = '';
		foreach($terminos as $termino) {
			if($termino->tipo != $tipo) {
				$pdf->SetFont('Courier', 'B', 10);
				$pdf->Cell(0, 5, utf8_decode($termino->tipo), 0, 1, 'L', false);
			}
			$pdf->SetFont('Courier', '', 9);
			$pdf->MultiCell(0, 4, '		-' . utf8_decode($termino->redaccion), 0, 'J', false);
			$tipo = $termino->tipo;
		}
		$pdf->Ln();
		$pdf->SetFont('Courier', 'B', 12);
		$pdf->Cell(0, 5, utf8_decode('Observaciones generales'), 0, 1, 'C', false);
					$pdf->SetFont('Courier', '', 9);
		foreach($observaciones as $observacion) {
			$pdf->MultiCell(0, 4, '	- ' . utf8_decode($observacion->redaccion), 0, 'J', false);
			$tipo = $observacion->tipo;
		}

		$pdf->Ln();
		$pdf->SetFont('Courier', 'B', 12);
		$pdf->Cell(0, 5, utf8_decode('Términos y condiciones específicas de la cotización ' . $encabezado->folio), 0, 1, 'C', false);
		$pdf->SetFont('Courier', '', 9);
		$pdf->MultiCell(0, 4, utf8_decode($encabezado->terminos_y_condiciones == 'Términos y condiciones de venta' ? '' : $encabezado->terminos_y_condiciones), 0, 'J', false);

		$pdf->Ln();
		$pdf->SetFont('Courier', 'B', 12);
		$pdf->Cell(0, 5, utf8_decode('Observaciones específicas de la cotización ' . $encabezado->folio), 0, 1, 'C', false);
		$pdf->SetFont('Courier', '', 9);
		$pdf->MultiCell(0, 4, utf8_decode($encabezado->observaciones == 'Observaciones' ? '' : $encabezado->observaciones), 0, 'J', false);

	}

	# Funcion para imprimir un conjunto de cotizaciones
	public function ImprimirCotizaciones(){
		# Cargamos el helper para las cookies y el modelo del encabezado de las cotizaciones
		$this->load->helper('cookie');
		$this->load->model('encabezado');
		if(null == get_cookie('impresiones')) exit('No se definio el conjunto de cotizaciones a imprimir');
		$folios = explode(',', get_cookie('impresiones'));
		# Comenzamos el diseño del reporte
		$this->load->library('Pdf');
		$pdf = new Pdf('P', 'mm', 'Letter');
		$pdf->SetMargins(15, 15 , 15);
		$pdf->SetAutoPageBreak(false, 15); 
		$pdf->AliasNbPages();
		$pdf->SetFillColor(192, 192, 192);
		$pdf->SetDrawColor(192, 192, 192);
		# Obtenemos la informacion necesaria para la impresion de las cotizaciones
		$encabezados = array();
		foreach ($folios as $folio) {
			array_push($encabezados, $this->encabezado->obtener($folio));
		}
		$te = count($folios) - 1;
		# Comenzamos a dibujar el modelo de la cotizacion
			$pdf->AddPage();
		# Cuadro superior izquierda
			$pdf->RoundedRect(15, 30, 120, 5, 1, 'DF', '12');
			$pdf->RoundedRect(15, 35, 120, 25, 1, 'D', '');
		# Cuadro superior derecho
			$pdf->RoundedRect(140, 30, 70, 5, 1, 'DF', '12');
			$pdf->RoundedRect(140, 35, 70, 5, 1, 'D', '');
		# Leyendas del formato / encabezado
			$pdf->SetFont('Courier', 'B', 14);
			$pdf->setXY(15, 20); $pdf->Cell(0, 5, utf8_decode('FORMATO DE COTIZACIÓN MULTIPLE'), 0, 1, 'C', false);
			$pdf->SetFont('Courier', 'B', 12);
			$pdf->setXY(15, 30); $pdf->Cell(120, 5, utf8_decode('Información del cliente'), 0, 1, 'L', false);
			$pdf->setXY(140, 30); $pdf->Cell(70, 5, utf8_decode('Fecha de Impresión'), 0, 1, 'R', false);
			$pdf->SetFont('Courier', '', 10);
			$pdf->setXY(140, 35); $pdf->Cell(70, 5, utf8_decode(date('d-m-Y')), 0, 1, 'R', false);
		# Se descarga la informacion de la cotizacion
			$pdf->SetFont('Courier', '', 10);
			$pdf->setXY(15, 35); $pdf->Cell(120, 5, utf8_decode($encabezados[0]->nombre_cliente), 0, 1, 'L', false);
			$pdf->Cell(120, 5, utf8_decode('ID: ' . $encabezados[0]->id_cliente . ', RFC: ' . $encabezados[0]->rfc), 0, 1, 'L', false);
			$pdf->Cell(120, 5, utf8_decode('Contacto: ' . $encabezados[0]->nombre_contacto), 0, 1, 'L', false);
			$pdf->Cell(120, 5, utf8_decode('Telelefono: '.$encabezados[0]->telefono), 0, 1, 'L', false);
			$pdf->Cell(120, 5, utf8_decode('Correo: '. $encabezados[0]->correo), 0, 1, 'L', false);
		# Cuadro inferior donde ira el contenido de la cotizacion
			$pdf->RoundedRect(15, 65, 195, 5, 1, 'DF', '12');
			$pdf->RoundedRect(15, 70, 195, 135, 1, 'D', '34');
		# Pintamos las partidas de la orden de compra
			$pdf->Line(30, 70, 30, 205);
			$pdf->Line(100, 70, 100, 205);
			$pdf->Line(118, 70, 118, 205);
			$pdf->Line(136, 70, 136, 205);
			$pdf->Line(154, 70, 154, 205);
			$pdf->Line(172, 70, 172, 205);
			$pdf->Line(190, 70, 190, 205);
		# Leyendas del formato / partidas
			$pdf->SetFont('Courier', 'B', 9);
			$pdf->setXY(15, 65);
			$pdf->Cell(15, 5, utf8_decode('Folio'), 0, 0, 'C', false);
			$pdf->Cell(70, 5, utf8_decode('Descripción'), 0, 0, 'L', false);
			$pdf->Cell(18, 5, 'T C', 0, 0, 'R', false);
			$pdf->Cell(18, 5, 'Fecha', 0, 0, 'R', false);
			$pdf->Cell(18, 5, 'Precio', 0, 0, 'R', false);
			$pdf->Cell(18, 5, 'Desc.', 0, 0, 'R', false);
			$pdf->Cell(18, 5, 'Imp', 0, 0, 'R', false);
			$pdf->Cell(20, 5, 'Total', 0, 0, 'R', false);
		# Impresion del contenido de las partidas
			$pdf->SetFont('Courier', '', 9);
			$pdf->SetWidths(array(15, 70, 18, 18, 18, 18, 18, 20));
			$pdf->SetAligns(array('C', 'L', 'R', 'R', 'R', 'R', 'R', 'R'));
			$pdf->setXY(15, 70);
			$tstUsdPrecioRDD = $tdescuentoPrecioRDD = $tivaPrecioRDD = $ttotalPrecioRDD = 0;
			foreach ($encabezados as $encabezado) {
				$pdf->Row(array(($encabezado->folio)*1, utf8_decode('TQ' . $encabezado->tq . ' ' . $encabezado->descripcion_armado), number_format($encabezado->tipo_cambios, 4), $encabezado->ffecha, $encabezado->stUsdPrecioRDD, number_format($encabezado->descuentost, 1), number_format($encabezado->tasa_impuesto, 2), number_format($encabezado->totalPrecioRDD, 2)));
				$tstUsdPrecioRDD += $encabezado->stUsdPrecioRDD;
				$tdescuentoPrecioRDD += $encabezado->descuentoPrecioRDD;
				$tivaPrecioRDD += $encabezado->ivaPrecioRDD;
				$ttotalPrecioRDD += $encabezado->totalPrecioRDD;
			}


		# Leyendas del pie de página
			$pdf->SetFont('Courier', 'B', 9);
			$pdf->setXY(155, 205); $pdf->Cell(22, 7, 'Subtotal', 0, 0, 'L', false);
			$pdf->setXY(155, 212); $pdf->Cell(22, 7, 'Descuento', 0, 0, 'L', false);
			$pdf->setXY(155, 219); $pdf->Cell(22, 7, 'Impuesto', 0, 0, 'L', false);
			$pdf->setXY(155, 226); $pdf->Cell(22, 7, 'Total', 0, 0, 'L', false);
			# Totales de la cotizacion
			$pdf->SetFont('Courier', 'B', 9);
			$pdf->setXY(155, 205); $pdf->Cell(55, 7, number_format($tstUsdPrecioRDD, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 212); $pdf->Cell(55, 7, number_format($tdescuentoPrecioRDD, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 219); $pdf->Cell(55, 7, number_format($tivaPrecioRDD, 2), 1, 0, 'R', false);
			$pdf->setXY(155, 226); $pdf->Cell(55, 7, number_format($ttotalPrecioRDD, 2), 1, 0, 'R', false);


			/*

		# Se descarga la informacion de las partidas de la cotizacion

			if(count($partidas) > 0) {
				$this->nuevaPagina($pdf, $encabezado, $partidas);
			}*/

					# Firmas de pie de página
			/*$pdf->Line(20, 240, 80, 240);
			$pdf->Line(90, 240, 150, 240);*/
		# Cuadro totales
			$pdf->RoundedRect(155, 205, 55, 35, 1, 'D', '');

		$pdf->Output();
	}

}
