$(document).ready(function () {
	/*************** CARGA INICIAL DE LA INFORMACION DE LA COTIZACION ***************/
	nuevaCotizacion();
	folios = [];
	var opened;
	var selectedRow = {};
	$('#tipo').change(function () {
		$('#nombre').prop('readonly', $(this).val() != '' ? false : true);
	});
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

	// Recargar la vista para crear una nueva cotizacion
	$('#nuevaCotizacion').click(function () {
		location.reload();
	});

	// Configuracion del modal de mensajes del sistema
	modalAlert = $('#modalAlert').modal({
		backdrop: 'static',
		keyboard: false,
		show: false
	});

	// Abrir el modal con el historico de cotizaciones
	$('#abrirCotizacion').click(function () {
		$('#modalCotizaciones').modal('show');
	});

	// Configuracion del autocomplete del cliente
	$('#nombre').autocomplete({
		source: "Clientes/ObtenerCliente",
		minLength: 3,
		select: function (evt, ui) {
			setearCliente(ui.item);
			$('#tipo').prop('disabled', true);
		}
	});

	// Configuracion del autocomplete del producto especial
	$('#inputProducto').autocomplete({
		source: "Productos/ObtenerProductoPorNombre",
		minLength: 1,
		select: function (evt, ui) {
			$('#inputPieza').val(ui.item.CVE_ART);
			$('#inputPrecio').val(ui.item.PRECIO);
			$('#ult_costo').val(ui.item.ULT_COSTO);
			if (ui.item.CVE_ART.substring(0, 1) == 'Z' || ui.item.CVE_ART.substring(0, 1) == 'z') {
				$('#inputPrecio').prop('readonly', false);
			}
		}
	});

	// Agregar el producto especial seleccionada a la tabla de cotizacion
	$('#confirmarParte').click(function () {
		descuento = 0;
		precioPiezaAD = $('#inputPrecio').val();
		precioPiezaDD = precioPiezaAD - (precioPiezaAD * descuento / 100);
		piezas = $('#inputPiezas').val();
		replicas = piezas * $('#replica').val();
		arrayProducto = $('#inputProducto').val().split(' - ');
		if ($('#inputPiezas').val() != '') {
			var row = {
				no_partida: 0,
				folio: '',
				ult_costo: $('#ult_costo').val(),
				cve_art: $('#inputPieza').val(),
				descripcion: arrayProducto[1],
				precioPiezaAD: precioPiezaAD,
				precioPiezaDD: precioPiezaDD,
				piezas: piezas,
				descuento: descuento,
				precioParteDD: precioPiezaDD * piezas,
				replicas: replicas,
				precioReplicaAD: precioPiezaAD * replicas,
				precioReplicaDD: precioPiezaDD * replicas,
				ult_costo: 0,
				utilidad: 0
			};
			$('#tablaCotizacion').bootstrapTable('append', row);
			data = $('#tablaCotizacion').bootstrapTable('getData');
			actualizarNumeroPartidas(data);
			actualizarTotales();
			$('#modalProducto').modal('hide');
		} else {
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
		init: function () {
			var self = this;
			self.on('sending', function (file, xhr, formData) {
				formData.append("tipo", $('#tipo').val());
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
			self.on("success", function (file, response) {
				response = JSON.parse(response);
				console.log(response);
				if (response.bandera == false) {
					$('#msjAlert').html(response.msj);
					modalAlert.modal('show');
				} else {
					replica = $('#replica').val();
					tc = $('#tc').val();
					$('#tablaCotizacion').bootstrapTable('load', response.data);
					actualizarTotales();
					$('#pre_folio').val(response.pre_folio);
					$('#folio').val('');
					if (response.faltantes.length > 0) {
						$('#faltantes').html("<strong>Los siguientes códigos de producto no se encuentran definidos en el catálogo de productos: </strong><strong style='color: red'>" + response.faltantes.join(', ') + "</strong>");
					} else {
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
	$('#replica').change(function () {
		replica = $(this);
		if (replica.val() != '') {
			modalAlert.modal('show');
			setTimeout(function () {
				resetReplica(replica.val());
				modalAlert.modal('hide');
			}, 1);
		}
	});

	// Configuramos la accion del cuadro de texto del descuento
	$('#std').change(function () {
		std = $(this);
		if (std.val() != '') {
			modalAlert.modal('show');
			setTimeout(function () {
				resetDescuento(std.val());
				modalAlert.modal('hide');
			}, 1);
		}
	});

	// Configuracion del accion del cuadro de texto de tipo de cambio
	$('#tc').change(function () {
		if ($(this).val() != '') {
			modalAlert.modal('show');
			setTimeout(function () {
				actualizarTotales();
				modalAlert.modal('hide');
			}, 1);
		}
	});

	// Abrir el modal para agregar un producto especial
	$('#agregarParte').click(function (e) {
		e.preventDefault();
		$('#modalProducto').modal('show');
	});

	// Modal para agregar un producto especial
	$('#modalProducto').on('hidden.bs.modal', function (e) {
		$('#inputPieza').val('');
		$('#inputProducto').val('');
		$('#inputPrecio').val('').prop('readonly', true);
		$('#inputPiezas').val('');
	});

	// Configuracion de la tabla de pre visualizacion de la cotizacion
	$('#tablaCotizacion').bootstrapTable({
		data: [],
		clickToSelect: true,
		toolbar: '#toolbar',
		uniqueId: 'no',
		columns: [[
			{ title: 'Información de las Piezas', halign: 'center', valign: 'middle', colspan: 6 },
			{ title: 'Información de las Partes', halign: 'center', valign: 'middle', colspan: 3 },
			{ title: 'Información de las Replicas', halign: 'center', valign: 'middle', colspan: 6 }
		], [
			{ radio: true, align: 'center' },
			{
				field: 'no_partida', title: 'Item', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
					return parseInt(value);
				}
			},
			{ field: 'cve_art', title: 'Código', align: 'center', halign: 'center', valign: 'middle' },
			{
				field: 'descripcion', title: 'Descripcion', valign: 'middle', formatter: function (value, row, index) {
					if (row.partida_armado == 'S' && (row.cve_art == 'TUB' || row.cve_art == 'RIE' || row.cve_art == 'GUI' || row.cve_art == 'SUP' || row.cve_art == 'TOR' || row.cve_art == 'OTR')) {
						prefijo = ''
						switch (row.cve_art) {
							case 'TUB':
								prefijo = '<strong>Tubos (</strong>';
								break;
							case 'RIE':
								prefijo = '<strong>Rieles (</strong>';
								break;
							case 'GUI':
								prefijo = '<strong>Guías (</strong>';
								break;
							case 'SUP':
								prefijo = '<strong>Superficies (</strong>';
								break;
							case 'TOR':
								prefijo = '<strong>Tornillos (</strong>';
								break;
							case 'OTR':
								prefijo = '<strong>Otros (</strong>';
								break;
							default:
								break;
						}
						value = prefijo + value + "<strong>) <span><i class='fa fa-external-link'></i></span></strong>";
					}
					return value
				}
			},
			{
				field: 'precioPiezaAD', title: 'Precio AD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}
			},
			{
				field: 'precioPiezaDD', title: 'Precio DD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}
			},
			{
				field: 'piezas', title: 'Piezas', align: 'right', halign: 'right', valign: 'middle', editable: {
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
				}
			},
			{
				field: 'descuento', title: 'Descuento', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
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
				}
			},
			{
				field: 'precioParteDD', title: 'Precio DD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}
			},
			{
				field: 'replicas', title: 'Replicas', align: 'right', halign: 'right', valign: 'middle', editable: {
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
				}
			},
			{
				field: 'precioReplicaAD', title: 'Precio AD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}
			},
			{
				field: 'precioReplicaDD', title: 'Precio DD', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}
			},
			{
				field: 'utilidad', title: 'Utilidad', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}
			},
			{ field: 'ult_costo', title: 'Costo', visible: false },
			{ field: 'folio', title: 'folio', visible: false }
		]],
		onClickRow: function (row, $element, field) {
			if (field == 'descripcion' && row.partida_armado == 'S' && (row.cve_art == 'TUB' || row.cve_art == 'RIE' || row.cve_art == 'GUI' || row.cve_art == 'SUP' || row.cve_art == 'TOR' || row.cve_art == 'OTR')) {
				selectedRow = row;
				selectedIndex = $element.attr('data-index');
				$('.combo').addClass('hidden');
				switch (row.cve_art) {
					case 'TUB':
						$('#divTubos').removeClass('hidden');
						opened = $('#selectTubos');
						break;
					case 'RIE':
						$('#divRieles').removeClass('hidden');
						opened = $('#selectRieles');
						break;
					case 'GUI':
						$('#divGuias').removeClass('hidden');
						opened = $('#selectGuias');
						break;
					case 'SUP':
						$('#divSuperficies').removeClass('hidden');
						opened = $('#selectSuperficies');
						break;
					case 'TOR':
						$('#divTornilleria').removeClass('hidden');
						opened = $('#selectTornilleria');
						break;
					case 'OTR':
						$('#divOtros').removeClass('hidden');
						opened = $('#selectOtros');
						break;
					default:
						break;
				}
				$('#modalCombos').modal('show');
			}
		}
	});

	// Actualizar el contenido de los combos de las partidas de la cotizacion armada
	$('#selecionarCombo').click(function (e) {
		agrupador = opened.attr('data-group');
		valores = opened.val();
		if (valores != null && valores.length > 0) {
			$.ajax({
				async: true,
				type: 'POST',
				cache: false,
				data: { agrupador: agrupador, valores: valores },
				url: 'Productos/Seleccion',
				dataType: 'json',
				success: function (json) {
					auxDescripcion = '';
					auxCosto = 0;
					auxPrecio = 0;
					$.each(json, function (index, item) {
						auxDescripcion = auxDescripcion + item.DESCR + ' <strong>&&</strong> ';
						auxCosto = auxCosto + item.ULT_COSTO;
						auxPrecio = auxPrecio + item.precioPiezaAD;
					});
					selectedRow.descripcion = auxDescripcion.substr(0, auxDescripcion.length - 21);;
					selectedRow.ult_costo = auxCosto;
					selectedRow.precioPiezaAD = auxPrecio;
					actualizarFila(selectedIndex, selectedRow);
					$('#modalCombos').modal('hide');
				}
			});
		} else {
			selectedRow.descripcion = 'Ninguno';
			selectedRow.ult_costo = 0;
			selectedRow.precioPiezaAD = 0;
			actualizarFila(selectedIndex, selectedRow);
			$('#modalCombos').modal('hide');
		}

	});

	// Funcion para remover la columna seleccionada de la previsualizacion
	$('#removerFila').click(function () {
		filasSeleccionadas = $('#tablaCotizacion').bootstrapTable('getSelections');
		if (filasSeleccionadas.length > 0) {
			if (filasSeleccionadas[0]['folio'] != null && filasSeleccionadas[0]['folio'] != '') {
				folios.push(filasSeleccionadas[0]['folio']);
			}
			$('#tablaCotizacion').bootstrapTable('remove', { field: 'no_partida', values: [filasSeleccionadas[0]['no_partida']] });
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
	}).on('hidden', function (e, reason) {
		actualizarTotales();
	});

	$('#descuento').editable({
		type: 'text',
		mode: 'popup',
		showbuttons: false,
		onblur: 'submit'
	}).on('hidden', function (e, reason) {
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
			{ field: 'folio', title: 'Cotizacion', align: 'center', halign: 'center', valign: 'middle' },
			{ field: 'nombre_cliente', title: 'Cliente' },
			{ field: 'fecha', title: 'Fecha', align: 'center', halign: 'center', valign: 'middle' },
			{
				field: 'totalPrecioRDD', title: 'Importe', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}
			},
			{
				field: 'estatus', title: 'Estatus', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
					string = '';
					switch (value) {
						case 'A':
							string = "<span class='label label-default'>Abierta</span>"
							break;
						case 'B':
							string = "<span class='label label-primary'>Autorizada</span>"
							break;
						case 'C':
							string = "<span class='label label-danger'>Rechazada</span>"
							break;
						case 'D':
							string = "<span class='label label-success'>Cerrada</span>"
							break;
						default:
							string = "<span class='label label-default'>N/A</span>"
							break;
					}
					return string
				}
			},
			{
				title: 'Acciones', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
					str = "<button type='button' class='btn btn-xs btn-primary open' title='Cargar esta cotización en el editor'><i class='fa fa-folder-open-o'></i></button>";
					if (row.estatus == 'A' || row.estatus == 'B') {
						str += "&nbsp;<button type='button' class='btn btn-xs btn-success cerrar' title='Cerrar esta cotización'><i class='fa fa-lock'></i></button>";
					}
					return str
				}
			},
		],
		onClickRow: function (row, $element, field) {
			window.folio = row.folio;
		}
	});

	// Abrir la cotizacion seleccionada
	$('#tablaCotizaciones tbody').on('click', 'button.open', function () {
		cargarCotizacion(window.folio);
	});

	// Cerrar la cotizacion seleccionada
	$('#tablaCotizaciones tbody').on('click', 'button.cerrar', function (e) {
		e.preventDefault();
		cerrarCotizacion();
	});

	// Guardamos los cambios hechos sobre la cotizacion
	$('#btnGuardar').click(function (e) {
		e.preventDefault();
		pre_folio = $('#pre_folio').val();
		folio = $('#folio').val();
		if (pre_folio == '' && folio == '') {
			$('#msjAlert').html('NADA POR GUARDAR O NO SE HA DEFINIDO EL FOLIO DE LA COTIZACION');
			modalAlert.modal('show');
			return true
		}
		guardarCotizacion();
	});

	// Obtener el listado de cotizaciones
	$('#filtrarCotizaciones').click(function (e) {
		e.preventDefault();
		filtrarCotizaciones();
	});

	// Imprimir la cotizacion en formato bulk
	$('#btnImprimir').click(function (e) {
		e.preventDefault();
		if ($('#folio').val() == '') {
			$('#msjAlert').html('ABRE O CREA UNA NUEVA COTIZACION QUE IMPRIMIR');
			modalAlert.modal('show');
			return true;
		}
		if ($('#tipo').is(':checked')) {
			window.open('Cotizador/ImprimirCotizacion/' + $('#folio').val() + '/armado');
		} else {
			window.open('Cotizador/ImprimirCotizacion/' + $('#folio').val() + '/bulk');
		}
	});

	// Autorizar una cotizacion
	$('#btnAutorizar').click(function (e) {
		e.preventDefault();
		if ($('#folio').val() == '') {
			$('#msjAlert').html('ABRE O CREA UNA NUEVA COTIZACION QUE IMPRIMIR');
			modalAlert.modal('show');
			return true;
		}
		cambiarEstado('B');
	});

	// Rechazar una cotizacion
	$('#btnRechazar').click(function (e) {
		e.preventDefault();
		if ($('#folio').val() == '') {
			$('#msjAlert').html('ABRE O CREA UNA NUEVA COTIZACION QUE IMPRIMIR');
			modalAlert.modal('show');
			return true;
		}
		cambiarEstado('C');
	});

	// Guardar la partida armado de la cotizacion
	$('#confirmarPartida').click(function () {
		guardarArmado($('#taArmado').val());
	});

	// Configuracion del dropzone para cargar el excel de la cotizacion
	$('#imgArea').dropzone({
		// URL a donde se envia el archivo en los controladores
		url: 'Cotizador/RecibirImagen',
		// Configuracion de las propiedades del archivo que se pueden cargar al servidor
		maxFilesize: 5,
		paramName: 'imagen',
		maxFiles: 1,
		acceptedFiles: '.jpg, .jpeg, .gif, .bmp, .tiff, .png',
		addRemoveLinks: true,
		capture: true,
		// Configuracion de los mensajes del dropzone
		dictDefaultMessage: 'Arrastra y suelta las imágenes que se anexaran a la cotización',
		dictFallbackMessage: 'Tu navegador no soporta la función de arrastra y suelta archivo, inténtalo nuevamente después de actualizarlo',
		dictFileTooBig: 'El archivo seleccionado tiene un tamaño mayor al permitido (5Mb)',
		dictInvalidFileType: 'Solo se permiten los formatos establecidos',
		dictResponseError: 'Se presento un error al recibir la(s) imagen(es) en el servidor',
		dictCancelUpload: 'Cancelar',
		dictCancelUploadConfirmation: '¿Estás seguro de querer cancelar la carga del archivo?',
		dictRemoveFile: 'Remover archivo',
		dictMaxFilesExceeded: 'Solo se permiten cargar un archivo a la vez',
		// Manipulacion de los momentos del enviado de archivos
		init: function () {
			var self = this;
			self.on('sending', function (file, xhr, formData) {
				formData.append("folio", $('#folio').val());
				formData.append("pre_folio", $('#pre_folio').val());
				modalAlert.modal('show');
				$('#msjAlert').html('ESPERA UN MOMENTO POR FAVOR, CARGANDO ARCHIVOS...');
			});
			self.on("success", function (file, response) {
				response = JSON.parse(response);
				if (response.bandera == false) {
					$('#msjAlert').html(response.msj);
					modalAlert.modal('show');
				} else {
					recargarGaleria();
					modalAlert.modal('hide');
				}
				self.removeFile(file);
			});
		}
	});

	// Funcion para remover una imagen
	$('#galeria').on('click', 'a.quitarimagen', function (e) {
		e.preventDefault();
		folio = $(this).prop('id');
		removerImagen(folio);
	});

});

// Funcion para dar formato a un numero
function formato_numero(numero, decimales, separador_decimal, separador_miles) {
	numero = parseFloat(numero);
	if (isNaN(numero)) return '';
	if (decimales !== undefined) numero = numero.toFixed(decimales);
	numero = numero.toString().replace('.', separador_decimal !== undefined ? separador_decimal : ',');
	if (separador_miles) {
		var miles = new RegExp("(-?[0-9]+)([0-9]{3})");
		while (miles.test(numero)) {
			numero = numero.replace(miles, '$1' + separador_miles + '$2');
		}
	}
	return numero;
}

// Funcion para setear el formulario con la informacion del cliente
var setearCliente = function (response) {
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
var obtenerMaximoDescuento = function () {
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
var resetReplica = function (replica) {
	data = $('#tablaCotizacion').bootstrapTable('getData');
	$.each(data, function (index, row) {
		row['replicas'] = row['piezas'] * replica;
		$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
	});
	actualizarTabla();
}

// Funcion para reestablecer el descuento de la tabla de cotizacion
var resetDescuento = function (descuento) {
	data = $('#tablaCotizacion').bootstrapTable('getData');
	$.each(data, function (index, row) {
		row['descuento'] = descuento;
		$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
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
var actualizarNumeroPartidas = function (data) {
	$.each(data, function (index, row) {
		row['no_partida'] = index + 1;
		$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
	});
}

// Funcion para actualizar el campo de replica y los campos calculados
var actualizarTabla = function () {
	data = $('#tablaCotizacion').bootstrapTable('getData');
	$.each(data, function (index, row) {
		actualizarFila(index, row);
	});
	actualizarTotales();
}

// Funcion para actualizar los campos calculados de la fila
var actualizarFila = function (index, row) {
	row['precioPiezaDD'] = row['precioPiezaAD'] - (row['precioPiezaAD'] * row['descuento'] / 100);
	row['precioParteDD'] = row['piezas'] * row['precioPiezaDD'];
	row['precioReplicaAD'] = row['replicas'] * row['precioPiezaAD'];
	row['precioReplicaDD'] = row['replicas'] * row['precioPiezaDD'];
	row['utilidad'] = row['precioReplicaDD'] - (row['ult_costo'] * row['replicas'])
	$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
}

// Funcion para calcular los totales de la cotizacion
var actualizarTotales = function () {
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

	$.each(data, function (index, row) {
		stUsdPrecioPDD = stUsdPrecioPDD + parseFloat(row['precioParteDD']);
		stUsdPrecioRAD = stUsdPrecioRAD + parseFloat(row['precioReplicaAD']);
		stUsdPrecioRDD = stUsdPrecioRDD + parseFloat(row['precioReplicaDD']);
		costo_total = costo_total + (parseFloat(row['ult_costo']) * parseFloat(row['replicas']));
	});

	descuentoPrecioPDD = stUsdPrecioPDD * descuento / 100;
	descuentoPrecioRAD = stUsdPrecioRAD * descuento / 100;
	descuentoPrecioRDD = stUsdPrecioRDD * descuento / 100;

	stPrecioPDD = stUsdPrecioPDD - descuentoPrecioPDD;
	stPrecioRAD = stUsdPrecioRAD - descuentoPrecioRAD;
	stPrecioRDD = stUsdPrecioRDD - descuentoPrecioRDD;

	ivaPrecioPDD = stPrecioPDD * impuestos / 100;
	ivaPrecioRAD = stPrecioRAD * impuestos / 100;
	ivaPrecioRDD = stPrecioRDD * impuestos / 100;

	totalPrecioPDD = stPrecioPDD + ivaPrecioPDD;
	totalPrecioRAD = stPrecioRAD + ivaPrecioRAD;
	totalPrecioRDD = stPrecioRDD + ivaPrecioRDD;

	utilidad = (totalPrecioRDD * 100 / costo_total) - 100;


	if ((stPrecioRDD / stUsdPrecioRAD * 100) < 84.99) {
		if (window.estatus == 'B') {
			$('#liImprimir').show();
			$('#rowMsj').addClass('hidden');
		} else {
			$('#liImprimir').hide();
			$('#rowMsj').removeClass('hidden');
		}
	} else {
		$('#liImprimir').show();
		$('#rowMsj').addClass('hidden');
	}



	// Se actualizan los valores de los controles de los totales
	$('#stUsdPrecioPDD').html(formato_numero(stUsdPrecioPDD, 2, '.', ','));
	$('#stUsdPrecioRAD').html(formato_numero(stUsdPrecioRAD, 2, '.', ','));
	$('#stUsdPrecioRDD').html(formato_numero(stUsdPrecioRDD, 2, '.', ','));

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
var nuevaCotizacion = function () {
	// Cargamos el tipo de cambio de la cotizacion
	var tc_info = obtenerTipoCambio();
	$('#tc').val(tc_info[0]).attr('title', 'Fuente Banxico, fecha ' + tc_info[1]);
	// Llenamos los combos de las partidas de armada
	llenarCombo($('#selectTubos'), 'TUB');
	llenarCombo($('#selectRieles'), 'RIE');
	llenarCombo($('#selectGuias'), 'GUI');
	llenarCombo($('#selectSuperficies'), 'SUP');
	llenarCombo($('#selectTornilleria'), 'TOR');
	llenarCombo($('#selectOtros'), 'OTR');
	$(".selectpicker").selectpicker();
}

// Funcion para cargar una cotizacion guardada con anterioriodad
var cargarCotizacion = function (folio) {
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { folio: folio },
		url: 'Cotizador/ObtenerEncabezado',
		dataType: 'json',
		beforeSend: function () {
			$('#msjAlert').html('CARGANDO COTIZACION, ESPERA POR FAVOR...');
			modalAlert.modal('show');
		},
		success: function (json) {
			$('#msjAlert').html(json.msj);
			if (json.bandera == true) {
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
				$('#tipo').val(en.tipo_impresion == 'A' ? 1 : 0 );
				setearPartidas();
				recargarGaleria();
				modalAlert.modal('hide');
				$('#modalCotizaciones').modal('hide');
				$('#rowCargar').hide();
				string = '';
				window.estatus = en.estatus;
				if (en.estatus == 'A') {
					$('#liGuardar').show();
					$('#liAutorizar').show();
					$('#liRechazar').show();
					$('#rowCargaImg').show();
					$('#rowGaleria').show();
					string = "<span class='label label-default'>Abierta</span>";
				}
				if (en.estatus == 'B') {
					$('#liGuardar').hide();
					$('#liAutorizar').hide();
					$('#liRechazar').show();
					$('#rowCargaImg').hide();
					$('#rowGaleria').hide();
					string = "<span class='label label-primary'>Autorizada</span>";
				}
				if (en.estatus == 'C') {
					$('#liGuardar').hide();
					$('#liAutorizar').hide();
					$('#liRechazar').hide();
					$('#rowCargaImg').hide();
					$('#rowGaleria').hide();
					string = "<span class='label label-danger'>Rechazada</span>";
				}
				if (en.estatus == 'D') {
					$('#liGuardar').hide();
					$('#liAutorizar').hide();
					$('#liRechazar').hide();
					$('#rowCargaImg').hide();
					$('#rowGaleria').hide();
					string = "<span class='label label-success'>Cerrada</span>"
				}
				$('#liEstatus').html(string);
			}
		}
	});
}

// Funcion para guardar los cambios sobre la cotizacion
var guardarCotizacion = function () {
	pre_folio = $('#pre_folio').val();
	folio = $('#folio').val();
	cliente = $('#formCliente').serializeArray();
	partidas = $('#tablaCotizacion').bootstrapTable('getData');
	encabezado = {
		tc: $('#tc').val(),
		replica: $('#replica').val(),
		representante: $('#gestorDeCuenta').text(),
		terminos: $('#terminosVenta').text(),
		observaciones: $('#observaciones').text(),
		stUsdPrecioPDD: $('#stUsdPrecioPDD').html(),
		stUsdPrecioRAD: $('#stUsdPrecioRAD').html(),
		stUsdPrecioRDD: $('#stUsdPrecioRDD').html(),
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
		descuentost: $('#descuento').html(),
		tipo: $('#tipo').val()
	};
	combos = {
		tub: $('#selectTubos').val(),
		rie: $('#selectRieles').val(),
		gui: $('#selectGuias').val(),
		sup: $('#selectSuperficies').val(),
		tor: $('#selectTornilleria').val(),
		otr: $('#selectOtros').val()
	};
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { pre_folio: pre_folio, folio: folio, cliente: cliente, encabezado: encabezado, partidas: partidas, folios: folios, combos: combos },
		url: 'Cotizador/GuardarCotizacion',
		dataType: 'json',
		beforeSend: function () {
			$('#msjAlert').html('GUARDANDO COTIZACION, ESPERA POR FAVOR...');
			modalAlert.modal('show');
		},
		success: function (json) {
			$('#msjAlert').html(json.msj);
			if (json.bandera == true) {
				$('#folio').val(json.folio);
				cargarCotizacion(json.folio);
				folios.length = 0;
				modalAlert.modal('hide');
			}
			console.log(json);
		}
	});
}

// Funcion para setear las partidas de la cotizacion en la vista
var setearPartidas = function () {
	folio = $('#folio').val();
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { folio: folio },
		url: 'Cotizador/ObtenerPartidas',
		dataType: 'json',
		success: function (json) {
			$('#tablaCotizacion').bootstrapTable('load', json.partidas);
			// Actualizamos los valores seleccionados de los combos
			options = [];
			$.each(json.tubos, function (index, item) {
				options.push(item.cve_art)
			});
			$('#selectTubos').selectpicker('val', options);

			options.lenght = 0;
			$.each(json.rieles, function (index, item) {
				options.push(item.cve_art)
			});
			$('#selectRieles').selectpicker('val', options);

			options.lenght = 0;
			$.each(json.guias, function (index, item) {
				options.push(item.cve_art)
			});
			$('#selectGuias').selectpicker('val', options);

			options.lenght = 0;
			$.each(json.superficies, function (index, item) {
				options.push(item.cve_art)
			});
			$('#selectSuperficies').selectpicker('val', options);

			options.lenght = 0;
			$.each(json.tornillos, function (index, item) {
				options.push(item.cve_art)
			});
			$('#selectTornilleria').selectpicker('val', options);

			options.lenght = 0;
			$.each(json.otros, function (index, item) {
				options.push(item.cve_art)
			});
			$('#selectOtros').selectpicker('val', options);
			// Actualizamos los totales
			actualizarTotales();
		}
	});
}

// Funcion para obtener las partidas armadas de la cotizacion
var partidasArmado = function () {
	var data = [];
	$.ajax({
		async: false,
		type: 'POST',
		cache: false,
		data: { folio: $('#folio').val() },
		url: 'Cotizador/ObtenerPartidasArmado',
		dataType: 'json',
		success: function (json) {
			data = json;
		}
	});
	return data;
}

// Funcion para guardar la partida de armado
var guardarArmado = function (armado) {
	$.ajax({
		async: false,
		type: 'POST',
		cache: false,
		data: { folio: $('#folio').val(), armado: armado },
		url: 'Cotizador/GuardarPartidaArmado',
		dataType: 'json',
		success: function (json) {
			if (json.bandera == true) {
				$('#modalArmado').modal('hide');
				window.open('Cotizador/ImprimirCotizacion/' + $('#folio').val() + '/armado');
			} else {
				$('#msjAlert').html('Se presento un error al guardar la partida de armado');
			}
		}
	});
	return data;
}

// Funcion para recargar la galeria de imagenes de la cotizacion
var recargarGaleria = function () {
	$.ajax({
		async: false,
		type: 'POST',
		cache: false,
		data: { folio: $('#folio').val(), pre_folio: $('#pre_folio').val() },
		url: 'Cotizador/ObtenerImagenes',
		dataType: 'json',
		success: function (json) {
			if (json.bandera == true) {
				$('#galeria').empty();
				$.each(json.imgs, function (index, img) {
					$('#galeria').append(pintarImagen(img));
				});
			}
		}
	});
}

// Funcion para cargar una nueva imagen en la galeria de imagenes
var pintarImagen = function (img) {
	return "" +
		"<div class='col-md-55'>" +
		"	<div class='thumbnail'>" +
		"		<div class='image view view-first'>" +
		"			<img style='width: 100%; display: block;' src='" + img.url + "' 'alt='image' />" +
		"			<div class='mask'>" +
		"				<p>Clic x para quitar</p>" +
		"				<div class='tools tools-bottom'>" +
		"					<a href='#' class='quitarimagen' id='" + img.folio + "'><i class='fa fa-times'></i></a>" +
		"				</div>" +
		"			</div>" +
		"		</div>" +
		"		<div class='caption'>" +
		"		<p>" + img.nombre_original + "</p>" +
		"		</div>" +
		"	</div>" +
		"</div>";
}

// Funcion para remover una imagen de la cotizacion
var removerImagen = function (folio) {
	$.ajax({
		async: false,
		type: 'POST',
		cache: false,
		data: { folio: folio },
		url: 'Cotizador/BorrarImagen',
		dataType: 'json',
		success: function (json) {
			if (json.bandera == true) {
				recargarGaleria();
			}
		}
	});
}

// Funcion para cambiar el estatus de una cotizacion
var cambiarEstado = function (estado) {
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { folio: $('#folio').val(), estatus: estado },
		url: 'Cotizador/CambiarEstado',
		dataType: 'json',
		success: function (json) {
			if (json.bandera == false) {
				$('#msjAlert').html(json.msj);
				modalAlert.modal('show');
			} else {
				cargarCotizacion($('#folio').val());
			}
		}
	});
}

// Funcion para cerrar una cotizacion
var cerrarCotizacion = function () {
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { folio: window.folio },
		url: 'Cotizador/CerrarCotizacion',
		dataType: 'json',
		success: function (json) {
			if (json.bandera == false) {
				$('#msjAlert').html(json.msj);
				modalAlert.modal('show');
			} else {
				filtrarCotizaciones();
			}
		}
	});
}

// Funcion para filtrar las cotizaciones segun los parametros seleccionados
var filtrarCotizaciones = function () {
	fi = $('#inputfi').val();
	ff = $('#inputff').val();
	estatus = $('#estatusCot').val();
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { fi: fi, ff: ff, estatus: estatus },
		url: 'Cotizador/ObtenerCotizaciones',
		dataType: 'json',
		success: function (json) {
			$('#tablaCotizaciones').bootstrapTable('load', json);
		}
	});
}

// Funcion para llenar los combos
var llenarCombo = function ($element, clasificador) {
	$.ajax({
		async: false,
		type: 'POST',
		cache: false,
		data: { clasificador: clasificador },
		url: 'Productos/Combo',
		dataType: 'json',
		success: function (json) {
			$.each(json, function (index, item) {
				$element.append("<option value='" + item.CVE_ART + "'>" + item.CVE_ART + ' - ' + item.DESCR + "</option>");
			});
		}
	});
}