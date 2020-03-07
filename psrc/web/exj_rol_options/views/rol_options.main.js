/*
 * Security.
 * Opciones del Rol.
 * Fecha: 26/08/2014
 * Autor: Byron Córdova
 */
Exj.ui.modules.RolOptions = function(senderMenu, paramsCallBack){
	var me = this;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('rol_options');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
			/*	nameListModel: 'rol_options', */
				namePanelMainModel: 'rol_option',
				nameEditableModel: 'rol_option',
				nameCriteriaModel: 'rol_options'
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
	
	this.onBeforeSearch = function(paramsCriteria, formPanelCriteria){
		var tree = Exj.getFieldFromName(me, 'tpSystemModules');
		tree.root.removeAll();
	};
	
	/**
	* Cuando se cargó la data desde el server
	*/
	this.onLoadFromStore = function(records, options, compMain, formPanelCriteria){
		compMain.setVisible(true);
		
		var tree = Exj.getFieldFromName(compMain, 'tpSystemModules');
		
		var itemsChilds = [];
		for(var i=0, r; i < records.length; i++){
			r = records[i];
			itemsChilds.push(r.data);
		}
		
		tree.root.appendChild(itemsChilds);
		tree.root.expand();
	};
	
	
	
	/**
	* Después de buscar, si la llamada fué satisfatoria
	*/
	this.onAfterSearch = function(records, options, formPanel){
		
	}; // this.onAfterSearch
	
	this.onAfterReset = function(btnReset, formPanel){
		var tree = Exj.getFieldFromName(me, 'tpSystemModules');
		tree.root.removeAll();
		 
		me.setVisiblePanelMainModel(false);
		
		me.criteriaFocus('gid');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = null;
	//	me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
	}; // buildListUI
	
	this.beforeBuildPanelMain = function(uiPanelMain, dataIdioma, dataResponse){
		var objTree = uiPanelMain.items[0];
		
		uiPanelMain.hidden = true;
		
		objTree.listeners = {
			'checkchange': function(node, checked){
				if(checked){
                    node.getUI().addClass('exj-checked');
                }
                else{
                    node.getUI().removeClass('exj-checked');
                }
                
                if(node.isLeaf()){
                	/* es nodo hijo */
                	if(node.parentNode){
	                	var checkedToParent = null;
	                	
	                	if(checked){
	                		if(!node.parentNode.getUI().isChecked()){
		                		/* se debe seleccionar el padre */
	                			checkedToParent = checked;
		                	}
	                	}
	                	else{
	                		/* no esta seleccionado el hijo.
	                		   ver si los hermanos estan desactivados, para desactivarlo al padre
	                		*/
	                		if(node.parentNode.getUI().isChecked()){
		                		var isUncheckedAll = true;
		                		node.parentNode.eachChild(function(nodeChild){
		                			if(nodeChild.getUI().isChecked()){
		                				isUncheckedAll = false;
		                				return false; /* break */
		                			}
		                		});
		                		
		                		if(isUncheckedAll){
		                			checkedToParent = checked;
		                		}
	                		}
	                	}
	                	
	                	if(checkedToParent !== null){
							node.parentNode.disableApplyCascade = true;
							node.parentNode.getUI().toggleCheck(checkedToParent);
							node.parentNode.disableApplyCascade = false;                		
	                	}
                	}
                }
                else{
                	/* es nodo padre */
                	if(!node.disableApplyCascade){
	                	/* afectar a los hijos */
	                	node.eachChild(function(nodeChild){
	                		// nodeChild.attributes.axo_section
		
	                		var nodeChildUI = nodeChild.getUI();
	                		
	                		if(!nodeChildUI.isChecked() || !checked){
	                			nodeChildUI.toggleCheck(checked);
	                		}
	                	});
                	}
                }
                
			}
		};
	};
	
	this.onActionSave = function(senderButton, e){
		var valuesCriteria = me.getFieldValuesCriteria();
		if(!valuesCriteria){
			return;
		}
		
		var gid = valuesCriteria.gid;
		
		if(!gid){
			Exj.moe('No se pudo recuperar gid!');
			return;
		}
		
		var tree = Exj.getFieldFromName(me, 'tpSystemModules');
		var nodeUI, isChecked, dataChanged = {
			news: [],
			removes: []
		};
		
		tree.root.cascade(function(){
			if(this.attributes && (this.attributes.originalChecked != undefined) && this.attributes.axo_section){
                nodeUI = this.getUI();
                isChecked = nodeUI.isChecked();
                if(isChecked != this.attributes.originalChecked){
                	if(isChecked){
                		dataChanged.news.push({
                			isChild: (this.isLeaf() ? 1:0),
	                		axo_section: this.attributes.axo_section,
	                		name_comp: this.attributes.name_comp
	                	});
                	}
                	else{
                		dataChanged.removes.push({
                			isChild: (this.isLeaf() ? 1:0),
	                		axo_section: this.attributes.axo_section
	                	});
                	}
                }
            }
		});
		
		if(dataChanged.news.length == 0){
			dataChanged.news = null;
		}
		if(dataChanged.removes.length == 0){
			dataChanged.removes = null;
		}
		
		if(!dataChanged.news && !dataChanged.removes){
			Exj.mou('No se han realizado cambios!', 'Guardar');
			return;
		}
		
		dataChanged = Ext.encode(dataChanged);
		
		Exj.submit({
			method: 'POST',
		    url: hUrl.getActionCustom('commitChanges'),
		    isUrlWithExtras: true,
		    params: {
		    	dataChanged: dataChanged,
		    	gid: gid
		    },
		    idMask: tree.getEl(),
		    mask: 'Guardando, espere por favor...',
		    showResult: false,
		    fnSuccess: function(response){
		    	// var data = response.data;
		    	me.callSearch();
		    }
		});
		
	};
	
	this.onActionCancel = function(senderButton, e){
		var tree = Exj.getFieldFromName(me, 'tpSystemModules');
		var nodeUI, nroUndo=0;
		
		tree.root.disableApplyCascade = true;
		tree.root.getUI().toggleCheck(false);
		tree.root.disableApplyCascade = false;
		
		tree.root.cascade(function(){
			if(this.attributes && (this.attributes.originalChecked != undefined)){
                nodeUI = this.getUI();
                
                if(nodeUI.isChecked() != this.attributes.originalChecked){
                	this.disableApplyCascade = true;
                	nodeUI.toggleCheck(this.attributes.originalChecked);
                	this.disableApplyCascade = false;
                	nroUndo += 1;
                }
            }
		});
		
		if(nroUndo == 0){
			Exj.mou('No se han hecho cambios!', 'Cancelar');
		}
		else{
			Exj.moi(nroUndo+' cambios cancelados.');
		}
		
	};
	
	/* --------AREA DE RENDERS---------- */
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		Exj.moi('Acción no permitida.', senderButton.text);
	};
	this.onActionEdit = function(senderButton, e, r){
		Exj.moi('Acción no permitida.', senderButton.text);
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		Exj.moi('Acción no permitida.', senderButton.text);
		return false;
	};
	
	/* --- INIT --- */
    Exj.ui.modules.RolOptions.superclass.constructor.call(me, {
        id: 'idRolOptions',
        title: '',
        border: false,
        monitorResize: true
    });
};
Ext.extend(Exj.ui.modules.RolOptions, Ext.Panel);
