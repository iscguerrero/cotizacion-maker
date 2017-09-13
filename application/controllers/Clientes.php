<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clientes extends Base_Controller {
	public function __construct(){
		parent::__construct();
		# Conexion a la base de datos para interactuar con las cotizaciones
			$this->load->database('sqlserver');
	}
	# Metodo usado para obtener los datos del autocomplete del cliente
	public function ObtenerCliente(){
		if(!$this->input->is_ajax_request()) show_404();
		# Guardamos el termino de busqueda en una variable local
		$nombre = $this->input->get('term');
		# Cargamos el modelo para obtener los datos del cliente
			$this->load->model('Cliente');
			$clientes = $this->Cliente->ObtenerCliente($nombre);
		exit(json_encode($clientes));
	}
}
