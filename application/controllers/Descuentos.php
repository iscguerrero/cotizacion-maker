<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Descuentos extends Base_Controller {
	public function __construct(){
		parent::__construct();
		# Conexion a la base de datos para interactuar con las cotizaciones
			$this->load->database('mysql');
	}
	# Metodo usado para obtener los datos del autocomplete del cliente
	public function ObtenerDescuentoMaximo(){
		if(!$this->input->is_ajax_request()) show_404();
		# Cargamos el modelo para obtener el maximo descuento permitido
			$this->load->model('Descuento');
			$maximoDescuento = $this->Descuento->ObtenerDescuentoMaximo();
		exit(json_encode($maximoDescuento));
	}
}