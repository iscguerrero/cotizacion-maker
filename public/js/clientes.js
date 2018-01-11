$(document).ready(function () { 
	$("#btnCliente").click(function () {
		$('#modalCliente').modal('show');
	});
	$("#btnContacto").click(function () {
		$('#modalContacto').modal('show');
	});
	// Configuracion del autocomplete del cliente
	$('#inEmpresa').autocomplete({
		source: "Clientes/ObtenerClienteEdit",
		minLength: 3,
		select: function (evt, ui) {
			setearClienteEdit(ui.item);
		}
	});
	// Configuracion del autocomplete de empresa en el formulario de contacto
	$('#icEmpresa').autocomplete({
		source: "Clientes/ObtenerClienteEdit",
		minLength: 3,
		select: function (evt, ui) {
			$('#icIdEmpresa').val(ui.item.id);
		}
	});
	// Configuracion del autocomplete del contacto
	$('#icContacto').autocomplete({
		source: function (request, response) {
			$.getJSON("Clientes/ObtenerContacto", { idempresa: $('#icIdEmpresa').val(), term: $('#icContacto').val() },
				response);
		},
		minLength: 3,
		select: function (evt, ui) {
			setearContacto(ui.item);
		}
	});
	// Cargamos los combos de los formularios
	cargarEstatus();
	cargarEstados();
	cargarMunicipios($('#inEstado').val());
	ObtenerTipoContacto();
	// Cargamos los municipios segun el estado seleccionado
	$('#inEstado').change(function () {
		cargarMunicipios($('#inEstado').val());
	});
	// Guardamos la informacion del cliente
	$('#formCrudCliente').submit(function (e) {
		e.preventDefault();
		guardarCliente();
	});
	// Guardamos la informacion del contacto
	$('#formContacto').submit(function (e) {
		e.preventDefault();
		guardarContacto();
	});
	// Resetear el formulario al cerrar el modal del cliente
	$('#modalCliente').on('hidden.bs.modal', function (e) {
		var data = { id: 'ID Cliente', value: '', estatus: '', rfc: '', telefono: '', mail: '', estado: 0, municipio: 0, colonia: '', direccion: '' }
		setearClienteEdit(data);
	});
	// Resetear el formulario al cerrar el modal del contacto
	$('#modalContacto').on('hidden.bs.modal', function (e) {
		$('#icIdEmpresa, #icEmpresa, #icContacto').val('');
		var data = { intidcontacto: '', strtelefono1: '', stremail: '', intidtipocontacto: '', strcampo1: '' };
		setearContacto(data);
	});
});
// Funcion para setear los datos cliente en el modal
function setearClienteEdit(data) {
	$('#btnId').text(data.id);
	$('#inEmpresa').val(data.value);
	$('#inEstatus').val(data.estatus);
	$('#inRFC').val(data.rfc);
	$('#inTelefono').val(data.telefono);
	$('#inCorreo').val(data.mail);
	$('#inEstado').val(data.estado);
	cargarMunicipios($('#inEstado').val());
	$('#inMunicipio').val(data.municipio);
	$('#inColonia').val(data.colonia);
	$('#inCP').val(data.cp);
	$('#inDireccion').val(data.direccion);
}

// Funcion para cargar la lista de posibles estatus del cliente
function cargarEstatus() {
	$.ajax({
		type: 'POST',
		url: 'Clientes/ObtenerEstatus',
		dataType: 'json',
		async: false,
		success: function (response) {
			$('#inEstatus').empty();
			$.each(response, function (index, item) {
				$('#inEstatus').append("<option value='" + item.intiddetallecatalogo + "'>" + item.strvalor + "</option>")
			});
		}
	});
}

// Funcion para cargar la lista de estados en el combo correspondiente
function cargarEstados() {
	$.ajax({
		type: 'POST',
		url: 'Clientes/ObtenerEstados',
		dataType: 'json',
		async: false,
		success: function (response) {
			$('#inEstado').empty();
			$.each(response, function (index, item) {
				$('#inEstado').append("<option value='" + item.lonidestado + "'>" + item.strnombre + "</option>")
			});
		}
	});
}

// Funcion para cargar la lista de municipios de un estado
function cargarMunicipios(estado) {
	$.ajax({
		type: 'POST',
		url: 'Clientes/ObtenerMunicipios',
		dataType: 'json',
		async: false,
		data: { estado: estado },
		success: function (response) {
			$('#inMunicipio').empty();
			$.each(response, function (index, item) {
				$('#inMunicipio').append("<option value='" + item.lonidmunicipio + "'>" + item.strnombre + "</option>")
			});
		}
	});
}

// Funcion para guardar la informacion del cliente
function guardarCliente() {
	var str = $('#formCrudCliente').serialize();
	str = str + "&id=" + $('#btnId').text();
	$.ajax({
		type: 'POST',
		url: 'Clientes/GuardarCliente',
		dataType: 'json',
		async: true,
		data: str,
		beforeSend: function () {
			$('#msjAlert').html('GUARDANDO DATOS, ESPERA POR FAVOR...');
			modalAlert.modal('show');
		},
		success: function (response) {
			$('#msjAlert').html(response.msj);
			if (response.bandera == true) {
				$('#modalCliente').modal('hide');
			}
		}
	});
}

// Funcion para guardar la informacion del contacto
function guardarContacto() {
	var str = $('#formContacto').serialize();
	$.ajax({
		type: 'POST',
		url: 'Clientes/GuardarContacto',
		dataType: 'json',
		async: true,
		data: str,
		beforeSend: function () {
			$('#msjAlert').html('GUARDANDO DATOS, ESPERA POR FAVOR...');
			modalAlert.modal('show');
		},
		success: function (response) {
			$('#msjAlert').html(response.msj);
			if (response.bandera == true) {
				$('#modalContacto').modal('hide');
			}
		}
	});
}

// Funcion para cargar el tipo de cliente
function ObtenerTipoContacto() {
	$.ajax({
		type: 'POST',
		url: 'Clientes/ObtenerTipoContacto',
		dataType: 'json',
		async: false,
		success: function (response) {
			$('#inTipoContacto').empty();
			$.each(response, function (index, item) {
				$('#icTipoContacto').append("<option value='" + item.intiddetallecatalogo + "'>" + item.strvalor + "</option>")
			});
		}
	});
}

// Funcion para setear los datos del contacto en el modal
function setearContacto(data) {
	$('#icIdContacto').val(data.intidcontacto);
	$('#icTelefono').val(data.strtelefono1);
	$('#icCorreo').val(data.stremail);
	$('#icTipoContacto').val(data.intidtipocontacto);
	$('#icArea').val(data.strcampo1);
}