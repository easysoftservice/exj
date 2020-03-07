/*
 * Administración.
 * Archivos.
 * Fecha: 29/11/2012
 * Autor: Byron Córdova
 */
Exj.ui.modules.Files = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('files');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'files',
				nameEditableModel: 'file',
				nameCriteriaModel: 'files'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 54
			},
			items:[{
	            columnWidth: 0.25,
	            items: [
	                criteria.getTextField('nameext_file')
	            ]
	        }, {
	            columnWidth: 0.21,
	            labelWidth: 54,
	            items: [
	            	criteria.getNumberField('size_file')
	            ]
	        }, {
	            columnWidth: 0.24,
	            labelWidth: 45,
	            items: [
	                criteria.getComboBox('module_allow')
	            ]
	        }, {
	            columnWidth: 0.30,
	            labelWidth: 36,
	            items: [
	                criteria.getComboBox('id_file_type')
	            ]
	        }]
		});

		return [
			pnlCriteriaMain
		];
	}; // this.getContentCriteria
	
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('nameext_file');
	};
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
	}; // buildListUI
	
	/* --------AREA DE RENDERS---------- */
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		newWinArchivo().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinArchivo(r).show(senderButton.getEl());
	};
	
	function newWinArchivo(rArchivo){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rArchivo,
	    	hUrl: hUrl,
	    	nameEntity: 'Archivo',
	        width: 390,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 90
    	});
	    
		var pnlCodesISO = Exj.newPanelCols({
			title: 'Información del Archivo',
			style: 'padding: 0px 0px 3px 0px',
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 60
			},
			items:[{
	            columnWidth: 0.50,
	            items: [
	                editable.getTextField('path_file')
	            ]
	        }, {
	            columnWidth: 0.50,
	            labelWidth: 69,
	            items: [
	                editable.getTextField('nameext_file')
	            ]
	        }]
		});
	    
	    winSubmit.addToForm(pnlCodesISO);
	    winSubmit.addToForm(editable.getTextField('name_file'));
	    winSubmit.addToForm(editable.getTextField('size_file'));
	    winSubmit.addToForm(editable.getTextField('sub_folder'));
	    
	    if(rArchivo){
	    	winSubmit.bindToContainer(rArchivo);
	    }
	    
		return winSubmit;
	}; // newWinArchivo
	
	
	/* --- INIT --- */
    Exj.ui.modules.Files.superclass.constructor.call(me, {
        id: 'idFiles',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.Files, Ext.Panel);
