<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class cliente extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	public function ObtenerCliente($searchTerm){
		$query = $this->db->query("SELECT em.intidempresa AS ID, em.strrfc AS RFC, em.strnombrefiscal AS value, em.strdomiciliolinea1 AS DOMICILIO, em.strdomiciliolinea2 AS COLONIA, em.strcp AS CP, mu.strnombre MUNICIPIO, es.strnombre AS ESTADO, em.strtelefono1 TELEFONO, em.stremail1 AS CORREO, em.strrepresentantelegal AS REPRESENTANTE FROM [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[empresas] AS em INNER JOIN [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[municipios] AS mu ON em.intidmunicipio = mu.lonidmunicipio INNER JOIN [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[estados] AS es ON em.intidestado = es.lonidestado WHERE em.strnombrefiscal LIKE '%$searchTerm%' OR em.intidempresa LIKE '%$searchTerm%'");
		return $query->result();
	}

	public function ObtenerClienteEdit($searchTerm) {
		$query = $this->db->query("SELECT em.intidempresa AS id, em.strnombrefiscal AS value, em.intestatusempresa estatus, isnull(em.strrfc, '') rfc, isnull(em.strtelefono1, '') telefono, isnull(em.stremail1, '') mail, isnull(em.intidestado, 0) estado, isnull(em.intidmunicipio, 0) as municipio, isnull(em.strdomiciliolinea2, '') as colonia, isnull(em.strcp, '') as cp, isnull(em.strdomiciliolinea1, '') as direccion FROM [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[empresas] em WHERE em.strnombrefiscal LIKE '%$searchTerm%' OR em.intidempresa like '%$searchTerm%'");
		return $query->result();
	}

	public function ObtenerEstatus() {
		$query = $this->db->query("SELECT intiddetallecatalogo, strvalor from [cbctecnologia_trilogiq].[catalogosdetalle] where intiddetallecatalogo = 0 or intidcatalogo = 15");
		return $query->result();
	}

	public function ObtenerEstados() {
		$query = $this->db->query("SELECT lonidestado, strnombre from [cbctecnologia_trilogiq].[estados]");
		return $query->result();
	}

	public function ObtenerMunicipios($estado) {
		$query = $this->db->query("SELECT lonidmunicipio, strnombre from [cbctecnologia_trilogiq].[municipios] where lonidestado = $estado or lonidmunicipio = 0");
		return $query->result();
	}

	public function AltaCliente($data) {
		return $this->db->query("INSERT INTO [cbctecnologia_trilogiq].[empresas] ([strnombrefiscal], [strnombrecomercial], [strdomiciliolinea1], [strdomiciliolinea2], [strdomiciliolinea3], [strcp], [intidmunicipio], [intidestado], [intidpais], [strtelefono1], [strtelefono2], [strfax], [stremail1], [inttipopersona], [strrfc], [strcurp], [bitactivo], [datregistro], [bitcorporacion], [bitproveedor], [bitcliente], [strlatitud], [strlongitud], [strdomiciliolinea1e], [strdomiciliolinea2e], [strdomiciliolinea3e], [strcpe], [intidmunicipioe], [intidestadoe], [intidpaise], [strgiro], [strgrupocorporativo], [bitpagousd], [bitpagomn], [bitpagoeur], [strpagobanco], [strpagobancocuenta], [bitpagotransferencia], [bitpagocheque], [bitpagodeposito], [bitentregasparciales], [bitfacturacionparcial], [bitbancoextranjero], [strentregamateriales], [stringresofactura], [intdiascredito], [strrepresentantetrilogiq], [strcuentabancaria], [strbanco], [intidcontactocrea], [intestatusempresa], [strrepresentantelegal], [bitadenda], [strdiasyhorariosdepago]) VALUES
		('".$data['inEmpresa']."', '', '".$data['inDireccion']."', '".$data['inColonia']."', '', '".$data['inCP']."', '".$data['inMunicipio']."', '".$data['inEstado']."', '', '".$data['inTelefono']."', '', '', '".$data['inCorreo']."', '0', '".$data['inRFC']."', '', '1', cast(getdate() as datetime), '', '', '1', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '15478', '".$data['inEstatus']."', '', '', '')");
	}

	public function EditarCliente($data) {
		return $this->db->query("UPDATE top(1) [cbctecnologia_trilogiq].[empresas] SET
		[strnombrefiscal] = '".$data['inEmpresa']."',
		[intestatusempresa] = '".$data['inEstatus']."',
		[strrfc] = '".$data['inRFC']."',
		[strtelefono1] = '".$data['inTelefono']."',
		[stremail1] = '".$data['inCorreo']."',
		[intidestado] = '".$data['inEstado']."',
		[intidmunicipio] = '".$data['inMunicipio']."',
		[strdomiciliolinea2] = '".$data['inColonia']."',
		[strcp] = '".$data['inCP']."',
		[strdomiciliolinea1] = '".$data['inDireccion']."',
		[intidcontactocrea] = '15478'
		WHERE intidempresa = '".$data['id']."'");
	}

	public function ObtenerId() {
		$query = $this->db->query("SELECT max(intidempresa) + 1 as id from [cbctecnologia_trilogiq].[empresas]");
		return $query->row();
	}

	public function VerificarRfc($rfc) {
		$query = $this->db->query("select intidempresa from [cbctecnologia_trilogiq].[empresas] where strrfc = '$rfc'");
		return $query->result();
	}

	public function ObtenerTipoContacto() {
		$query = $this->db->query("SELECT intiddetallecatalogo, strvalor from [cbctecnologia_trilogiq].[cbctecnologia_trilogiq]	.[catalogosdetalle] where intidcatalogo = 12");
		return $query->result();
	}

	public function ObtenerContacto($searchTerm, $idempresa) {
		$query = $this->db->query("SELECT intidcontacto, strnombre as value, stremail, strtelefono1, intidtipocontacto, strcampo1 from [cbctecnologia_trilogiq].[contactos] where intidempresa = $idempresa and strnombre like '%$searchTerm%'");
		return $query->result();
	}

	public function ObtenerContactoID($id, $idempresa) {
		$query = $this->db->query("SELECT intidcontacto, strnombre as value, stremail, strtelefono1, intidtipocontacto, strcampo1 from [cbctecnologia_trilogiq].[contactos] where intidempresa = $idempresa and intidcontacto = $id");
		return $query->row();
	}

	public function AltaContacto($data) {
		return $this->db->query("INSERT into [cbctecnologia_trilogiq].[contactos] (intidempresa, strnombre, stremail, strtelefono1, intidtipocontacto, strcampo1) values ('".$data['icIdEmpresa']."', '".$data['icContacto']."', '".$data['icCorreo']."', '".$data['icTelefono']."', '".$data['icTipoContacto']."', '".$data['icArea']."')");
	}

	public function EditarContacto($data) {
		return $this->db->query("UPDATE top(1) [cbctecnologia_trilogiq].[contactos] set
		strnombre = '".$data['icContacto']."',
		stremail = '".$data['icCorreo']."',
		strtelefono1 = '".$data['icTelefono']."',
		intidtipocontacto = '".$data['icTipoContacto']."',
		strcampo1 = '".$data['icArea']."'
		where intidempresa = '".$data['icIdEmpresa']."' and intidcontacto = '".$data['icIdContacto']."'");
	}

	public function ObtenerContactos($idempresa) {
		$query = $this->db->query("SELECT TOP 1000 [intidcontacto], [intidempresa], [strnombre], [stremail], [strtelefono1], [strcampo1] FROM [cbctecnologia_trilogiq].[cbctecnologia_trilogiq].[contactos] where intidempresa = '$idempresa'");
		return $query->result();
	}

	public function ObtenerContactoByID($contacto) {
		$query = $this->db->query("SELECT stremail, strtelefono1, strcampo1 from [cbctecnologia_trilogiq].[contactos] where [intidcontacto] = '$contacto'");
		return $query->row();
	}

}