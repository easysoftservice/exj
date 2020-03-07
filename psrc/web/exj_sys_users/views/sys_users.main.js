/*
 * Sistema.
 * SysUsers.
 * Fecha: 18/11/2013
 * Autor: Byron Córdova
 */
Exj.ui.modules.SysUsers = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('sys_users');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'sys_users',
				nameEditableModel: 'sys_user',
				nameCriteriaModel: 'sys_users'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaSisUsuario = Exj.newPanelCols({
			title: 'Persona',
			style: 'padding: 0px 6px 0px 0px;',
			defaults: {
				border: true,
	            xtype: 'fieldset',
	            labelWidth: 69
			},
			items:[{
	            columnWidth: 0.30,
	            items: [
	                criteria.getTextField('nro_doc_persona')
	            ]
	        }, {
	            columnWidth: 0.35,
	            labelWidth: 69,
	            items: [
	            	criteria.getTextField('nombres_persona')
	            ]
	        }, {
	            columnWidth: 0.35,
	            labelWidth: 69,
	            items: [
	                criteria.getTextField('apellidos_persona')
	            ]
	        }]
		});
		
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 39
			},
			items:[{
	            columnWidth: 0.70,
	            items: [
	            	pnlCriteriaSisUsuario
	                
	            ]
	        }, {
	            columnWidth: 0.30,
	            items: [
	            	criteria.getComboBox('id_empresa'),
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
		me.criteriaFocus('nro_doc_persona');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		Exj.action.grid.onCustom(me.gridMainList, _onSendMail, 'send_mail', true);		
	}; // buildListUI
	
	function _getHUrlFromController(controller){
    	var hUrlTrx = new Exj.HUrl({
    		option: hUrl.getOption(),
    		controller: controller
    	});
		
    	return hUrlTrx;
	};
	
	
	function _onSendMail(btn, e, r){
	
		var _cmbExpMsgs;
		rRegSol = null;
		
		Exj.showEditableModel({
			title: btn.text,
			recordEditable: rRegSol,
			idValue: 0,
			senderButton: btn,
			hUrl: _getHUrlFromController('send_mails'),
			width: Exj.calcWidth(72),
			idMask: me.gridMainList.getEl(),
			labelWidth: 99,
	        params: {
	        	id_persona: r.data.id_persona
	        },
	        fnGetParamsData: function(senderWin, basicForm){
	        	// var valueExp = _cmbExpMsgs.getValue();
	        	
	        	return {
	        		ApplicationName: r.data.col2ApplicationName,
	        		type: r.data.col4Type,
	        		id_mdb_expmsg: _cmbExpMsgs.getValue()
	        	}
	        },
			getItemsUI: _getItemsUIRegSolComun,
			fnSuccessSave: function(form, result, action){
				me.gridMainList.store.reload();
			},
			fnIsValid: function(){
				if(!_cmbExpMsgs){
					Exj.moe('No se ha cargado combo de mensajes expresiones!');
					return false;
				}
				
				if(!_cmbExpMsgs.getValue()){
					Exj.moi('Debe seleccionar un mensaje expresión', function(){
						_cmbExpMsgs.focus();
					});
					return false;
				}
				
				return true;
			},
			fnSuccess: function(editable, editableModel){
				
			},
			fnBeforeShowWin: function(senderWin){
				
			}
		}, me);
		
		
		function _getItemsUIRegSolComun(editable, editableModel){
	    	_cmbExpMsgs = editable.getComboBox('id_mdb_expmsg', {
	    		style: 'color:blue;'
	    	});
	    	
			var pnlInfo = Exj.newPanelCols({
				title: 'General fields',
				style: 'padding: 3px 0px 0px 0px;',
				defaults: {
		            labelWidth: 60
				},
				items:[{
		            columnWidth: 0.50,
		            labelWidth: 69,
		            items: [
			    		editable.getTextField('ApplicationName', {
			    			value: r.data.col2ApplicationName,
			    			readOnly: true,
			    			style: 'color:blue;'
			    		}),
			    		editable.getTextField('type', {
			    			value: r.data.col4Type,
			    			readOnly: true,
			    			style: 'color:blue;'
			    		}),
			    		_cmbExpMsgs
		            ]
		        }, {
		            columnWidth: 0.50,
		            items: [
			    		editable.getTextField('source', {
			    			value: r.data.col5Source,
			    			readOnly: true,
			    			style: 'color:blue;'
			    		}),
			    		{
			    			xtype: 'textarea',
			    			fieldLabel: 'Mensaje',
			    			value: r.data.col6Message,
			    			readOnly: true,
			    			anchor: '99%',
			    			style: 'color:green;'
			    		}
		            ]
		        }]
			});
			
			var pnlCambios = Exj.newPanelCols({
				title: 'Ultimo Cambio',
				style: 'padding: 3px 0px 0px 0px;',
				defaults: {
		            labelWidth: 60,
		            defaults: {
			            xtype: 'textfield',
			            anchor: '99%',
			            disabled: true
		            }
				},
				items:[{
		            columnWidth: 0.50,
		            labelWidth: 69,
		            items: [{
		    			fieldLabel: 'User',
		    			value: editableModel.data.usr_name
			    	}]
		        }, {
		            columnWidth: 0.50,
		            items: [{
		    			fieldLabel: 'Date',
		    			value: editableModel.data.fecha_cambio
			    	}]
		        }]
			});
	    	
			return[
				pnlInfo,
				editable.getTextArea('desc_sol_comun'),
				pnlCambios
			]
		}; // _getItemsUIRegSolComun
		
		
	}; // _onSendMail

	
	/* --------AREA DE RENDERS---------- */
	
	this.renderDataPersona = function(value, cfg, r){
		return Exj.rendererText(r.data.nombres_persona+', '+ r.data.apellidos_persona+' ('+r.data.type_sexo+')');
	};
	
	// No Usado
	/*
	this.renderDataUser = function(value, cfg, r){
		return Exj.rendererText(r.data.username_usr+' ('+r.data.name_usr+')<br/>'+ r.data.usertype);
	};
	*/
	
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
	
	function _showWinSysUser(senderButton, rSisUsuario){
	    var editable = me.editable; // shortcut
	    var cmbUser = editable.getComboBox('id_user');
	    
	    if(!rSisUsuario){
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
	    	recordEditable: rSisUsuario,
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
	    
	    if(rSisUsuario){
	    	winSubmit.bindToContainer(rSisUsuario);
	    }
	    
	    winSubmit.show(senderButton.getEl());
	    
		return winSubmit;
	}; // _showWinSysUser
	
	
	/* --- INIT --- */
    Exj.ui.modules.SysUsers.superclass.constructor.call(me, {
        id: 'idSysUsers',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.SysUsers, Ext.Panel);
