$(document).ready(function(){
/*************** CARGA INICIAL DE LA INFORMACION DE LA COTIZACION ***************/
	nuevaCotizacion();
	folios = [];
/*************** CONFIGURACION GENERAL DEL COMPORTAMIENTO DE LA VISTA ***************/
// Configuracion del tooltip en la vista
	$('[data-toggle="tooltip"]').tooltip();

// Configuracion basica del datetimepicker
	$('.simple-dp').datetimepicker({
		locale: 'es',
		format: 'DD-MMMM-YYYY',
		ignoreReadonly: true,
		allowInputToggle: true
	});

// Configuracion del modal de mensajes del sistema
	modalAlert = $('#modalAlert').modal({
		backdrop: 'static',
		keyboard: false,
		show: false
	});

// Abrir el modal con el historico de cotizaciones
	$('#abrirCotizacion').click(function(){
		$('#modalCotizaciones').modal('show');
	});

	// Configuracion del autocomplete del cliente
	$('#nombre').autocomplete({
		source: "Clientes/ObtenerCliente",
		minLength: 3,
		select:function(evt, ui){
			setearCliente(ui.item);
		}
	});

	// Configuracion del autocomplete del producto especial
	$('#inputProducto').autocomplete({
		source: "Productos/ObtenerProductoPorNombre",
		minLength: 3,
		select:function(evt, ui){
			$(this).val(ui.item.value);
			$('#inputFam').val(ui.item.CON_SERIE);
			$('#inputPieza').val(ui.item.CVE_ART);
			$('#inputProducto').val(ui.item.DESCR);
			$('#inputPrecio').val(ui.item.PRECIO);
			$('#ult_costo').val(ui.item.ULT_COSTO);
		}
	});

// Agregar el producto especial seleccionada a la tabla de cotizacion
	$('#confirmarParte').click(function(){
		descuento = $('#std').val();
		precioPiezaAD = $('#inputPrecio').val();
		precioPiezaDD = precioPiezaAD - (precioPiezaAD * descuento / 100);
		piezas = $('#inputPiezas').val();
		replicas = piezas * $('#replica').val();
		if($('#inputPiezas').val() != ''){
			var row = {
				no_partida: 0,
				folio: '',
				ult_costo: $('#ult_costo').val(),
				cve_art: $('#inputPieza').val(),
				descripcion: $('#inputProducto').val(),
				precioPiezaAD: precioPiezaAD,
				precioPiezaDD: precioPiezaDD,
				piezas: piezas,
				descuento: descuento,
				precioParteDD: precioPiezaDD * piezas,
				replicas: replicas,
				precioReplicaAD: precioPiezaAD * replicas,
				precioReplicaDD: precioPiezaDD * replicas,
			};
			console.log(row);
			$('#tablaCotizacion').bootstrapTable('append', row);
			data = $('#tablaCotizacion').bootstrapTable('getData');
			actualizarNumeroPartidas(data);
			actualizarTotales();
			$('#modalProducto').modal('hide');
		} else{
			$('#msjAlert').html('DEBES PROPORCIONAR LA CANTIDAD DE PIEZAS')
			modalAlert.modal('show');
		}
	});

// Configuracion del dropzone para cargar el excel de la cotizacion
	Dropzone.autoDiscover = false;
	$('#excelArea').dropzone({
		// URL a donde se envia el archivo en los controladores
		url: 'Productos/RecibirExcel',
		// Configuracion de las propiedades del archivo que se pueden cargar al servidor
		maxFilesize: 2,
		paramName: 'cotizacion',
		maxFiles: 1,
		acceptedFiles: '.xls, .xlsx',
		addRemoveLinks: true,
		capture: false,
		// Configuracion de los mensajes del dropzone
		dictDefaultMessage: 'Arrastra y suelta la nueva cotización, en formato xlsx(excel), aquí',
		dictFallbackMessage: 'Tu navegador no soporta la función de arrastra y suelta archivo, inténtalo nuevamente después de actualizarlo',
		dictFileTooBig: 'El archivo seleccionado tiene un tamaño mayor al permitido (2Mb)',
		dictInvalidFileType: 'Solamente se permiten cargar arhivos en formato xlsx(Excel)',
		dictResponseError: 'Se presento un error al recibir la cotizacion en el servidor',
		dictCancelUpload: 'Cancelar',
		dictCancelUploadConfirmation: '¿Estás seguro de querer cancelar la carga del archivo?',
		dictRemoveFile: 'Remover archivo',
		dictMaxFilesExceeded: 'Solo se permite cargar un archivo a la vez',
		// Manipulacion de los momentos del enviado de archivos
		init:function(){
			var self = this;
			self.on('sending', function (file, xhr, formData) {
				formData.append("id_cliente", $('#ID').val());
				formData.append("nombre_cliente", $('#nombre').val());
				formData.append("nombre_empresa", $('#nombreEmpresa').val());
				formData.append("rfc", $('#RFC').val());
				formData.append("direccion", $('#direccion').val());
				formData.append("colonia", $('#colonia').val());
				formData.append("municipio", $('#municipio').val());
				formData.append("estado", $('#estado').val());
				formData.append("codigo_postal", $('#CP').val());
				formData.append("nombre_contacto", $('#contacto').val());
				formData.append("telefono", $('#telefono').val());
				formData.append("correo", $('#correo').val());
				formData.append("representante_ventas", $('#gestorDeCuenta').html());
				formData.append("terminos_y_condiciones", $('#terminosVenta').html());
				formData.append("observaciones", $('#observaciones').html());
				formData.append("tc", $('#tc').val());
				formData.append("replica", $('#replica').val());
				formData.append("std", $('#std').val());
				formData.append("impuestos", $('#impuestos').html());
				formData.append("descuento", $('#descuento').html());
				modalAlert.modal('show');
				$('#msjAlert').html('ESPERA UN MOMENTO POR FAVOR, CARGANDO ARCHIVO...');
			});
			self.on("queuecomplete", function (progress) {
				//modalAlert.modal('hide');
			});
			self.on("success", function (file, response) {
				response = JSON.parse(response);
				if(response.bandera == false){
					$('#msjAlert').html(response.msj);
					modalAlert.modal('show');
				} else{
					replica = $('#replica').val();
					tc = $('#tc').val();
					$('#tablaCotizacion').bootstrapTable('load', response.data);
					actualizarTotales();
					$('#pre_folio').val(response.pre_folio);
					$('#folio').val('');
					if(response.faltantes.length > 0){
						$('#faltantes').html("<strong>Los siguientes códigos de producto no se encuentran definidos en el catálogo de productos: </strong><strong style='color: red'>" + response.faltantes.join(', ') + "</strong>");
					} else{
						$('#faltantes').html("");
					}
					modalAlert.modal('hide');
					$('#rowCargar').hide();
				}
				self.removeFile(file);
			});
		}
	});

// Configuramos la accion del cuadro de texto de replica
	$('#replica').change(function(){
		replica = $(this);
		if(replica.val() != ''){
			modalAlert.modal('show');
			setTimeout(function() {
				resetReplica(replica.val());
				modalAlert.modal('hide');
			}, 1);
		}
	});

// Configuramos la accion del cuadro de texto del descuento
	$('#std').change(function(){
		std = $(this);
		if(std.val() != ''){
			modalAlert.modal('show');
			setTimeout(function() {
				resetDescuento(std.val());
				modalAlert.modal('hide');
			}, 1);
		}
	});

// Configuracion del accion del cuadro de texto de tipo de cambio
	$('#tc').change(function(){
		if($(this).val() != ''){
			modalAlert.modal('show');
			setTimeout(function() {
				actualizarTotales();
				modalAlert.modal('hide');
			}, 1);
		}
	});

// Abrir el modal para agregar un producto especial
	$('#agregarParte').click(function(e){
		e.preventDefault();
		$('#modalProducto').modal('show');
	});

// Modal para agregar un producto especial
	$('#modalProducto').on('hidden.bs.modal', function (e) {
		$('#inputPieza').val('');
		$('#inputProducto').val('');
		$('#inputPrecio').val('');
		$('#inputPiezas').val('');
	});

// Configuracion de la tabla de pre visualizacion de la cotizacion
	$('#tablaCotizacion').bootstrapTable({
		data: [],
		clickToSelect: true,
		toolbar: '#toolbar',
		uniqueId: 'no',
		columns: [[
			{title: 'Información de las Piezas', halign: 'center', valign: 'middle', colspan: 6},
			{title: 'Información de las Partes', halign: 'center', valign: 'middle', colspan: 3},
			{title: 'Información de las Replicas', halign: 'center', valign: 'middle', colspan: 3}
		], [
			{radio: true, align: 'center'},
			{field: 'no_partida', title: 'Item', align: 'center', halign: 'center', valign: 'middle',formatter: function (value, row, index) {
				return parseInt(value);
			}},
			{field: 'cve_art', title: 'Código', align: 'center', halign: 'center', valign: 'middle'},
			{field: 'descripcion', title: 'Descripción', valign: 'middle'},
			{field: 'precioPiezaAD', title: 'Precio AD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
				return formato_numero(value, 2, '.', ',')
			}},
			{field: 'precioPiezaDD', title: 'Precio DD', align: 'right', halign: 'right', formatter: function (value, row, index) {
				return formato_numero(value, 2, '.', ',')
			}},
			{field: 'piezas', title: 'Piezas', align: 'right', halign: 'right', valign: 'middle', editable: {
				type: 'text',
				mode: 'popup',
				showbuttons: false,
				success: function (response, newValue) {
					data = $('#tablaCotizacion').bootstrapTable('getData');
					index = $(this).closest('tr').attr('data-index');
					row = data[index];
					row['piezas'] = newValue;
					row['replicas'] = newValue * $('#replica').val();
					actualizarFila(index, row);
					actualizarTotales();
				}
			}},
			{field: 'descuento', title: 'Descuento', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
				return formato_numero(value, 2, '.', ',')
			}, editable: {
				type: 'text',
				mode: 'popup',
				showbuttons: false,
				success: function (response, newValue) {
					data = $('#tablaCotizacion').bootstrapTable('getData');
					index = $(this).closest('tr').attr('data-index');
					row = data[index];
					row['descuento'] = newValue;
					actualizarFila(index, row);
					actualizarTotales();
				}
			}},
			{field: 'precioParteDD', title: 'Precio DD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
				return formato_numero(value, 2, '.', ',')
			}},
			{field: 'replicas', title: 'Replicas', align: 'right', halign: 'right', valign: 'middle', editable: {
				type: 'text',
				mode: 'popup',
				showbuttons: false,
				success: function (response, newValue) {
					data = $('#tablaCotizacion').bootstrapTable('getData');
					index = $(this).closest('tr').attr('data-index');
					row = data[index];
					row['replicas'] = row['piezas'] * newValue;
					actualizarFila(index, row);
					actualizarTotales();
				}
			}},
			{field: 'precioReplicaAD', title: 'Precio AD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
				return formato_numero(value, 2, '.', ',')
			}},
			{field: 'precioReplicaDD', title: 'Precio DD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
				return formato_numero(value, 2, '.', ',')
			}},
			{field: 'ult_costo', title: 'costo', visible: false},
			{field: 'folio', title: 'folio', visible: false}
		]]
	});

