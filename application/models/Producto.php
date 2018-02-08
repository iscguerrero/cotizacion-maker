<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Producto extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	public function obtenerProducto($cve_art){
		$this->db->select('CVE_ART, DESCR, LIN_PROD, CON_SERIE, ULT_COSTO');
		$this->db->from('INVE03');
		$this->db->where('CVE_ART', $cve_art);
		$this->db->limit(1);
		return $this->db->get()->row();
	}

	public function obtenerProductoPorNombre($descr){
		$query = $this->db->query("SELECT CVE_ART, (CVE_ART || ' - ' || DESCR) AS DESCR, CON_SERIE, ULT_COSTO FROM INVE03 WHERE trim(CVE_ART) CONTAINING '$descr' OR trim(DESCR) CONTAINING '$descr'");
		return $query->result();
		#return $this->db->get_compiled_select();
	}

	public function obtenerPorAgrupador($agrupador){
		$this->db->select('CVE_ART, DESCR');
		$this->db->from('INVE03');
		$this->db->where('COLOR ', $agrupador);
		$query = $this->db->get();
		return $query->result();
	}

	public function obtenerSeleccion($agrupador, $valores){
		$this->db->select('CVE_ART, DESCR, ULT_COSTO');
		$this->db->from('INVE03');
		$this->db->where('COLOR ', $agrupador);
		$this->db->where_in('CVE_ART ', $valores);
		$query = $this->db->get();
		return $query->result();
	}

}