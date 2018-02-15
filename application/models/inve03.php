<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class inve03 extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	public function obtener($cve_art){
		$this->db->select('CVE_ART, DESCR, LIN_PROD, CON_SERIE, ULT_COSTO')
		->from('INVE03')
		->where('CVE_ART', $cve_art);
		return $this->db->get()->row();
	}

	public function xnombre($descr){
		$query = $this->db->query("SELECT CVE_ART, (CVE_ART || ' - ' || DESCR) AS DESCR, CON_SERIE, ULT_COSTO FROM INVE03 WHERE (trim(CVE_ART) CONTAINING '$descr' OR trim(DESCR) CONTAINING '$descr') AND COLOR is null and CVE_ART != 'ZPROYECTOS01' and CVE_ART != 'ZPROYECTOS02' and CVE_ART != 'ZPROYECTOS04'");
		return $query->result();
		#return $this->db->get_compiled_select();
	}

	public function xclasificador($clasificador){
		$this->db->select('CVE_ART, DESCR')
		->from('INVE03')
		->where('COLOR ', $clasificador);
		$query = $this->db->get();
		return $query->result();
	}

	public function xvalores($clasificador, $valores){
		$this->db->select('CVE_ART, DESCR, ULT_COSTO')
		->from('INVE03')
		->where('COLOR ', $clasificador)
		->where_in('CVE_ART ', $valores);
		$query = $this->db->get();
		return $query->result();
	}

}