// Funcion para remover la columna seleccionada de la previsualizacion
	$('#removerFila').click(function(){
		filasSeleccionadas = $('#tablaCotizacion').bootstrapTable('getSelections');
		if(filasSeleccionadas.length > 0){
			if(filasSeleccionadas[0]['folio'] != null && filasSeleccionadas[0]['folio'] != '') {
				folios.push(filasSeleccionadas[0]['folio']);
			}
			$('#tablaCotizacion').bootstrapTable('remove', {field: 'no_partida', values: [filasSeleccionadas[0]['no_partida']]});
			data = $('#tablaCotizacion').bootstrapTable('getData');
			actualizarNumeroPartidas(data);
			actualizarTotales();
			console.log(folios);
		}
	});

// Configuracion del pie de pagina de la tabla de previsualizacion de la cotizacion
	$('#gestorDeCuenta').editable({
		type: 'text',
		defaultValue: '',
		emptytext: 'Representante de Ventas',
		mode: 'popup',
		showbuttons: false,
		onblur: 'submit',
		classes: 'table-condensed'
	});

	$('#terminosVenta').editable({
		type: 'textarea',
		defaultValue: '',
		emptytext: 'Términos y Condiciones de Venta',
		mode: 'popup',
		showbuttons: true,
		onblur: 'submit'
	});

	$('#observaciones').editable({
		type: 'textarea',
		defaultValue: '',
		emptytext: 'Observaciones',
		mode: 'popup',
		showbuttons: true,
		onblur: 'submit'
	});

	$('#impuestos').editable({
		type: 'text',
		mode: 'popup',
		showbuttons: false,
		onblur: 'submit'
	}).on('hidden', function(e, reason) {
		actualizarTotales();
	});

	$('#descuento').editable({
		type: 'text',
		mode: 'popup',
		showbuttons: false,
		onblur: 'submit'
	}).on('hidden', function(e, reason) {
		actualizarTotales();
	});

