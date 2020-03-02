/*
 * Desarrolo.
 * Componentes.
 * Fecha: 02/05/2016
 * Autor: Byron Córdova
 */
Exj.ui.modules.Components = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('components');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'components',
				nameEditableModel: 'component',
				nameCriteriaModel: 'components'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'panel',
	            layout: 'form',
	            labelWidth: 81
			},
			items:[{
	            columnWidth: 0.21,
	            items: [
	                criteria.getTextField('name')
	            ]
	        }, {
	            columnWidth: 0.21,
	            labelWidth: 60,
	            items: [
	            	criteria.getTextField('name_cat')
	            ]
	        }]
		});

		return pnlCriteriaMain;
	}; // this.getContentCriteria
	
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('name');
	};
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		Exj.action.grid.onCustom(me.gridMainList, _onGenerateCmp, 'generateCmp', false);
		Exj.action.grid.onCustom(me.gridMainList, _onDelAllGrpAXO, 'delAllGrpAXO', true);
	}; // buildListUI

	function _onDelAllGrpAXO(senderButton, e, rec){
		var msgConfirm = 'Está seguro de '+senderButton.text+'?';
		msgConfirm += '<br>Se eliminar grupo: ' +rec.data.nombre_com+' y toda relación con otras tablas';

		Exj.msgQuestion({
            msg: msgConfirm,
            fnYes: function () {
                Exj.submitAction({
                	url: me.hUrl.getActionCustom('deleteAllFromGrupo'),
                	params: {
                		id_gj: rec.data.id_group_joomla
                	},
                	fnSuccess: function (response) {
                		me.gridMainList.store.reload();
                	}
                });
            }
        });
	};

	
	function _onGenerateCmp(senderButton, e){

		var hUrlGC = new Exj.HUrl({
			option: me.hUrl.getOption(),
			controller: 'generate_components'
		});
		
		
		Exj.showEditableModel({
			title: 'Generar Código',
			recordEditable: null,
			idValue: 0,
			hUrl: hUrlGC,
			width: 840,
			idMask: me.gridMainList.getEl(),
			labelWidth: 66,
	        params: {
	        },
	        fnGetParamsData: function(senderWin, basicForm){
	        	
	        	return {
	        	};
	        },
			getItemsUI: _getItemsUI,
			fnSuccessSave: function(form, result, action){
				me.gridMainList.store.reload();
			}
		}, me);
		
		function _getItemsUI(editable, editableModel){
			var cmbAppTables, gridCols, txfNameComp, txfCompSingular, txfCompPlural, cmbTplFile, tabPanelCodigo;
			
			cmbAppTables = editable.getComboBox('nombre_tabla_com', {
				listeners: {
					select: function(senderCmbTable, rTable){
						txfNameComp.setValue(rTable.data.nameComponent);
						txfCompSingular.setValue(rTable.data.singularComp);
						txfCompPlural.setValue(rTable.data.pluralComp);
						
						cmbTplFile.setDisabled(false);
						
						var rTplFile = cmbTplFile.getRecordSelected();
						if(rTplFile){
							cmbTplFile.fireEvent('select', cmbTplFile, rTplFile);
						}
						
						gridCols.store.rejectChanges();
						gridCols.store.setBaseParam('table_name', rTable.data.value);
						gridCols.store.load({
							callback: function(){
								gridCols.focus();
							}
						});
					}
				}
			});
			
			gridCols = Exj.newGridFromEditableModel({
				editableModel: editableModel,
				nameList: 'table_cols',
				baseParams: {},
				scopeModule: me
			});
			
			txfNameComp = editable.getTextField('nombre_com');
			txfCompSingular = editable.getTextField('singular_com');
			txfCompPlural = editable.getTextField('plural_com');
			cmbTplFile = editable.getComboBox('tpl_file', {
				disabled: true,
				listeners: {
					select: function(senderTplFile, rTplFile){
						var nameTable = cmbAppTables.getValue();
						if(!nameTable){
							senderTplFile.reset();
							Exj.moi('Seleccione una tabla.', function(){
								cmbAppTables.focus();
							});
							
							return;
						}
						
						var recsCols = gridCols.store.getModifiedRecords();
						var itemsModifiedCols = new Array();
						for(var i=0, rCol; i < recsCols.length; i++){
							rCol = recsCols[i];
							
							itemsModifiedCols.push({
								nameCol: rCol.data.nameCol,
								labelCol: rCol.data.labelCol
							});
						}
						
						var paramsViewFile = {
							nameFileTpl: rTplFile.data.value,
							nameTable: nameTable,
							nameComp: txfNameComp.getValue(),
							plural_com: txfCompPlural.getValue(),
							singular_com: txfCompSingular.getValue(),
							itemsModifiedCols: Ext.encode(itemsModifiedCols)
						};
						
						Exj.submitAction({
							method: 'GET',
							mask: 'Por favor espere...',
							url: hUrlGC.getActionCustom('getContentHTMLFileGenerated'),
							idMask : tabPanelCodigo.getEl(),
							/* withParams: true, */
							params: paramsViewFile,
							fnSuccess: function(response){
								var contentHTMLFile = response.data;
								if(!contentHTMLFile){
									contentHTMLFile = 'NO DISPONIBLE!';
								}
								
								var pnlViewFile = tabPanelCodigo.find('dataIndex', 'contentFileView')[0];
								
								pnlViewFile.removeAll();
								pnlViewFile.add({
									xtype: 'panel',
									border: false,
									autoHeight: true,
									html: contentHTMLFile
								});
								pnlViewFile.doLayout();
							}
						});						
						
					}
				}
			});
			
			var itemsUI = [
				cmbAppTables, {
					xtype: 'panel',
					layout: 'column',
					title: 'Componente',
					defaults: {
						border: false,
						layout: 'form',
						labelWidth: 75
					},
					items: [{
			            columnWidth: 0.40,
			            items: [
			                txfNameComp
			            ]
			        }, {
			            columnWidth: 0.30,
			            labelWidth: 45,
			            items: [
			            	txfCompPlural
			            ]
			        }, {
			            columnWidth: 0.30,
			            labelWidth: 54,
			            items: [
			            	txfCompSingular
			            ]
			        }]
				}, {
					xtype: 'tabpanel',
					activeTab: 0,
					items: [{
						title: 'Columnas',
						items: gridCols
					}, {
						title: 'Ver Código',
						layout: 'form',
						items: [
							cmbTplFile, {
								xtype: 'panel',
								dataIndex: 'contentFileView',
								autoScroll: true,
								style: 'font-size: 14px;',
								height: 333
							}
						]
					}],
					listeners: {
						tabchange: function(senderTab, senderPanel){
							senderTab.doLayout();
						},
						afterrender: function(senderTab){
							tabPanelCodigo = senderTab;
						}
					}
				}
			];
	    	
	    	return itemsUI;
		};
    	

	};
	
	/* --------AREA DE RENDERS---------- */
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		Exj.moi('Acción no permitida.');
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinComponent(r).show(senderButton.getEl());
	};
	
	function newWinComponent(rComponent){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rComponent,
	    	hUrl: me.hUrl,
	    	nameEntity: 'Componente',
	        width: 420,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 81
    	});
    	
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		id_group_joomla: rComponent.data.id_group_joomla
        	}]
        };
            	
    	winSubmit.addToForm(editable.getComboBox('nombre_tabla_com'));
    	winSubmit.addToForm(editable.getTextField('plural_com'));
    	winSubmit.addToForm(editable.getTextField('singular_com'));
        
	    winSubmit.bindToContainer(rComponent, 'plural_com');
	    
		return winSubmit;
	}; // newWinComponent
	
	
	/* --- INIT --- */
    Exj.ui.modules.Components.superclass.constructor.call(me, {
        id: 'idComponents',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.Components, Ext.Panel);
