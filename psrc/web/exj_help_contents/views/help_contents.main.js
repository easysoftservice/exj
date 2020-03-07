/*
 * Ayuda.
 * Contenido
 * Fecha: 07/05/2017
 * Autor: Byron C�rdova
 */
Exj.ui.modules.HelpContents = function(senderMenu, paramsCallBack){
	var me = this;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('help_contents');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				namePanelMainModel: 'help_content'
			}
		}
	};
	
	/**
	* Construye la UI. Seg�n el modelo list
	* Llamado desde la base de la aplicaci�n, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la funci�n. T�cnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = null;
	}; // buildListUI
	
	this.autoCreateStorePanelMain = false;
	this.beforeBuildPanelMain = function(uiPanelMain, dataIdioma, dataResponse){
		// var xxx = uiPanelMain.items[0];
		
		
	};
	
	/* --------AREA DE RENDERS---------- */
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		Exj.moi('Acci�n no permitida.', senderButton.text);
	};
	this.onActionEdit = function(senderButton, e, r){
		Exj.moi('Acci�n no permitida.', senderButton.text);
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		Exj.moi('Acci�n no permitida.', senderButton.text);
		return false;
	};
	
	/* --- INIT --- */
    Exj.ui.modules.HelpContents.superclass.constructor.call(me, {
        id: 'idHelpContents',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.HelpContents, Ext.Panel);