// Configuracion de la tabla de cotizaciones
	$('#tablaCotizaciones').bootstrapTable({
		data: [],
		pagination: true,
		sidePagination: 'client',
		pageList: [10, 25, 50, 100, 200],
		locale: 'es-MX',
		clickToSelect: true,
		toolbar: '#toolbarCotizaciones',
		toolbarAlign: 'right',
		classes: 'table-condensed table-hover table-bordered',
		columns: [
			{field: 'folio', title: 'Cotizacion', align: 'center', halign: 'center', valign: 'middle'},
			{field: 'nombre_cliente', title: 'Cliente'},
			{field: 'fecha', title: 'Fecha', align: 'center', halign: 'center', valign: 'middle'},
			{field: 'totalPrecioRDD', title: 'Importe', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
				return formato_numero(value, 2, '.', ',')
			}},
			{field: 'estatus', title: 'Estatus', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
				string = '';
				switch(value){
					case 'A':
						string = "<span class='label label-default'>Abierta</span>"
						break;
					case 'B':
						string = "<span class='label label-primary'>Autorizada</span>"
						break;
					case 'C':
						string = "<span class='label label-info'>Impresa</span>"
						break;
					case 'B':
						string = "<span class='label label-success'>Cerrada</span>"
						break;
					case 'B':
						string = "<span class='label label-danger'>Rechazada</span>"
						break;
				}
				return string
			}},
			{title: 'Abrir', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
				return "<button type='button' class='btn btn-xs bttn-primary open'><i class='fa fa-folder-open-o'></i></button>"
			}},
		],
		onClickRow: function(row, $element, field){
			cargarCotizacion(row.folio);
		}
	});

