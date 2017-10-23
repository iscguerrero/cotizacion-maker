<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Prepartidas_cotizacion_bulk extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	# Metodo para crear un nuevo registro en el catalogo de usuarios
	public function altaPrePartida($data) {
		return $this->db->insert('prepartidas_cotizacion_bulk', $data);
	}
/*
	# Funcion para obtener la informacion basica del usuario con su clave
	public function obtenerUsuario($cve_usuario) {
		$this->db->from('gl_cat_usuarios');
		$this->db->where('cve_usuario', $cve_usuario);
		return $this->db->get()->row();
	}

	# Funcion para obtener la lista de usuarios
	public function obtenerClientes($estatus){
		$this->db->select('gcu.cve_usuario, nombre, correo, facebook, twitter, instagram, paginaweb, estatus');
		$this->db->from('gl_cat_usuarios gcu');
		$this->db->join('vn_detalles_cliente vdc', 'gcu.cve_usuario = vdc.cve_usuario', 'INNER');
		$this->db->where('cve_perfil', '002');
		if($estatus == 'A') $this->db->where('estatus', 'A');
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para editar la informacion de un cliente
	public function editarUsuario($cve_usuario, $nombre, $correo){
		$this->db->set('nombre', $nombre);
		$this->db->set('correo', $correo);
		$this->db->set('updated_at', date('Y-m-j H:i:s'));
		$this->db->where('cve_usuario', $cve_usuario);
		$this->db->limit(1);
		$this->db->update('gl_cat_usuarios');
	}

	# Metodo para eliminar logicamente un usuario
	public function suspenderUsuario($cve_usuario, $estatus){
		$this->db->set('estatus', $estatus);
		$this->db->set('updated_at', date('Y-m-j H:i:s'));
		$this->db->where('cve_usuario', $cve_usuario);
		$this->db->limit(1);
		$this->db->update('gl_cat_usuarios');
	}
*/
}