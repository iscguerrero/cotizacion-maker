<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Términos y Condiciones </title>
		<link rel='icon' type='image/jpeg' href="<?php echo base_url('public/images/trilogiq.jpeg')?>" />
		<link rel="stylesheet" href="<?php echo base_url('resources/bootstrap/dist/css/bootstrap.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/font-awesome/css/font-awesome.min.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/handsontable/handsontable.css')?>">
		<link rel="stylesheet" href="<?php echo base_url('resources/handsontable/pikaday/pikaday.css')?>">
	</head>
	<body style="padding-right: 0 !important">
		<div class="modal fade" tabindex="-1" role="dialog" id="modalAlerta">
			<div class="modal-dialog modal-sm" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Mensaje del Sistema</h4>
					</div>
					<div class="modal-body">
						<strong id="msjAlerta"></strong>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid" style="max-width: 1200px">
			<div class="row">
				<div class="col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<strong>Términos, condiciones y observaciones</strong>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-xs-12">
									<button type="button" class="btn btn-default" id="btnGuardar"><i class="fa fa-floppy-o"></i> Guardar cambios</button>
								</div>
							</div>
							<br>
							<div id="tCaptura"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="<?php echo base_url('resources/jquery/dist/jquery.min.js')?>"></script>
		<script src="<?php echo base_url('resources/bootstrap/dist/js/bootstrap.min.js')?>"></script>
		<script src="<?php echo base_url('resources/handsontable/pikaday/pikaday.js')?>"></script>
		<script src="<?php echo base_url('resources/handsontable/numbro/numbro.js')?>"></script>
		<script src="<?php echo base_url('resources/handsontable/handsontable.js')?>"></script>
		<script src="<?php echo base_url('/public/js/terminos.js')?>"></script>
	</body>
</html>
