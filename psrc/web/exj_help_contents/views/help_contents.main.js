/*
 * Ayuda.
 * Contenido
 * Fecha: 07/05/2017
 * Autor: Byron Córdova
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
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
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
		Exj.moi('Acción no permitida.', senderButton.text);
	};
	this.onActionEdit = function(senderButton, e, r){
		Exj.moi('Acción no permitida.', senderButton.text);
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		Exj.moi('Acción no permitida.', senderButton.text);
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
