<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class partidas extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en la tabla de partidas de la cotizacion
	public function alta($data) {
		return $this->db->insert('partidas', $data);
	}

	# Metodo para actualizar la informacion de la partida de la cotizacion
	public function editar($data) {
		$this->db->limit(1)
		->where('folio', $data['folio']);
		return $this->db->update('partidas', $data);
	}

	# Metodo para obtener las partidas de la cotizacion
	public function obtener($folio) {
		$this->db->select("*")
		->from('partidas')
		->where('folio_encabezado', $folio)
		->where('estatus', 'A')
		->order_by('no_partida', 'ASC');
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para borrar una partida
	public function borrar($folio) {
		$this->db->limit(1)
		->where('folio', $folio);
		return $this->db->update('partidas', array('estatus'=>'X'));
	}
}