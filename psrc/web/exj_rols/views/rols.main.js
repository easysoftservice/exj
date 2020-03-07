/*
 * Security.
 * Roles.
 * Fecha: 04/08/2014
 * Autor: Byron Córdova
 */
Exj.ui.modules.Rols = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('rols');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'rols',
				nameEditableModel: 'rol'
			/*	nameCriteriaModel: 'rols' */
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 45
			},
			items:[{
	            columnWidth: 0.30,
	            items: [
	            	criteria.getTextField('code_rol')
	            ]
	        }, {
	            columnWidth: 0.60,
	            items: [
	            	criteria.getTextField('name_rol')
	            ]
	        }]
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
		
	}; // this.onAfterSearch
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('name_rol');
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
		newWinRol().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinRol(r).show(senderButton.getEl());
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		var is_required_rol = r.get('is_required_rol');
		if(is_required_rol === 1){
			Exj.moi('It can not eliminate, the role ('+r.data.name_rol+') required for the system.');
			return false;
		}
	};
	
	function newWinRol(rRol){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rRol,
	    	hUrl: hUrl,
	    	nameEntity: 'Rol',
	        width: 300,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 63
    	});
	    
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		is_internal_rol: (rRol ? rRol.data.is_internal_rol: 0),
        		id_group_acl_aro: (rRol ? rRol.data.id_group_acl_aro: -1)
        	}]
        };

	    /*
		winSubmit.fnGetFieldsExtras = function(){
        	return [
        		xxx
        	]
        };
        */
	    
	    winSubmit.addToForm(editable.getTextField('code_rol'));
	    winSubmit.addToForm(editable.getTextField('name_rol'));
	   
	    winSubmit.addToForm(editable.getTextArea('detail_rol'));
	    
	    if(rRol){
	    	winSubmit.bindToContainer(rRol);
	    }
	    
		return winSubmit;
	}; // newWinRol
	
	
	/* --- INIT --- */
    Exj.ui.modules.Rols.superclass.constructor.call(me, {
        id: 'idRols',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.Rols, Ext.Panel);
