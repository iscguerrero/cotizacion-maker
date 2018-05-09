<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Cotizador </title>
		<link rel='icon' type='image/jpeg' href="<?php echo base_url('public/images/trilogiq.jpeg')?>" />
		<style>
			.pace {
				-webkit-pointer-events: none;
				pointer-events: none;

				-webkit-user-select: none;
				-moz-user-select: none;
				user-select: none;

				-webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				-ms-box-sizing: border-box;
				-o-box-sizing: border-box;
				box-sizing: border-box;

				-webkit-border-radius: 10px;
				-moz-border-radius: 10px;
				border-radius: 10px;

				-webkit-background-clip: padding-box;
				-moz-background-clip: padding;
				background-clip: padding-box;

				z-index: 2000;
				position: fixed;
				margin: auto;
				top: 12px;
				left: 0;
				right: 0;
				bottom: 0;
				width: 200px;
				height: 50px;
				overflow: hidden;
			}

			.pace .pace-progress {
				-webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				-ms-box-sizing: border-box;
				-o-box-sizing: border-box;
				box-sizing: border-box;

				-webkit-border-radius: 2px;
				-moz-border-radius: 2px;
				border-radius: 2px;

				-webkit-background-clip: padding-box;
				-moz-background-clip: padding;
				background-clip: padding-box;

				-webkit-transform: translate3d(0, 0, 0);
				transform: translate3d(0, 0, 0);

				display: block;
				position: absolute;
				right: 100%;
				margin-right: -7px;
				width: 93%;
				top: 7px;
				height: 14px;
				font-size: 12px;
				background: #29d;
				color: #29d;
				line-height: 60px;
				font-weight: bold;
				font-family: Helvetica, Arial, "Lucida Grande", sans-serif;

				-webkit-box-shadow: 120px 0 #fff, 240px 0 #fff;
				-ms-box-shadow: 120px 0 #fff, 240px 0 #fff;
				box-shadow: 120px 0 #fff, 240px 0 #fff;
			}

			.pace .pace-progress:after {
				content: attr(data-progress-text);
				display: inline-block;
				position: fixed;
				width: 45px;
				text-align: right;
				right: 0;
				padding-right: 16px;
				top: 4px;
			}

			.pace .pace-progress[data-progress-text="0%"]:after { right: -200px }
			.pace .pace-progress[data-progress-text="1%"]:after { right: -198.14px }
			.pace .pace-progress[data-progress-text="2%"]:after { right: -196.28px }
			.pace .pace-progress[data-progress-text="3%"]:after { right: -194.42px }
			.pace .pace-progress[data-progress-text="4%"]:after { right: -192.56px }
			.pace .pace-progress[data-progress-text="5%"]:after { right: -190.7px }
			.pace .pace-progress[data-progress-text="6%"]:after { right: -188.84px }
			.pace .pace-progress[data-progress-text="7%"]:after { right: -186.98px }
			.pace .pace-progress[data-progress-text="8%"]:after { right: -185.12px }
			.pace .pace-progress[data-progress-text="9%"]:after { right: -183.26px }
			.pace .pace-progress[data-progress-text="10%"]:after { right: -181.4px }
			.pace .pace-progress[data-progress-text="11%"]:after { right: -179.54px }
			.pace .pace-progress[data-progress-text="12%"]:after { right: -177.68px }
			.pace .pace-progress[data-progress-text="13%"]:after { right: -175.82px }
			.pace .pace-progress[data-progress-text="14%"]:after { right: -173.96px }
			.pace .pace-progress[data-progress-text="15%"]:after { right: -172.1px }
			.pace .pace-progress[data-progress-text="16%"]:after { right: -170.24px }
			.pace .pace-progress[data-progress-text="17%"]:after { right: -168.38px }
			.pace .pace-progress[data-progress-text="18%"]:after { right: -166.52px }
			.pace .pace-progress[data-progress-text="19%"]:after { right: -164.66px }
			.pace .pace-progress[data-progress-text="20%"]:after { right: -162.8px }
			.pace .pace-progress[data-progress-text="21%"]:after { right: -160.94px }
			.pace .pace-progress[data-progress-text="22%"]:after { right: -159.08px }
			.pace .pace-progress[data-progress-text="23%"]:after { right: -157.22px }
			.pace .pace-progress[data-progress-text="24%"]:after { right: -155.36px }
			.pace .pace-progress[data-progress-text="25%"]:after { right: -153.5px }
			.pace .pace-progress[data-progress-text="26%"]:after { right: -151.64px }
			.pace .pace-progress[data-progress-text="27%"]:after { right: -149.78px }
			.pace .pace-progress[data-progress-text="28%"]:after { right: -147.92px }
			.pace .pace-progress[data-progress-text="29%"]:after { right: -146.06px }
			.pace .pace-progress[data-progress-text="30%"]:after { right: -144.2px }
			.pace .pace-progress[data-progress-text="31%"]:after { right: -142.34px }
			.pace .pace-progress[data-progress-text="32%"]:after { right: -140.48px }
			.pace .pace-progress[data-progress-text="33%"]:after { right: -138.62px }
			.pace .pace-progress[data-progress-text="34%"]:after { right: -136.76px }
			.pace .pace-progress[data-progress-text="35%"]:after { right: -134.9px }
			.pace .pace-progress[data-progress-text="36%"]:after { right: -133.04px }
			.pace .pace-progress[data-progress-text="37%"]:after { right: -131.18px }
			.pace .pace-progress[data-progress-text="38%"]:after { right: -129.32px }
			.pace .pace-progress[data-progress-text="39%"]:after { right: -127.46px }
			.pace .pace-progress[data-progress-text="40%"]:after { right: -125.6px }
			.pace .pace-progress[data-progress-text="41%"]:after { right: -123.74px }
			.pace .pace-progress[data-progress-text="42%"]:after { right: -121.88px }
			.pace .pace-progress[data-progress-text="43%"]:after { right: -120.02px }
			.pace .pace-progress[data-progress-text="44%"]:after { right: -118.16px }
			.pace .pace-progress[data-progress-text="45%"]:after { right: -116.3px }
			.pace .pace-progress[data-progress-text="46%"]:after { right: -114.44px }
			.pace .pace-progress[data-progress-text="47%"]:after { right: -112.58px }
			.pace .pace-progress[data-progress-text="48%"]:after { right: -110.72px }
			.pace .pace-progress[data-progress-text="49%"]:after { right: -108.86px }
			.pace .pace-progress[data-progress-text="50%"]:after { right: -107px }
			.pace .pace-progress[data-progress-text="51%"]:after { right: -105.14px }
			.pace .pace-progress[data-progress-text="52%"]:after { right: -103.28px }
			.pace .pace-progress[data-progress-text="53%"]:after { right: -101.42px }
			.pace .pace-progress[data-progress-text="54%"]:after { right: -99.56px }
			.pace .pace-progress[data-progress-text="55%"]:after { right: -97.7px }
			.pace .pace-progress[data-progress-text="56%"]:after { right: -95.84px }
			.pace .pace-progress[data-progress-text="57%"]:after { right: -93.98px }
			.pace .pace-progress[data-progress-text="58%"]:after { right: -92.12px }
			.pace .pace-progress[data-progress-text="59%"]:after { right: -90.26px }
			.pace .pace-progress[data-progress-text="60%"]:after { right: -88.4px }
			.pace .pace-progress[data-progress-text="61%"]:after { right: -86.53999999999999px }
			.pace .pace-progress[data-progress-text="62%"]:after { right: -84.68px }
			.pace .pace-progress[data-progress-text="63%"]:after { right: -82.82px }
			.pace .pace-progress[data-progress-text="64%"]:after { right: -80.96000000000001px }
			.pace .pace-progress[data-progress-text="65%"]:after { right: -79.1px }
			.pace .pace-progress[data-progress-text="66%"]:after { right: -77.24px }
			.pace .pace-progress[data-progress-text="67%"]:after { right: -75.38px }
			.pace .pace-progress[data-progress-text="68%"]:after { right: -73.52px }
			.pace .pace-progress[data-progress-text="69%"]:after { right: -71.66px }
			.pace .pace-progress[data-progress-text="70%"]:after { right: -69.8px }
			.pace .pace-progress[data-progress-text="71%"]:after { right: -67.94px }
			.pace .pace-progress[data-progress-text="72%"]:after { right: -66.08px }
			.pace .pace-progress[data-progress-text="73%"]:after { right: -64.22px }
			.pace .pace-progress[data-progress-text="74%"]:after { right: -62.36px }
			.pace .pace-progress[data-progress-text="75%"]:after { right: -60.5px }
			.pace .pace-progress[data-progress-text="76%"]:after { right: -58.64px }
			.pace .pace-progress[data-progress-text="77%"]:after { right: -56.78px }
			.pace .pace-progress[data-progress-text="78%"]:after { right: -54.92px }
			.pace .pace-progress[data-progress-text="79%"]:after { right: -53.06px }
			.pace .pace-progress[data-progress-text="80%"]:after { right: -51.2px }
			.pace .pace-progress[data-progress-text="81%"]:after { right: -49.34px }
			.pace .pace-progress[data-progress-text="82%"]:after { right: -47.480000000000004px }
			.pace .pace-progress[data-progress-text="83%"]:after { right: -45.62px }
			.pace .pace-progress[data-progress-text="84%"]:after { right: -43.76px }
			.pace .pace-progress[data-progress-text="85%"]:after { right: -41.9px }
			.pace .pace-progress[data-progress-text="86%"]:after { right: -40.04px }
			.pace .pace-progress[data-progress-text="87%"]:after { right: -38.18px }
			.pace .pace-progress[data-progress-text="88%"]:after { right: -36.32px }
			.pace .pace-progress[data-progress-text="89%"]:after { right: -34.46px }
			.pace .pace-progress[data-progress-text="90%"]:after { right: -32.6px }
			.pace .pace-progress[data-progress-text="91%"]:after { right: -30.740000000000002px }
			.pace .pace-progress[data-progress-text="92%"]:after { right: -28.880000000000003px }
			.pace .pace-progress[data-progress-text="93%"]:after { right: -27.02px }
			.pace .pace-progress[data-progress-text="94%"]:after { right: -25.16px }
			.pace .pace-progress[data-progress-text="95%"]:after { right: -23.3px }
			.pace .pace-progress[data-progress-text="96%"]:after { right: -21.439999999999998px }
			.pace .pace-progress[data-progress-text="97%"]:after { right: -19.58px }
			.pace .pace-progress[data-progress-text="98%"]:after { right: -17.72px }
			.pace .pace-progress[data-progress-text="99%"]:after { right: -15.86px }
			.pace .pace-progress[data-progress-text="100%"]:after { right: -14px }


			.pace .pace-activity {
				position: absolute;
				width: 100%;
				height: 28px;
				z-index: 2001;
				box-shadow: inset 0 0 0 2px #29d, inset 0 0 0 7px #FFF;
				border-radius: 10px;
			}

			.pace.pace-inactive {
				display: none;
			}

			.pace-running > *:not(.pace) {
				opacity:0;
			}

		</style>
		<script>
			window.paceOptions = {
				ajax: false,
				restartOnRequestAfter: false,
			};
		</script>
		<script src="<?php echo base_url('resources/pace.min.js')?>"></script>
		<link rel="stylesheet" href="<?php echo base_url('resources/bootstrap/dist/css/bootstrap.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/jquery-ui/jquery-ui.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/font-awesome/css/font-awesome.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/nprogress/nprogress.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/datetimepicker/build/css/bootstrap-datetimepicker.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/dropzone/dist/min/dropzone.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/bootstrap-table/bootstrap-table.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/bootstrap-table/extensions/editable/bootstrap-editable.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/bootstrap-select.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('public/css/custom.css')?>">
		<style>
			.form-inline .form-control {
				display: inline-block;
				width: auto;
				vertical-align: middle;
			}
		</style>
	</head>
	<body style="padding-right: 0 !important">
		<div class="container-fluid" style="max-width: 1200px">
			<div class="row">
				<div class="col-xs-12 form-inline">
					<div class="btn-group" role="group">
						<?php
							if(in_array($tipo_usuario, array('gerente', 'director'))) { ?>
								<a href="<?php echo base_url('Cotizador/Condiciones') ?>" target="_blank" role="button" data-tool="tooltip" data-placement="bottom" class="btn btn-info" title="Modulo de términos y condiciones"><i class="fa fa-list-alt hidden-lg"></i> <font class="visible-lg">T y C</font></a> <?php
							}
						?>
						<button type="button" data-tool="tooltip" data-placement="bottom" class="btn btn-primary" title="Dar de alta un nuevo cliente o prospecto" data-toggle="modal" data-target="#modalCliente"><i class="fa fa-users hidden-lg"></i> <font class="visible-lg">Clientes</font></button>
						<button type="button" data-tool="tooltip" data-placement="bottom" class="btn btn-primary" title="Dar de alta un nuevo contacto" data-toggle="modal" data-target="#modalContacto"><i class="fa fa-phone hidden-lg"></i> <font class="visible-lg">Contactos</font></button>
					</div>
					<input type="text" class="form-control text-center" name="pre_folio" id="pre_folio" value="" readonly placeholder="Pre-folio" style="max-width: 150px">
					<input type="text" class="form-control text-center" name="folio" id="folio" value="" readonly placeholder="Folio" style="max-width: 150px">
					<font id="fontEstatus"></font>
					<div class="btn-group pull-right" role="group">
						<a href="<?php echo base_url() ?>" role="button" data-tool="tooltip" data-placement="bottom" class="btn btn-info" title="Crear nueva cotización"><i class="fa fa-file-o hidden-lg"></i> <font class="visible-lg">Nueva</font></a>
						<button type="button" data-tool="tooltip" data-placement="bottom" class="btn btn-info" data-toggle="modal" data-target="#modalCotizaciones" title="Abrir historial de cotizaciones"><i class="fa fa-folder-open-o hidden-lg"></i> <font class="visible-lg">Abrir</font></button>
						<button type="button" data-tool="tooltip" data-placement="bottom" class="btn btn-info" title="Imprimir cotización (Guarda cambios antes de imprimir)" id="btnImprimir"><i class="fa fa-file-pdf-o hidden-lg"></i> <font class="visible-lg">Imprimir</font>
						<button type="button" onclick="guardarCotizacion()" data-tool="tooltip" data-placement="bottom" class="btn btn-primary" title="Guardar los cambios" id="btnGuardar"><i class="fa fa-floppy-o hidden-lg"></i> <font class="visible-lg">Guardar</font></button>
					</div>
				</div>
			</div> <hr style="margin-top: 5px; margin-bottom: 2px;">
			<div class="row">
				<div class="col-xs-12">
					<div class="btn-group pull-right" role="group">
						<?php
							if(in_array($tipo_usuario, array('gerente', 'director'))) { ?>
								<button type="button" onclick="cambiarEstado('B')" data-tool="tooltip" data-placement="bottom" class="btn btn-info" title="Solicitar modificar el descuento de la autorización" id="btnB"><i class="fa fa-thumbs-down hidden-lg"></i> <font class="visible-lg">Solicitar Modificación del Descuento</font></button> <?php
							}
						?>
						<button type="button" onclick="cambiarEstado('C')" data-tool="tooltip" data-placement="bottom" class="btn btn-info" title="Solicitar autorizar el descuento de la cotización" id="btnC"><i class="fa fa-star-half-o hidden-lg"></i> <font class="visible-lg">Solicitar Autorización del Descuento</font></button>
						<?php
							if(in_array($tipo_usuario, array('gerente', 'director'))) { ?>
								<button type="button" onclick="cambiarEstado('D')" data-tool="tooltip" data-placement="bottom" class="btn btn-primary" title="Autorizar el descuento de la cotización" id="btnD"><i class="fa fa-certificate hidden-lg"></i> <font class="visible-lg">Autorizar Descuento</font></button> <?php
							}
						?>
						<?php
							if(in_array($tipo_usuario, array('gerente', 'director'))) { ?>
								<button type="button" onclick="cambiarEstado('E')" data-tool="tooltip" data-placement="bottom" class="btn btn-info" title="Solicitar modificar la utilidad de la cotización" id="btnE"><i class="fa fa-star hidden-lg"></i> <font class="visible-lg">Solicitar Modificación de la Utilidad</font></button> <?php
							}
						?>
						<button type="button" onclick="cambiarEstado('F')" data-tool="tooltip" data-placement="bottom" class="btn btn-info" title="Solicitar autorización la utilidad de la cotización" id="btnF"><i class="fa fa-star hidden-lg"></i> <font class="visible-lg">Solicitar Autorización de la Utilidad</font></button>
						<?php
							if($tipo_usuario == 'director') { ?>
								<button type="button" onclick="cambiarEstado('G')" data-tool="tooltip" data-placement="bottom" class="btn btn-primary" title="Autorizar Utilidad" id="btnG"><i class="fa fa-certificate hidden-lg"></i> <font class="visible-lg">Autorizar Utilidad</font></button> <?php
							}
						?>
						<?php
							if(in_array($tipo_usuario, array('gerente', 'director'))) { ?>
								<button type="button" onclick="cambiarEstado('H')" data-tool="tooltip" data-placement="bottom" class="btn btn-warning" title="Archivar cotización" id="btnH"><i class="fa fa-archive hidden-lg"></i> <font class="visible-lg">Archivar</font></button> <?php
							}
						?>
						<?php
							if(in_array($tipo_usuario, array('gerente', 'director'))) { ?>
								<button type="button" onclick="cambiarEstado('I')" data-tool="tooltip" data-placement="bottom" class="btn btn-danger" title="Rechazar cotización" id="btnI"><i class="fa fa-ban hidden-lg"></i> <font class="visible-lg">Rechazar</font></button> <?php
							}
						?>
					</div>
				</div>
			</div>
			<hr style="margin-top: 2px; margin-bottom: 5px; border: 0;" />

			<!-- Mensaje de alerta sobre el descuento sobre la cotizacion -->
			<div class="row" id="alerta">
				<div class="col-xs-12">
					<div class="alert alert-warning" role="alert" id="fontMsj">
					</div>
				</div>
			</div>
			<hr style="margin-top: 2px; margin-bottom: 1px; border: 0;" />

			<!-- Panel para mostrar los datos del cliente -->
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="x_panel">
						<form method="POST" action="#" id="formCliente">
							<div class="x_title">
								<h2>Información del cliente</h2>
								<ul class="nav navbar-right panel_toolbox">
									<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
								</ul>
								<div class="clearfix"></div>
							</div>
							<div class="x_content">
								<div class="row">
									<div class="col-xs-4 col-sm-3 col-md-3 col-lg-3">
										<label for="tipo">Tipo Cotizacion</label>
										<select class="form-control" name="tipo" id="tipo" autofocus >
											<?php
												foreach($tipos as $key => $item) {
													echo "<option value=".$item['value'].">".$item['text']."</option>";
												}
											?>
										</select>
									</div>
									<div class="col-xs-8 col-sm-9 col-md-6 col-lg-6">
										<label for="nombreEmpresa">Nombre</label>
										<div class="input-group">
											<span class="input-group-btn"><button type="button" class="btn btn-default" id="ID">ID Cliente</button></span>
											<input type="text" class="form-control" name="nombreEmpresa" id="nombreEmpresa" placeholder="Al seleccionar el cliente no se podrá cambiar el tipo de cotización" readonly >
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
										<div class="form-group">
											<label for="RFC">RFC</label>
											<input type="text" class="form-control" name="RFC" id="RFC" readonly>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
										<div class="form-group">
											<label for="estado">Estado</label>
											<input type="text" class="form-control" name="estado" id="estado" readonly>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
										<div class="form-group">
											<label for="municipio">Municipio</label>
											<input type="text" class="form-control" name="municipio" id="municipio" readonly>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
										<div class="form-group">
											<label for="colonia">Colonia</label>
											<input type="text" class="form-control" name="colonia" id="colonia" readonly>
										</div>
									</div>
									<div class="col-xs-3 col-sm-3 col-md-2 col-lg-2">
										<div class="form-group">
											<label for="CP">C P</label>
											<input type="text" class="form-control" name="CP" id="CP" readonly>
										</div>
									</div>
									<div class="col-xs-7 col-sm-7 col-md-8 col-lg-8">
										<div class="form-group">
											<label for="direccion">Dirección</label>
											<input type="text" class="form-control" name="direccion" id="direccion" readonly>
										</div>
									</div>
									<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
										<div class="form-group">
											<label for="tq">TQ</label>
											<select class="form-control" name="tq" id="tq"></select>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
										<div class="form-group">
											<label for="contacto">Contacto</label>
											<select class="form-control" name="contacto" id="contacto"></select>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-2">
										<div class="form-group">
											<label for="telefono">Teléfono</label>
											<input type="text" class="form-control" name="telefono" id="telefono" readonly>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
										<div class="form-group">
											<label for="correo">Correo</label>
											<input type="text" class="form-control" name="correo" id="correo" readonly>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-2">
										<div class="form-group">
											<label for="area">Área</label>
											<input type="text" class="form-control" name="area" id="area" readonly>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-2">
										<div class="checkbox disabled">
											<label>
												<input type="checkbox" value="1" name="nuevoTQ" id="nuevoTQ" disabled>
												Crear TQ al guardar
											</label>
										</div>
									</div>
									<div class="col-xs-6 col-sm-6 col-md-4 col-lg-2">
										<button type="button" class="btn btn-warning" id="btnActualizarCliente" data-tool="tooltip" data-placement="bottom" title="Actualizar los datos del cliente en esta cotización"><i class="fa fa-refresh"></i> Actualizar datos</button>
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
					<div class="x_panel">
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
					<div class="x_panel">
						<div class="x_title">
							<h2>Partidas</h2>
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
										<th colspan="5"><a id="descArmada"></a></th>
										<th class="text-right">SubTotal</th>
										<th></th>
										<th></th>
										<th id="stUsdPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="stUsdPrecioRAD" class="text-right"></th>
										<th id="stUsdPrecioRDD" class="text-right"></th>
										<th class="text-right"><font id="utilidadST"></font>%</th>
									</tr>
									<tr>
										<th colspan="5"><a id="gestorDeCuenta"></a></th>
										<th class="text-right">Descuento</th>
										<th></th>
										<th class="text-right"><label id="descuento">0</label></th>
										<th id="descuentoPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="descuentoPrecioRAD" class="text-right"></th>
										<th id="descuentoPrecioRDD" class="text-right"></th>
										<th></th>
									</tr>
									<tr>
										<th colspan="5" rowspan="2"><a id="observaciones"></a></th>
										<th class="text-right">SubTotal DD</th>
										<th></th>
										<th></th>
										<th id="stPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="stPrecioRAD" class="text-right"></th>
										<th id="stPrecioRDD" class="text-right"></th>
										<th class="text-right"><font id="utilidadSTDD"></font>%</th>
									</tr>
									<tr>
										<th class="text-right">Impuestos</th>
										<th></th>
										<th class="text-right"><label id="impuestos">16</label></th>
										<th id="ivaPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="ivaPrecioRAD" class="text-right"></th>
										<th id="ivaPrecioRDD" class="text-right"></th>
										<th></th>
									</tr>
									<tr>
										<th colspan="5" rowspan="2"><a id="terminosVenta"></a></th>
										<th class="text-right">Total</th>
										<th></th>
										<th></th>
										<th id="totalPrecioPDD" class="text-right"></th>
										<th></th>
										<th id="totalPrecioRAD" class="text-right"></th>
										<th id="totalPrecioRDD" class="text-right"></th>
										<th></th>
									</tr>
									<tr>
										<th colspan="8" id="faltantes"></th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>

			<!-- Panel para cargar las imagenes que se anexaran a la cotizacion -->
			<div class="row" id="rowCargaImg">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="x_panel">
						<div class="x_title">
							<h2>Cargar imágenes</i></h2>
							<ul class="nav navbar-right panel_toolbox">
								<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<form enctype="multipart/form-data" action="#" class="dropzone" id = 'imgArea'></form>
						</div>
					</div>
				</div>
			</div>

			<!-- Panel de la galeria de las imagenes de la cotizacion -->
			<div class="row" id="rowGaleria">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="x_panel">
						<div class="x_title">
							<h2>Imágenes en la cotización</i></h2>
							<ul class="nav navbar-right panel_toolbox">
								<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
								</li>
							</ul>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<div class="row" id="galeria"></div>
						</div>
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
								<input type="text" class="form-control text-right" name="inputPiezas" id="inputPiezas" value="1" required>
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

		<!-- Toolbar para la tabla de previsualizacion de la vista -->
		<div id="toolbar">
			<div class="row">
				<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
					<button type="button" class="btn btn-success btn-block btn-sm" data-tool="tooltip" data-placement="bottom" data-toggle="modal" data-target="#modalProducto" title="Agregar un producto especial"><i class="fa fa-plus-square"></i> <font class="hidden-xs">Agregar</font></button>
				</div>
				<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
					<button type="button" class="btn btn-warning btn-block btn-sm" data-tool="tooltip" data-placement="bottom" id="removerFila" title="Quitar el producto seleccionado de la cotización"><i class="fa fa-eraser"></i> <font class="hidden-xs">Quitar</font></button>
				</div>
				<div class="col-xs-4 col-sm-3 col-md-2 col-lg-3">
					<div class="form-group">
						<input type="text" class="form-control text-right btn-sm" name="tc" id="tc" data-tool="tooltip" data-placement="bottom" placeholder="Tipo de cambio">
					</div>
				</div>
				<div class="col-xs-4 col-sm-3 col-md-2 col-lg-3">
					<div class="form-group">
						<input type="text" class="form-control text-right" id="replica" value="1" placeholder="Replicas">
					</div>
				</div>
			</div>
		</div>

		<!-- Toolbar para la tabla de historico de cotizaciones -->
		<div id="toolbarCotizaciones">
			<div class="row">
				<div class="col-xs-6 col-sm-3 col-md-3 col-lg-3">
					<div class="form-group">
						<input type="text" class="form-control input-sm text-center simple-dp" name="inputfi" id="inputfi" readonly placeholder="Desde">
					</div>
				</div>
				<div class="col-xs-6 col-sm-3 col-md-3 col-lg-3">
					<div class="form-group">
						<input type="text" class="form-control input-sm text-center simple-dp" name="inputff" id="inputff" readonly placeholder="Hasta">
					</div>
				</div>
				<div class="col-xs-6 col-sm-3 col-md-2 col-lg-2">
					<div class="form-group">
						<select class="form-control input-sm" name="estatusCot" id="estatusCot">
							<option value="">Estatus...</option>
							<option value="A">Abiertas</option>
							<option value="B">Requiere Modificar Descuento</option>
							<option value="C">Requiere Autorizar Descuento</option>
							<option value="D">Descuento Autorizado</option>
							<option value="E">Requiere Modificar Utilidad</option>
							<option value="F">Requiere Autorizar Utilidad</option>
							<option value="G">Utilidad Autorizada</option>
							<option value="H">Cerradas</option>
							<option value="J">Rechazadas</option>
						</select>
					</div>
				</div>
				<div class="col-xs-3 col-sm-1 col-md-2 col-lg-1">
					<button type="button" class="btn btn-primary btn-sm btn-block" title="Buscar Cotizaciones acorde a los parametros proporcionados" data-tool="tooltip" data-placement="bottom" onclick="filtrarCotizaciones()"><i class="fa fa-filter"></i></button>
				</div>
				<div class="col-xs-3 col-sm-1 col-md-2 col-lg-1">
					<button type="button" class="btn btn-primary btn-sm btn-block" title="Fusionar cotizaciones e imprimir" data-tool="tooltip" data-placement="bottom" onclick="imprimirCotizaciones()"><i class="fa fa-file-pdf-o"></i></button>
				</div>
			</div>
		</div>

		<!-- Modal para dar de alta un cliente -->
		<div id="modalCliente" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Cliente</h4>
					</div>
					<div class="modal-body">
						<form method="POST" action="#" id="formCrudCliente" class="form-horizontal form-label-left input_mask">
							<div class="row">
								<div class="col-xs-12 col-sm-8">
									<label for="inEmpresa">Nombre</label>
									<div class="input-group">
										<span class="input-group-btn"><button type="button" class="btn btn-default" id="btnId">ID Cliente</button></span>
										<input type="text" class="form-control" name="inEmpresa" id="inEmpresa" placeholder="Nombre Empresa">
									</div>
								</div>
								<div class="col-xs-6 col-sm-4">
									<div class="form-group">
										<label for="inRFC">RFC</label>
										<input type="text" class="form-control" name="inRFC" id="inRFC">
									</div>
								</div>
								<div class="col-xs-6 col-sm-4">
									<div class="form-group">
										<label for="inEstatus">Estatus</label>
										<select name="inEstatus" id="inEstatus" class="form-control"></select>
									</div>
								</div>
								<div class="col-xs-6 col-sm-4">
									<div class="form-group">
										<label for="inEstado">Estado</label>
										<select name="inEstado" id="inEstado" class="form-control"></select>
									</div>
								</div>
								<div class="col-xs-6 col-sm-4">
									<div class="form-group">
										<label for="inMunicipio">Municipio</label>
										<select name="inMunicipio" id="inMunicipio" class="form-control"></select>
									</div>
								</div>
								<div class="col-xs-9 col-sm-4">
									<div class="form-group">
										<label for="inColonia">Colonia</label>
										<input type="text" class="form-control" name="inColonia" id="inColonia">
									</div>
								</div>
								<div class="col-xs-3 col-sm-4">
									<div class="form-group">
										<label for="inCP">Código Postal</label>
										<input type="text" class="form-control" name="inCP" id="inCP">
									</div>
								</div>
								<div class="col-xs-4 col-sm-4">
									<div class="form-group">
										<label for="inTelefono">Teléfono</label>
										<input type="text" class="form-control" name="inTelefono" id="inTelefono">
									</div>
								</div>
								<div class="col-xs-8 col-sm-4">
									<div class="form-group">
										<label for="inCorreo">Correo</label>
										<input type="text" class="form-control" name="inCorreo" id="inCorreo">
									</div>
								</div>
								<div class="col-xs-12 col-sm-8">
									<div class="form-group">
										<label for="inDirección">Dirección</label>
										<textarea class="form-control" name="inDireccion" id="inDireccion" rows="1"></textarea>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12 text-right">
									<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
									<button type="submit" class="btn btn-success" id="confirmarAltaCliente"><i class="fa fa-send"></i> Guardar</button>
								</div>
							</div>
						</form>
						<div class="row">
							<div class="col-xs-8 col-sm-4 col-md-4">
								<div class="form-group">
									<label for="innContacto">Contacto</label>
									<select class="form-control" name="innContacto" id="innContacto"></select>
								</div>
							</div>
							<div class="col-xs-4 col-sm-4 col-md-2">
								<div class="form-group">
									<label for="innTelefono">Teléfono</label>
									<input type="text" class="form-control" name="innTelefono" id="innTelefono" readonly>
								</div>
							</div>
							<div class="col-xs-8 col-sm-4 col-md-4">
								<div class="form-group">
									<label for="innCorreo">Correo</label>
									<input type="text" class="form-control" name="innCorreo" id="innCorreo" readonly>
								</div>
							</div>
							<div class="col-xs-4 col-sm-4 col-md-2">
								<div class="form-group">
									<label for="innArea">Área</label>
									<input type="text" class="form-control" name="innArea" id="innArea" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal para dar de alta un contacto -->
		<div id="modalContacto" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Alta Contacto</h4>
					</div>
					<form method="POST" action="#" id="formContacto">
						<div class="modal-body">
							<div class="row">
								<div class="col-xs-12">
									<div class="row">
										<div class="col-xs-6">
											<div class="form-group">
												<label for="icIdEmpresa">ID Empresa</label>
												<input type="text" class="form-control" name="icIdEmpresa" id="icIdEmpresa" readonly>
											</div>
										</div>
										<div class="col-xs-6">
											<div class="form-group">
												<label for="icIdContacto">ID Contacto</label>
												<input type="text" class="form-control" name="icIdContacto" id="icIdContacto" readonly>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="icEmpresa">Empresa</label>
										<input type="text" class="form-control" name="icEmpresa" id="icEmpresa">
									</div>
									<div class="form-group">
										<label for="icContacto">Contacto</label>
										<input type="text" class="form-control" name="icContacto" id="icContacto">
									</div>
									<div class="form-group">
										<label for="icTelefono">Teléfono</label>
										<input type="text" class="form-control" name="icTelefono" id="icTelefono">
									</div>
									<div class="form-group">
										<label for="icCorreo">Correo</label>
										<input type="text" class="form-control" name="icCorreo" id="icCorreo">
									</div>
									<div class="form-group">
										<label for="icTipoContacto">Tipo contacto</label>
										<select name="icTipoContacto" id="icTipoContacto" class="form-control"></select>
									</div>
									<div class="form-group">
										<label for="icArea">Área</label>
										<input type="text" name="icArea" id="icArea" class="form-control">
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
							<button type="submit" class="btn btn-success"><i class="fa fa-send"></i> Guardar</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Modal con los combos de los selects de las partidas armada -->
		<div id="modalCombos" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-body">
						<div class="row">
							<div class="col-xs-12 combo" id="divTubos">
								<div class="form-group">
									<label for="selectTubos">Tubos (Selecciona "Ninguno" si la categoría no aplica)</label>
									<select multiple title="Vacío" class="selectpicker form-control" data-size="10" name="selectTubos" id="selectTubos" data-group="TUB" ></select>
								</div>
							</div>
							<div class="col-xs-12 combo" id="divRieles">
								<div class="form-group">
									<label for="selectRieles">Rieles (Selecciona "Ninguno" si la categoría no aplica)</label>
									<select multiple title="Vacío" class="selectpicker form-control" data-size="10" name="selectRieles" id="selectRieles" data-group="RIE" ></select>
								</div>
							</div>
							<div class="col-xs-12 combo" id="divGuias">
								<div class="form-group">
									<label for="selectGuias">Guias (Selecciona "Ninguno" si la categoría no aplica)</label>
									<select multiple title="Vacío" class="selectpicker form-control" data-size="10" name="selectGuias" id="selectGuias" data-group="GUI" ></select>
								</div>
							</div>
							<div class="col-xs-12 combo" id="divSuperficies">
								<div class="form-group">
									<label for="selectSuperficies">Superficies (Selecciona "Ninguno" si la categoría no aplica)</label>
									<select multiple title="Vacío" class="selectpicker form-control" data-size="10" name="selectSuperficies" id="selectSuperficies" data-group="SUP" ></select>
								</div>
							</div>
							<div class="col-xs-12 combo" id="divTornilleria">
								<div class="form-group">
									<label for="selectTornilleria">Tornilleria (Selecciona "Ninguno" si la categoría no aplica)</label>
									<select multiple title="Vacío" class="selectpicker form-control" data-size="10" name="selectTornilleria" id="selectTornilleria" data-group="TOR" ></select>
								</div>
							</div>
							<div class="col-xs-12 combo" id="divOtros">
								<div class="form-group">
									<label for="selectOtros">Otros (Selecciona "Ninguno" si la categoría no aplica)</label>
									<select multiple title="Vacío" class="selectpicker form-control" data-size="10" name="selectOtros" id="selectOtros" data-group="OTR" ></select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 text-right">
								<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
								<button type="button" class="btn btn-success" id="selecionarCombo">Seleccionar</button>
							</div>
						</div>
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
						<strong id="msjAlert">Cargando datos, espera un momento por favor...</strong>
					</div>
				</div>
			</div>
		</div>
		<script src="<?php echo base_url('resources/jquery/dist/jquery.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap/dist/js/bootstrap.min.js')?>"></script>
		<script src="<?php echo base_url('resources/jquery-ui/jquery-ui.min.js')?>"></script>
		<script src="<?php echo base_url('resources/fastclick/lib/fastclick.js')?>"></script>
		<script src="<?php echo base_url('resources/moment/min/moment.min.js')?>"></script>
		<script src="<?php echo base_url('resources/moment/locale/es.js')?>"></script>
		<script src="<?php echo base_url('resources/datetimepicker/build/js/bootstrap-datetimepicker.min.js')?>"></script>
		<script src="<?php echo base_url('resources/dropzone/dist/min/dropzone.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/bootstrap-table.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/locale/bootstrap-table-es-MX.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/extensions/editable/bootstrap-table-editable.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-table/extensions/editable/bootstrap-editable.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap-select.min.js')?>"></script>
		<script src="<?php echo base_url('resources/jquery.cookie.js')?>"></script>
		<script src="<?php echo base_url('public/js/custom.js')?>"></script>
		<script>
			$(document).ready(function(){
				$.cookie('intValCambio', 0);
				$('input:text, input:checkbox, input:radio, textarea, select').on('change', function () {
					$.cookie('intValCambio', 1);
				});
			});
		</script>
		<script src="<?php echo base_url('public/js/cotizador.js')?>"></script>
		<script src="<?php echo base_url('public/js/clientes.js')?>"></script>
	</body>
</html>
