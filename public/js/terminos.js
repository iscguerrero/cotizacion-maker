var $container = document.getElementById('tCaptura'), hot;

hot = new Handsontable($container, {
	data: cargarTerminos(),
	rowHeaders: true,
	colHeaders: true,
	minSpareRows: 1,
	contextMenu: true,
	colHeaders: ['ID', 'Clase', 'Tipo', 'Redacción', 'E'],
	colWidths: [100, 80, 150, 620, 30],
	columnSorting: true,
	contextMenu: true,
	minSpareRows: 1,
	contextMenu: ['copy', 'paste'],
	columns: [
		{
			data: 'id',
			readOnly: true
		}, {
			data: 'clase',
			editor: 'select',
			selectOptions: ['tyc', 'obs']
		}, {
			data: 'tipo',
			editor: 'select',
			selectOptions: cargarTipos()
		}, {
			data: 'redaccion'
		}, {
			data: 'estatus',
			editor: 'select',
			selectOptions: ['A', 'X']
		}
	]
});

$('#btnGuardar').click(function () {
//	var handsontable = $container.data('handsontable');
	var data = hot.getData();
	$.ajax({
		type: 'POST',
		url: 'Condiciones/GuardarCambios',
		data: {condiciones: data},
		dataType: 'json',
		async: true,
		beforeSend: function () {
			$('#msjAlerta').html('Procesando solicitud, espera por favor...');
			$('#modalAlerta').modal('show');
		},
		success: function (response) {
			$('#msjAlerta').html(response.msj);
			if (response.bandera == true) {
				hot.loadData(cargarTerminos());
			}
		}
	});
});

function cargarTerminos() {
	var data = [];
	$.ajax({
		type: 'POST',
		url: 'Condiciones/ObtenerCondiciones',
		dataType: 'json',
		async: false,
		success: function (response) {
			data = response;
		}
	});
	return data;
}

function cargarTipos() {
	var data = [];
	$.ajax({
		type: 'POST',
		url: 'Condiciones/ObtenerTipos',
		dataType: 'json',
		async: false,
		success: function (response) {
			$.each(response, function (index, item) {
				data.push(item.tipo);
			});
		}
	});
	return data;
}