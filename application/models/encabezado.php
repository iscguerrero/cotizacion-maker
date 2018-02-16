<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class encabezado extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	#Metodo para obtener el nuevo folio de la precotizacion
	public function folio(){
		$this->db->select('folio')
		->from('encabezado')
		->order_by('folio', 'DESC');
		return $this->db->get()->row();
	}

	# Metodo para crear un nuevo registro en el catalogo de usuarios
	public function alta($data) {
		return $this->db->insert('encabezado', $data);
	}

	# Metodo para actualizar la informacion del encabezado de la precotizacion
	public function editar($data) {
		$this->db->limit(1)
		->where('folio', $data['folio']);
		return $this->db->update('encabezado', $data);
	}

	# Funcion para obtener la lista de cotizaciones en el periodo seleccionado
	public function listar($fi, $ff, $estatus) {
		$this->db->select("folio, tq, id_cliente, nombre_cliente, DATE_FORMAT(created_at, '%d-%m-%Y') AS ffecha, totalPrecioRDD, estatus")
		->from('encabezado')
		->where('created_at >', $fi)
		->where('created_at <', $ff.' 23:59:59');
		if($estatus != '') $this->db->where('estatus', $estatus);
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para obtener el encabezado de una cotizacion
	public function obtener($folio){
		$this->db->select("*, date_format(created_at, '%d-%m-%Y') AS ffecha")
		->from('encabezado')
		->where('folio', $folio);
		return $this->db->get()->row();
	}

}