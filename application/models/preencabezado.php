<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class preencabezado extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	#Metodo para obtener el nuevo folio de la precotizacion
	public function folio(){
		$this->db->select('ifnull(folio, 0) as folio')
		->from('preencabezado')
		->order_by('folio', 'DESC');
		return $this->db->get()->row();
	}

	#Metodo para obtener la descripcion de la cotizacion armada
	public function obtener($folio){
		$this->db->select('descripcion_armado')
		->from('preencabezado')
		->where('folio', $folio);
		return $this->db->get()->row();
	}

	# Metodo para crear un nuevo registro en el catalogo de usuarios
	public function alta($data) {
		return $this->db->insert('preencabezado', $data);
	}

	# Metodo para actualizar la informacion del encabezado de la precotizacion
	public function editar($data) {
		$this->db->limit(1)
		->where('folio', $data['folio']);
		return $this->db->update('preencabezado', $data);
	}

}