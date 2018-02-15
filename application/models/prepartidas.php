<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class prepartidas extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en el catalogo de usuarios
	public function alta($data) {
		return $this->db->insert('prepartidas', $data);
	}
}