<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class imagenes_cotizacion extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en la tabla de imagenes
	public function altaImagen($data) {
		return $this->db->insert('imagenes_cotizacion', $data);
	}

	# Metodo para actualizar la informacion de la imagen
	public function editarImagen($data) {
		$this->db->limit(1);
		$this->db->where('folio', $data['folio']);
		return $this->db->update('imagenes_cotizacion', $data);
	}

	# Metodo para obtener las partidas de la cotizacion
	public function obtenerImagenes($folio, $pre_folio) {
		$this->db->select("folio, nombre_original, nombre_unico")
							->from('imagenes_cotizacion')
							->where('folio_preencabezado != ', '')
							->where('folio_preencabezado', $pre_folio)
							->where('estatus', 'A');
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para borrar una imagen
	public function borrarImagen($folio) {
		$this->db->limit(1);
		$this->db->where('folio', $folio);
		return $this->db->update('imagenes_cotizacion', array('estatus'=>'X'));
	}
}