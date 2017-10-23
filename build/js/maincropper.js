window.onload = function () {
	var Cropper = window.Cropper;
	var URL = window.URL || window.webkitURL;
	var container = document.querySelector('.img-container');
	var image = container.getElementsByTagName('img').item(0);
	var actions = document.getElementById('actions');
	var dataX = document.getElementById('dataX');
	var dataY = document.getElementById('dataY');
	var dataHeight = document.getElementById('dataHeight');
	var dataWidth = document.getElementById('dataWidth');
	var dataRotate = document.getElementById('dataRotate');
	var options = {
		aspectRatio: NaN,
		crop: function (e) {
			var data = e.detail;
			dataX.value = Math.round(data.x);
			dataY.value = Math.round(data.y);
			dataHeight.value = Math.round(data.height);
			dataWidth.value = Math.round(data.width);
			dataRotate.value = typeof data.rotate !== 'undefined' ? data.rotate : '';
		}
	};
	var cropper = new Cropper(image, options);
	var uploadedImageType = 'image/jpeg';
	var uploadedImageURL;
	// Metodos del area de trabajo
	actions.querySelector('.docs-buttons').onclick = function (event) {
		var e = event || window.event;
		var target = e.target || e.srcElement;
		var result;
		var input;
		var data;
		if (!cropper) {
			return;
		}
		while (target !== this) {
			if (target.getAttribute('data-method')) {
				break;
			}
			target = target.parentNode;
		}
		if (target === this || target.disabled || target.className.indexOf('disabled') > -1) {
			return;
		}
		data = {
			method: target.getAttribute('data-method'),
			target: target.getAttribute('data-target'),
			option: target.getAttribute('data-option'),
			secondOption: target.getAttribute('data-second-option')
		};
		if (data.method) {
			if (typeof data.target !== 'undefined') {
				input = document.querySelector(data.target);
				if (!target.hasAttribute('data-option') && data.target && input) {
					try {
						data.option = JSON.parse(input.value);
					} catch (e) {
						console.log(e.message);
					}
				}
			}
			switch (data.method) {
				case 'rotate':
					cropper.clear();
					break;
			}
			result = cropper[data.method](data.option, data.secondOption);
			switch (data.method) {
				case 'rotate':
					cropper.crop();
					break;
			}
			if (typeof result === 'object' && result !== cropper && input) {
				try {
					input.value = JSON.stringify(result);
				} catch (e) {
					console.log(e.message);
				}
			}
		}
	};

	// Abrir el modal con el resultado del recorte
	/*$('#btnGuardar').click(function(e){
		e.preventDefault();
		newCropper = cropper.getCroppedCanvas({
			width: 160,
			height: 90,
			fillColor: '#fff',
			imageSmoothingEnabled: false,
			imageSmoothingQuality: 'high',
		});
		$('#getCroppedCanvasModal').modal().find('.modal-body').html(newCropper);
	});*/

	// Importar imagen al area de trabajo
	var inputImage = document.getElementById('inputImage');
	if (URL) {
		inputImage.onchange = function () {
			var files = this.files;
			var file;
			if (cropper && files && files.length) {
				file = files[0];
				if (/^image\/\w+/.test(file.type)) {
					uploadedImageType = file.type;
					if (uploadedImageURL) {
						URL.revokeObjectURL(uploadedImageURL);
					}
					image.src = uploadedImageURL = URL.createObjectURL(file);
					cropper.destroy();
					cropper = new Cropper(image, options);
					inputImage.value = null;
				} else {
					window.alert('Por favor selecciona un archivo de imagen valido');
				}
			}
		};
	} else {
		inputImage.disabled = true;
		inputImage.parentNode.className += ' disabled';
	}
};