<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class imagenes extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en la tabla de imagenes
	public function alta($data) {
		return $this->db->insert('imagenes', $data);
	}

	# Metodo para actualizar la informacion de la imagen
	public function editar($data) {
		$this->db->limit(1)
		->where('folio', $data['folio']);
		return $this->db->update('imagenes', $data);
	}

	# Metodo para obtener las partidas de la cotizacion
	public function listar($folio, $pre_folio) {
		$this->db->select("folio, nombre_original, nombre_unico")
		->from('imagenes')
		->where('folio_preencabezado != ', '')
		->where('folio_preencabezado', $pre_folio)
		->where('estatus', 'A');
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para borrar una imagen
	public function borrar($folio) {
		$this->db->limit(1)
		->where('folio', $folio);
		return $this->db->update('imagenes', array('estatus'=>'X'));
	}
}