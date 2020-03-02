/*
 * Deploy.
 * Deploys.
 * Fecha: 02/08/2015
 * Autor: Byron Córdova
 */
Exj.ui.modules.Deploys = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('deploys');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'deploys',
				nameEditableModel: 'deploy',
				nameCriteriaModel: 'deploys'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaNumFiles = Exj.newPanelCols({
			title: 'Archivos',
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 30
			},
			items:[{
	            columnWidth: 0.25,
	            items: [
	            	criteria.getNumberField('num_filesphp')
	            ]
	        }, {
	            columnWidth: 0.25,
	            labelWidth: 54,
	            items: [
	            	criteria.getNumberField('num_filesjs')
	            ]
	        }, {
	            columnWidth: 0.25,
	            items: [
	            	criteria.getNumberField('num_filescss')
	            ]
	        }, {
	            columnWidth: 0.25,
	            labelWidth: 51,
	            items: [
	            	criteria.getNumberField('num_filesimg')
	            ]
	        }]
		});
		
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 42
			},
			items:[{
	            columnWidth: 0.40,
	            items: [
	            	criteria.getTextField('version_dpy')
	            ]
	        }, {
	            columnWidth: 0.60,
	            items: [
	            	pnlCriteriaNumFiles
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
		me.criteriaFocus('version_dpy');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		
		Exj.action.grid.onCustom(me.gridMainList, _onVerComps, 'view_comps', false);
		Exj.action.grid.onCustom(me.gridMainList, _onCopyPreProduccion, 'copy_prod_local', true, true);
		Exj.action.grid.onCustom(me.gridMainList, _onBK_DB, 'bk_db', true, true);
		Exj.action.grid.onCustom(me.gridMainList, _onOfuscarPHP, 'ofs_php', true, true);
	}; // buildListUI
	
	function _onOfuscarPHP(senderButton, e, recordEditable){
		Exj.submitAction({
			url: hUrl.getActionCustom('ofuscarPHP'),
			senderButton: senderButton,
	        params: {
	        	id_deploy: recordEditable.data.id_deploy
	        },
			fnSuccess: function(response){
				me.gridMainList.store.reload();
			}
		});
	};
	
	function _onBK_DB(senderButton, e, recordEditable){
		Exj.submitAction({
			url: hUrl.getActionCustom('bkDB'),
			timeout: 60000,
			mask: 'Ejecutando backup de la base de datos versión: '+recordEditable.data.version_dpy,
	        params: {
	        	id_deploy: recordEditable.data.id_deploy,
	        },
			fnSuccess: function(response){
				var nameFileBKDB = response.data;
				Exj.moi('Archivo generado:<br/>'+ nameFileBKDB);
				
				me.gridMainList.store.reload();
			}
		});
	};
	
	
	function _onCopyPreProduccion(senderButton, e, recordEditable){
		Exj.submitAction({
			url: hUrl.getActionCustom('copyToPreProduction'),
			timeout: 60000,
			mask: 'Copiando a Pre-Producción versión: '+recordEditable.data.version_dpy,
	        params: {
	        	id_deploy: recordEditable.data.id_deploy,
	        },
			fnSuccess: function(response){
				// var url_release = response.data;
				// alert('url_release: '+url_release);
				
				me.gridMainList.store.reload();
			}
		});
	};
	
	
	function _onVerComps(btn, e){
//		hUrl.setController('deploys');

		var hUrlComp = new Exj.HUrl({
			controller: 'comps',
        	option: hUrl.getOption()
		});
		
    	Exj.showListModel({
    		hUrl: hUrlComp,
    		idMask: me.gridMainList.getEl(),
    		senderButton: btn,
    		width: Exj.calcWidth(81),
	        params: {
	        	
	        }
    	}, this);
	};
	
	
	/* --------AREA DE RENDERS---------- */
	this.renderNumFilesTot = function(value, p, r){
		return (r.data.num_filesphp + r.data.num_filesjs+ r.data.num_filescss+r.data.num_filesimg+r.data.num_filesotros);
	};
	
	
	this.renderIsCopiedPreProd = function(value, p, r){
		if(r.data.is_copied_preprod){
			return Exj.getLinkHTML(r.data.url_dpy, Exj.rendererTextSiNo(value));
		}
		
		return Exj.renderURLDownload(r.data.url_dpy)+' '+Exj.rendererTextSiNo(value);
	};
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		newWinDeploy().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinDeploy(r).show(senderButton.getEl());
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		/*
		var idParent = r.get('nationality_cou');
		if(idParent === 0){
			Exj.moi('No se puede eliminar el tipo principal:<br/>'+ r.data.num_filesjs);
			return false;
		}
		*/
	};
	
	function newWinDeploy(rDeploy){
	    var editable = me.editable; // shortcut
	    
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rDeploy,
	    	hUrl: hUrl,
	    	timeOutSec: 72,
	    	nameEntity: 'Deploy',
	        width: 540,
	        fnIsValid: function(basicForm){
	        	
	        	if(!rDeploy){
		        	if(Exj.isModeDebug){
		        		Exj.moe('En el cliente, está en modo de debug, cambie a modo NO debug.');
		        		return false;
		        	}
	        	}
	        	
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        	
	        	/*
				Exj.msgQuestion({
					msg: 'Deseas copiar el Deploy a PreProducción',
					fnYes: function(){
						_onCopyPreProduccion('', '', rDeploy);
					}
				});
	        	*/
	        }
	    }, {
    		labelWidth: 69
    	});
	    
	    /*
		winSubmit.fnGetFieldsExtras = function(){
        	return [
        		xxx
        	]
        };
        */

	    var txfVersion = editable.getTextField('version_dpy');
	    if(rDeploy){
	    	txfVersion.setValue(rDeploy.data.version_dpy);
	    	txfVersion.setDisabled(true);
	    }
	    else{
	    	txfVersion.setValue(Exj.verApp);
	    }
	    
		/*
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		version_dpy: Exj.verApp
        	}]
        };
	    */
	    
	    winSubmit.addToForm(txfVersion);
	    if(rDeploy){
		    var txfPath = Exj.newTextField({
		        fieldLabel: 'Path',
		        name: 'path_dpy',
		        anchor: '99%',
		        style: 'color: blue;',
		        readOnly : true
		    });
	    	
	    	winSubmit.addToForm(txfPath);
	    	
		    var txfFileBKDB = Exj.newTextField({
		        fieldLabel: 'DB BK',
		        name: 'file_bkdb',
		        anchor: '90%',
		        style: 'color: green;',
		        readOnly : true
		    });
	    	
	    	winSubmit.addToForm(txfFileBKDB);	    	
	    }
	    winSubmit.addToForm(editable.getTextArea('obs_dpy'));
	    
	    if(rDeploy){
	    	winSubmit.bindToContainer(rDeploy);
	    }
	    
		return winSubmit;
	}; // newWinDeploy
	
	
	/* --- INIT --- */
    Exj.ui.modules.Deploys.superclass.constructor.call(me, {
        id: 'idDeploys',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.Deploys, Ext.Panel);
