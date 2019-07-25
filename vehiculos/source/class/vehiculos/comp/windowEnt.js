qx.Class.define("vehiculos.comp.windowEnt",
{
	extend : componente.comp.ui.ramon.window.Window,
	construct : function (vehiculo)
	{
	this.base(arguments);
	
	this.set({
		caption: "Entrada",
		width: 500,
		height: 370,
		showMinimize: false,
		showMaximize: false,
		allowMaximize: false,
		resizable: false
	});
		
	this.setLayout(new qx.ui.layout.Canvas());

	this.addListenerOnce("appear", function(e){
		var timer = qx.util.TimerManager.getInstance();
		timer.start(function() {
			this.setCaption("Entrada, " + vehiculo.nro_patente + "  " + vehiculo.marca);
			txtKilo.focus();
		}, null, this, null, 50);
	}, this);
	
	
	var application = qx.core.Init.getApplication();
	
	var form = new qx.ui.form.Form();
	

	
	var txtKilo = new componente.comp.ui.ramon.spinner.Spinner(0, 0, 10000000);
	//txtKilo.setRequired(true);
	//txtKilo.setWidth(80);
	txtKilo.setNumberFormat(application.numberformatEntero);
	txtKilo.getChildControl("upbutton").setVisibility("excluded");
	txtKilo.getChildControl("downbutton").setVisibility("excluded");
	txtKilo.setSingleStep(0);
	txtKilo.setPageStep(0);
	form.add(txtKilo, "Kilometraje", null, "kilo", null, {grupo: 1, item: {row: 1, column: 1, colSpan: 4}});
	
	
	var txtAsunto_cargo = new qx.ui.form.TextField("");
	//txtAsunto_cargo.setWidth(500);
	txtAsunto_cargo.asunto = false;
	txtAsunto_cargo.addListener("blur", function(e){
		this.setValue(this.getValue().trim());
		
		var p = {};
		p.documentacion_id = txtAsunto_cargo.getValue();
		
		var rpc = new vehiculos.comp.rpc.Rpc("services/", "comp.Vehiculo");
		rpc.mostrar = false;
		rpc.addListener("completed", function(e){
			var data = e.getData();

			var aux = "";
			
			aux = "Documento: " + data.result.documento;
			aux+= String.fromCharCode(13) + "Iniciador: " + data.result.documentacion_tmp_iniciador;
			aux+= String.fromCharCode(13) + "Texto: " + data.result.documentacion_asunto;
			
			txtAsunto_descrip.setValue(aux);
			
			txtAsunto_cargo.asunto = true;

		}, this);
		rpc.addListener("failed", function(e){
			var data = e.getData();
			
			txtAsunto_descrip.setValue("");
			
			txtAsunto_cargo.asunto = false;

		}, this);
		rpc.callAsyncListeners(true, "leer_asunto", p);
	});
	form.add(txtAsunto_cargo, "Asunto", function(value) {
		if (! txtAsunto_cargo.asunto) throw new qx.core.ValidationError("Validation Error", "Asunto inválido");
	}, "documentacion_id", null, {grupo: 1, item: {row: 2, column: 1, colSpan: 5}});
	
	
	var txtAsunto_descrip = new qx.ui.form.TextArea("");
	//txtAsunto_descrip.setRich(true);
	txtAsunto_descrip.setReadOnly(true);
	txtAsunto_descrip.setDecorator("main");
	txtAsunto_descrip.setBackgroundColor("#ffffc0");
	//this.add(txtAsunto_descrip, {left: 240, top: 0, right: 0});
	form.add(txtAsunto_descrip, "", null, "asunto_descrip", null, {grupo: 1, item: {row: 3, column: 1, colSpan: 11}});
	
	
	var txtResp_ent = new qx.ui.form.TextField("");
	txtResp_ent.setEnabled(false);
	//form.add(txtResp_ent, "Responsable", null, "resp_ent", null, {grupo: 1, item: {row: 2, column: 1, colSpan: 11}});
	
	var txtObserva_ent = new qx.ui.form.TextArea("");
	txtObserva_ent.setRequired(true);
	txtObserva_ent.addListener("blur", function(e){
		this.setValue(this.getValue().trim());
	});
	form.add(txtObserva_ent, "Observaciones", null, "observa_ent", null, {grupo: 1, item: {row: 4, column: 1, colSpan: 11, rowSpan: 17}});
	
	var cboUnipresu = new componente.comp.ui.ramon.combobox.ComboBoxAuto({url: "services/", serviceName: "comp.Vehiculo", methodName: "autocompletarUnipresu"});
	cboUnipresu.setEnabled(false);
	var lstUnipresu = cboUnipresu.getChildControl("list");
	lstUnipresu.addListener("changeSelection", function(e){
		/*
		if (lstUnipresu.isSelectionEmpty()) {
			this.setLabel('Particular');
			functionActualizarVehiculo();
		} else {
			this.setLabel(lstUnipresu.getSelection()[0].getLabel());
			functionActualizarVehiculo(lstUnipresu.getModelSelection().getItem(0));
		}
		*/
	}, this);
	//form.add(cboUnipresu, "Unidad presup.", null, "cod_up", null, {grupo: 1, item: {row: 20, column: 1, colSpan: 11}});

	

	
	var controllerForm = new qx.data.controller.Form(null, form);
	
	var formView = new componente.comp.ui.ramon.abstractrenderer.Grid(form, 20, 20, 1);
	//var formView = new qx.ui.form.renderer.Single(form);
	this.add(formView, {left: 0, top: 15});
	
	
	

	
	
	
	
	var btnAceptar = new qx.ui.form.Button("Aceptar");
	btnAceptar.addListener("execute", function(e){
		if (form.validate()) {
			var p = {};
			p.id_vehiculo = vehiculo.id_vehiculo;
			p.resp_ent = txtResp_ent.getValue();
			p.kilo = txtKilo.getValue();
			p.observa = txtObserva_ent.getValue();
			p.documentacion_id = txtAsunto_cargo.getValue();
			p.cod_up = ((lstUnipresu.isSelectionEmpty()) ? 0 : lstUnipresu.getSelection()[0].getModel());
			p.vehiculo_estado = vehiculo.estado;
			
			var rpc = new vehiculos.comp.rpc.Rpc("services/", "comp.Vehiculo");
			rpc.addListener("completed", function(e){
				var data = e.getData();
				
				btnCancelar.execute();
				
				this.fireDataEvent("aceptado", data.result);
			}, this);
			rpc.addListener("failed", function(e){
				btnCancelar.execute();
				
				this.fireDataEvent("estado");
			}, this);
			rpc.callAsyncListeners(true, "entrada_vehiculo", p);
			
		} else {
			form.getValidationManager().getInvalidFormItems()[0].focus();
		}
	}, this);
	
	var btnCancelar = new qx.ui.form.Button("Cancelar");
	btnCancelar.addListener("execute", function(e){
		this.close();
		
		this.destroy();
	}, this);
	
	this.add(btnAceptar, {left: "20%", bottom: 0});
	this.add(btnCancelar, {right: "20%", bottom: 0});
	
	btnAceptar.setTabIndex(6);
	btnCancelar.setTabIndex(7);
	
	},

	events : 
	{
		"aceptado": "qx.event.type.Event",
		"estado": "qx.event.type.Event"
	}
});