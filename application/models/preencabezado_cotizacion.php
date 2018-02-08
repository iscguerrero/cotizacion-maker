<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class preencabezado_cotizacion extends CI_Model{

	# Constructor del modelo
	function construct(){
		parent::__construct();
	}

	#Metodo para obtener el nuevo folio de la precotizacion
	public function obtenerUltimoFolio(){
		$this->db->select('folio');
		$this->db->from('preencabezado_cotizacion');
		$this->db->order_by('folio', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row();
	}

	#Metodo para obtener la descripcion de la cotizacion armada
	public function obtenerDescripcionArmada($folio){
		$this->db->select('descripcion_armado');
		$this->db->from('preencabezado_cotizacion');
		$this->db->where('folio', $folio);
		$this->db->limit(1);
		return $this->db->get()->row();
	}

	# Metodo para crear un nuevo registro en el catalogo de usuarios
	public function altaPreencabezado($data) {
		return $this->db->insert('preencabezado_cotizacion', $data);
	}

	# Metodo para actualizar la informacion del encabezado de la precotizacion
	public function editarPreencabezado($data) {
		$this->db->limit(1);
		$this->db->where('folio', $data['folio']);
		return $this->db->update('preencabezado_cotizacion', $data);
	}

}