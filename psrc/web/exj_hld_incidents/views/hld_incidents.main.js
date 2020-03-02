/*
 * Mesa de Ayuda.
 * Incidentes.
 * Fecha: 31/03/2015
 * Autor: Byron Córdova
 */
Exj.ui.modules.HldIncidents = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('hld_incidents');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'hld_incidents',
				nameEditableModel: 'hld_incident',
				nameCriteriaModel: 'hld_incidents'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var cmbHelpDesks = criteria.getComboBox('id_helpdesk');
		
		cmbHelpDesks.addListener('select', function(combo, r, index){
			me.callSearch();
		});
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 63
			},
			items:[{
	            columnWidth: 0.55,
	            labelWidth: 120,
	            items: [
	            	cmbHelpDesks,
	            	criteria.getTextField('title_incident')
	            ]
	        }, {
	            columnWidth: 0.39,
	            items: [
	            	criteria.getComboBox('id_hld_catalog_priority'),
	            	criteria.getComboBox('id_hld_catalog_state')
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
		this.idHelpDesk = Exj.getFieldFromName(formPanel, 'id_helpdesk').getValue();
	};
	
	this.onAfterReset = function(btnReset, formPanel){
		this.idHelpDesk = 0;
		
		Exj.clearDataGrid(me.gridMainList);
		me.criteriaFocus('id_helpdesk');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		
		Exj.action.grid.onCustom(me.gridMainList, _onVistasHldInc, 'vistasHldInc', true);
		Exj.action.grid.onCustom(me.gridMainList, _onAccionesHldInc, 'accionesHldInc', true);
		
		Exj.action.grid.onCustom(me.gridMainList, _onCloseInc, 'closeInc', true);
		Exj.action.grid.onCustom(me.gridMainList, _onDocInc, 'docInc', true);
		
	}; // buildListUI
	
	function _getHUrlFromController(controller){
    	var hUrlTrx = new Exj.HUrl({
    		option: hUrl.getOption(),
    		controller: controller
    	});
		
    	return hUrlTrx;
	};
	
	
	function _onDocInc(btn, e, r){
		_onVistasHldInc(btn, e, r);
		
		return;
		
		var id_hld_incident = r.data.id_hld_incident;
		
		Exj.showEditableModel({
			title: ('Documentación del Inicidente'),
			/*	recordEditable: recordEditable, */
			recordEditable: null,
			senderButton: senderButton,
			hUrl: _getHUrlFromController('hld_inc_docs'),
			width: 450,
			idMask: me.gridMainList.getEl(),
			labelWidth: 99,
	        params: {
	        	id_hld_incident: id_hld_incident
	        },
	        fnGetParamsData: function(senderWin, basicForm){
	        	return {
	        	}
	        },
			getItemsUI: _getItemsUIDoc,
			fnSuccessSave: function(form, result, action){
				me.gridMainList.store.reload();
			},
			fnIsValid: function(){
				
				return true;
			},
			fnBeforeShowWin: function(win){
				/*
				win.addListener('show', function(){
					var bf = win.getBasicForm();
					bf.findField('response_inc_res').setValue(itemStateNew.description);
				});
				*/
			}
		}, me);
		
		function _getItemsUIDoc(editable, editableModel){
	    	
			
	    	var _gridDocs = Exj.newGridFromEditableModel({
	    		editableModel: editableModel,
	    		nameList: 'hld_inc_docs',
	    		scopeModule: me,
	    		onActionNew: onActionNewRespuestas,
	    		onActionEdit: onActionEditRespuestas
	    	});
	    	
	    	
	    	// xxx

			var pnlTextos = Exj.newPanel({
				title: '',
				style: 'padding: 3px 0px 0px 0px;',
				layout: 'form',
				defaults: {
		            labelWidth: 102
				},
				items:[
					Exj.newTextField({
						value: itemStateNew.text,
						fieldLabel: 'Estado',
						disabled: true,
						anchor: '90%'
					}),
					editable.getTextArea('desc_doc')
				]
			});
			
	    	return[
	    		_getPanelInfoInc(r),
	    	/*	_gridDocs, */
	    		pnlTextos
	    	]
		};
		
	};
	
	
	function _onCloseInc(btn, e, r){
		btn.idStateAllowed = parseInt(btn.idStateAllowed);

		if(btn.idStateAllowed != r.data.id_hld_catalog_state){
			Exj.moi('Solo se puede establecer a estado <b>Cerrado</b>, si el incidente está en estado: <b>Resuelto</b>');
			return;
		}
		btn.idState = parseInt(btn.idState);
		
		_onActionResposeInc(r, {
			value: btn.idState,
			text: 'Cerrado',
			description: btn.descState
		}, btn, btn.text);
	};
	
	function _onAccionesHldInc(btn, e, r){
		btn.idState = parseInt(btn.idState);
		
		if(btn.idState == r.data.id_hld_catalog_state){
			Exj.moi('No se puede establecer el mismo estado');
			return;
		}
		
		var idStateCurrent = r.data.id_hld_catalog_state;
		var itemsAllowed = btn.itemsAllowed;
		
		if(!Exj.inArray(idStateCurrent, itemsAllowed, 'value')){
			var tplMsgState = 'No se puede establecer al estado: <b>{0}</b>.<br/>Los estados anteriores permitidos son: <br/>{1}<br/>Estado actual: {2}';
			
			Exj.moi(String.format(tplMsgState, btn.text, Exj.convertFromItemsToString(itemsAllowed), me.renderState(r.data.name_state, '', r)));
			return;
		}
		
		_onActionResposeInc(r, {
			value: btn.idState,
			text: btn.text,
			description: btn.descState
		}, btn);
	};
	
	function _onActionResposeInc(recordEditable, itemStateNew, senderButton, titleWin){
		var _id_hld_incident = recordEditable.data.id_hld_incident;
		// var _gridRespuestas;
		
		Exj.showEditableModel({
			title: (titleWin ? titleWin : 'Respuesta al Inicidente'),
		/*	recordEditable: recordEditable, */
			recordEditable: null,
			senderButton: senderButton,
			hUrl: _getHUrlFromController('hld_inc_responses'),
			width: Exj.calcWidth(54),
			idMask: me.gridMainList.getEl(),
			labelWidth: 99,
	        params: {
	        	id_hld_incident: _id_hld_incident
	        },
	        fnGetParamsData: function(senderWin, basicForm){
	        	return {
	        		id_hld_catalog_state: itemStateNew.value
	        	}
	        },
			getItemsUI: _getItemsUIResponse,
			fnSuccessSave: function(form, result, action){
				me.gridMainList.store.reload();
			},
			fnIsValid: function(){
				
				return true;
			},
			fnBeforeShowWin: function(win){
				win.addListener('show', function(){
					var bf = win.getBasicForm();
					bf.findField('response_inc_res').setValue(itemStateNew.description);
				});
			}
		}, me);
		
		
   		function onActionNewRespuestas(senderButton, e, hUrlList, editable, gridListModel){
			newWinEventoStaff(_id_hld_incident, editable, hUrlList, true, gridListModel).show(senderButton.getEl());
   		};
   		function onActionEditRespuestas(senderButton, e, r, hUrlList, editable, gridListModel){
			newWinEventoStaff(_id_hld_incident, editable, hUrlList, true, gridListModel, r).show(senderButton.getEl());
   		};
		
		function _getItemsUIResponse(editable, editableModel){
	    	
			/*
	    	_gridRespuestas = Exj.newGridFromEditableModel({
	    		editableModel: editableModel,
	    		nameList: 'hld_inc_responses',
	    		scopeModule: me,
	    		onActionNew: onActionNewRespuestas,
	    		onActionEdit: onActionEditRespuestas
	    	});
	    	*/
	    	
	    	// xxx

			var pnlTextos = Exj.newPanel({
				title: '',
				style: 'padding: 3px 0px 0px 0px;',
				layout: 'form',
				defaults: {
		            labelWidth: 102
				},
				items:[
					Exj.newTextField({
						value: itemStateNew.text,
						fieldLabel: 'Estado',
						disabled: true,
						anchor: '90%'
					}),
					/* editable.getComboBox('id_hld_catalog_response'), */
					editable.getTextArea('response_inc_res')
				]
			});
			
	    	return[
	    		_getPanelInfoInc(recordEditable),
	    	/*	_gridRespuestas, */
	    		pnlTextos
	    	]
		};
	}; // _onActionResposeInc
	
	
	function _onVistasHldInc(btn, e, r){
		var cfgListModel = {
    		hUrl: '',
    		idMask: me.gridMainList.getEl(),
    		senderButton: btn,
	        params: {
	        	id_hld_incident: r.data.id_hld_incident
	        }
		};
		
		if(btn.isResponses){
			cfgListModel.hUrl = _getHUrlFromController('hld_inc_responses');
			cfgListModel.componentTop = _getPanelInfoInc(r);
		}
		else if(btn.isDocs){
			cfgListModel.hUrl = _getHUrlFromController('hld_inc_docs');
			cfgListModel.componentTop = _getPanelInfoInc(r);
		}
		
		if(!cfgListModel.hUrl){
			Exj.moe('Acción no soportada para vistas.', 'ERROR DE IMPLEMENTACION');
			return;
		}
		
	    Exj.showListModel(cfgListModel, me);
	};
	
	function _getPanelInfoInc(rIncidente){
		
    	var txfAsuntoInc = Exj.newTextField({
			fieldLabel: 'Título',
			anchor: '96%',
			disabled: true
    	});
    	
		var txaDescInc = new Ext.form.TextArea({
            fieldLabel: 'Descripción',
            width: '96%',
            readOnly : true
        });
    	
    	
    	if(rIncidente){
    		txfAsuntoInc.setValue(rIncidente.data.title_incident);
    		txaDescInc.setValue(rIncidente.data.desc_incident);
    	}
		
        var pnlInfoInc = Exj.newPanel({
            title: 'Información de Incidente',
            labelWidth: 66,
            layout: 'form',
            style: 'padding: 0px 0px 3px 0px;',
            items: [
				txfAsuntoInc, 
				txaDescInc
            ]
        });
		
        return pnlInfoInc;
	};
	
	/* --------AREA DE RENDERS---------- */
	this.renderHelpDesk = function(value, cfg, r){
		return Exj.rendererTextColor(value, r.data.color_hld);
	};
	
	this.renderState = function(value, cfg, r){
		return Exj.rendererTextColor(value, r.data.color_state);
	};
	this.renderPriority = function(value, cfg, r){
		return Exj.rendererTextColor(value, r.data.color_pri);
	};
	
	this.renderUsrCre = function(value, cfg, r){
		return value;
		// return Exj.rendererText(value+' ('+r.data.typ_usr_cre+')');
	};
	this.renderUsrChg = function(value, cfg, r){
		return value;
		// return Exj.rendererText(value+' ('+r.data.typ_usr_chg+')');
	};
	
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		/*
		if(!me.idHelpDesk){
			Exj.moi('Seleccione la Mesa de Ayuda', function(){
				me.criteriaFocus('id_helpdesk');
			});
			return false;
		}
		*/
		
		newWinIncidente().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinIncidente(r).show(senderButton.getEl());
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		var canDel = r.get('canDel');
		if(!canDel){
			Exj.moi('No puede eliminar este Inicidente.<br/>El usuario quien creó el incidente lo puede eliminar.<br/>El usuario es: '+ r.data.name_usr_cre);
			return false;
		}
	};
	
	function newWinIncidente(rIncidente){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rIncidente,
	    	hUrl: hUrl,
	    	nameEntity: 'Incidente',
	        width: 540,
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
        		
        	]
        };
        */
	    
	    var isNew = (rIncidente ? false:true);
	    
		var cmbMesaAyuda = editable.getComboBox('id_helpdesk');
		if(isNew && me.idHelpDesk){
			cmbMesaAyuda.setValue(me.idHelpDesk);
		}
		var cmbEstado = editable.getComboBox('id_hld_catalog_state');
		
		
		if(isNew){
			Exj.comboSelectFirst(cmbEstado);
		}
		cmbEstado.setDisabled(true);
		
		
		var txfAsunto = editable.getTextField('title_incident');
		
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		id_hld_catalog_state: cmbEstado.getValue(),
        		id_helpdesk: cmbMesaAyuda.getValue(),
        		title_incident: txfAsunto.getValue()
        	}]
        };
	    
	    winSubmit.addToForm(cmbMesaAyuda);
	    winSubmit.addToForm(txfAsunto);
	    winSubmit.addToForm(editable.getTextArea('desc_incident'));
	    winSubmit.addToForm(editable.getComboBox('id_hld_catalog_priority'));
	    winSubmit.addToForm(editable.getComboBox('id_sys_user_asignado'));
	    winSubmit.addToForm(cmbEstado);
	    
	    if(rIncidente){
	    	winSubmit.bindToContainer(rIncidente);
	    }
	    
		return winSubmit;
	}; // newWinIncidente
	
	
	/* --- INIT --- */
    Exj.ui.modules.HldIncidents.superclass.constructor.call(me, {
        id: 'idHldIncidents',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.HldIncidents, Ext.Panel);
