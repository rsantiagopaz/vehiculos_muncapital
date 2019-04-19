qx.Class.define("vehiculos.comp.windowVehiculo",
{
	extend : componente.comp.ui.ramon.window.Window,
	construct : function ()
	{
	this.base(arguments);
	
	this.set({
		caption: "Nuevo vehículo",
		//width: 460,
		width: 800,
		height: 550,
		showMinimize: false,
		showMaximize: false,
		allowMaximize: false,
		resizable: false
	});
		
	this.setLayout(new qx.ui.layout.Canvas());

	this.addListenerOnce("appear", function(e){
		lstVehiculo.fireDataEvent("changeSelection", []);
		cboVehiculo.focus();
		
		
		var fineUploaderOptionsComodato = {
		    // options
			button: lblComodato.getContentElement().getDomElement(),
			autoUpload: true,
			multiple: false,
			request: {
				endpoint: 'services/php-traditional-server-master/endpoint.php'
			},
			validation: {
				allowedExtensions: ['jpeg', 'jpg', 'gif', 'png'],
				//acceptFiles: "image/png, image/jpeg",
				acceptFiles: ".jpeg, .jpg, .gif, .png"
            },
		    callbacks: {
		        onSubmit: function(id, name) {
		        	//application.popupCargando.mostrarModal();
		        	imgComodato.setSource("./services/documentos/loading66.gif" + "?" + Math.random());
		        },
		        
		        onError: function(id, name, errorReason, xhr) {
		        	//alert(qx.lang.Json.stringify({id: id, name: name, errorReason: errorReason, xhr: xhr}, null, 2));
					dialog.Dialog.error(errorReason);
		        },
		        
		        onComplete: qx.lang.Function.bind(function(id, name, responseJSON, xhr) {
		        	//application.popupCargando.ocultarModal();
		        	
		        	if (responseJSON.success) {
		        		var p = {};
		        		p.uuid = responseJSON.uuid;
		        		p.uploadName = responseJSON.uploadName;
		        		
		        		//alert(qx.lang.Json.stringify(p, null, 2));
		        		
						var rpc = new qx.io.remote.Rpc("services/", "comp.Vehiculo");
						rpc.callAsync(qx.lang.Function.bind(function(resultado, error, id){
							//application.popupCargando.ocultarModal();
							
							//alert(qx.lang.Json.stringify(resultado, null, 2));
							//alert(qx.lang.Json.stringify(error, null, 2));
							
							imgComodato.setSource("./services/documentos/comodato_0.jpg" + "?" + Math.random());
						}, this), "agregar_foto_comodato", p);
		        	} else {
		        		//application.popupCargando.ocultarModal();
		        	}
		        }, this)
		    }
		};
		
		fineUploaderComodato = new qq.FineUploaderBasic(fineUploaderOptionsComodato);
		
		
		var fineUploaderOptionsVehiculo = {
		    // options
			button: lblVehiculo.getContentElement().getDomElement(),
			autoUpload: true,
			multiple: false,
			request: {
				endpoint: 'services/php-traditional-server-master/endpoint.php'
			},
			validation: {
				allowedExtensions: ['jpeg', 'jpg', 'gif', 'png'],
				//acceptFiles: "image/png, image/jpeg",
				acceptFiles: ".jpeg, .jpg, .gif, .png"
            },
		    callbacks: {
		        onSubmit: function(id, name) {
		        	//application.popupCargando.mostrarModal();
		        	imgVehiculo.setSource("./services/documentos/loading66.gif" + "?" + Math.random());
		        },
		        
		        onError: function(id, name, errorReason, xhr) {
		        	//alert(qx.lang.Json.stringify({id: id, name: name, errorReason: errorReason, xhr: xhr}, null, 2));
		        	dialog.Dialog.error(errorReason);
		        },
		        
		        onComplete: qx.lang.Function.bind(function(id, name, responseJSON, xhr) {
		        	//application.popupCargando.ocultarModal();
		        	
		        	if (responseJSON.success) {
		        		var p = {};
		        		p.uuid = responseJSON.uuid;
		        		p.uploadName = responseJSON.uploadName;
		        		
		        		//alert(qx.lang.Json.stringify(p, null, 2));
		        		
						var rpc = new qx.io.remote.Rpc("services/", "comp.Vehiculo");
						rpc.callAsync(qx.lang.Function.bind(function(resultado, error, id){
							//application.popupCargando.ocultarModal();
							
							//alert(qx.lang.Json.stringify(resultado, null, 2));
							//alert(qx.lang.Json.stringify(error, null, 2));
							
							imgVehiculo.setSource("./services/documentos/vehiculo_0.jpg" + "?" + Math.random());
						}, this), "agregar_foto_vehiculo", p);
		        	} else {
		        		//application.popupCargando.ocultarModal();
		        	}
		        }, this)
		    }
		};
		
		fineUploaderVehiculo = new qq.FineUploaderBasic(fineUploaderOptionsVehiculo);
		
		this.add(imgComodato, {right: 0, top: 40});
		this.add(imgVehiculo, {right: 0, top: 220});
	}, this);
	
	
	var application = qx.core.Init.getApplication();
	
	
	var cboVehiculo = new componente.comp.ui.ramon.combobox.ComboBoxAuto({url: "services/", serviceName: "comp.Vehiculo", methodName: "autocompletarVehiculoCompleto"});
	cboVehiculo.setWidth(330);
	var lstVehiculo = cboVehiculo.getChildControl("list");
	lstVehiculo.addListener("changeSelection", function(e){
		var datos, modelForm;
		
		txtNro_patente.setValid(true);

		if (lstVehiculo.isSelectionEmpty()) {
			this.setCaption("Nuevo vehículo");
			
			datos = {id_vehiculo: "0", nro_patente: "", marca: "", id_tipo_vehiculo: null, modelo: "", nro_motor: "", nro_chasis: "", observa: "", nro_poliza: "", localidad_id: null, id_dependencia: null, id_depositario: null, id_responsable: null, cboLocalidad: "", cboDependencia: "", cboDepositario: "", cboResponsable: ""};
			
			cboDependencia.removeAll();
			cboDependencia.setValue("");
			
			cboResponsable.removeAll();
			cboResponsable.setValue("");
			
			
			
			var p = {id_vehiculo: datos.id_vehiculo};
			
			var rpc = new vehiculos.comp.rpc.Rpc("services/", "comp.Vehiculo");
			rpc.addListener("completed", function(e){
				var data = e.getData();
				
				imgComodato.setSource("./services/documentos/comodato_0.jpg" + "?" + Math.random());
				imgVehiculo.setSource("./services/documentos/vehiculo_0.jpg" + "?" + Math.random());
			}, this);
			
			rpc.callAsyncListeners(true, "preparar_foto", p);
			
		}
		
		modelForm = qx.data.marshal.Json.createModel(datos, true);
		controllerFormInfoVehiculo.setModel(modelForm);
	}, this);
	var popupVehiculo = cboVehiculo.getChildControl("popup");
	popupVehiculo.addListener("disappear", function(e){
		var datos, modelForm;
		
		txtNro_patente.setValid(true);

		if (! lstVehiculo.isSelectionEmpty()) {
			this.setCaption("Modificar vehículo");
			datos = lstVehiculo.getSelection()[0].getUserData("datos");
			datos.vehiculo.cboLocalidad = "";
			datos.vehiculo.cboDependencia = "";
			datos.vehiculo.cboDepositario = "";
			datos.vehiculo.cboResponsable = "";
			
			if (datos.cboLocalidad == null) {
				cboLocalidad.removeAll();
				cboLocalidad.setValue("");
			} else {
				cboLocalidad.add(new qx.ui.form.ListItem(datos.cboLocalidad.label, null, datos.cboLocalidad.model));
			}
			
			if (datos.cboDependencia == null) {
				cboDependencia.removeAll();
				cboDependencia.setValue("");
			} else {
				cboDependencia.add(new qx.ui.form.ListItem(datos.cboDependencia.label, null, datos.cboDependencia.model));
			}
			
			if (datos.cboDepositario == null) {
				cboDepositario.removeAll();
				cboDepositario.setValue("");
			} else {
				cboDepositario.add(new qx.ui.form.ListItem(datos.cboDepositario.label, null, datos.cboDepositario.model));
			}
			
			if (datos.cboResponsable == null) {
				cboResponsable.removeAll();
				cboResponsable.setValue("");
			} else {
				cboResponsable.add(new qx.ui.form.ListItem(datos.cboResponsable.label, null, datos.cboResponsable.model));
			}
			
			datos = datos.vehiculo;
			
			
			
			var p = {id_vehiculo: datos.id_vehiculo};
			
			var rpc = new vehiculos.comp.rpc.Rpc("services/", "comp.Vehiculo");
			rpc.addListener("completed", function(e){
				var data = e.getData();
				
				imgComodato.setSource("./services/documentos/comodato_" + datos.id_vehiculo + ".jpg" + "?" + Math.random());
				imgVehiculo.setSource("./services/documentos/vehiculo_" + datos.id_vehiculo + ".jpg" + "?" + Math.random());
			}, this);
			
			rpc.callAsyncListeners(true, "preparar_foto", p);
		}
		
		modelForm = qx.data.marshal.Json.createModel(datos, true);
		controllerFormInfoVehiculo.setModel(modelForm);
	}, this);
	
	this.add(new qx.ui.basic.Label("Buscar:"), {left: 55, top: 3});
	this.add(cboVehiculo, {left: 100, top: 0});
	cboVehiculo.setTabIndex(1);
	
	var lblLinea = new qx.ui.basic.Label("<hr>");
	lblLinea.setRich(true);
	lblLinea.setWidth(1000);
	this.add(lblLinea, {left: 0, top: 22, right: 0});
	
	
	
	
	
	
	var formInfoVehiculo = new qx.ui.form.Form();
	
	var txtNro_patente = new qx.ui.form.TextField();
	txtNro_patente.setRequired(true);
	txtNro_patente.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim().toUpperCase());
	});
	formInfoVehiculo.add(txtNro_patente, "Nro.patente", null, "nro_patente", null, {tabIndex: 3, item: {row: 1, column: 1, colSpan: 4}});
	
	
	aux = new qx.ui.form.TextField();
	aux.setRequired(true);
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	formInfoVehiculo.add(aux, "Nro.chasis", null, "nro_chasis", null, {item: {row: 2, column: 1, colSpan: 6}});
	
	
	var aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	formInfoVehiculo.add(aux, "Marca", null, "marca", null, {item: {row: 3, column: 1, colSpan: 8}});
	

	aux = new qx.ui.form.SelectBox();
	//aux.setRequired(true);
	var rpc = new vehiculos.comp.rpc.Rpc("services/", "comp.Vehiculo");
	try {
		var resultado = rpc.callSync("autocompletarTipo_vehiculo", {texto: ""});
	} catch (ex) {
		alert("Sync exception: " + ex);
	}
	for (var x in resultado) {
		aux.add(new qx.ui.form.ListItem(resultado[x].label, null, resultado[x].model));
	}
	
	formInfoVehiculo.add(aux, "Tipo", null, "id_tipo_vehiculo", null, {item: {row: 4, column: 1, colSpan: 8}});

	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	formInfoVehiculo.add(aux, "Modelo (año)", null, "modelo", null, {item: {row: 5, column: 1, colSpan: 2}});
	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	formInfoVehiculo.add(aux, "Nro.motor", null, "nro_motor", null, {item: {row: 6, column: 1, colSpan: 6}});
	
	
	aux = new qx.ui.form.TextArea();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	formInfoVehiculo.add(aux, "Observaciones", null, "observa", null, {item: {row: 7, column: 1, colSpan: 8}});
	
	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	formInfoVehiculo.add(aux, "Nro.seguro/póliza", null, "nro_poliza", null, {item: {row: 8, column: 1, colSpan: 6}});
	
	
	var cboLocalidad = new componente.comp.ui.ramon.combobox.ComboBoxAuto({url: "services/", serviceName: "comp.Parametros", methodName: "autocompletarLocalidad"});
	cboLocalidad.setRequired(true);
	formInfoVehiculo.add(cboLocalidad, "Localidad", function(value) {
		if (lstLocalidad.isSelectionEmpty()) throw new qx.core.ValidationError("Validation Error", "Debe seleccionar localidad");
	}, "cboLocalidad", null, {item: {row: 9, column: 1, colSpan: 13}});
	var lstLocalidad = cboLocalidad.getChildControl("list");
	formInfoVehiculo.add(lstLocalidad, "", null, "localidad_id");
	
	
	var cboDependencia = new componente.comp.ui.ramon.combobox.ComboBoxAuto({url: "services/", serviceName: "comp.Vehiculo", methodName: "autocompletarDependencia"});
	cboDependencia.setRequired(true);
	formInfoVehiculo.add(cboDependencia, "Dependencia", function(value) {
		if (lstDependencia.isSelectionEmpty()) throw new qx.core.ValidationError("Validation Error", "Debe seleccionar dependencia");
	}, "cboDependencia", null, {item: {row: 10, column: 1, colSpan: 13}});
	var lstDependencia = cboDependencia.getChildControl("list");
	formInfoVehiculo.add(lstDependencia, "", null, "id_dependencia");
	
	
	var cboDepositario = new componente.comp.ui.ramon.combobox.ComboBoxAuto({url: "services/", serviceName: "comp.Vehiculo", methodName: "autocompletarDepositario"});
	cboDepositario.setRequired(true);
	formInfoVehiculo.add(cboDepositario, "Depositario", function(value) {
		if (lstDepositario.isSelectionEmpty()) throw new qx.core.ValidationError("Validation Error", "Debe seleccionar depositario");
	}, "cboDepositario", null, {item: {row: 11, column: 1, colSpan: 13}});
	var lstDepositario = cboDepositario.getChildControl("list");
	formInfoVehiculo.add(lstDepositario, "", null, "id_depositario");
	
	

	var cboResponsable = new componente.comp.ui.ramon.combobox.ComboBoxAuto({url: "services/", serviceName: "comp.Responsable", methodName: "autocompletarResponsable"});
	cboResponsable.setRequired(true);
	formInfoVehiculo.add(cboResponsable, "Responsable", function(value) {
		if (lstResponsable.isSelectionEmpty()) throw new qx.core.ValidationError("Validation Error", "Debe seleccionar responsable");
	}, "cboResponsable", null, {item: {row: 12, column: 1, colSpan: 13}});
	var lstResponsable = cboResponsable.getChildControl("list");
	formInfoVehiculo.add(lstResponsable, "", null, "id_responsable");

	

	var controllerFormInfoVehiculo = new qx.data.controller.Form(null, formInfoVehiculo);
	//modelForm = controllerFormInfoVehiculo.createModel(true);
	
	var formViewVehiculo = new componente.comp.ui.ramon.abstractrenderer.Grid(formInfoVehiculo, 20, 20);
	//var formViewVehiculo = new qx.ui.form.renderer.Single(formInfoVehiculo);
	this.add(formViewVehiculo, {left: 0, top: 45});
	
	

	
	
	var lblComodato = new qx.ui.basic.Label("Foto comodato...");
	lblComodato.setPadding(5, 5, 5, 5);
	lblComodato.setDecorator("main");
	this.add(lblComodato, {right: 200, top: 40});
	
	var imgComodato = new qx.ui.basic.Image();
	imgComodato.setWidth(180);
	imgComodato.setHeight(160);
	imgComodato.setBackgroundColor("#FFFFFF");
	imgComodato.setDecorator("main");
	imgComodato.setScale(true);
	imgComodato.addListener("loaded", function(e){
		imgComodato.abrir = true;
	});
	imgComodato.addListener("loadingFailed", function(e){
		imgComodato.abrir = false;
	});
	imgComodato.addListener("tap", function(e){
		if (imgComodato.abrir) window.open(imgComodato.getSource());
	});
	
	this.add(imgComodato, {right: 10, top: 30});
	
	var fineUploaderComodato;
	
	

	
	var lblVehiculo = new qx.ui.basic.Label("Foto vehículo...");
	lblVehiculo.setPadding(5, 5, 5, 5);
	lblVehiculo.setDecorator("main");
	this.add(lblVehiculo, {right: 200, top: 195});
	
	var imgVehiculo = new qx.ui.basic.Image();
	imgVehiculo.setWidth(180);
	imgVehiculo.setHeight(160);
	imgVehiculo.setBackgroundColor("#FFFFFF");
	imgVehiculo.setDecorator("main");
	imgVehiculo.setScale(true);
	imgVehiculo.addListener("loaded", function(e){
		imgVehiculo.abrir = true;
	});
	imgVehiculo.addListener("loadingFailed", function(e){
		imgVehiculo.abrir = false;
	});
	imgVehiculo.addListener("tap", function(e){
		if (imgVehiculo.abrir) window.open(imgVehiculo.getSource());
	});
	
	this.add(imgVehiculo, {right: 10, top: 50});
	
	var fineUploaderVehiculo;

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	var btnAceptar = new qx.ui.form.Button("Grabar");
	btnAceptar.addListener("execute", function(e){
		if (formInfoVehiculo.validate()) {
			var p = {};
			p.model = qx.util.Serializer.toNativeObject(controllerFormInfoVehiculo.getModel());

			var rpc = new vehiculos.comp.rpc.Rpc("services/", "comp.Vehiculo");
			
			rpc.addListener("completed", function(e){
				var data = e.getData();
				
				application.popupGrabado.placeToWidget(this, true);
				application.popupGrabado.show();

				if (p.model.id_vehiculo == "0") {
					lstVehiculo.fireDataEvent("changeSelection", []);
					txtNro_patente.focus();
				} else {
					cboVehiculo.setValue("");
					lstVehiculo.removeAll();
					cboVehiculo.focus();
				}
			}, this);
			
			rpc.addListener("failed", function(e){
				var data = e.getData();
				
				if (data.message == "duplicado") {
					txtNro_patente.setInvalidMessage("Nro.patente duplicado");
					txtNro_patente.setValid(false);
					txtNro_patente.focus();
					
					var manager = qx.ui.tooltip.Manager.getInstance();
					manager.showToolTip(txtNro_patente);
				}
			}, this);
			
			rpc.callAsyncListeners(true, "alta_modifica_vehiculo", p);
			
			
			
			
			
		} else {
			formInfoVehiculo.getValidationManager().getInvalidFormItems()[0].focus();
		}
	}, this);
	
	var btnCancelar = new qx.ui.form.Button("Cerrar");
	btnCancelar.addListener("execute", function(e){
		this.destroy();
	}, this);
	
	this.add(btnAceptar, {left: "25%", bottom: 0});
	this.add(btnCancelar, {right: "25%", bottom: 0});
	
	btnAceptar.setTabIndex(20);
	btnCancelar.setTabIndex(21);
	
	
	
	
	
	},
	members : 
	{

	},
	events : 
	{
		"aceptado": "qx.event.type.Event"
	}
});