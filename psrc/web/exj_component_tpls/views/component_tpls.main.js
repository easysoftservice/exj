/*
 * {labelComponents}.
 * Fecha: {date_current}
 * Autor: {name_author}
 */
Exj.ui.modules.ComponentTpls = function(senderMenu, paramsCallBack){
	var me = this;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('component_tpls');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'component_tpls',
				nameEditableModel: 'component_tpl',
				nameCriteriaModel: 'component_tpls'
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
	            labelWidth: 60
			},
			items:[{js.criteria.items}]
		});

		return pnlCriteriaMain;
	}; // this.getContentCriteria
	
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('{js.criteria.field.focus}');
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
		newWinComponentTpl().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinComponentTpl(r).show(senderButton.getEl());
	};
	
	function newWinComponentTpl(rComponentTpl){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rComponentTpl,
	    	hUrl: hUrl,
	    	nameEntity: '{labelComponent}',
	        width: 390,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 81
    	});

    	/*js.winSubmit.addToForm*/
	    
	    if(rComponentTpl){
	    	winSubmit.bindToContainer(rComponentTpl, '{js.winSubmit.field.focus}');
	    }
	    
		return winSubmit;
	}; // newWinComponentTpl
	
	
	/* --- INIT --- */
    Exj.ui.modules.ComponentTpls.superclass.constructor.call(me, {
        id: 'idComponentTpls',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.ComponentTpls, Ext.Panel);