// Guardamos los cambios hechos sobre la cotizacion
	$('#btnGuardar').click(function(e){
		e.preventDefault();
		pre_folio = $('#pre_folio').val();
		folio = $('#folio').val();
		if(pre_folio == '' && folio == '') {
			$('#msjAlert').html('NADA POR GUARDAR O NO SE HA DEFINIDO EL FOLIO DE LA COTIZACION');
			modalAlert.modal('show');
			return true
		}
		guardarCotizacion();
	});

// Obtener el listado de cotizaciones
	$('#filtrarCotizaciones').click(function(e){
		e.preventDefault();
		fi = $('#inputfi').val();
		ff = $('#inputff').val();
		$.ajax({
			async: true,
			type: 'POST',
			cache: false,
			data: {fi: fi, ff: ff},
			url: 'Cotizador/ObtenerCotizaciones',
			dataType: 'json',
			success: function(json){
				$('#tablaCotizaciones').bootstrapTable('load', json);
			}
		});
	});

// Imprimir la cotizacion en formato bulk
	$('#aBulk').click(function(e){
		e.preventDefault();
		if($('#folio').val() == '') {
			$('#msjAlert').html('ABRE O CREA UNA NUEVA COTIZACION QUE IMPRIMIR');
			modalAlert.modal('show');
			return true;
		}
		window.open('Cotizador/ImprimirCotizacion/'+$('#folio').val());
	});
});

// Funcion para dar formato a un numero
	function formato_numero(numero, decimales, separador_decimal, separador_miles){
		numero = parseFloat(numero);
		if(isNaN(numero)) return '';
		if(decimales!==undefined) numero=numero.toFixed(decimales);
		numero = numero.toString().replace('.', separador_decimal!==undefined ? separador_decimal : ',');
		if(separador_miles) {
			var miles=new RegExp("(-?[0-9]+)([0-9]{3})");
			while(miles.test(numero)) {
				numero=numero.replace(miles, '$1' + separador_miles + '$2');
			}
		}
		return numero;
	}

