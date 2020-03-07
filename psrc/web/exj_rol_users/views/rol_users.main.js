/*
 * Sistema.
 * RolUsers.
 * Fecha: 04/06/2014
 * Autor: Byron Córdova
 */
Exj.ui.modules.RolUsers = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('rol_users');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'rol_users',
			/*	namesListsModels: ['rol_users', 'rol_unassigned_users'], */
				nameEditableModel: 'rol_user',
				nameCriteriaModel: 'rol_users'
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
	            	criteria.getComboBox('gid', {
	            		listeners: {
	            			'select': function(senderCombo, r, index){
	            				me.callSearch();
	            			}
	            		}
	            	})
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
		me.gridMainList.getStore().removeAll();
		
		me.criteriaFocus('gid');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		me.gridMainList.addListener('beforeedit', function(e){
			if(e.field == 'valueFirstOfcsRel'){
				var cmbOfcsRel = e.grid.colModel.getColumnAt(e.column).editor;
				cmbOfcsRel.store.loadData(e.record.get('itemsOfcsRel'));
			}
		});
		
		me.gridMainList.addListener('validateedit', function(e){
			if(e.field == 'valueFirstOfcsRel'){
				e.cancel = true;
			//	e.record.data[e.field] = e.value;
			}
		});
		
		
		Exj.action.grid.onCustom(me.gridMainList, _onUnassignedUsers, 'unassigned_users', false);
	}; // buildListUI
	
	/*
	this.buildListsSecsUI = function(listModel, dataIdioma, dataResponse, hURLSec){
		me.gridsSecLists.push(Exj.newGridPanelFromListModel(listModel, hURLSec.getActionView()));
	}; 
	*/
	
	function _getHUrlFromController(controller){
    	var hUrlTrx = new Exj.HUrl({
    		option: hUrl.getOption(),
    		controller: controller
    	});
		
    	return hUrlTrx;
	};
	
	function _onActionAddUser(senderButton, e){
		
	};
	
	function _onActionEditUser(senderButton, e, r){
		
	};
	
	function _onActionDelUser(senderButton, e, r){
		
	};
	
	me.getClassUserAdd = function(v, meta, r, rowIndex, colIndex, store){
	//	alert('xxxxxx');
		this.items[0].tooltip = 'Adicionar Usuario: ' + r.data.user_login;
        return 'user_add-col';
	};
	
	me.handlerUserAdd = function(grid, rowIndex, colIndex){
		var r = grid.getStore().getAt(rowIndex);
		grid.getSelectionModel().selectRow(rowIndex);
		
		var jusr_gid = Exj.getParamFromGrid(grid, 'gid', 0);
		if(!jusr_gid){
			jusr_gid = Exj.getParamFromGrid(me.gridMainList, 'gid', 0);
		}
		
		if(!jusr_gid){
			Exj.moi("No se seleccionó Rol del usuario!");
			return;
		}
		
		var hUrlAddUser = _getHUrlFromController('rol_unassigned_users');
		var id_user = r.get('id_user');
		
		Exj.submitAction({
			method: 'POST',
			mask: 'Asignando, espere por favor...',
			url: hUrlAddUser.setId(id_user).getActionCustom('addUser'),
			params: {
				jusr_gid: jusr_gid
			},
			forceSaveDataChangedEmpty: true,
			fnSuccess: function(response){
				grid.store.reload();
				me.gridMainList.store.reload();
			}
		});		
	};
	
	me.getClassUserDel = function(v, meta, r, rowIndex, colIndex, store){
		this.items[0].tooltip = 'Eliminar Usuario ' + r.data.user_login;
        return 'delete-col';
	};
	
	me.handlerUserDel = function(grid, rowIndex, colIndex){
		var r = grid.getStore().getAt(rowIndex);
		grid.getSelectionModel().selectRow(rowIndex);
		
		var hUrlDelUser = _getHUrlFromController('rol_unassigned_users');
		var id_sys_user = r.get('id_sys_user');
		
		Exj.submitAction({
			method: 'POST',
			mask: 'Eliminando, espere por favor...',
			url: hUrlDelUser.setId(id_sys_user).getActionCustom('delUser'),
			forceSaveDataChangedEmpty: true,
			fnSuccess: function(response){
				grid.store.reload();
				/* me.gridMainList.store.reload(); */
			}
		});		
	};
	
	/*
	me.convertOfcRel = function(v){
		return v;
	};
	*/
	
	me.renderComboOfcsRel = function(value, cfg, r){
		var itemsOfcsRel = r.get('itemsOfcsRel');
		
		if(itemsOfcsRel && itemsOfcsRel.length > 0){
			var codesOffices = new Array();
			for(var i=0, itemOfcRel; i < itemsOfcsRel.length; i++){
				itemOfcRel = itemsOfcsRel[i];
				codesOffices.push(itemOfcRel.cod_empresa);
			}
			
			return codesOffices.join(', ');
		}
		
		return '';
	};
	
	function _onUnassignedUsers(senderButton, e){
		// esta función valida la criteria
		var fieldValuesCriteria = me.getFieldValuesCriteria();
		if(!fieldValuesCriteria){
			return;
		}
		
		if(!fieldValuesCriteria.gid){
			Exj.moi('Debe seleccionar el Rol en el área de filtros!');
			return;
		}
		
		Exj.showListModel({
			hUrl: _getHUrlFromController('rol_unassigned_users'),
			title: senderButton.text,
			params: fieldValuesCriteria,
			senderButton: senderButton,
			width: Exj.calcWidth(66),
			showInWindow: true,
			onActionNew: _onActionAddUser,
			onActionEdit: _onActionEditUser,
			onActionDel: _onActionDelUser,
			fnSuccess: function(gridUsers, listModel, response){
				// gridUsers.setTitle('');
				// var colsttt = gridUsers.columns;
			}
		}, me);	
		
		return;
		
		/*
		var _cmbExpMsgs;
		rRegSol = null;
		
		Exj.showEditableModel({
			title: btn.text,
			recordEditable: rRegSol,
			idValue: 0,
			senderButton: btn,
			hUrl: _getHUrlFromController('unassigned_users'),
			width: Exj.calcWidth(72),
			idMask: me.gridMainList.getEl(),
			labelWidth: 99,
	        params: {
	        	id_persona: r.data.id_persona
	        },
	        fnGetParamsData: function(senderWin, basicForm){
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
		*/
		
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
		
		
	}; // _onUnassignedUsers

	
	/* --------AREA DE RENDERS---------- */
	
	this.renderDataPersona = function(value, cfg, r){
		return Exj.rendererText(r.data.nombres_persona+', '+ r.data.apellidos_persona+' ('+r.data.type_sexo+')');
	};
	
	this.renderActionUserActive = function(value, cfg, r){
		/*
		cfg.css = (cfg.css ? cfg.css:'');
		cfg.css += ' user_add-col';
		*/
		// selColModel.getColumnData();
		return '';
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
	
	function _showWinSysUser(senderButton, rSisUsuario){
	    var editable = me.editable; // shortcut
	    // xxx
		var fieldValuesCriteria = me.getFieldValuesCriteria(), gidUser=0;
		if(fieldValuesCriteria && fieldValuesCriteria.gid){
			gidUser = fieldValuesCriteria.gid;
		}
		
		    
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rSisUsuario,
	    	hUrl: hUrl,
	    	nameEntity: 'Usuario del Sistema',
	        width: 666,
	        fnIsValid: function(basicForm){
	        	var values = basicForm.getValues(false);
	        	
	        	if(values.pwd_usr || values.pwd2_usr){
	        		if(!values.pwd_usr){
	        			Exj.moi('Ingrese contraseña', 'Usuario del Sistema', function(){
	        				basicForm.findField('pwd_usr').focus();
	        			});
	        			return false;
	        		}
	        		
	        		if(!values.pwd2_usr){
	        			Exj.moi('Confirme contraseña', 'Usuario del Sistema', function(){
	        				basicForm.findField('pwd2_usr').focus();
	        			});
	        			return false;
	        		}
	        		
	        		if(values.pwd_usr != values.pwd2_usr){
	        			Exj.moi('Contraseñas no son iguales', 'Usuario del Sistema', function(){
	        				basicForm.findField('pwd2_usr').focus();
	        			});
	        			return false;
	        		}
	        		
	        		if(values.pwd_usr.length < 6){
	        			Exj.moi('La contraseña es muy pobre', 'Usuario del Sistema', function(){
	        				basicForm.findField('pwd_usr').focus();
	        			});
	        			return false;
	        		}
	        	}
	        	
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
        		id_empresa: cmbEmpresa.getValue(),
        		id_user: (rSisUsuario ? rSisUsuario.data.id_user : 0),
        		gid: gidUser
        	}]
        };
        
	    winSubmit.addToForm(pnlPersona);
	    
	    var fsUser = Exj.newFieldSet({
	    	title: 'Información del Usuario',
	    	labelWidth: 120,
	    	defaultType: 'textfield',
	    	defaults: {
		        allowBlank: false,
		        width: '201px',
		        maxLength: 60,
		        minLength: 3
		    },
	    	items: [{
		    	name: 'user_name',
		    	fieldLabel: 'Nombes'
		    }, {
		    	name: 'user_login',
		    	fieldLabel: 'Nombre de Usuario'
		    }, {
		    	inputType: 'password',
		    	name: 'pwd_usr',
		    	fieldLabel: 'Contraseña',
		    	allowBlank: (winSubmit.isNew ? false : true)
		    }, {
		    	inputType: 'password',
		    	name: 'pwd2_usr',
		    	fieldLabel: 'Confirmar contraseña',
		    	allowBlank: (winSubmit.isNew ? false : true)
		    }, {
		    	vtype: 'email',
		    	name: 'user_email',
		    	fieldLabel: 'Correo',
		    	listeners: {
		    		'focus': function(senderEmail){
		    			if(!senderEmail.getValue()){
		    				var emailPerson = pnlPersona.getEmail();
		    				if(emailPerson){
		    					senderEmail.setValue(emailPerson);
		    				}
		    			}
		    		}
		    	}
		    }, 
		    cmbEmpresa, {
		    	xtype: 'checkbox',
		    	name: 'is_user_active',
		    	boxLabel: 'Activo',
		    	fieldLabel: 'Estado',
		    	checked: true,
		    	width: 'auto'
		    }]
	    });
	    
	    winSubmit.addToForm(fsUser);
	    
	 //   winSubmit.addToForm(editable.getComboBox('id_sys_lang'));
	  //  winSubmit.addToForm(editable.getComboBox('sys_type_theme'));
	  //  winSubmit.addToForm(editable.getRadioGroup('enable_debug'));
	    
	    if(rSisUsuario){
	    	winSubmit.bindToContainer(rSisUsuario);
	    }
	    
	    winSubmit.show(senderButton.getEl());
	    
		return winSubmit;
	}; // _showWinSysUser
	
	
	/* --- INIT --- */
    Exj.ui.modules.RolUsers.superclass.constructor.call(me, {
        id: 'idRolUsers',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.RolUsers, Ext.Panel);
