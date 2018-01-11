<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class terminosycondiciones extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Retorna los registros de la tabla
	public function obtenerRegistros($estatus = '', $clase = '') {
		$this->db->from('terminosycondiciones');
		$this->db->order_by('tipo', 'asc');
		if($estatus != '') $this->db->where('estatus !=', 'X');
		if($clase != '') $this->db->where('clase =', $clase);
		$query = $this->db->get();
		return $query->result();
	}

	public function obtenerTipos() {
		$this->db->select('tipo');
		$this->db->from('terminosycondiciones');
		$this->db->group_by('tipo'); 
		$this->db->order_by('tipo', 'asc');
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para guardar en la tabla de condiciones
	public function guardar($data) {
		if (isset($data['id']) && $data['id'] != null && $data['id'] != 0) {
			$this->db->where('id', $data['id']);
			$this->db->update('terminosycondiciones', $data);
		} else {
			$this->db->insert('terminosycondiciones', $data);
			$data['id'] = $this->db->insert_id();
		}
	}

}