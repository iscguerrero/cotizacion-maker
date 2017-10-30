<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class partidas_cotizacion_bulk extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en la tabla de partidas de la cotizacion
	public function altaPartida($data) {
		return $this->db->insert('partidas_cotizacion_bulk', $data);
	}

	# Metodo para actualizar la informacion de la partida de la cotizacion
	public function editarPartida($data) {
		$this->db->limit(1);
		$this->db->where('folio', $data['folio']);
		return $this->db->update('partidas_cotizacion_bulk', $data);
	}

	# Metodo para obtener las partidas de la cotizacion
	public function obtenerPartidas($folio) {
		$this->db->select("*");
		$this->db->from('partidas_cotizacion_bulk');
		$this->db->where('folio_encabezado', $folio);
		$this->db->where('estatus', 'A');
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para borrar una partida
	public function borrarPartida($folio) {
		$this->db->limit(1);
		$this->db->where('folio', $folio);
		return $this->db->update('partidas_cotizacion_bulk', array('estatus'=>'X'));
	}
}