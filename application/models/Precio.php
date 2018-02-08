<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Precio extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	public function obtenerPrecio($cve_art, $cve_precio){
		$this->db->select('PRECIO, DESCRIPCION');
		$this->db->from('PRECIO_X_PROD03 as pp');
		$this->db->join('PRECIOS03 as p', 'pp.CVE_PRECIO = p.CVE_PRECIO', 'INNER');
		$this->db->where('p.STATUS', 'A');
		$this->db->where('pp.CVE_ART', $cve_art);
		$this->db->where('pp.CVE_PRECIO', $cve_precio);
		$this->db->limit(1);
		return $this->db->get()->row();
		#return $this->db->get_compiled_select();
	}
}