<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Cliente extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	public function ObtenerCliente($searchTerm){
		$query = $this->db->query("SELECT em.intidempresa AS ID, em.strrfc AS RFC, em.strnombrefiscal AS value, em.strdomiciliolinea1 AS DOMICILIO, em.strdomiciliolinea2 AS COLONIA, em.strcp AS CP, mu.strnombre MUNICIPIO, es.strnombre AS ESTADO, em.strtelefono1 TELEFONO, em.stremail1 AS CORREO, em.strrepresentantelegal AS REPRESENTANTE FROM [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[empresas] AS em INNER JOIN [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[municipios] AS mu ON em.intidmunicipio = mu.lonidmunicipio INNER JOIN [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[estados] AS es ON em.intidestado = es.lonidestado WHERE em.strnombrefiscal LIKE '%$searchTerm%' OR em.intidempresa LIKE '%$searchTerm%'");
		return $query->result();
	}
}