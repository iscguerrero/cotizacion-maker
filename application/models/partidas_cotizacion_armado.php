<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class partidas_cotizacion_armado extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en la tabla de partidas de la cotizacion
	public function altaPartida($data) {
		return $this->db->insert('partidas_cotizacion_armado', $data);
	}

	# Metodo para obtener las partidas de la cotizacion
	public function obtenerPartidas($folio) {
		$this->db->select("*");
		$this->db->from('partidas_cotizacion_armado');
		$this->db->where('folio_encabezado', $folio);
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para obtener las partidas de la cotizacion por folio y clasificador
	public function obtenerPartidasClasificador($folio, $clasificador) {
		$this->db->select("*");
		$this->db->from('partidas_cotizacion_armado');
		$this->db->where('folio_encabezado', $folio);
		$this->db->where('clasificador', $clasificador);
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para borrar las partidas de armado de una cotizacion
	public function borrarPartidas($folio) {
		return $this->db->delete('partidas_cotizacion_armado', array('folio_encabezado' => $folio));
	}

}