<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Condiciones extends Base_Controller {

	public function __construct() {
		parent::__construct();
			$this->load->database('mysql');
	}

	# Retornar la vista del abc de los terminos y condiciones de venta
	public function index() {
		if($this->session->userdata('tipo_usuario') == 'diseñadores')
			$this->load->view('terminos');
		else
			show_404();
	}

	# Metodo para obtener las condiciones de pago
	public function ObtenerCondiciones() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('terminosycondiciones');
		exit(json_encode($this->terminosycondiciones->obtener()));
	}

	# Metodo para obtener los tipos de condiciones
	public function ObtenerTipos() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('terminosycondiciones');
		exit(json_encode($this->terminosycondiciones->tipos()));
	}

	# Metodo para guardar las condiciones de pago
	public function GuardarCambios() {
		$this->load->model('terminosycondiciones');
		$data = $_POST['condiciones'];

		$this->db->trans_start();
		foreach($data as $key => $row) {
			if(isset($row[1]) && $row[1] != null && $row[1] != '') {
			$tosend = array(
				'id' => $row[0],
				'clase' => $row[1],
				'tipo' => $row[2],
				'redaccion' => $row[3],
				'estatus' => $row[4],
			);
				$this->terminosycondiciones->guardar($tosend);
			}
		}
		$this->db->trans_complete();
		$this->db->trans_status() === FALSE ? exit(json_encode(array('bandera'=> false, 'msj'=>'Se presento un error al guardar'))) : exit(json_encode(array('bandera'=> true, 'msj'=>'Los cambios se guardaron con éxito')));
	}

}
