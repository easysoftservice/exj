/*
 * Sistema.
 * SysLogPers.
 * Fecha: 30/01/2014
 * Autor: Byron Córdova
 */
Exj.ui.modules.SysLogPers = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('sys_log_pers');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'sys_log_pers',
				nameEditableModel: 'sys_log_per',
				nameCriteriaModel: 'sys_log_pers'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 39
			},
			items:[{
	            columnWidth: 0.30,
	            items: [
	                criteria.getComboBox('id_empresa')
	            ]
	        }, {
	            columnWidth: 0.30,
	            items: [
	            	
	            	criteria.getComboBox('id_user')
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
		me.criteriaFocus('id_empresa');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		Exj.action.grid.onCustom(me.gridMainList, _onxxxx, 'send_mail', true);		
	}; // buildListUI
	
	function _getHUrlFromController(controller){
    	var hUrlTrx = new Exj.HUrl({
    		option: hUrl.getOption(),
    		controller: controller
    	});
		
    	return hUrlTrx;
	};
	
	
	/* --------AREA DE RENDERS---------- */
	
	this.renderDataPersona = function(value, cfg, r){
		return Exj.rendererText(r.data.nombres_persona+', '+ r.data.apellidos_persona+' ('+r.data.type_sexo+')');
	};
	this.renderDataUser = function(value, cfg, r){
		return Exj.rendererText(r.data.username_usr+' ('+r.data.name_usr+')<br/>'+ r.data.usertype);
	};
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		_showWinSysUser(senderButton);
	};
	this.onActionEdit = function(senderButton, e, r){
		_showWinSysUser(senderButton, r);
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		/*
		var idParent = r.get('nacionalidad_pais');
		if(idParent === 0){
			Exj.moi('No se puede eliminar el tipo principal:<br/>'+ r.data.nro_doc_persona);
			return false;
		}
		*/
	};
	
	function _showWinSysUser(senderButton, rSisLog){
	    var editable = me.editable; // shortcut
	    var cmbUser = editable.getComboBox('id_user');
	    
	    if(!rSisLog){
	    	// es nuevo
	    	var stoUsers = cmbUser.getStore();
	    	
	    	if(stoUsers.getCount() == 0){
	    		Exj.moi('No registered users. You must register users in the backend area');
	    		return;
	    	}
	    	
	    	var nFree = 0;
	    	stoUsers.each(function(r){
	    		if(r.data.is_user_free){
	    			nFree += 1;
	    		}
	    	});
	    	
	    	if(nFree == 0){
	    		Exj.moi('You can create users for RIDE SyStem.<br/>There are no available users, and are all assigned.<br/>You must register users in the backend area.');
	    		return;
	    	}
	    }
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rSisLog,
	    	hUrl: hUrl,
	    	nameEntity: 'Usuario del Sistema',
	        width: 666,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 72
    	});
	    
	    /*
		winSubmit.fnGetFieldsExtras = function(){
        	return [
        		xxx
        	]
        };
        */
	    
		var hPersonas = new Exj.ui.helpers.Personas(editable.getCfgCmp('id_persona'), winSubmit.isNew);
		var pnlPersona = hPersonas.getPanelPersona();
		var cmbEmpresa = editable.getComboBox('id_empresa');
		
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		id_persona: pnlPersona.getValueId(),
        		id_empresa: cmbEmpresa.getValue()
        	}]
        };
        
	    winSubmit.addToForm(pnlPersona);
	    winSubmit.addToForm(cmbUser);
	    winSubmit.addToForm(cmbEmpresa);
	    winSubmit.addToForm(editable.getComboBox('id_sys_lang'));
	    winSubmit.addToForm(editable.getComboBox('sys_type_theme'));
	    winSubmit.addToForm(editable.getRadioGroup('enable_debug'));
	    
	    if(rSisLog){
	    	winSubmit.bindToContainer(rSisLog);
	    }
	    
	    winSubmit.show(senderButton.getEl())
	    
		return winSubmit;
	}; // _showWinSysUser
	
	
	/* --- INIT --- */
    Exj.ui.modules.SysLogPers.superclass.constructor.call(me, {
        id: 'idSysLogPers',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.SysLogPers, Ext.Panel);
