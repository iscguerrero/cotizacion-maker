<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class precio_x_prod03 extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	public function obtener($cve_art, $cve_precio){
		$this->db->select('PRECIO, DESCRIPCION')
		->from('PRECIO_X_PROD03 as pp')
		->join('PRECIOS03 as p', 'pp.CVE_PRECIO = p.CVE_PRECIO', 'INNER')
		->where('p.STATUS', 'A')
		->where('pp.CVE_ART', $cve_art)
		->where('pp.CVE_PRECIO', $cve_precio);
		return $this->db->get()->row();
		#return $this->db->get_compiled_select();
	}
}