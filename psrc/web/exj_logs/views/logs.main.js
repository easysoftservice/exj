/*
 * Sistema.
 * Logs.
 * Fecha: 23/11/2012
 * Autor: Byron Córdova
 */
Exj.ui.modules.Logs = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('logs');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'logs',
				nameEditableModel: 'log',
				nameCriteriaModel: 'logs'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		me.cmbLogsCriteria = criteria.getComboBox('fileLog');
		me.cmbLogsCriteria.addListener('select', function(){
			me.callSearch();
		});
		
		
		var cmbTiposCriteria = criteria.getComboBox('col7TypeError');
		cmbTiposCriteria.addListener('select', function(){
			me.callSearch();
		});
		
		
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 30
			},
			items:[{
	            columnWidth: 0.40,
	            labelWidth: 45,
	            items: [
	            	criteria.getTextField('col4UserName')
	        	]}, {
	            columnWidth: 0.30,
	            items: [
	            	cmbTiposCriteria
	        	]}, {
	            columnWidth: 0.30,
	            items: [
	            	me.cmbLogsCriteria
	        	]}
	        ]
		});
		
		return [
			pnlCriteriaMain
		];
	}; // this.getContentCriteria
	
	this.onAfterAddContentCriteria = function(formPanel){
		/*
		formPanel.getForm().items.each(function(f) {
			if(f.allowBlank !== undefined){
				f.allowBlank = true;	
			}
		});
		*/
	};
	
	/**
	* Antes de buscar, retornar false para evitar la búsqueda
	*/
	this.onBeforeSearch = function(paramsCriteria, formPanel){

	};
	
	/**
	* Después de buscar, si la llamada fué satisfatoria
	*/
	this.onAfterSearch = function(records, options, formPanel){
		// this.fileLog = Exj.getFieldFromName(formPanel, 'fileLog').getValue();
	}; // this.onAfterSearch
	
	
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('fileLog');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());

		Exj.action.grid.onCustom(me.gridMainList, _onViewLogsPhp, 'viewLogsPhp', false, false);
		Exj.action.grid.onCustom(me.gridMainList, _oneditPhpini, 'editPhpini', false, false);
		
		Exj.action.grid.onCustom(me.gridMainList, _onViewVarServer, 'viewVarServer', false, false);
	};

	
	function _onViewVarServer(btn, e) {
		Exj.submitAction({
			url: me.hUrl.getActionCustom('getVarServer'),
			senderButton: btn,
	        params: {},
			fnSuccess: function(response){
				var info = response.data;
				if (!info) {
					// Exj.moi('No respondió el servidor!');
					return;
				}

				var win = new Ext.Window({
		            title: info.title,
		            closable: true,
		            width:800,
		            height:540,
		            plain:true,
		            maximizable: true,
		           /* maximized: true, */
		          	autoScroll: true,
		            layout: 'anchor',
		            items: {
		            	xtype: 'panel',
		            	style: 'padding: 3px;background-color: lightyellow;',
		            	border: true,
		            	html: info.content
		            }
		        });

		        win.show(btn.getEl());
			}
		});
	};

	function _onViewLogsPhp(btn, e) {
		Exj.submitAction({
			url: me.hUrl.getActionCustom('getLogsInternals'),
			senderButton: btn,
	        params: {},
			fnSuccess: function(response){
				var info = response.data;
				if (!info) {
					// Exj.moi('No respondió el servidor!');
					return;
				}

				var win = new Ext.Window({
		            title: info.nameFile,
		            closable: true,
		            width:720,
		            height:540,
		            plain:true,
		            maximizable: true,
		           /* maximized: true, */
		          	autoScroll: true,
		            layout: 'anchor',
		            items: {
		            	xtype: 'panel',
		            	style: 'padding: 3px;background-color: lightyellow;',
		            	border: true,
		            	html: info.content
		            }
		        });

		        win.show(btn.getEl());
			}
		});
	};

	function _oneditPhpini(btn, e) {
		Exj.submitAction({
			url: me.hUrl.getActionCustom('getContentIniInternals'),
			senderButton: btn,
	        params: {},
			fnSuccess: function(response){
				var info = response.data;
				if (!info) {
					// Exj.moi('No respondió el servidor!');
					return;
				}

				var txaContent = new Ext.form.TextArea({
	            	style: 'background-color: lightyellow;',
	            	width: '98%',
	            	height: '96%',
	            	value: info.content
	            });

				var win = new Ext.Window({
		            title: info.nameFile,
		            closable: true,
		            width:720,
		            height:540,
		            plain:true,
		            maximizable: true,
		           /* maximized: true, */
		          	autoScroll: true,
		            layout: 'anchor',
		            items: txaContent,
		            buttons: [{
	                    text: 'Guardar',
	                    disabled: false,
	                    handler: function(btnSave){
	                    	var txt = txaContent.getValue();
	                    	// alert(txt);
	                    	Exj.submitAction({
	                    		url: me.hUrl.getActionCustom('saveCntIniInt'),
	                    		method: 'POST',
								senderButton: btnSave,
						        params: {
						        	txtInter: txt
						        },
						        fnSuccess: function(response){
						        	win.hide();
						        }
	                    	});

	                    }
	                }, {
	                    text: 'Cancelar',
	                    handler: function(){
	                        win.hide();
	                    }
	                }]
		        });

		        win.show(btn.getEl());
			}
		});
	};

	
	/* --------AREA DE RENDERS---------- */
	
	this.renderDataUser = function(value, cfg, r){
		return Exj.rendererText(r.data.xxx+' ('+r.data.col4UserName+')<br/>'+ r.data.usertype);
	};
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		Exj.moi('No soportado '+senderButton.text);
	};
	this.onActionEdit = function(senderButton, e, r){
		Exj.moi('No soportado '+senderButton.text);
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		/*
		var idParent = r.get('nacionalidad_pais');
		if(idParent === 0){
			Exj.moi('No se puede eliminar el tipo principal:<br/>'+ r.data.fileLog);
			return false;
		}
		*/
	};
	
	
	/* --- INIT --- */
    Exj.ui.modules.Logs.superclass.constructor.call(me, {
        id: 'idLogs',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.Logs, Ext.Panel);
