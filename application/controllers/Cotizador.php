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
		print_r($_POST);
	}
}
