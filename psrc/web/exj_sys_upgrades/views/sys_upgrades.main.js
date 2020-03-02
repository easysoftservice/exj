/*
 * Sistema.
 * SysUpgrades.
 * Fecha: 20/11/2012
 * Autor: Byron Córdova
 */
Exj.ui.modules.SysUpgrades = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('sys_upgrades');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'sys_upgrades',
				nameEditableModel: 'sys_upgrade',
				nameCriteriaModel: 'sys_upgrades'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaFilesZips = Exj.newPanelCols({
			title: 'Arhivos Zips',
			style: 'padding: 0px 6px 0px 0px;',
			defaults: {
				border: true,
	            xtype: 'fieldset',
	            labelWidth: 69
			},
			items:[{
	            columnWidth: 0.50,
	            labelWidth: 51,
	            items: [
	            	criteria.getTextField('file_zip_code')
	            ]
	        }, {
	            columnWidth: 0.50,
	            labelWidth: 51,
	            items: [
	                criteria.getTextField('file_zip_sql')
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
	            	pnlCriteriaFilesZips
	            ]
	        }, {
	            columnWidth: 0.30,
	            items: [
	            	criteria.getComboBox('version_upg'),
	            	criteria.getComboBox('state_upg')
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
		me.criteriaFocus('file_zip_code');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		Exj.action.grid.onCustom(me.gridMainList, _onExecute, 'executeCode', true, true);
		Exj.action.grid.onCustom(me.gridMainList, _onExecute, 'executeSql', true);
		
		Exj.action.grid.onCustom(me.gridMainList, _onViewFiles, 'viewFiles', true);

		Exj.action.grid.onCustom(me.gridMainList, _onExecScriptSql, 'execScriptSql', false, true);
		Exj.action.grid.onCustom(me.gridMainList, _onBackupDB, 'backupDB', false, true);

		Exj.action.grid.onCustom(me.gridMainList, _onRebuildJs, 'rebuildJs', false, false);

	}; // buildListUI
	
	/* --------AREA DE RENDERS---------- */
	
	
	/* --------FIN AREA DE RENDERS---------- */
	
	function _onViewFiles(btn, e, r){
		var cfgListModel = {
    		hUrl: '',
    		idMask: me.gridMainList.getEl(),
    		senderButton: btn,
	        params: {
	        	id_sys_upg: r.data.id_sys_upg,
	        	isCode: (btn.isCode ? 1:0),
	        	isSql: (btn.isSql ? 1:0)
	        }
		};
		
		if(btn.isCode && !r.data.id_file_code){
			Exj.moi('No está cargado el archivo para código');
			return;
		}
		if(btn.isSql && !r.data.id_file_sql){
			Exj.moi('No está cargado el archivo para db');
			return;
		}

		if(btn.isCode || btn.isSql){
			cfgListModel.hUrl = hUrl;
		}
		
		if(!cfgListModel.hUrl){
			Exj.moe('Acción no soportada para vistas.', 'ERROR DE IMPLEMENTACION');
			return;
		}
		
	    Exj.showListModel(cfgListModel, me);
	};

	
	function _onRebuildJs(btn, e){
		Exj.msgQuestion({
            msg: 'Seguro de reconstruír archivos js en el public',
            fnYes: function () {
                Exj.submitAction({
					url: hUrl.getActionCustom('rebuildJs'),
					senderButton: btn
				});
            }
        });
	};

	function _onBackupDB(btn, e){
		var win = new Exj.WinSubmit({
	        title: btn.text,
	        urlSubmit: hUrl.getActionCustom('backupDB'),
	        width: 720,
	        textOk: 'Ejecutar',
	        maximizable: true,
	        isSuccessActionNone: true,
	        waitMsg: 'Ejecutando...'
	    }, {
	        labelWidth: 66
	    });

	    win.fnSuccess = function(form, result, action){
	    	win.gridFiles.store.loadData(result.data);
	    };

	    win.searchProgBackup = function(btnSearch, e){
	    	var txfProg = this.getFieldFromName('path_mysqldump');
	    	txfProg.setValue(txfProg.getValue().trim());
	    	var valProg = txfProg.getValue();
	    	if (!valProg.length) {
	    		Exj.moe('Ruta/Programa de backup no válido');
	    		return;
	    	}

	    	var refWin = this;

	    	Exj.submitAction({
				url: hUrl.getActionCustom('searchProgBackup'),
				senderButton: btnSearch,
		        params: {
		        	path_probk: valProg
		        },
				fnSuccess: function(response){
					var data = response.data;
					if (!data) {
						Exj.moe('No se respondió de data al buscar!');
						return;
					}

					var msg = [];
					if (data.founds) {
						if (data.founds.length >= 1) {
							refWin.getFieldFromName('path_mysqldump').setValue(data.founds[0]);
							if (data.founds.length > 1) {
								msg.push('Se encontraron:');
								msg.push(data.founds.join('<br>'));
							}
							else{
								msg.push('Se encontró única coincidencia');
							}
						}
						else{
							msg.push('No se encontraron!');
						}
					}
					else{
						msg.push('No se encontró, indique un subdirectorio');
					}

					if (data.dirToRecall) {
						msg.push('Aún no se leyó todo el directorio, última busqueda en: '+data.dirToRecall);
					}

					if (msg.length) {
						Exj.moi(msg.join('<br>'), 'Buscando programa de backup');
					}
				}
			});
	    };

	    win.addToForm({
	    	xtype: 'fieldset',
	    	title: 'Base de datos',
	    	layout: 'column',
	    	defaults: {
	    		xtype: 'panel',
	    		border: false,
	    		layout: 'form'
	    	},
	    	items: [{
	    		columnWidth: 0.50,
	    		labelWidth: 54,
	    		items: {
			    	xtype: 'textfield',
			    	name: 'usr',
			    	fieldLabel: 'Usuario',
			    	allowBlank: false,
			    	anchor: '90%'
			    }
	    	}, {
	    		columnWidth: 0.50,
	    		items: {
			    	xtype: 'textfield',
			    	name: 'pwd',
			    	fieldLabel: 'Contraseña',
			    	allowBlank: true,
			    	anchor: '90%'
			    }
	    	}]
	    });

	    win.addToForm({
	    	xtype: 'fieldset',
	    	title: 'Ruta mysqldump',
	    	layout: 'column',
	    	defaults: {
	    		xtype: 'panel',
	    		layout: 'form',
	    		border: false
	    	},
	    	items: [{
	    		columnWidth: 0.9,
	    		labelWidth: 30,
	    		items: [{
			    	xtype: 'textfield',
			    	name: 'path_mysqldump',
			    	fieldLabel: 'Ruta',
			    	allowBlank: false,
			    	value: btn.path_mysqldump,
			    	anchor: '99%'
			    }]
	    	}, {
	    		width: 81,
	    		items: [{
			    	xtype: 'button',
			    	iconCls: 'exj-btn-search',
			    	text: 'Buscar...',
			    	handler: function(bs, e){
			    		win.searchProgBackup(bs, e);
			    	}
			    }]
	    	}]
	    });

	    win.gridFiles = _getGridFiledBK(win);
	    win.addToForm(win.gridFiles);

	    win.on('show', function(senderWin){
	    	Exj.submitAction({
				url: hUrl.getActionCustom('getDataFilesBks'),
				/* senderButton: btn, */
		        params: {
		        },
				fnSuccess: function(response){
					senderWin.gridFiles.store.loadData(response.data);
				}
			});
	    });

	    win.show(btn.getEl());
	};

	function _getGridFiledBK(refWin) {
		var store = new Ext.data.JsonStore({
			autoDestroy: true,
	        fields: [
	           {name: 'name_file'},
	           {name: 'size_file',     type: 'string'},
	           {name: 'd_change_file', type: 'date', dateFormat: 'Y-m-d H:i:s'}
	        ]
	 	});

		var grid = new Ext.grid.GridPanel({
	        store: store,
	        columns: [
	            {
	                id       :'name_file',
	                header   : 'Archivo', 
	                width    : 180, 
	                sortable : true, 
	                dataIndex: 'name_file'
	            },
	            {
	                header   : 'Tamaño', 
	                width    : 90, 
	                sortable : true,
	                align: 'right',
	                dataIndex: 'size_file'
	            },
	            {
	                header   : 'Modificación', 
	                width    : 120, 
	                sortable : true,
	                align: 'right', 
	                renderer : Ext.util.Format.dateRenderer('d/m/Y H:i'), 
	                dataIndex: 'd_change_file'
	            },
	            {
	                xtype: 'actioncolumn',
	                header: 'Acciones',
	                align: 'center',
	                width: 72,
	                items: [{
	                	tooltip: 'Descargar',
	                    getClass: function(v, meta, rec) {
	                        return 'arrow-col';
	                    },
	                    handler: function(grid, rowIndex, colIndex) {
	                        var rec = store.getAt(rowIndex);
	                        var nameFile = rec.get('name_file');

	                        // alert("Descargar " + nameFile);

	                        Exj.downLoadFile({
						        url: hUrl.getActionDownloadFile({
		                        	nameFile: nameFile
		                        })
						    });
	                    }
	                }, {
	                    tooltip: 'Eliminar',
	                    getClass: function(){
	                    	return 'delete-col';
	                    },
	                    handler: function(grid, rowIndex, colIndex) {
	                        var rec = store.getAt(rowIndex);
	                        // alert("Eliminar: " + rec.get('name_file'));
	                        var nameFile = rec.get('name_file');

	                        Exj.submitAction({
	                        	confirm: {
	                        		msg: 'Está seguro de eliminar: '+nameFile
	                        	},
								url: hUrl.getActionCustom('deleteFileBk'),
								/* senderButton: btn, */
						        params: {
						        	nameFile: nameFile
						        },
								fnSuccess: function(response){
									refWin.gridFiles.store.loadData(response.data);
								}
							});
	                    }
	                }]
	            }
	        ],
	        stripeRows: true,
	        autoExpandColumn: 'name_file',
	        height: 180,
	        title: 'Archivos BK Generados'
	    });

		return grid;
	};

	function _onExecScriptSql(btn, e){
		var win = new Exj.WinSubmit({
	        title: btn.text,
	        urlSubmit: hUrl.getActionCustom('execScriptSql'),
	        width: 720,
	        textOk: 'Ejecutar',
	        maximizable: true,
	        isSuccessActionNone: true,
	        waitMsg: 'Ejecutando...'
	    }, {
	        labelWidth: 66
	    });


	    win.fnSuccess = function(form, result, action){
	    	win.compResponse.removeAll();

	    	if (!result.data) {
	    		Exj.moi('No se retorno data!');
	    		return;
	    	}

	    	if (result.data.grid) {
	    		win.showResponseGrid(result.data.grid);
	    	}
	    	else if(result.data.resultSQL){
	    		if (Ext.isArray(result.data.resultSQL) || Ext.isObject(result.data.resultSQL)) {
	    			result.data.resultSQL = Ext.encode(result.data.resultSQL);
	    		}

	    		win.compResponse.add({
	    			xtype: 'label',
	    			html: result.data.resultSQL
	    		});
	    	}
	    	else{
	    		win.compResponse.add({
	    			xtype: 'label',
	    			html: 'No se obtubo respuesta!'
	    		});
	    	}

	    	win.getFormPanelMain().doLayout();
	    };

	  	win.showResponseGrid = function(listModel){
	    	var grid = Exj.newGridPanelFromListModel(listModel, '');
	    	this.compResponse.add(grid);
	    };


	    win.addToForm({
	    	xtype: 'fieldset',
	    	title: 'Cambio de acceso para DB',
	    	items: [{
		    	xtype: 'textfield',
		    	name: 'usr',
		    	fieldLabel: 'Usuario',
		    	allowBlank: true,
		    	anchor: '99%'
		    }, {
		    	xtype: 'textfield',
		    	name: 'pwd',
		    	fieldLabel: 'Contraseña',
		    	allowBlank: true,
		    	anchor: '99%'
		    }]
	    });

	    win.addToForm({
	    	xtype: 'panel',
	    	layout: 'form',
	    	labelAlign: 'top',
	    	border: false,
	    	items: [{
		    	xtype: 'textarea',
		    	name: 'scrbsql',
		    	fieldLabel: 'Script SQL',
		    	allowBlank: false,
		    	anchor: '99%'
		    }]
	    });

	    win.compResponse = new Ext.Panel({
	    	title: 'RESPUESTA',
	    	height: 210
	    });
	    win.addToForm(win.compResponse);

	    win.show(btn.getEl());
	};
	
	function _onExecute(btn, e, r){
		if(!btn.isCode && !btn.isSql){
			Exj.moi('Comando no reconocido!');
			return;
		}
		
		if(btn.isCode && !r.data.id_file_code){
			Exj.moi('No se ha cargado el archivo de código');
			return;
		}
		if(btn.isSql && !r.data.id_file_sql){
			Exj.moi('No se ha cargado el archivo db');
			return;
		}

		var paramsSubmit = {
        	id_sys_upg: r.data.id_sys_upg,
        	executeCode: (btn.isCode ? 1:0),
        	executeSql: (btn.isSql ? 1:0)
        };

		if (btn.isSql) {
			_executeScriptSQL({
				urlSubmit: hUrl.getActionCustom('executeFileZip'),
				senderButton: btn,
				params: paramsSubmit
			});
			return;
		}

		Exj.submitAction({
			url: hUrl.getActionCustom('executeFileZip'),
			senderButton: btn,
	        params: paramsSubmit,
			fnSuccess: function(response){
				me.gridMainList.store.reload();
			}
		});
	};

	function _executeScriptSQL(params){
		params.textOk = 'Ejecutar';
		params.width = 333;
		params.title = params.senderButton.text;
		params.idValue = 0;

		var win = new Exj.WinSubmit(
			params, {
	        	labelWidth: 66
	    	}
	    );

	    win.fnSuccess = function(){
	    	me.gridMainList.store.reload();
	    };

	    win.addToForm({
	    	xtype: 'fieldset',
	    	title: 'Cambio de acceso para DB',
	    	items: [{
		    	xtype: 'textfield',
		    	name: 'usr',
		    	fieldLabel: 'Usuario',
		    	allowBlank: true,
		    	anchor: '99%'
		    }, {
		    	xtype: 'textfield',
		    	name: 'pwd',
		    	fieldLabel: 'Contraseña',
		    	allowBlank: true,
		    	anchor: '99%'
		    }]
	    });

	    win.show(params.senderButton.getEl());
	};
	
	this.onActionNew = function(senderButton, e){
		newWinSisActualizacion().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinSisActualizacion(r).show(senderButton.getEl());
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		/*
		var idParent = r.get('nacionalidad_pais');
		if(idParent === 0){
			Exj.moi('No se puede eliminar el tipo principal:<br/>'+ r.data.xxx);
			return false;
		}
		*/
	};
	
	function newWinSisActualizacion(rSisActualizacion){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rSisActualizacion,
	    	hUrl: hUrl,
	    	nameEntity: 'Actualización del Sistema',
	        width: 540,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
	    	fileUpload: true, 
    		labelWidth: 72
    	});
	    
	    /*
		winSubmit.fnGetFieldsExtras = function(){
        	return [
        		xxx
        	]
        };
        */
	    
		var fufZipCode = editable.getFileUploadField('file_zip_code');
		var fufZipSQL = editable.getFileUploadField('file_zip_sql');
		
		/*
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		version_upg: ''
        	}]
        };
        */
	    
	    winSubmit.addToForm(editable.getTextField('version_upg'));
	    winSubmit.addToForm(fufZipCode);
	    winSubmit.addToForm(fufZipSQL);
	    winSubmit.addToForm(editable.getTextArea('desc_upg'));
	    
	    if(rSisActualizacion){
	    	winSubmit.bindToContainer(rSisActualizacion);
	    }
	    
		return winSubmit;
	}; // newWinSisActualizacion
	
	
	/* --- INIT --- */
    Exj.ui.modules.SysUpgrades.superclass.constructor.call(me, {
        id: 'idSysUpgrades',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.SysUpgrades, Ext.Panel);
