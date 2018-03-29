$(document).ready(function () {
	window.descuento_maximo = 15;
	window.utilidad_minima = 20;

	window.descuento_autorizado = 0;
	window.utilidad_autorizada = 0;
	window.fase_uno_usuario_autorizacion = '';
	window.fase_dos_usuario_autorizacion = '';

	/*************** CARGA INICIAL DE LA INFORMACION DE LA COTIZACION ***************/
	nuevaCotizacion();
	folios = [];
	var opened;
	var selectedRow = {};
	$('#tipo').change(function () {
		$('#nombreEmpresa').prop('readonly', $(this).val() != '' ? false : true);
	});
	// Configuracion del tooltip en la vista
	$('[data-tool="tooltip"]').tooltip();
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
	// Modal para agregar un producto especial
	$('#modalProducto').on('hidden.bs.modal', function (e) {
		$('#inputPieza, #inputProducto, #inputPrecio').val('');
		$('#inputPrecio').prop('readonly', true);
		$('#inputPiezas').val('1');
	});
	// Configuracion del pie de pagina de la tabla de previsualizacion de la cotizacion
	$('#descArmada').editable({
		type: 'textarea',
		emptytext: 'Descripción de cotización armada',
		mode: 'popup',
		showbuttons: true,
	}).on("shown", function (e, editable) {
		$this = $(this);
		if (arguments.length == 2) {
			setTimeout(function () { $this.data('editable').input.$input.select(); }, 50);
		}
	});
	$('#gestorDeCuenta').editable({
		type: 'text',
		defaultValue: '',
		emptytext: 'Representante de ventas',
		mode: 'popup',
		showbuttons: false,
		onblur: 'submit',
		classes: 'table-condensed'
	}).on("shown", function (e, editable) {
		$this = $(this);
		if (arguments.length == 2) {
			setTimeout(function () { $this.data('editable').input.$input.select(); }, 50);
		}
	});
	$('#terminosVenta').editable({
		type: 'textarea',
		defaultValue: '',
		emptytext: 'Términos y condiciones de venta',
		mode: 'popup',
		showbuttons: true,
		onblur: 'submit'
	}).on("shown", function (e, editable) {
		$this = $(this);
		if (arguments.length == 2) {
			setTimeout(function () { $this.data('editable').input.$input.select(); }, 50);
		}
	});
	$('#observaciones').editable({
		type: 'textarea',
		defaultValue: '',
		emptytext: 'Observaciones',
		mode: 'popup',
		showbuttons: true,
		onblur: 'submit'
	}).on("shown", function (e, editable) {
		$this = $(this);
		if (arguments.length == 2) {
			setTimeout(function () { $this.data('editable').input.$input.select(); }, 50);
		}
	});
	$('#impuestos').editable({
		type: 'text',
		mode: 'popup',
		showbuttons: false,
		onblur: 'submit'
	}).on('hidden', function (e, reason) {
		actualizarTotales();
	}).on("shown", function (e, editable) {
		$this = $(this);
		if (arguments.length == 2) {
			setTimeout(function () { $this.data('editable').input.$input.select(); }, 50);
		}
	});
	$('#descuento').editable({
		type: 'text',
		mode: 'popup',
		showbuttons: false,
		onblur: 'submit'
	}).on('hidden', function (e, reason) {
		actualizarTotales();
	}).on("shown", function (e, editable) {
		$this = $(this);
		if (arguments.length == 2) {
			setTimeout(function () { $this.data('editable').input.$input.select(); }, 50);
		}
	});

	$('#btnImprimir').click(function (e) {
		e.preventDefault();
		window.open('Cotizador/ImprimirCotizacion/' + $('#folio').val());
	});
	/*************** CONFIGURACION GENERAL DEL COMPORTAMIENTO DE LA VISTA ***************/
	// Configuracion del autocomplete del cliente
	$('#nombreEmpresa').autocomplete({
		source: "Clientes/ObtenerCliente",
		minLength: 3,
		select: function (evt, ui) {
			setearCliente(ui.item);
			$('#tipo').prop('disabled', true);
			if ($('#tipo').val() == 'B') {
				$('#tablaCotizacion').bootstrapTable('hideColumn', 'aparece_en_armada');
			} else {
				$('#tablaCotizacion').bootstrapTable('showColumn', 'aparece_en_armada');
			}
		}
	});

	// Actualizacion de los datos del cliente
	$('#btnActualizarCliente').click(function () {
		$.ajax({
			async: true,
			type: 'POST',
			cache: false,
			data: { ID: $('#ID').html() },
			url: 'Clientes/ObtenerClientexID',
			dataType: 'json',
			beforeSend: function () {
				$('#msjAlert').html('ACTUALIZANDO, ESPERA POR FAVOR...');
				modalAlert.modal('show');
			},
			success: function (json) {
				xc = json[0];
				$('#nombreEmpresa').val(xc.strnombrefiscal);
				$('#RFC').val(xc.RFC);
				$('#direccion').val(xc.DOMICILIO);
				$('#colonia').val(xc.COLONIA);
				$('#municipio').val(xc.MUNICIPIO);
				$('#estado').val(xc.ESTADO);
				$('#CP').val(xc.CP);
				$('#telefono').val(xc.TELEFONO);
				$('#correo').val(xc.CORREO);
				modalAlert.modal('hide');
			}
		});
	});

	// Configuracion del dropzone para cargar el excel de la cotizacion
	Dropzone.autoDiscover = false;
	$('#excelArea').dropzone({
		url: 'Productos/RecibirExcel',
		maxFilesize: 2,
		paramName: 'cotizacion',
		maxFiles: 1,
		acceptedFiles: '.xls, .xlsx',
		addRemoveLinks: true,
		capture: false,
		dictDefaultMessage: 'Arrastra y suelta la nueva cotización, en formato xlsx(excel), aquí',
		dictFallbackMessage: 'Tu navegador no soporta la función de arrastra y suelta archivo, inténtalo nuevamente después de actualizarlo',
		dictFileTooBig: 'El archivo seleccionado tiene un tamaño mayor al permitido (2Mb)',
		dictInvalidFileType: 'Solamente se permiten cargar arhivos en formato xlsx(Excel)',
		dictResponseError: 'Se presento un error al recibir la cotizacion en el servidor',
		dictCancelUpload: 'Cancelar',
		dictCancelUploadConfirmation: '¿Estás seguro de querer cancelar la carga del archivo?',
		dictRemoveFile: 'Remover archivo',
		dictMaxFilesExceeded: 'Solo se permite cargar un archivo a la vez',
		init: function () {
			var self = this;
			self.on('sending', function (file, xhr, formData) {
				formData.append("tipo", $('#tipo').val());
				formData.append("id_cliente", $('#ID').text());
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
				formData.append("area", $('#area').val());
				formData.append("representante_ventas", $('#gestorDeCuenta').editable('getValue', true));
				formData.append("terminos_y_condiciones", $('#terminosVenta').editable('getValue', true));
				formData.append("observaciones", $('#observaciones').editable('getValue', true));
				formData.append("tc", $('#tc').val());
				formData.append("replica", $('#replica').val());
				formData.append("std", $('#std').val());
				formData.append("tq", $('#tq').val());
				formData.append("impuestos", $('#impuestos').editable('getValue', true));
				formData.append("descuento", $('#descuento').editable('getValue', true));
				modalAlert.modal('show');
				$('#msjAlert').html('Cargando archivo, espera un momento por favor...');
			});
			self.on("success", function (file, response) {
				response = JSON.parse(response);
				console.log(response);
				if (response.bandera == false) {
					$('#msjAlert').html(response.msj);
					modalAlert.modal('show');
				} else {
					$('#pre_folio').val(response.pre_folio);
					$('#descArmada').editable('setValue', response.desc_armada, false);
					$('#tablaCotizacion').bootstrapTable('load', response.data);
					actualizarTotales();
					mergeCells();
					if (response.faltantes.length > 0) {
						$('#faltantes').html("<strong>Los siguientes códigos de producto no se encuentran definidos en el catálogo de productos: </strong><strong style='color: red'>" + response.faltantes.join(', ') + "</strong>");
					} else {
						$('#faltantes').html("");
					}
					modalAlert.modal('hide');
					$('#rowCargar').hide();
				}
				self.removeFile(file);
				$('#btnGuardar').removeClass('hidden');
			});
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
		if ($('#inputPiezas').val() != '') {
			arrayProducto = $('#inputProducto').val().split(' - ');
			row = crearPartida($('#inputPieza').val(), arrayProducto[1], $('#ult_costo').val(), $('#inputPrecio').val(), $('#inputPiezas').val(), '');
			$('#tablaCotizacion').bootstrapTable('append', row);
			actualizarNumeroPartidas();
			actualizarTotales();
			$('#modalProducto').modal('hide');
			totalData = $('#tablaCotizacion').bootstrapTable('getData');
			if (totalData.length == 1) {
				$('#btnGuardar').removeClass('hidden');
			}
		} else {
			$('#msjAlert').html('Debes proporcionar la cantidad de piezas del item')
			modalAlert.modal('show');
		}
	});

	// Configuramos la accion del cuadro de texto de replica
	$('#replica').change(function () {
		if ($(this).val() != '') {
			modalAlert.modal('show');
			setTimeout(function () {
				resetReplica();
				modalAlert.modal('hide');
			}, 1);
		}
	});

	// Configuracion de la tabla de pre visualizacion de la cotizacion
	$('#tablaCotizacion').bootstrapTable({
		data: [],
		clickToSelect: true,
		toolbar: '#toolbar',
		uniqueId: 'no',
		columns: [[
			{ title: 'Información de las Piezas', halign: 'center', valign: 'middle', colspan: 5 },
			{ title: 'Información de las Partes', halign: 'center', valign: 'middle', colspan: 3 },
			{ title: 'Información de las Replicas', halign: 'center', valign: 'middle', colspan: 9 },
		], [
			{ radio: true, align: 'center' },
			{
				field: 'no_partida', title: 'Item', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
					return parseFloat(value);
				}
			},
			{
				field: 'cve_art', title: 'Código', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
					if (value == 'TUB' || value == 'RIE' || value == 'GUI' || value == 'SUP' || value == 'TOR' || value == 'OTR')
						return '<strong>' + value + '</strong>'
					else
						return value
				}
			},
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
					if (row.clasificador != '' && row.no_partida % 1 > 0) {
						value = "<i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + value + "</i>";
					}
					return value
				}
			},
			{
				field: 'precioPiezaAD', title: 'Precio', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',')
				}, editable: {
					type: 'text',
					mode: 'popup',
					showbuttons: false,
					success: function (response, newValue) {
						data = $('#tablaCotizacion').bootstrapTable('getData');
						index = $(this).closest('tr').attr('data-index');
						row = data[index];
						row['precioPiezaAD'] = newValue;
						actualizarFila(index, row);
						mergeCells();
						actualizarTotales();
					}
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
						mergeCells();
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
						mergeCells();
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
						mergeCells();
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
				field: 'utilidad', title: 'Utilidad %', align: 'right', halign: 'right', valign: 'middle', formatter: function (value, row, index) {
					return formato_numero(value, 2, '.', ',') + '%';
				}
			},
			{ field: 'ult_costo', title: 'Costo', visible: false },
			{ field: 'folio', title: 'folio', visible: false },
			{ field: 'clasificador', title: 'clasificador', visible: false },
			{ field: 'partida_armado', title: 'armado', visible: false },
			{
				field: 'aparece_en_armada', title: 'Armada', align: 'center', halign: 'center', valign: 'middle', editable: {
					type: 'select',
					mode: 'popup',
					value: 'No',
					showbuttons: false,
					source: [
						{ value: 'No', text: 'No' },
						{ value: 'Si', text: 'Si' }
					],
					success: function (response, newValue) {
						data = $('#tablaCotizacion').bootstrapTable('getData');
						index = $(this).closest('tr').attr('data-index');
						row = data[index];
						row['aparece_en_armada'] = newValue;
						$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
						mergeCells();
					}
				}
			},
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
		}, onEditableShown(editable, field, row, $el) {
			setTimeout(function () {
				$el.input.$input.select();
			}, 0);
		}
	});

	// Actualizar el contenido de los combos de las partidas de la cotizacion armada
	$('#selecionarCombo').click(function (e) {
		clasificador = opened.attr('data-group');
		valores = opened.val();
		// En caso de que no se haya seleccionado ningún elemento del combo
		if (valores == null) {
			$('#msjAlert').html('Debes seleccionar al menos un item del combo o seleccionar "Ninguno" si los items de la categoría no aplican para esta cotización');
			modalAlert.modal('show');
			return false;
		}
		// En caso de que se seleccione "Ninguno" al mismo tiempo que se selecciona algún elemento de la categoría
		flag = false;
		$.each(valores, function (index, item) {
			if (item == '') {
				flag = true;
				return false;
			}
		});
		if (valores.length > 1 && flag == true) {
			$('#msjAlert').html('No puedes seleccionar "Ninguno" a la vez que seleccionas items de la categoría');
			modalAlert.modal('show');
			return false;
		}
		// En caso de que se seleccione "Ninguno" en el combo
		if (valores.length == 1 && valores[0] == '') {
			$('#tablaCotizacion').bootstrapTable('remove', { field: 'clasificador', values: [clasificador] });
			selectedRow.descripcion = 'Ninguno';
			selectedRow.ult_costo = 0;
			selectedRow.precioPiezaAD = 0;
			selectedRow.clasificador = '';
			actualizarFila(selectedIndex, selectedRow);
			actualizarNumeroPartidas();
			mergeCells();
			actualizarTotales();
			$('#modalCombos').modal('hide');
			return false;
		}
		// En caso de que se seleccione uno o más items de la categoria
		$.ajax({
			async: true,
			type: 'POST',
			cache: false,
			data: { clasificador: clasificador, valores: valores },
			url: 'Productos/Seleccion',
			dataType: 'json',
			beforeSend: function () {
				$('#msjAlert').html('ESPERA POR FAVOR...');
				modalAlert.modal('show');
			},
			success: function (json) {
				var data = $('#tablaCotizacion').bootstrapTable('getData');
				$.each(data, function (index, item) {
					if (item.clasificador == clasificador) {
						folios.push(item.folio);
					}
				});
				$('#tablaCotizacion').bootstrapTable('remove', { field: 'clasificador', values: [clasificador] });
				selectedRow.descripcion = valores.length + ' Items utilizados';
				selectedRow.ult_costo = 0;
				selectedRow.precioPiezaAD = 0;
				selectedRow.clasificador = '';
				actualizarFila(selectedIndex, selectedRow);
				i = 1; nextIndex = 0;
				$.each(json, function (index, item) {
					nextIndex = parseInt(selectedIndex) + i;
					row = crearPartida(item.CVE_ART, item.DESCR, item.ULT_COSTO, item.precioPiezaAD, 1, clasificador)
					$('#tablaCotizacion').bootstrapTable('insertRow', { index: nextIndex, row: row });
					i = i + 1;
				});
				actualizarNumeroPartidas();
				mergeCells();
				actualizarTotales();
				$('#modalCombos').modal('hide');
				modalAlert.modal('hide');
			}
		});

	});

	// Funcion para remover la columna seleccionada de la previsualizacion
	$('#removerFila').click(function () {
		filasSeleccionadas = $('#tablaCotizacion').bootstrapTable('getSelections');
		if (filasSeleccionadas.length > 0) {
			if (filasSeleccionadas[0]['cve_art'] == 'TUB' || filasSeleccionadas[0]['cve_art'] == 'RIE' || filasSeleccionadas[0]['cve_art'] == 'GUI' || filasSeleccionadas[0]['cve_art'] == 'SUP' || filasSeleccionadas[0]['cve_art'] == 'TOR' || filasSeleccionadas[0]['cve_art'] == 'OTR' || filasSeleccionadas[0]['cve_art'] == 'ZPROYECTOS02' || filasSeleccionadas[0]['cve_art'] == 'ZPROYECTOS01' || filasSeleccionadas[0]['cve_art'] == 'ZPROYECTOS04') {
				$('#msjAlert').html('La partida seleccionada es requerida en la creación de una cotización armada, no puedes borrarla');
				modalAlert.modal('show');
			} else if (filasSeleccionadas[0]['clasificador'] == 'TUB' || filasSeleccionadas[0]['clasificador'] == 'RIE' || filasSeleccionadas[0]['clasificador'] == 'GUI' || filasSeleccionadas[0]['clasificador'] == 'SUP' || filasSeleccionadas[0]['clasificador'] == 'TOR' || filasSeleccionadas[0]['clasificador'] == 'OTR') {
				$('#msjAlert').html('Para agregar o borrar items categorizados utiliza el combo de selección correspondiente');
				modalAlert.modal('show');
			} else {
				if (filasSeleccionadas[0]['folio'] != null && filasSeleccionadas[0]['folio'] != '') {
					folios.push(filasSeleccionadas[0]['folio']);
				}
				$('#tablaCotizacion').bootstrapTable('remove', { field: 'no_partida', values: [filasSeleccionadas[0]['no_partida']] });
				actualizarNumeroPartidas();
				actualizarTotales();
			}
		}
	});

	// Configuracion de la tabla de cotizaciones
	$('#tablaCotizaciones').bootstrapTable({
		data: [],
		pagination: true,
		sidePagination: 'client',
		pageList: [5, 10],
		pageSize: 5,
		clickToSelect: true,
		toolbar: '#toolbarCotizaciones',
		classes: 'table-condensed table-hover table-bordered',
		search: true,
		columns: [
			{ checkbox: true },
			{
				field: 'folio', title: 'Folio', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
					return value * 1
				}
			},
			{
				field: 'tq', title: 'TQ', align: 'center', halign: 'center', valign: 'middle', formatter: function (value, row, index) {
					return value == null ? '' : 'TQ' + value
				}
			},
			{ field: 'nombre_cliente', title: 'Cliente' },
			{ field: 'ffecha', title: 'Fecha', align: 'center', halign: 'center', valign: 'middle' },
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
			{ field: 'id_cliente', visible: false }
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
	$('#tablaCotizaciones tbody').on('click', 'button.cerrar', function () {
		cerrarCotizacion(window.folio);
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

	// Cargar los datos del contacto de la empresa
	$('#contacto').change(function () {
		$.getJSON("Clientes/ObtenerContactoID", { idempresa: $('#ID').text(), term: $('#contacto').val() }).done(function (json) {
			xxsetearContacto(json);
		});
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
	$('#ID').text(response.ID);
	$('#RFC').val(response.RFC);
	$('#direccion').val(response.DOMICILIO);
	$('#colonia').val(response.COLONIA);
	$('#municipio').val(response.MUNICIPIO);
	$('#estado').val(response.ESTADO);
	$('#CP').val(response.CP);
	$('#telefono').val(response.TELEFONO);
	$('#correo').val(response.CORREO);
	xsetearContacto(response.ID);
	setearTQ(response.ID);
}

// Funcion para reestablecer la replica de la tabla de cotizacion
var resetReplica = function () {
	data = $('#tablaCotizacion').bootstrapTable('getData');
	$.each(data, function (index, row) {
		if (row.cve_art != 'TUB' || row.cve_art != 'RIE' || row.cve_art != 'GUI' || row.cve_art != 'SUP' || row.cve_art != 'TOR' || row.cve_art != 'OTR') {
			row['replicas'] = row['piezas'] * $('#replica').val();
			$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
		}
	});
	actualizarTabla();
}

// Funcion para obtener el tipo de cambio del web service de banxico
var obtenerTipoCambio = function () {
	//var tc = 0;
	$.ajax({
		type: 'POST',
		url: 'Cotizador/ObtenerTC',
		dataType: 'json',
		async: true,
		beforeSend: function () {
			$('.loadingPage').show();
		},
		success: function (response) {
			//tc = response.tc;
			//fecha_tc = response.fecha_tc;
		}
	});
	//return [tc, fecha_tc];
}

// Actualizacion del numero de la partida
var actualizarNumeroPartidas = function () {
	data = $('#tablaCotizacion').bootstrapTable('getData');
	i = iTub = iRie = iGui = iSup = iTor = iOtr = 1;
	$.each(data, function (index, row) {
		switch (row.clasificador) {
			case '':
				row['no_partida'] = i;
				$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
				i = i + 1;
				break;
			case 'TUB':
				row['no_partida'] = '1.' + iTub;
				$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
				iTub = iTub + 1;
				break;
			case 'RIE':
				row['no_partida'] = '2.' + iRie;
				$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
				iRie = iRie + 1;
				break;
			case 'GUI':
				row['no_partida'] = '3.' + iGui;
				$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
				iGui = iGui + 1;
				break;
			case 'SUP':
				row['no_partida'] = '4.' + iSup;
				$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
				iSup = iSup + 1;
				break;
			case 'TOR':
				row['no_partida'] = '5.' + iTor;
				$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
				iTot = iTor + 1;
				break;
			case 'OTR':
				row['no_partida'] = '6.' + iOtr;
				$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
				iOtr = iOtr + 1;
				break;
			default:
				break;
		}
	});
	mergeCells();
}

// Funcion para actualizar el campo de replica y los campos calculados
var actualizarTabla = function () {
	data = $('#tablaCotizacion').bootstrapTable('getData');
	$.each(data, function (index, row) {
		if (row.cve_art != 'TUB' || row.cve_art != 'RIE' || row.cve_art != 'GUI' || row.cve_art != 'SUP' || row.cve_art != 'TOR' || row.cve_art != 'OTR') {
			actualizarFila(index, row);
		}
	});
	mergeCells();
	actualizarTotales();
}

// Funcion para actualizar los campos calculados de la fila
var actualizarFila = function (index, row) {
	if (row.cve_art != 'TUB' || row.cve_art != 'RIE' || row.cve_art != 'GUI' || row.cve_art != 'SUP' || row.cve_art != 'TOR' || row.cve_art != 'OTR') {
		var precioPiezaDD = row['precioPiezaAD'] - (row['precioPiezaAD'] * row['descuento'] / 100);
		row['precioParteDD'] = row['piezas'] * precioPiezaDD;
		row['precioReplicaAD'] = row['replicas'] * row['precioPiezaAD'];
		row['precioReplicaDD'] = row['replicas'] * precioPiezaDD;
		row['utilidad'] = 100 * (row['precioReplicaDD'] - row['ult_costo'] * row['replicas']) / row['precioReplicaDD'];
		$('#tablaCotizacion').bootstrapTable('updateRow', { index, row });
	}
}

// Funcion para calcular los totales de la cotizacion
var actualizarTotales = function () {
	replica = $('#replica').val();
	impuestos = parseInt($('#impuestos').editable('getValue', true));
	descuento = parseInt($('#descuento').editable('getValue', true));
	data = $('#tablaCotizacion').bootstrapTable('getData');

	stUsdPrecioPDD = 0;
	stUsdPrecioRAD = 0;
	stUsdPrecioRDD = 0;
	costo_total = 0;
	utilidad = 0;

	$.each(data, function (index, row) {
		if (row.cve_art != 'TUB' || row.cve_art != 'RIE' || row.cve_art != 'GUI' || row.cve_art != 'SUP' || row.cve_art != 'TOR' || row.cve_art != 'OTR') {
			stUsdPrecioPDD = stUsdPrecioPDD + parseFloat(row['precioParteDD']);
			stUsdPrecioRAD = stUsdPrecioRAD + parseFloat(row['precioReplicaAD']);
			stUsdPrecioRDD = stUsdPrecioRDD + parseFloat(row['precioReplicaDD']);
			costo_total = costo_total + (parseFloat(row['ult_costo']) * parseInt(row['replicas']));
		}
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

	utilidad = 100 * (totalPrecioRDD - costo_total) / totalPrecioRDD;

	utilidadST = 100 * (stUsdPrecioRDD - costo_total) / stUsdPrecioRDD;
	utilidadSTDD = 100 * (stPrecioRDD - costo_total) / stPrecioRDD;
	$('#utilidadST').html(formato_numero(utilidadST, 2, '.', ',') + '%');
	$('#utilidadSTDD').html(formato_numero(utilidadSTDD, 2, '.', ',') + '%');


	$('#fontMsj').empty();
	descuento_calculado = (((stPrecioRDD / stUsdPrecioRAD * 100) - 100) * -1);
	if (descuento_calculado > window.descuento_maximo) {
		if (window.fase_uno_usuario_autorizacion != null && window.fase_uno_usuario_autorizacion != '') {
			if (window.descuento_autorizado < window.descuento_calculado) {
				$('#btnImprimir').addClass('hidden');
				$('#fontMsj').text("Es necesario volver a autorizar el descuento total sobre la cotización pues es mayor al descuento autorizado previamente");
				$('#alerta, #btnAutorizarE1').removeClass('hidden');
			} else {
				$('#btnImprimir').removeClass('hidden');
				$('#alerta, #btnAutorizarE1').addClass('hidden');
			}
		} else {
			$('#btnImprimir').addClass('hidden');
			$('#fontMsj').text("Cotizaciones con descuento mayor al " + window.descuento_maximo + "% deben ser autorizadas para su uso e impresión");
			$('#alerta, #btnAutorizarE1').removeClass('hidden');
		}
	} else {
		$('#btnImprimir').removeClass('hidden');
		$('#alerta, #btnAutorizarE1').addClass('hidden');
	}

	if (utilidad < window.utilidad_minima) {
		if (window.fase_dos_usuario_autorizacion != null && window.fase_dos_usuario_autorizacion != '') {
			if (window.utilidad_autorizada < window.utilidad) {
				$('#btnImprimir').addClass('hidden');
				$('#fontMsj').append("<br>Es necesario volver a autorizar la utilidad calculada sobre la cotización pues es menor a la utilidad autorizada previamente");
				$('#alerta, #btnAutorizarE2').removeClass('hidden');
			} else {
				$('#btnImprimir').removeClass('hidden');
				$('#alerta, #btnAutorizarE2').addClass('hidden');
			}
		} else {
			$('#btnImprimir').addClass('hidden');
			$('#fontMsj').html("Cotizaciones con utilidad menor al " + window.utilidad_minima + "% deben ser autorizadas para su uso e impresión");
			$('#alerta, #btnAutorizarE2').removeClass('hidden');
		}
	} else {
		$('#btnImprimir').removeClass('hidden');
		$('#alerta, #btnAutorizarE1').addClass('hidden');
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
	//$('#tc').val(tc_info[0]).attr('title', 'Fuente Banxico, fecha ' + tc_info[1]);
	// Llenamos los combos de las partidas de armada
	llenarCombo($('#selectTubos'), 'TUB');
	llenarCombo($('#selectRieles'), 'RIE');
	llenarCombo($('#selectGuias'), 'GUI');
	llenarCombo($('#selectSuperficies'), 'SUP');
	llenarCombo($('#selectTornilleria'), 'TOR');
	llenarCombo($('#selectOtros'), 'OTR');
	$(".selectpicker").selectpicker();
}

// Funcion para guardar los cambios sobre la cotizacion
var guardarCotizacion = function () {
	pre_folio = $('#pre_folio').val();
	folio = $('#folio').val();
	cliente = [
		{ name: 'tipo', value: $('#tipo').val() }, // 0
		{ name: 'ID', value: $('#ID').html() }, // 1
		{ name: 'nombreEmpresa', value: $('#nombreEmpresa').val() }, // 2
		{ name: 'RFC', value: $('#RFC').val() }, // 3
		{ name: 'estado', value: $('#estado').val() }, // 4
		{ name: 'municipio', value: $('#municipio').val() }, // 5
		{ name: 'colonia', value: $('#colonia').val() }, // 6
		{ name: 'CP', value: $('#CP').val() }, // 7
		{ name: 'direccion', value: $('#direccion').val() }, // 8
		{ name: 'tq', value: $('#tq').val() }, // 9
		{ name: 'contacto', value: $('#contacto').val() }, // 10
		{ name: 'telefono', value: $('#telefono').val() }, // 11
		{ name: 'correo', value: $('#correo').val() }, // 12
		{ name: 'area', value: $('#area').val() }, // 13
		{ name: 'nuevoTQ', value: $('#nuevoTQ').val() } // 14
	];
	/*cliente = $('#formCliente').serializeArray();
	cliente.push({ name: 'id_cliente', value: $('#ID').text() });*/
	partidas = $('#tablaCotizacion').bootstrapTable('getData');
	encabezado = {
		tc: $('#tc').val(),
		replica: $('#replica').val(),
		descArmada: $('#descArmada').editable('getValue', true),
		representante: $('#gestorDeCuenta').editable('getValue', true),
		terminos: $('#terminosVenta').editable('getValue', true),
		observaciones: $('#observaciones').editable('getValue', true),
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
		tasa_impuesto: $('#impuestos').editable('getValue', true),
		descuentost: $('#descuento').editable('getValue', true),
		tipo: $('#tipo').val(),
		area: $('#area').val(),
	};
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { pre_folio: pre_folio, folio: folio, cliente: cliente, encabezado: encabezado, partidas: partidas, folios: folios },
		url: 'Cotizador/GuardarCotizacion',
		dataType: 'json',
		beforeSend: function () {
			$('#msjAlert').html('GUARDANDO COTIZACION, ESPERA POR FAVOR...');
			modalAlert.modal('show');
		},
		success: function (json) {
			console.log(json);
			$('#msjAlert').html(json.msj);
			if (json.bandera == true) {
				$('#folio').val(json.folio);
				cargarCotizacion(json.folio);
				folios.length = 0;
				modalAlert.modal('hide');
			}
		}
	});
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
				$('#folio').val(parseInt(en.folio));
				$('#pre_folio').val(parseInt(en.folio_preencabezado));
				$('#nombre').val(en.nombre_cliente);
				$('#nombreEmpresa').val(en.nombre_empresa);
				$('#ID').text(en.id_cliente);
				$('#RFC').val(en.rfc);
				$('#direccion').val(en.direccion);
				$('#colonia').val(en.colonia);
				$('#municipio').val(en.municipio);
				$('#estado').val(en.estado);
				$('#CP').val(en.codigo_postal);
				$('#telefono').val(en.telefono);
				$('#correo').val(en.correo);
				$('#tc').val(en.tipo_cambios);
				$('#replica').val(en.replicas);
				$('#descArmada').editable('setValue', en.descripcion_armado, false);
				$('#gestorDeCuenta').editable('setValue', en.representante_ventas, false);
				$('#terminosVenta').editable('setValue', en.terminos_y_condiciones, false);
				$('#observaciones').editable('setValue', en.observaciones, false);
				$('#descuento').editable('setValue', parseFloat(en.descuentost, 2), false);
				$('#impuestos').editable('setValue', parseFloat(en.tasa_impuesto, 2), false);
				$('#tipo').val(en.tipo_impresion);
				$('#area').val(en.area);
				xsetearContacto(en.id_cliente);
				setearTQ(en.id_cliente);
				$('#contacto').val(en.nombre_contacto);
				$('#tq').val(en.tq);
				window.descuento_autorizado = en.descuento_total;
				window.utilidad_autorizada = en.utilidad;
				window.fase_uno_usuario_autorizacion = en.fase_uno_usuario_autorizacion;
				window.fase_dos_usuario_autorizacion = en.fase_dos_usuario_autorizacion;
				if (en.tipo_impresion == 'B') {
					$('#tablaCotizacion').bootstrapTable('hideColumn', 'aparece_en_armada');
				} else {
					$('#tablaCotizacion').bootstrapTable('showColumn', 'aparece_en_armada');
				}
				setearPartidas();
				recargarGaleria();
				modalAlert.modal('hide');
				$('#modalCotizaciones').modal('hide');
				$('#rowCargar').hide();
				$('#tipo').prop('disabled', true);
				window.estatus = en.estatus;
				if (en.estatus == 'A') {
					$('#btnGuardar, #btnRechazar, #btnImprimir, #rowCargaImg, #rowGaleria').removeClass('hidden');
					string = "<span class='label label-default'>Abierta</span>";
				}
				if (en.estatus == 'C') {
					$('#btnGuardar, #btnRechazar, #rowCargaImg, #rowGaleria').addClass('hidden');
					$('#btnImprimir').removeClass('hidden');
					string = "<span class='label label-danger'>Rechazada</span>";
				}
				if (en.estatus == 'D') {
					$('#btnGuardar,  #btnRechazar, #rowCargaImg, #rowGaleria').addClass('hidden');
					$('#btnImprimir').removeClass('hidden');
					string = "<span class='label label-success'>Cerrada</span>"
				}
				if (en.estatus == 'B') {
					$('#btnGuardar, #rowCargaImg, #rowGaleria').addClass('hidden');
					$('#btnRechazar, #btnImprimir').removeClass('hidden');
					string = "<span class='label label-primary'>Autorizada</span>";
					$('#btnAutorizarE1, #btnAutorizarE2').addClass('hidden');
				}
				if (en.descuento_total > window.descuento_maximo && (en.fase_uno_usuario_autorizacion == null || en.fase_uno_usuario_autorizacion == '')) {
					$('#btnAutorizarE1').removeClass('hidden');
				} else if (en.utilidad < window.utilidad_minima && (en.fase_dos_usuario_autorizacion == null || en.fase_dos_usuario_autorizacion == '')) {
					$('#btnAutorizarE2').removeClass('hidden');
				}
				$('#fontEstatus').html(string);
			}
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
			mergeCells();
			actualizarTotales();
			$('#selectTubos').selectpicker('val', json.tubos);
			$('#selectRieles').selectpicker('val', json.rieles);
			$('#selectGuias').selectpicker('val', json.guias);
			$('#selectSuperficies').selectpicker('val', json.superficies);
			$('#selectTornilleria').selectpicker('val', json.tornilleria);
			$('#selectOtros').selectpicker('val', json.otros);
		}
	});
}

// Funcion para recargar la galeria de imagenes de la cotizacion
var recargarGaleria = function () {
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { folio: $('#folio').val(), pre_folio: $('#pre_folio').val() },
		url: 'Cotizador/ObtenerImagenes',
		dataType: 'json',
		success: function (json) {
			$('#galeria').empty();
			$.each(json, function (index, img) {
				$('#galeria').append(pintarImagen(img));
			});
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
		"				<p>Clic 'x' para quitar</p>" +
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
		async: true,
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
var cerrarCotizacion = function (folio) {
	$.ajax({
		async: true,
		type: 'POST',
		cache: false,
		data: { folio: folio },
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
			$element.append("<optgroup><option value=''>Ninguno</option></optgroup><optgroup>");
			$.each(json, function (index, item) {
				$element.append("<option value='" + item.CVE_ART + "'>" + item.CVE_ART + ' - ' + item.DESCR + "</option>");
			});
			$element.append("</optgroup>");
		}
	});
}

// Funcion para agrupar las celdas de las partidas de armada
mergeCells = function () {
	data = $('#tablaCotizacion').bootstrapTable('getData');
	$.each(data, function (index, row) {
		if (row.cve_art == 'TUB' || row.cve_art == 'RIE' || row.cve_art == 'GUI' || row.cve_art == 'SUP' || row.cve_art == 'TOR' || row.cve_art == 'OTR') {
			$('#tablaCotizacion').bootstrapTable('mergeCells', {
				index: index,
				field: 'descripcion',
				colspan: 10,
				rowspan: 1
			});
		}
	});
}

// Funcion para crear una nueva partida
crearPartida = function (cve_art, descripcion, costo, precio, piezas, clasificador) {
	return {
		no_partida: 0,
		folio: '',
		ult_costo: costo,
		cve_art: cve_art,
		descripcion: descripcion,
		precioPiezaAD: precio,
		piezas: piezas,
		descuento: 0,
		precioParteDD: precio * piezas,
		replicas: piezas * $('#replica').val(),
		precioReplicaAD: precio * piezas * $('#replica').val(),
		precioReplicaDD: precio * piezas * $('#replica').val(),
		ult_costo: 0,
		utilidad: 0,
		clasificador: clasificador,
		partida_armado: 'N',
		aparece_en_armada: 'No'
	};
}

// Funcion para setear la lista de contactos de la empresa seleccionada
function xsetearContacto(cliente) {
	$.ajax({
		type: 'POST',
		url: 'Clientes/ObtenerContactos',
		dataType: 'json',
		data: { idempresa: cliente },
		async: false,
		success: function (response) {
			if (response.length > 0) {
				$('#contacto').empty().append("<option value=''>Selecciona...</option>");
				$.each(response, function (index, item) {
					$('#contacto').append("<option value=" + item.intidcontacto + ">" + item.strnombre + "</option>");
				});
			}
		}
	});
}

// Funcion para setear los datos de contacto del cliente
function xxsetearContacto(data) {
	$('#contacto').val(data.intidcontacto);
	$('#telefono').val(data.strtelefono1);
	$('#correo').val(data.stremail);
	$('#area').val(data.strcampo1);
}

// Funcion para imprimir un conjunto de cotizaciones
function imprimirCotizaciones() {
	var cotizaciones = $('#tablaCotizaciones').bootstrapTable('getSelections');
	var impresiones = [], ids_cliente = [];
	canceladas = false;
	$.each(cotizaciones, function (index, item) {
		impresiones.push(item.folio);
		ids_cliente.push(item.id_cliente);
		if (item.estatus == 'C') canceladas = true;
	});
	ids_cliente = eliminateDuplicados(ids_cliente);
	if (impresiones.length == 0) {
		$('#msjAlert').html('Selecciona las cotizaciones a imprimir');
		modalAlert.modal('show');
		return false;
	}
	if (canceladas == true) {
		$('#msjAlert').html('No puedes agregar una cotizacion rechazada a una impresión grupal');
		modalAlert.modal('show');
		return false;
	}
	if (ids_cliente.length > 1) {
		$('#msjAlert').html('Las impresiones grupales solo se pueden realizar si pertenecen al mismo cliente');
		modalAlert.modal('show');
		return false;
	}
	$.cookie('impresiones', impresiones);
	window.open("Cotizador/ImprimirCotizaciones");
	ids_cliente.length = 0;
	impresiones.length = 0;
}

function eliminateDuplicados(arr) {
	var i,
		len = arr.length,
		out = [],
		obj = {};
	for (i = 0; i < len; i++) {
		obj[arr[i]] = 0;
	}
	for (i in obj) {
		out.push(i);
	}
	return out;
}

// Funcion para obtener los ultimos tq del cliente
function setearTQ(cliente) {
	$('#tq').empty().append("<option value=''>Selecciona...</option>");
	$.ajax({
		type: 'POST',
		url: 'Clientes/ObtenerTQs',
		dataType: 'json',
		data: { idempresa: cliente },
		async: false,
		success: function (response) {
			if (response.length > 0) {
				$.each(response, function (index, item) {
					$('#tq').append("<option value=" + item.intidcotizacion + ">TQ" + item.intidcotizacion + '-' + item.datfecharegistro + "</option>");
				});
			}
		}
	});
}