<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Base_Controller extends CI_Controller {
	public $templates;
	public function __construct(){
		parent::__construct();
		# Cargamos Helpers basicos
			$this->load->helper(array('url','form'));
		# Cargamos la libreria para la validacion de los formularios
			$this->load->library(array('form_validation', 'session'));

			$sesion = array(
				'cve_usuario' => 'dguerrero',
				#'tipo_usuario' => 'ventas',
				'tipo_usuario' => 'diseñadores',
				'logueado' => true
			);
			$this->session->set_userdata($sesion);

		# Comprobamos que exista una sesion de usuario creada
			if($this->session->userdata('logueado') == false){
				show_404();
			}
		# Seteamos la clave de usuario en variables globales
			$this->created_user = $this->session->userdata() ? $this->session->userdata('cve_usuario') : null;
			$this->updated_user = $this->session->userdata() ? $this->session->userdata('cve_usuario') : null;


	}
	# Funcion para formatear la fecha a formato Y-m-d
	public function str_to_date($string){
		$meses = array("enero" => "01", "febrero" => "02", "marzo" => "03", "abril" => "04", "mayo" => "05", "junio" => "06", "julio" => "07", "agosto" => "08", "septiembre" => "09", "octubre" => "10", "noviembre" => "11", "diciembre" => "12");
		if(!isset($string)) exit(json_encode(array('flag'=>false, 'msj'=>'UNA O VARIAS DE LAS FECHAS NO FUE PROPORCIONADA CORRECTAMENTE')));
		if($string == null) exit(json_encode(array('flag'=>false, 'msj'=>'UNA O VARIAS DE LAS FECHAS ES NULA')));
		if($string == '') exit(json_encode(array('flag'=>false, 'msj'=>'UNA O VARIAS DE LAS FECHAS ES NULA')));
		isset($string)?$fecha=explode("-", $string):exit(array('flag'=>false, 'msj'=>'UNA DE LAS FECHAS NO SE PROPORCIONO CORRECTAMENTE'));
		$date = $fecha[2].'-'.$meses[$fecha[1]].'-'.$fecha[0];
		return $date;
	}
}