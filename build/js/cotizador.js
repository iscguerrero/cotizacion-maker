$(document).ready(function(){
/*************** CARGA INICIAL DE LA INFORMACION DE LA COTIZACION ***************/
	nuevaCotizacion();
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
		}
	});
// Agregar el producto especial seleccionada a la tabla de cotizacion
	$('#confirmarParte').click(function(){
		descuento = $('#std').val();
		precioPiezaAD = $('#inputPieza').val();
		precioPiezaDD = precioPiezaAD - (precioPiezaAD * descuento / 100);
		piezas = $('#inputPiezas').val();
		replicas = piezas * $('#replica').val();
		if($('#inputPiezas').val() != ''){
			var row = {
				no: 0,
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
				formData.append("tc", $('#tc').val());
				formData.append("replica", $('#replica').val());
				formData.append("std", $('#std').val());
				modalAlert.modal('show');
				$('#msjAlert').html('ESPERA UN MOMENTO POR FAVOR, CARGANDO ARCHIVO...');
			});
			self.on("queuecomplete", function (progress) {
				modalAlert.modal('hide');
			});
			self.on("success", function (file, response) {
				replica = $('#replica').val();
				tc = $('#tc').val();
				response = JSON.parse(response);
				$('#tablaCotizacion').bootstrapTable('load', response.data);
				actualizarTotales(response.data, tc, replica);
				if(response.faltantes.length > 0){
					$('#faltantes').html("<strong>Los siguientes códigos de producto no se encuentran definidos en el catálogo de productos: </strong><strong style='color: red'>" + response.faltantes.join(', ') + "</strong>");
				} else{
					$('#faltantes').html("");
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
			{field: 'no', title: 'Item', align: 'center', halign: 'center', valign: 'middle'},
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
			}}
		]]
	});
// Funcion para remover la columna seleccionada de la previsualizacion
	$('#removerFila').click(function(){
		filasSeleccionadas = $('#tablaCotizacion').bootstrapTable('getSelections');
		if(filasSeleccionadas.length > 0){
			$('#tablaCotizacion').bootstrapTable('remove', {field: 'no', values: [filasSeleccionadas[0]['no']]});
			data = $('#tablaCotizacion').bootstrapTable('getData');
			actualizarNumeroPartidas(data);
			actualizarTotales();
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
		clickToSelect: true,
		toolbar: '#toolbarCotizaciones',
		toolbarAlign: 'right',
		classes: 'table-condensed table-hover',
		columns: [
			{radio: true, align: 'center'},
			{field: 'no', title: 'Cotizacion', align: 'center', halign: 'center', valign: 'middle'},
			{field: 'cliente', title: 'Cliente', align: 'center', halign: 'center', valign: 'middle'},
			{field: 'fecha', title: 'Fecha', align: 'center', halign: 'center', valign: 'middle'},
			{field: 'importe', title: 'Importe', align: 'center', halign: 'center', valign: 'middle'},
			{field: 'estatus', title: 'Estatus', align: 'center', halign: 'center', valign: 'middle'},
		]
	});
// Guardamos los cambios hechos sobre la cotizacion
	$('#btnGuardar').click(function(e){
		e.preventDefault();
		cliente = $('#formCliente').serializeArray();
		encabezado = { tc: $('#tc').val(), replica: $('#replica').val(), descuento: $('#descuento').val(), representante: $('#accountManager').text(), terminos: $('#paymentTerms').text(), observaciones: $('#observaciones').text(), subTotalUsdUnitPrice: $('#subTotalUsdUnitPrice').html(), subTotalUsdSubTotMulti: $('#subTotalUsdSubTotMulti').html(), subTotalUsdTotalCostUnit: $('#subTotalUsdTotalCostUnit').html(), subTotalMxpUnitPrice: $('#subTotalMxpUnitPrice').html(), subTotalMxpSubTotMulti: $('#subTotalMxpSubTotMulti').html(), subTotalMxpTotalCostUnit: $('#subTotalMxpTotalCostUnit').html(), ivaUnitPrice: $('#ivaUnitPrice').html(), ivaSubTotMulti: $('#ivaSubTotMulti').html(), ivaTotalCostUnit: $('#ivaTotalCostUnit').html(), totalConIvaUnitPrice: $('#totalConIvaUnitPrice').html(), totalConIvaSubTotMulti: $('#totalConIvaSubTotMulti').html(), totalConIvaTotalCostUnit: $('#totalConIvaTotalCostUnit').html(), unitRealUnitPrice: $('#unitRealUnitPrice').html(), tRealSubTotMulti: $('#unitRealSubTotMulti').html(), unitRealTotalCostUnit: $('#unitRealTotalCostUnit').html()}
		partidas = $('#tablaCotizacion').bootstrapTable('getData');
		$.ajax({
			async: true,
			type: 'POST',
			cache: false,
			data: {cliente: cliente, encabezado: encabezado, partidas: partidas},
			url: 'Cotizador/GuardarCotizacion',
			dataType: 'json',
			beforeSend: function(){
				$('#msjAlert').html('CARGANDO COTIZACION, ESPERA POR FAVOR...');
				modalAlert.modal('show');
			},
			success: function(json){
				$('#msjAlert').html(json.msj);
				if(json.flag == true){
					
				}
			}
		});
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
			row['no'] = index + 1;
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
		row['precioParteDD'] = row['replicas'] * row['precioPiezaDD'];
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
		$.each(data, function(index, row){
			stUsdPrecioPDD = stUsdPrecioPDD + row['precioParteDD'];
			stUsdPrecioRAD = stUsdPrecioRAD + row['precioReplicaAD'];
			stUsdPrecioRDD = stUsdPrecioRDD + row['precioReplicaDD'];
		});

		stMxpPrecioPDD = stUsdPrecioPDD * tc;
		stMxpPrecioRAD = stUsdPrecioRAD * tc;
		stMxpPrecioRDD = stUsdPrecioRDD * tc;

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
	}
// Funcion para inicializar una nueva cotizacion
	var nuevaCotizacion = function(){
		// Cargamos el tipo de cambio de la cotizacion
		var tc_info = obtenerTipoCambio();
		$('#tc').val(tc_info[0]).attr('title', 'Fuente Banxico, fecha ' + tc_info[1]);
	}
// Funcion para cargar una cotizacion guardada con anterioriodad
	var cargarCotizacion = function(folio){

	}
// Funcion para guardar los cambios sobre la cotizacion
	var guardarCotizacion = function(){

	}