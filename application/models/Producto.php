<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Producto extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	public function obtenerProducto($cve_art){
		$this->db->select('CVE_ART, DESCR, LIN_PROD, CON_SERIE');
		$this->db->from('INVE03');
		$this->db->where('CVE_ART', $cve_art);
		$this->db->limit(1);
		$query = $this->db->get();
		return $query->result();
	}
	public function obtenerProductoPorNombre($descr){
		$this->db->select('CVE_ART, DESCR, CON_SERIE');
		$this->db->from('INVE03');
		$this->db->like('DESCR', $descr);
		$this->db->limit(15);
		$query = $this->db->get();
		return $query->result();
		#return $this->db->get_compiled_select();
	}
}