// Funcion para setear el formulario con la informacion del cliente
	var setearCliente = function(response){
		$('#nombreEmpresa').val(response.value);
		$('#ID').val(response.ID);
		$('#RFC').val(response.RFC);
		$('#direccion').val(response.DOMICILIO);
		$('#colonia').val(response.COLONIA);
		$('#municipio').val(response.MUNICIPIO);
		$('#estado').val(response.ESTADO);
		$('#CP').val(response.CP);
		$('#contacto').val(response.REPRESENTANTE);
		$('#telefono').val(response.TELEFONO);
		$('#correo').val(response.CORREO);
	}

// Funcion para obtener el maximo descuento permitido por gerencia
	var obtenerMaximoDescuento = function(){
		var maximoDescuento;
		$.ajax({
			type: 'POST',
			url: 'Descuentos/ObtenerDescuentoMaximo',
			dataType: 'json',
			async: false,
			success: function (response) {
				maximoDescuento = response.descuento;
			}
		});
		return maximoDescuento;
	}

// Funcion para reestablecer la replica de la tabla de cotizacion
	var resetReplica = function(replica){
		data = $('#tablaCotizacion').bootstrapTable('getData');
		$.each(data, function(index, row){
			row['replicas'] = row['piezas'] * replica;
			$('#tablaCotizacion').bootstrapTable('updateRow', {index, row});
		});
		actualizarTabla();
	}

// Funcion para reestablecer el descuento de la tabla de cotizacion
	var resetDescuento = function(descuento){
		data = $('#tablaCotizacion').bootstrapTable('getData');
		$.each(data, function(index, row){
			row['descuento'] = descuento;
			$('#tablaCotizacion').bootstrapTable('updateRow', {index, row});
		});
		actualizarTabla();
	}

// Funcion para obtener el tipo de cambio del web service de banxico
	var obtenerTipoCambio = function () {
		var tc = 0;
		$.ajax({
			type: 'POST',
			url: 'Cotizador/ObtenerTC',
			dataType: 'json',
			async: false,
			success: function (response) {
				tc = response.tc;
				fecha_tc = response.fecha_tc;
			}
		});
		return [tc, fecha_tc];
	}

// Actualizacion del numero de la partida
	var actualizarNumeroPartidas = function(data){
		$.each(data, function(index, row){
			row['no_partida'] = index + 1;
			$('#tablaCotizacion').bootstrapTable('updateRow', {index, row});
		});
	}

// Funcion para actualizar el campo de replica y los campos calculados
	var actualizarTabla = function(){
		data = $('#tablaCotizacion').bootstrapTable('getData');
		$.each(data, function(index, row){
			actualizarFila(index, row);
		});
		actualizarTotales();
	}

// Funcion para actualizar los campos calculados de la fila
	var actualizarFila = function(index, row){
		row['precioPiezaDD'] = row['precioPiezaAD'] - (row['precioPiezaAD'] * row['descuento'] / 100);
		row['precioParteDD'] = row['piezas'] * row['precioPiezaDD'];
		row['precioReplicaAD'] = row['replicas'] * row['precioPiezaAD'];
		row['precioReplicaDD'] = row['replicas'] * row['precioPiezaDD'];
		$('#tablaCotizacion').bootstrapTable('updateRow', {index, row});
	}

