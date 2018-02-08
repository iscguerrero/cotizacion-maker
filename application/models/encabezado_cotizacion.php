<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class encabezado_cotizacion extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	#Metodo para obtener el nuevo folio de la precotizacion
	public function obtenerUltimoFolio(){
		$this->db->select('IFNULL(folio, 0) AS folio');
		$this->db->from('encabezado_cotizacion');
		$this->db->order_by('folio', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row();
	}

	# Metodo para crear un nuevo registro en el catalogo de usuarios
	public function altaEncabezado($data) {
		return $this->db->insert('encabezado_cotizacion', $data);
	}

	# Metodo para actualizar la informacion del encabezado de la precotizacion
	public function editarEncabezado($data) {
		$this->db->limit(1);
		$this->db->where('folio', $data['folio']);
		return $this->db->update('encabezado_cotizacion', $data);
	}

	# Funcion para obtener la lista de cotizaciones en el periodo seleccionado
	public function obtenerCotizaciones($fi, $ff, $estatus) {
		$this->db->select("folio, nombre_cliente, DATE_FORMAT(created_at, '%d-%M-%Y') AS fecha, totalPrecioRDD, estatus");
		$this->db->from('encabezado_cotizacion');
		$this->db->where('created_at >', $fi);
		$this->db->where('created_at <', $ff.' 23:59:59');
		if($estatus != '') $this->db->where('estatus', $estatus);
		$query = $this->db->get();
		return $query->result();
	}

	# Metodo para obtener el encabezado de una cotizacion
	public function obtenerEncabezado($folio){
		$this->db->select('*');
		$this->db->from('encabezado_cotizacion');
		$this->db->where('folio', $folio);
		$this->db->limit(1);
		return $this->db->get()->row();
	}

	# Metodo para obtener toda la informacion del encabezado
	public function obtenerEncabezadoPdf($folio){
		$this->db->select("*, date_format(created_at, '%d-%b-%Y') AS ffecha");
		$this->db->from('encabezado_cotizacion');
		$this->db->where('folio', $folio);
		$this->db->limit(1);
		return $this->db->get()->row();
	}

}