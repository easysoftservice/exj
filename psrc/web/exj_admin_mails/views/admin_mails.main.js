/*
 * Administración.
 * Correos.
 * Fecha: 31/03/2013
 * Autor: Byron Córdova
 */
Exj.ui.modules.AdminMails = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('mails');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'mails',
				nameEditableModel: 'mail',
				nameCriteriaModel: 'mails'
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
	            	criteria.getTextField('to_email')
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
		me.criteriaFocus('to_email');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		Exj.action.grid.onCustom(me.gridMainList, _onMailTpls, 'mail_tpls', false);
		Exj.action.grid.onCustom(me.gridMainList, _onMailResend, 'mail_resend', true, true);
		Exj.action.grid.onCustom(me.gridMainList, _onMailPreview, 'mail_preview', true);
		
	}; // buildListUI
	
	function _getHUrlFromController(controller){
    	var hUrlTrx = new Exj.HUrl({
    		option: hUrl.getOption(),
    		controller: controller
    	});
		
    	return hUrlTrx;
	};
	

	function _onMailResend(btn, e, r){
		
		Exj.mail.send({
			idMail: r.id,
			mask: 'Renviando correo...',
			fnSuccess: function(response){
				me.gridMainList.store.reload();
			}
		});
		
	}; // _onMailResend
	
	
	function _onMailPreview(btn, e, r){
		
		Exj.mail.preview({
			idMail: r.id,
			fnSuccess: function(response){
				
			}
		});
		
	}; // _onMailPreview
	
	
	function _onMailTpls(btn, e){
	
		var responseListModel = Exj.showListModel({
    		hUrl: _getHUrlFromController('tpls'),
    		idMask: me.gridMainList.getEl(),
    		senderButton: btn,
	        params: {
	        	criteria: '{}'
	        },
	        width: Exj.calcWidth(90),
	        onActionNew: function(senderButton, e){
	        	_newWinPlantilla(null, senderButton);
	        },
	        onActionEdit: function(senderButton, e, r){
	        	_newWinPlantilla(r, senderButton);
	        }
		}, me);
		
		if(!responseListModel){
			return;
		}
	
		function _newWinPlantilla (rTpl, btnTpl){
			var gridTpl = responseListModel.getGridListModel();
			
			var _cmbTypeTpl;
			
			Exj.showEditableModel({
				/* title: btnTpl.text, */
				recordEditable: rTpl,
				idValue: (rTpl ? rTpl.data.id_mail_tpl: 0),
				senderButton: btnTpl,
				hUrl: _getHUrlFromController('tpls'),
				width: Exj.calcWidth(90),
				idMask: gridTpl.getEl(),
				labelWidth: 60,
		        params: {
		        },
		        fnGetParamsData: function(senderWin, basicForm){
		        	// var valueTpl = _cmbTypeTpl.getValue();
		        	
		        	return {
		        	}
		        },
				getItemsUI: _getItemsUIRegTpl,
				fnSuccessSave: function(form, result, action){
					gridTpl.store.reload();
				},
				fnIsValid: function(){
					if(!_cmbTypeTpl.getValue()){
						Exj.moi('Debe seleccionar el tipo de plantilla', function(){
							_cmbTypeTpl.focus();
						});
						return false;
					}
					
					return true;
				},
				fnSuccess: function(editable, editableModel){
					
				},
				fnBeforeShowWin: function(senderWin){
				    if(rTpl){
				    	senderWin.bindToContainer(rTpl);
				    }
				}
			}, me);
			
			function _getItemsUIRegTpl(editable, editableModel){
		    	_cmbTypeTpl = editable.getComboBox('type_tpl', {
		    		style: 'color:blue;'
		    	});
		    	
				var pnlCamposGen = Exj.newPanelCols({
					title: 'Campos Generales',
					style: 'padding: 3px 0px 0px 0px;',
					defaults: {
			            labelWidth: 60
					},
					items:[{
			            columnWidth: 0.50,
			            labelWidth: 69,
			            items: [
				    		editable.getTextField('title_tpl'),
				    		editable.getTextField('subject_default'),
				    		_cmbTypeTpl
			            ]
			        }, {
			            columnWidth: 0.50,
			            items: [
				    		editable.getRadioGroup('is_published'),
				    		editable.getRadioGroup('is_default_tpl')
			            ]
			        }]
				});
				
				var txfCntTpl = editable.getTextArea('cnt_tpl');
				
				var tabsContentsTpl = new Ext.TabPanel({
			        activeTab: 0,
			        autoWidth: true,
			        height: 510,
			        plain:true,
			        defaults:{
			        	autoScroll: true,
			        	layout: 'fit'
			        },
			        items:[{
			                title: 'CONTENIDO',
			                items: [
			                	txfCntTpl
			                ]
			            }, {
			                title: 'VISTA PREVIA',
			                height: 210,
			                frame: true,
			                listeners: {
			                	activate: function(tab){
			                		var elemTpl = document.getElementById('divPreviewTpl');
			                		elemTpl.innerHTML = txfCntTpl.getValue();
			                		
			                		tab.doLayout();
			                	}
			                },
			                html: '<div id="divPreviewTpl"></div>'
			            }, {
			                title: 'VARIABLES DISPONIBLES',
			            	height: 290,
			                listeners: {
			                	activate: function(tab){
			                		if(tab.isLoadedVars){
			                			return;
			                		}
			                		
									Exj.showListModel({
							    		hUrl: _getHUrlFromController('vars'),
							    		idMask: tabsContentsTpl.getEl(),
										title: 'Variables para Plantilla de Correo',
										iconCls: '',
								        showInWindow: false,
								        fnSuccess: function(gridListModel, listModel, response){
				                			gridListModel.addListener('afterrender', function(senderCmp){
					                			tab.doLayout();
					                		});

								        	var elemVar = document.getElementById('divVarsTpl');
								        	gridListModel.height = tab.getInnerHeight();
								        	
								        	gridListModel.render(elemVar);
								       
								        	tab.isLoadedVars = true; 	
								        }
									}, me);
			                	}
			                },
			            	html: '<div id="divVarsTpl"></div>'
			            }
			        ]
			    });				
		    	
				return[
					pnlCamposGen,
					tabsContentsTpl
				]
			}; // _getItemsUIRegTpl			
		}; // _newWinPlantilla
	}; // _onMailTpls	
	
	
	/* --------AREA DE RENDERS---------- */
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		newWinCorreo().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinCorreo(r).show(senderButton.getEl());
	};
	
	function newWinCorreo(rCorreo){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rCorreo,
	    	hUrl: hUrl,
	    	nameEntity: 'E-mail',
	        width: 720,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(form, responseMail, action){
	        	me.gridMainList.store.reload();
	        	
				Exj.msgQuestion({
					msg: 'Deseas envíar el correo',
					fnYes: function(){
						Exj.mail.send({
							idMail: responseMail.data,
							fnSuccess: function(response){
								me.gridMainList.store.reload();
							}
						});
					}
				});
	        }
	    }, {
    		labelWidth: 63
    	});
	    
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		is_html: (rCorreo ? rCorreo.data.is_html: 0)
        	}]
        };

	    /*
		winSubmit.fnGetFieldsExtras = function(){
        	return [
        		xxx
        	]
        };
        */
	    
	    // xxx
		var tapDestinos = new Ext.TabPanel({
	        activeTab: 0,
	        autoWidth: true,
	        height: 210,
	        plain:true,
	        defaults:{
	        	autoScroll: true,
	        	layout: 'fit'
	        },
	        items:[
	        ]
		});
	    
	    
	    
	    
	    
	    var cmbTpl = editable.getComboBox('id_mail_tpl');
	    
	    winSubmit.addToForm(tapDestinos);
	    winSubmit.addToForm(editable.getTextField('to_email'));
	    winSubmit.addToForm(editable.getTextField('cc_mail'));
	    winSubmit.addToForm(editable.getTextField('bcc_mail'));
	    winSubmit.addToForm(editable.getTextField('subject_mail'));
	    winSubmit.addToForm(editable.getTextArea('body_mail'));
	    winSubmit.addToForm(cmbTpl);
	    
	    if(rCorreo){
	    	winSubmit.bindToContainer(rCorreo);
	    }
	    
		return winSubmit;
	}; // newWinCorreo
	
	
	/* --- INIT --- */
    Exj.ui.modules.AdminMails.superclass.constructor.call(me, {
        id: 'idAdminMails',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.AdminMails, Ext.Panel);