// Funcion para calcular los totales de la cotizacion
	var actualizarTotales = function(){
		tc = $('#tc').val();
		replica = $('#replica').val();
		impuestos = parseInt($('#impuestos').html());
		descuento = parseInt($('#descuento').html());
		data = $('#tablaCotizacion').bootstrapTable('getData');

		stUsdPrecioPDD = 0;
		stUsdPrecioRAD = 0;
		stUsdPrecioRDD = 0;
		costo_total = 0;
		utilidad = 0;

		$.each(data, function(index, row){
			stUsdPrecioPDD = stUsdPrecioPDD + parseFloat(row['precioParteDD']);
			stUsdPrecioRAD = stUsdPrecioRAD + parseFloat(row['precioReplicaAD']);
			stUsdPrecioRDD = stUsdPrecioRDD + parseFloat(row['precioReplicaDD']);
			costo_total = costo_total + (parseFloat(row['ult_costo']) * parseFloat(row['replicas']));
		});

		stMxpPrecioPDD = stUsdPrecioPDD * tc;
		stMxpPrecioRAD = stUsdPrecioRAD * tc;
		stMxpPrecioRDD = stUsdPrecioRDD * tc;
		costo_total = costo_total * tc;

		descuentoPrecioPDD = stMxpPrecioPDD * descuento / 100;
		descuentoPrecioRAD = stMxpPrecioRAD * descuento / 100;
		descuentoPrecioRDD = stMxpPrecioRDD * descuento / 100;

		stPrecioPDD = stMxpPrecioPDD - descuentoPrecioPDD;
		stPrecioRAD = stMxpPrecioRAD - descuentoPrecioRAD;
		stPrecioRDD = stMxpPrecioRDD -descuentoPrecioRDD;

		ivaPrecioPDD = stPrecioPDD * impuestos / 100;
		ivaPrecioRAD = stPrecioRAD * impuestos / 100;
		ivaPrecioRDD = stPrecioRDD * impuestos / 100;

		totalPrecioPDD = stPrecioPDD + ivaPrecioPDD;
		totalPrecioRAD = stPrecioRAD + ivaPrecioRAD;
		totalPrecioRDD = stPrecioRDD + ivaPrecioRDD;

		utilidad = totalPrecioRDD - costo_total;

		// Se actualizan los valores de los controles de los totales
		$('#stUsdPrecioPDD').html(formato_numero(stUsdPrecioPDD, 2, '.', ','));
		$('#stUsdPrecioRAD').html(formato_numero(stUsdPrecioRAD, 2, '.', ','));
		$('#stUsdPrecioRDD').html(formato_numero(stUsdPrecioRDD, 2, '.', ','));

		$('#stMxpPrecioPDD').html(formato_numero(stMxpPrecioPDD, 2, '.', ','));
		$('#stMxpPrecioRAD').html(formato_numero(stMxpPrecioRAD, 2, '.', ','));
		$('#stMxpPrecioRDD').html(formato_numero(stMxpPrecioRDD, 2, '.', ','));

		$('#descuentoPrecioPDD').html(formato_numero(descuentoPrecioPDD, 2, '.', ','));
		$('#descuentoPrecioRAD').html(formato_numero(descuentoPrecioRAD, 2, '.', ','));
		$('#descuentoPrecioRDD').html(formato_numero(descuentoPrecioRDD, 2, '.', ','));

		$('#stPrecioPDD').html(formato_numero(stPrecioPDD, 2, '.', ','));
		$('#stPrecioRAD').html(formato_numero(stPrecioRAD, 2, '.', ','));
		$('#stPrecioRDD').html(formato_numero(stPrecioRDD, 2, '.', ','));

		$('#ivaPrecioPDD').html(formato_numero(ivaPrecioPDD, 2, '.', ','));
		$('#ivaPrecioRAD').html(formato_numero(ivaPrecioRAD, 2, '.', ','));
		$('#ivaPrecioRDD').html(formato_numero(ivaPrecioRDD, 2, '.', ','));

		$('#totalPrecioPDD').html(formato_numero(totalPrecioPDD, 2, '.', ','));
		$('#totalPrecioRAD').html(formato_numero(totalPrecioRAD, 2, '.', ','));
		$('#totalPrecioRDD').html(formato_numero(totalPrecioRDD, 2, '.', ','));

		$('#utilidad').html(formato_numero(utilidad, 2, '.', ','));
	}

// Funcion para inicializar una nueva cotizacion
	var nuevaCotizacion = function(){
		// Cargamos el tipo de cambio de la cotizacion
		var tc_info = obtenerTipoCambio();
		$('#tc').val(tc_info[0]).attr('title', 'Fuente Banxico, fecha ' + tc_info[1]);
	}

