<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clientes extends Base_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->database('sqlserver');
	}
	# Metodo usado para obtener los datos del autocomplete del cliente
	public function ObtenerCliente(){
		if(!$this->input->is_ajax_request()) show_404();
		# Guardamos el termino de busqueda en una variable local
		$nombre = $this->input->get('term');
		# Cargamos el modelo para obtener los datos del cliente
			$this->load->model('cliente');
			$clientes = $this->cliente->ObtenerCliente($nombre);
		exit(json_encode($clientes));
	}

	# Metodo usado para obtener los datos cliente con el ID
	public function ObtenerClientexID(){
		if(!$this->input->is_ajax_request()) show_404();
		# Guardamos el termino de busqueda en una variable local
		$ID = $this->input->post('ID');
		# Cargamos el modelo para obtener los datos del cliente
			$this->load->model('cliente');
			$clientes = $this->cliente->ObtenerClientexID($ID);
		exit(json_encode($clientes));
	}

	# Metodo usado para obtener los datos del autocomplete del cliente de modal
	public function ObtenerClienteEdit(){
		if(!$this->input->is_ajax_request()) show_404();
		# Guardamos el termino de busqueda en una variable local
		$nombre = $this->input->get('term');
		# Cargamos el modelo para obtener los datos del cliente
			$this->load->model('Cliente');
			$clientes = $this->Cliente->ObtenerClienteEdit($nombre);
		exit(json_encode($clientes));
	}
	# Metodo para obtener los posibles estatus del cliente
	public function ObtenerEstatus(){
		if(!$this->input->is_ajax_request()) show_404();
			$this->load->model('Cliente');
			$response = $this->Cliente->ObtenerEstatus();
		exit(json_encode($response));
	}
	# Metodo para obtener los estados de la federación
	public function ObtenerEstados(){
		if(!$this->input->is_ajax_request()) show_404();
			$this->load->model('Cliente');
			$response = $this->Cliente->ObtenerEstados();
		exit(json_encode($response));
	}
	# Metodo para obtener los municipios de un estado
	public function ObtenerMunicipios(){
		if(!$this->input->is_ajax_request()) show_404();
			$this->load->model('Cliente');
			$response = $this->Cliente->ObtenerMunicipios($this->input->post('estado'));
		exit(json_encode($response));
	}
	# Metodo para guardar la informacion del cliente
	public function GuardarCliente() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->form_validation->set_rules('inEmpresa', 'Nombre Empresa', 'trim|required', array(
			'required' => 'El campo Nombre de la Empresa es requerido'
		));
		$this->form_validation->set_rules('inRFC', 'RFC', 'trim|required|min_length[12]|max_length[13]', array(
			'required' => 'El campo RFC es requerido',
			'min_length' => 'El campo RFC debe contener al menos 12 caracteres',
			'max_length' => 'El campo RFC debe contener máximo 13 caracteres'
		));
		if ($this->form_validation->run() === false) exit(json_encode(array('bandera'=>false, 'msj'=>'Las validaciones del formulario no se completaron, atiende: ' . validation_errors())));

		$data = array(
			'inEmpresa' => $this->input->post('inEmpresa'),
			'inEstatus' => $this->input->post('inEstatus'),
			'inRFC' => $this->input->post('inRFC'),
			'inTelefono' => $this->input->post('inTelefono'),
			'inCorreo' => $this->input->post('inCorreo'),
			'inEstado' => $this->input->post('inEstado'),
			'inMunicipio' => $this->input->post('inMunicipio'),
			'inColonia' => $this->input->post('inColonia'),
			'inCP' => $this->input->post('inCP'),
			'inDireccion' => $this->input->post('inDireccion'),
			'id' => $this->input->post('id')
		);

		$this->load->model('Cliente');
		$this->db->trans_start();

		if( $data['id'] == 'ID Cliente' ) {
			if( count($this->Cliente->VerificarRfc($data['inRFC'])) > 0 ) exit(json_encode(array('bandera'=>false, 'msj'=>'El rfc proporcionado ya se encuentra registrado en el sistema')));
			$this->Cliente->AltaCliente($data);
		} else {
			$this->Cliente->EditarCliente($data);
		}
		$this->db->trans_complete();

		$this->db->trans_status() === FALSE ? exit(json_encode(array('bandera'=>false, 'msj'=>'Se presento un error al ejecutar la operación'))) : exit(json_encode(array('bandera'=>true, 'msj'=>'La operación se realizó con éxito')));
	}
	# Funcion para obtener el tipo de contacto
	public function ObtenerTipoContacto(){
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('Cliente');
		$response = $this->Cliente->ObtenerTipoContacto();
		exit(json_encode($response));
	}
	# Metodo usado para obtener los datos del autocomplete del contacto
	public function ObtenerContacto(){
		if(!$this->input->is_ajax_request()) show_404();
		# Guardamos el termino de busqueda en una variable local
		$nombre = $this->input->get('term');
		$idempresa = $this->input->get('idempresa');
		# Cargamos el modelo para obtener los datos del contacto
			$this->load->model('Cliente');
			$response = $this->Cliente->ObtenerContacto($nombre, $idempresa);
		exit(json_encode($response));
	}

	# Metodo usado para obtener los datos del autocomplete del contacto
	public function ObtenerContactoID(){
		if(!$this->input->is_ajax_request()) show_404();
		# Guardamos el termino de busqueda en una variable local
		$nombre = $this->input->get('term');
		$idempresa = $this->input->get('idempresa');
		# Cargamos el modelo para obtener los datos del contacto
			$this->load->model('Cliente');
			$response = $this->Cliente->ObtenerContactoID($nombre, $idempresa);
		exit(json_encode($response));
	}

	# Metodo para guardar la informacion del contacto
	public function GuardarContacto() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->form_validation->set_rules('icIdEmpresa', 'ID Empresa', 'trim|required', array(
			'required' => 'El campo ID Empresa es requerido'
		));
		$this->form_validation->set_rules('icContacto', 'Contacto', 'trim|required', array(
			'required' => 'El campo Contacto es requerido'
		));
		$this->form_validation->set_rules('icTipoContacto', 'Tipo Contacto', 'trim|required', array(
			'required' => 'El campo Tipo contacto es requerido'
		));
		if ($this->form_validation->run() === false) exit(json_encode(array('bandera'=>false, 'msj'=>'Las validaciones del formulario no se completaron, atiende: ' . validation_errors())));

		$data = array(
			'icIdEmpresa' => $this->input->post('icIdEmpresa'),
			'icIdContacto' => $this->input->post('icIdContacto'),
			'icContacto' => $this->input->post('icContacto'),
			'icTelefono' => $this->input->post('icTelefono'),
			'icCorreo' => $this->input->post('icCorreo'),
			'icTipoContacto' => $this->input->post('icTipoContacto'),
			'icArea' => $this->input->post('icArea')
		);

		$this->load->model('Cliente');
		$this->db->trans_start();

		if( $data['icIdContacto'] == '' ) {
			$this->Cliente->AltaContacto($data);
		} else {
			$this->Cliente->EditarContacto($data);
		}
		$this->db->trans_complete();

		$this->db->trans_status() === FALSE ? exit(json_encode(array('bandera'=>false, 'msj'=>'Se presento un error al ejecutar la operación'))) : exit(json_encode(array('bandera'=>true, 'msj'=>'La operación se realizó con éxito')));
	}
	# Metodo para obtener la lista de contactos de una empresa
	public function ObtenerContactos() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('Cliente');
		$response = $this->Cliente->ObtenerContactos($this->input->post('idempresa'));
		exit(json_encode($response));
	}
	# Metodo usado para obtener los datos del autocomplete del contacto
	public function ObtenerContactoByID(){
		if(!$this->input->is_ajax_request()) show_404();
		# Cargamos el modelo para obtener los datos del contacto
			$this->load->model('Cliente');
			$response = $this->Cliente->ObtenerContactoByID($this->input->post('contacto'));
		exit(json_encode($response));
	}
	# Metodo para obtener la lista de contactos de una empresa
	public function ObtenerTQs() {
		if(!$this->input->is_ajax_request()) show_404();
		$this->load->model('Cliente');
		$response = $this->Cliente->ObtenerTQs($this->input->post('idempresa'));
		exit(json_encode($response));
	}

}
