qx.Class.define("vehiculos.comp.windowResponsable",
{
	extend : componente.comp.ui.ramon.window.Window,
	construct : function ()
	{
	this.base(arguments);
	
	this.set({
		caption: "Nuevo responsable",
		width: 440,
		height: 350,
		showMinimize: false,
		showMaximize: false,
		allowMaximize: false,
		resizable: false
	});
		
	this.setLayout(new qx.ui.layout.Canvas());

	this.addListenerOnce("appear", function(e){
		lstBuscar.fireDataEvent("changeSelection", []);
		cboBuscar.focus();
	}, this);
	
	
	var application = qx.core.Init.getApplication();
	var aux;
	
	
	var cboBuscar = new componente.comp.ui.ramon.combobox.ComboBoxAuto({url: "services/", serviceName: "comp.Responsable", methodName: "autocompletarResponsableCompleto"});
	cboBuscar.setWidth(250);
	var lstBuscar = cboBuscar.getChildControl("list");
	lstBuscar.addListener("changeSelection", function(e){
		var datos, modelForm;
		
		txtDni.setValid(true);
		txtApenom.setValid(true);
		
		if (lstBuscar.isSelectionEmpty()) {
			this.setCaption("Nuevo responsable");
			
			datos = {id_responsable: "0", apenom: "", dni: "", domicilio: "", localidad: "", telefono: "", cargo: "", organizacion: ""};

		} else {
			this.setCaption("Modificar responsable");
			datos = lstBuscar.getSelection()[0].getUserData("datos");
			
			datos = datos.responsable;
		}
		
		modelForm = qx.data.marshal.Json.createModel(datos, true);
		controllerForm.setModel(modelForm);
	}, this);
	
	this.add(new qx.ui.basic.Label("Buscar:"), {left: 0, top: 3});
	this.add(cboBuscar, {left: 84, top: 0});
	cboBuscar.setTabIndex(1);
	
	var lblLinea = new qx.ui.basic.Label("<hr>");
	lblLinea.setRich(true);
	lblLinea.setWidth(500);
	this.add(lblLinea, {left: 0, top: 22, right: 0});
	
	
	
	
	
	
	var form = new qx.ui.form.Form();
	
	//form.addGroupHeader("Chofer", {item: {row: 0, column: 0, colSpan: 5}});
	
	var txtDni = new qx.ui.form.TextField("");
	txtDni.setRequired(true);
	txtDni.addListener("blur", function(e){
		var value = this.getValue().trim();
		
		if (value != "") {
			if (isNaN(value) || parseInt(value) == 0) value = "";
		}
		
		this.setValue(value);
	});
	form.add(txtDni, "DNI", null, "dni", null, {tabIndex: 3, item: {row: 1, column: 1, colSpan: 3}});
	
	
	var txtApenom = new qx.ui.form.TextField();
	txtApenom.setRequired(true);
	txtApenom.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	form.add(txtApenom, "Ape.y Nom.", null, "apenom", null, {item: {row: 2, column: 1, colSpan: 13}});
	
	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	form.add(aux, "Domicilio", null, "domicilio", null, {item: {row: 3, column: 1, colSpan: 13}});
	
	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	form.add(aux, "Localidad", null, "localidad", null, {item: {row: 4, column: 1, colSpan: 13}});
	
	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	form.add(aux, "Teléfono", null, "telefono", null, {item: {row: 5, column: 1, colSpan: 6}});
	
	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	form.add(aux, "Cargo", null, "cargo", null, {item: {row: 6, column: 1, colSpan: 13}});
	
	
	aux = new qx.ui.form.TextField();
	aux.addListener("blur", function(e){
		var value = this.getValue();
		this.setValue((value == null) ? "" : value.trim());
	});
	form.add(aux, "Organización", null, "organizacion", null, {item: {row: 7, column: 1, colSpan: 13}});
	
	


	

	var controllerForm = new qx.data.controller.Form(null, form);
	//modelForm = controllerForm.createModel(true);
	
	var formView = new componente.comp.ui.ramon.abstractrenderer.Grid(form, 30, 30);
	//var formView = new qx.ui.form.renderer.Single(form);
	this.add(formView, {left: 0, top: 45});
	
	
	
	var validationManager = form.getValidationManager();
	validationManager.setValidator(new qx.ui.form.validation.AsyncValidator(
		function(items, validator) {
			if (validationManager.getInvalidFormItems().length == 0){
				var p = {};
				p.model = qx.util.Serializer.toNativeObject(controllerForm.getModel());
	
				var rpc = new vehiculos.comp.rpc.Rpc("services/", "comp.Responsable");
				rpc.addListener("completed", function(e){
					var data = e.getData();
	
					validator.setValid(true);
				}, this);
				rpc.addListener("failed", function(e){
					var data = e.getData();
					
					if (data.message == "personal") {
						txtDni.setInvalidMessage("DNI ingresado no es parte de personal");
						txtDni.setValid(false);
					} else if (data.message == "dni") {
						txtDni.setInvalidMessage("DNI duplicado");
						txtDni.setValid(false);
					} else if (data.message == "apenom") {
						txtApenom.setInvalidMessage("Ape.y Nom duplicado");
						txtApenom.setValid(false);
					}
					
					validator.setValid(false);
				}, this);
				rpc.callAsyncListeners(true, "alta_modifica_responsable", p);
				
			} else validator.setValid(false);
		}
	));
	
	validationManager.addListener("complete", qx.lang.Function.bind(function(e){
		if (validationManager.getValid()) {
			var p = {};
			p.model = qx.util.Serializer.toNativeObject(controllerForm.getModel());
			
			application.popupGrabado.placeToWidget(this, true);
			application.popupGrabado.show();
			
			if (p.model.id_responsable == "0") {
				lstBuscar.fireDataEvent("changeSelection", []);
				txtDni.focus();
			} else {
				cboBuscar.setValue("");
				lstBuscar.removeAll();
				cboBuscar.focus();
			}
		} else {
			validationManager.getInvalidFormItems()[0].focus();
		}
	}, this));
	
	
	
	var btnAceptar = new qx.ui.form.Button("Grabar");
	btnAceptar.addListener("execute", function(e){
		form.validate();
	});
	
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