// Funcion para cargar una cotizacion guardada con anterioriodad
	var cargarCotizacion = function(folio){
		$.ajax({
			async: true,
			type: 'POST',
			cache: false,
			data: {folio: folio},
			url: 'Cotizador/ObtenerEncabezado',
			dataType: 'json',
			beforeSend: function(){
				$('#msjAlert').html('CARGANDO COTIZACION, ESPERA POR FAVOR...');
				modalAlert.modal('show');
			},
			success: function(json){
				$('#msjAlert').html(json.msj);
				if(json.bandera == true) {
					en = json.encabezado;
					// Se carga el encabezado
					$('#folio').val(parseInt(en.folio));
					$('#pre_folio').val(parseInt(en.folio_preencabezado));
					$('#nombre').val(en.nombre_cliente);
					$('#nombreEmpresa').val(en.nombre_empresa);
					$('#ID').val(en.id_cliente);
					$('#RFC').val(en.rfc);
					$('#direccion').val(en.direccion);
					$('#colonia').val(en.colonia);
					$('#municipio').val(en.municipio);
					$('#estado').val(en.estado);
					$('#CP').val(en.codigo_postal);
					$('#contacto').val(en.nombre_contacto);
					$('#telefono').val(en.telefono);
					$('#correo').val(en.correo);
					$('#tc').val(en.tipo_cambios);
					$('#replica').val(en.replicas);
					$('#std').val(parseFloat(en.descuento_sobre_pieza, 2));
					$('#gestorDeCuenta').html(en.representante_ventas);
					$('#terminosVenta').html(en.terminos_y_condiciones);
					$('#observaciones').html(en.observaciones);
					$('#descuento').html(parseFloat(en.descuentost, 2));
					$('#impuestos').html(parseFloat(en.tasa_impuesto, 2));
					setearPartidas();
					modalAlert.modal('hide');
					$('#modalCotizaciones').modal('hide');
				}
			}
		});
	}

// Funcion para guardar los cambios sobre la cotizacion
	var guardarCotizacion = function(){
		pre_folio = $('#pre_folio').val();
		folio = $('#folio').val();
		cliente = $('#formCliente').serializeArray();
		partidas = $('#tablaCotizacion').bootstrapTable('getData');
		encabezado = {
			tc: $('#tc').val(),
			replica: $('#replica').val(),
			std: $('#std').val(),
			representante: $('#gestorDeCuenta').text(),
			terminos: $('#terminosVenta').text(),
			observaciones: $('#observaciones').text(),
			stUsdPrecioPDD: $('#stUsdPrecioPDD').html(),
			stUsdPrecioRAD: $('#stUsdPrecioRAD').html(),
			stUsdPrecioRDD: $('#stUsdPrecioRDD').html(),
			stMxpPrecioPDD: $('#stMxpPrecioPDD').html(),
			stMxpPrecioRAD: $('#stMxpPrecioRAD').html(),
			stMxpPrecioRDD: $('#stMxpPrecioRDD').html(),
			descuentoPrecioPDD: $('#descuentoPrecioPDD').html(),
			descuentoPrecioRAD: $('#descuentoPrecioRAD').html(),
			descuentoPrecioRDD: $('#descuentoPrecioRDD').html(),
			stPrecioPDD: $('#stPrecioPDD').html(),
			stPrecioRAD: $('#stPrecioRAD').html(),
			stPrecioRDD: $('#stPrecioRDD').html(),
			ivaPrecioPDD: $('#ivaPrecioPDD').html(),
			ivaPrecioRAD: $('#ivaPrecioRAD').html(),
			ivaPrecioRDD: $('#ivaPrecioRDD').html(),
			totalPrecioPDD: $('#totalPrecioPDD').html(),
			totalPrecioRAD: $('#totalPrecioRAD').html(),
			totalPrecioRDD: $('#totalPrecioRDD').html(),
			utilidad: $('#utilidad').html(),
			tasa_impuesto: $('#impuestos').html(),
			descuentost: $('#descuento').html()
		}
		$.ajax({
			async: true,
			type: 'POST',
			cache: false,
			data: {pre_folio: pre_folio, folio: folio, cliente: cliente, encabezado: encabezado, partidas: partidas, folios: folios},
			url: 'Cotizador/GuardarCotizacion',
			dataType: 'json',
			beforeSend: function(){
				$('#msjAlert').html('CARGANDO COTIZACION, ESPERA POR FAVOR...');
				modalAlert.modal('show');
			},
			success: function(json){
				$('#msjAlert').html(json.msj);
				if(json.bandera == true) {
					$('#folio').val(json.folio);
					setearPartidas();
					modalAlert.modal('hide');
				}
			}
		});
	}

// Funcion para setear las partidas de la cotizacion en la vista
var setearPartidas = function(){
	folio = $('#folio').val();
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: {folio: folio},
		url: 'Cotizador/ObtenerPartidas',
		dataType: 'json',
		success: function(json){
			$('#tablaCotizacion').bootstrapTable('load', json);
			actualizarTotales();
		}
	});
}