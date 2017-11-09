<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Cotizador </title>
		<link rel='icon' type='image/jpeg' href="<?php echo base_url('resources/images/trilogiq.png')?>" />
		<link href="<?php echo base_url('resources/bootstrap/dist/css/bootstrap.min.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/jquery-ui/jquery-ui.min.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/font-awesome/css/font-awesome.min.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/nprogress/nprogress.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/datetimepicker/build/css/bootstrap-datetimepicker.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/dropzone/dist/min/dropzone.min.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/bootstrap-table/bootstrap-table.min.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/bootstrap-table/extensions/editable/bootstrap-editable.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/cropper/dist/cropper.min.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('resources/iCheck/skins/flat/green.css')?>" rel="stylesheet">
		<link href="<?php echo base_url('build/css/custom.css')?>" rel="stylesheet">
	</head>
	<body style="padding-right: 0 !important">
		<!-- Modal para mostrar la imagen antes de cargarla al servidor -->
		<div class="modal fade docs-cropped" id="getCroppedCanvasModal" role="dialog" aria-hidden="true" aria-labelledby="getCroppedCanvasTitle" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="getCroppedCanvasTitle">Recorte</h4>
					</div>
					<div class="modal-body"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						<button class="btn btn-success" id="download">Enviar</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal para cargar un producto especial -->
		<div id="modalProducto" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Producto especial</h4>
					</div>
					<form method="POST" action="#" id="formProducto">
						<div class="modal-body">
							<input type="hidden" name="ult_costo" id="ult_costo">
							<input type="hidden" name="inputPieza" id="inputPieza">
							<div class="form-group">
								<label for="inputProducto">Producto</label>
								<input type="text" class="form-control" name="inputProducto" id="inputProducto">
							</div>
							<div class="form-group">
								<label for="inputPrecio">Precio</label>
								<input type="text" class="form-control text-right" name="inputPrecio" id="inputPrecio" readonly>
							</div>
							<div class="form-group">
								<label for="inputPiezas">Piezas</label>
								<input type="text" class="form-control text-right" name="inputPiezas" id="inputPiezas" required>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
							<button type="button" class="btn btn-success" id="confirmarParte"><i class="fa fa-send"></i> Agregar</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- Toolbar para la tabla de historico de cotizaciones -->
		<div class="row" id="toolbarCotizaciones">
			<div class="col-xs-6 col-sm-4 col-md-4 col-lg-3">
				<div class="input-group input-group-sm"">
					<span class="input-group-addon"><i class="fa fa-calendar"></i> <font class="hidden-xs hidden-sm">Desde</font></span>
					<input type="text" class="form-control text-center simple-dp" name="inputfi" id="inputfi" readonly placeholder="Desde">
				</div>
			</div>
			<div class="col-xs-6 col-sm-4 col-md-4 col-lg-3">
				<div class="input-group input-group-sm">
					<span class="input-group-addon"><i class="fa fa-calendar"></i> <font class="hidden-xs hidden-sm">Hasta</font></span>
					<input type="text" class="form-control text-center simple-dp" name="inputff" id="inputff"  readonly placeholder="Hasta">
				</div>
			</div>
			<div class="col-xs-6 col-sm-1 col-md-1 col-lg-2">
				<button type="button" class="btn btn-warning btn-sm btn-block" id="filtrarCotizaciones" title="Buscar Cotizaciones acorde a los parametros proporcionados" data-toggle="tooltip" data-placement="bottom"><i class="fa fa-filter"></i> <font class="hidden-sm hidden-md">Buscar</font></button>
			</div>
		</div>
		<!-- Modal para cargar el listado de cotizaciones -->
		<div id="modalCotizaciones" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Cotizaciones</h4>
					</div>
					<div class="modal-body">
						<table id="tablaCotizaciones"></table>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal para bloquear la vista mientras se realizan peticiones ajax al servidor -->
		<div id="modalAlert" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Mensaje del sistema</h4>
					</div>
					<div class="modal-body">
						<strong id="msjAlert"></strong>
					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid">
		<!-- Lista de Acciones sobre la cotizacion -->
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<ul class="nav nav-pills pull-right form-inline">
						<input type="text" class="form-control text-center" name="pre_folio" id="pre_folio" value="" readonly placeholder="Pre-folio">
						<input type="text" class="form-control text-center" name="folio" id="folio" value="" readonly placeholder="Folio">
						<li role="presentation"><button type="button" data-toggle="tooltip" data-placement="bottom" class="btn btn-info" id="abrirCotizacion" title="Abrir historial de cotizaciones"><i class="fa fa-folder-open-o"></i> <font class="hidden-xs">Abrir...</font></button></li>
						<li role="presentation"><button type="button" data-toggle="tooltip" data-placement="bottom" class="btn btn-primary" title="Guardar los cambios" id="btnGuardar"><i class="fa fa-floppy-o"></i> <font class="hidden-xs">Guardar</font></button></li>
						<li role="presentation"><button type="button" data-toggle="tooltip" data-placement="bottom" class="btn btn-success" title="Autorizar la impresión de la cotización"><i class="fa fa-unlock"></i> <font class="hidden-xs">Autorizar</font></button></li>
						<li role="presentation" id="liImprimir">
							<button type="button" id="btnImprimir" data-toggle="tooltip" data-placement="bottom" class="btn btn-info" title="Imprimir cotización"><i class="fa fa-file-pdf-o"></i> <font class="hidden-xs">Imprimir</font></button>
						</li>
						<li role="presentation"><button type="button" data-toggle="tooltip" data-placement="bottom" class="btn btn-warning" title="Rechazar uso de la cotización"><i class="fa fa-close"></i> <font class="hidden-xs">Rechazar</font></button></li>
					</ul>
				</div>
			</div>
			<div class="row" id="rowMsj">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<strong style="color: red">Cotizaciones con descuentos mayores a 15% deben ser aprobados para su impresión</strong>
				</div>
			</div>
			<hr style="margin-top: 2px; margin-bottom: 10px; border: 0;" />
			<!-- Panel para mostrar los datos del cliente -->
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="x_panel"">
						<form method="POST" action="#" id="formCliente">
							<div class="x_title">
								<h2>Datos del cliente</h2>
								<ul class="nav navbar-right panel_toolbox">
									<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
									</li>
								</ul>
								<input type="text" class="form-control autocomplete" name="nombre" id="nombre" placeholder="Buscar por nombre de cliente" autofocus >
								<div class="clearfix"></div>
							</div>
							<div class="x_content">
								<div class="row">
									<div class="col-xs-12 col-sm-9 col-md-6">
										<div class="form-group">
											<label for="nombreEmpresa">Nombre empresa</label>
											<input type="text" class="form-control" name="nombreEmpresa" id="nombreEmpresa" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-3 col-md-3">
										<div class="form-group">
											<label for="ID">ID</label>
											<input type="text" class="form-control" name="ID" id="ID" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-4 col-md-3">
										<div class="form-group">
											<label for="RFC">RFC</label>
											<input type="text" class="form-control" name="RFC" id="RFC" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-8 col-md-6">
										<div class="form-group">
											<label for="direccion">Dirección</label>
											<input type="text" class="form-control" name="direccion" id="direccion" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-4 col-md-3">
										<div class="form-group">
											<label for="colonia">Colonia</label>
											<input type="text" class="form-control" name="colonia" id="colonia" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-4 col-md-3">
										<div class="form-group">
											<label for="municipio">Municipio</label>
											<input type="text" class="form-control" name="municipio" id="municipio" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-4">
										<div class="form-group">
											<label for="estado">Estado</label>
											<input type="text" class="form-control" name="estado" id="estado" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-3">
										<div class="form-group">
											<label for="CP">C P</label>
											<input type="text" class="form-control" name="CP" id="CP" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-5">
										<div class="form-group">
											<label for="contacto">Contacto</label>
											<input type="text" class="form-control" name="contacto" id="contacto" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-4">
										<div class="form-group">
											<label for="telefono">Teléfono</label>
											<input type="text" class="form-control" name="telefono" id="telefono" readonly>
										</div>
									</div>
									<div class="col-xs-12 col-sm-8">
										<div class="form-group">
											<label for="correo">Correo</label>
											<input type="text" class="form-control" name="correo" id="correo" readonly>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- Panel para cargar la cotizacion en formato xls -->
			<div class="row" id="rowCargar">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="x_panel"">
						<div class="x_title">
							<h2>Cargar archivo de cotización</i></h2>
							<ul class="nav navbar-right panel_toolbox">
								<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<form enctype="multipart/form-data" action="#" class="dropzone" id = 'excelArea'></form>
						</div>
					</div>
				</div>
			</div>
			<!-- Panel para previsualizar la cotizacion proporcionada en el archivo de excel -->
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="x_panel"">
						<div class="x_title">
							<h2>Pre visualizacion</h2>
							<ul class="nav navbar-right panel_toolbox">
								<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<table id="tablaCotizacion" class="jambo_table bulk_action">
								<tfoot>
									<tr>
										<th colspan="6"><label id="gestorDeCuenta"></label></th>
										<th colspan="3" class="text-right">SubTotal Usd</th>
										<th id="stUsdPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="stUsdPrecioRAD" class="text-right"></th>
										<th id="stUsdPrecioRDD" class="text-right"></th>
									</tr>
									<tr>
										<th colspan="7" rowspan="3"><label id="terminosVenta"></label></th>
										<th colspan="2" class="text-right">Subtotal Mxn</th>
										<th id="stMxpPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="stMxpPrecioRAD" class="text-right"></th>
										<th id="stMxpPrecioRDD" class="text-right"></th>
									</tr>
									<tr>
										<th class="text-right">Descuento</th>
										<th class="text-right"><label id="descuento">0</label></th>
										<th id="descuentoPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="descuentoPrecioRAD" class="text-right"></th>
										<th id="descuentoPrecioRDD" class="text-right"></th>
									</tr>
									<tr>
										<th colspan="2" class="text-right">Subtotal Mxn DD</th>
										<th id="stPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="stPrecioRAD" class="text-right"></th>
										<th id="stPrecioRDD" class="text-right"></th>
									</tr>
									<tr>
									<th colspan="7" rowspan="2"><label id="observaciones"></label></th>
										<th class="text-right">Impuestos</th>
										<th class="text-right"><label id="impuestos">16</label></th>
										<th id="ivaPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="ivaPrecioRAD" class="text-right"></th>
										<th id="ivaPrecioRDD" class="text-right"></th>
									</tr>
									<tr>
										<th colspan="2" class="text-right">Total</th>
										<th id="totalPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="totalPrecioRAD" class="text-right"></th>
										<th id="totalPrecioRDD" class="text-right"></th>
									</tr>
									<tr>
										<th colspan="7"></th>
										<th colspan="2" class="text-right">Utilidad Bruta</th>
										<th colspan="4" id="utilidad" class="text-right"></th>
									</tr>
									<tr>
										<th colspan="13" id="faltantes"></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- Toolbar para la tabla de previsualizacion de la vista -->
			<div id="toolbar">
				<div class="row form-inline">
					<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2">
						<button type="button" class="btn btn-success btn-block btn-sm" data-toggle="tooltip" data-placement="bottom" id="agregarParte" title="Agregar un producto especial"><i class="fa fa-plus-square"></i> <font class="hidden-xs">Agregar</font></button>
					</div>
					<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2">
						<button type="button" class="btn btn-warning btn-block  btn-sm" data-toggle="tooltip" data-placement="bottom" id="removerFila" title="Quitar el producto seleccionado de la cotización"><i class="fa fa-eraser"></i> <font class="hidden-xs">Quitar</font></button>
					</div>
					<div class="col-xs-6 col-sm-3 col-md-2 col-lg-3">
						<div class="input-group input-group-sm">
							<span class="input-group-addon">T.C.</span>
							<input type="text" class="form-control text-right  btn-sm" name="tc" id="tc" data-toggle="tooltip" data-placement="bottom">
						</div>
					</div>
					<div class="col-xs-5 col-sm-3 col-md-2 col-lg-3">
						<div class="input-group input-group-sm">
							<span class="input-group-addon">Replica</span>
							<input type="text" class="form-control text-right" id="replica" value="1">
						</div>
					</div>
					<div class="col-xs-7 col-sm-4 col-md-3 col-lg-3 hidden">
						<div class="input-group input-group-sm">
							<span class="input-group-addon" >Descuento %</span>
							<input type="text" class="form-control text-right" id="std" value="0">
						</div>
					</div>
					<div class="col-xs-3 col-sm-2 col-md-2 col-lg-2">
						<div class="input-group input-group-sm">
						<div class="checkbox">
							<label><input type="checkbox" name="tipo" id="tipo"> Armado</label>
						</div>
						</div>
					</div>
				</div>
				<div class="row" id="divArmado">
						<textarea class="form-control" rows="1" id="taArmado" name="taArmado" placeholder="Proporciona la descripción del producto armado"></textarea>
				</div>
			</div>
			<!-- Panel para cargar y editar las imagenes que se anexaran a la cotizacion -->
			<div class="row hidden-xs">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="x_panel">
						<div class="x_title">
							<h2>Cargar imagenes</h2>
							<ul class="nav navbar-right panel_toolbox">
								<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
						<div class="x_content" >
							<div class="row" id="actions">
								<div class="col-md-12 docs-buttons">
									<div class="btn-group">
										<label class="btn btn-success btn-upload" for="inputImage" title="Cargar nueva imagen" data-toggle="tooltip" data-placement="bottom">
											<input type="file" class="sr-only" id="inputImage" name="file" accept=".jpg,.jpeg,.png,.gif,.bmp,.tiff">
											<span class="fa fa-upload"></span>
										</label>
										<button type="button" class="btn btn-success" id="imgGuardar"  title="Guardar imagen" data-toggle="tooltip" data-placement="bottom"><i class="fa fa-floppy-o"></i></button>
									</div>
								</div>
							</div>
							<hr style="margin-top: 2px; margin-bottom: 10px; border: 0;" />
							<div class="row">
								<div class="col-lg-10">
									<div class="img-container">
										<img id="image" src="<?php echo base_url('resources/images/cropperInicio.jpg') ?>" alt="Picture">
									</div>
								</div>
								<div class="col-lg-2">
									<div class="input-group input-group-sm">
										<label class="input-group-addon" for="dataX">X</label>
										<input type="text" class="form-control text-right" id="dataX" placeholder="x" readonly>
										<span class="input-group-addon">px</span>
									</div>
									<div class="input-group input-group-sm">
										<label class="input-group-addon" for="dataY">Y</label>
										<input type="text" class="form-control text-right" id="dataY" placeholder="y" readonly>
										<span class="input-group-addon">px</span>
									</div>
									<div class="input-group input-group-sm">
										<label class="input-group-addon" for="dataWidth">Ancho</label>
										<input type="text" class="form-control text-right" id="dataWidth" placeholder="Ancho de la imagen" readonly>
										<span class="input-group-addon">px</span>
									</div>
									<div class="input-group input-group-sm">
										<label class="input-group-addon" for="dataHeight">Alto</label>
										<input type="text" class="form-control text-right" id="dataHeight" placeholder="Alto de la imagen" readonly>
										<span class="input-group-addon">px</span>
									</div>
									<div class="input-group input-group-sm">
										<label class="input-group-addon" for="dataRotate">Rotación</label>
										<input type="text" class="form-control text-right" id="dataRotate" placeholder="Rotación de la imagen" readonly>
										<span class="input-group-addon">deg</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="<?php echo base_url('resources/jquery/dist/jquery.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap/dist/js/bootstrap.min.js')?>"></script>
		<script src="<?php echo base_url('resources/jquery-ui/jquery-ui.min.js')?>"></script>
		<script src="<?php echo base_url('resources/fastclick/lib/fastclick.js')?>"></script>
		<script src="<?php echo base_url('resources/nprogress/nprogress.js')?>"></script>
		<script src="<?php echo base_url('resources/moment/min/moment.min.js')?>"></script>
		<script src="<?php echo base_url('resources/moment/locale/es.js')?>"></script>
		<script src="<?php echo base_url('resources/datetimepicker/build/js/bootstrap-datetimepicker.min.js')?>"></script>
		<script src="<?php echo base_url('resources/dropzone/dist/min/dropzone.min.js')?>"></script>
		<script src="<?php echo base_url('resources/cropper/dist/cropper.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/bootstrap-table.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/locale/bootstrap-table-es-MX.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/extensions/editable/bootstrap-table-editable.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/extensions/editable/bootstrap-editable.js')?>"></script>
		<script src="<?php echo base_url('resources/js.cookie/js.cookie.js')?>"></script>
		<script src="<?php echo base_url('resources/iCheck/icheck.min.js')?>"></script>
		<script src="<?php echo base_url('/build/js/custom.js')?>"></script>
		<script src="<?php echo base_url('/build/js/cotizador.js')?>"></script>
		<script src="<?php echo base_url('/build/js/maincropper.js')?>"></script>
	</body>
</html>
