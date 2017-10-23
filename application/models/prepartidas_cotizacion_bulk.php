<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Prepartidas_cotizacion_bulk extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en el catalogo de usuarios
	public function altaPrePartida($data) {
		return $this->db->insert('prepartidas_cotizacion_bulk', $data);
	}
}