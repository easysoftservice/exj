/*
 * Helpdesks.
 * Fecha: 19/03/2015
 * Autor: Byron C�rdova
 */
Exj.ui.modules.Helpdesks = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('helpdesks');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'helpdesks',
				nameEditableModel: 'helpdesk',
				nameCriteriaModel: 'helpdesks'
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
	            	criteria.getTextField('name_hld')
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
	* Antes de buscar, retornar false para evitar la b�squeda
	*/
	this.onBeforeSearch = function(paramsCriteria, formPanel){

	};
	
	/**
	* Despu�s de buscar, si la llamada fu� satisfatoria
	*/
	this.onAfterSearch = function(records, options, formPanel){
		
	}; // this.onAfterSearch
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('name_hld');
	};
	
	
	/**
	* Construye la UI. Seg�n el modelo list
	* Llamado desde la base de la aplicaci�n, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la funci�n. T�cnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
	}; // buildListUI
	
	/* --------AREA DE RENDERS---------- */
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		newWinHelpdesk().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinHelpdesk(r).show(senderButton.getEl());
	};
	
	/*
	* Antes de eliminar
	*/
	/*
	this.onBeforeDel = function(senderButton, e, r){
	};
	*/
	
	function newWinHelpdesk(rHelpdesk){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rHelpdesk,
	    	hUrl: hUrl,
	    	nameEntity: 'Mesa de Ayuda',
	        width: 360,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 45
    	});
	    
    	/*
		winSubmit.fnGetParamsData = function(senderWin, basicForm){
        	return [{
        		
        	}]
        };
		*/
        
	    /*
		winSubmit.fnGetFieldsExtras = function(){
        	return [
        		xxx
        	]
        };
        */
	    
	    winSubmit.addToForm(editable.getTextField('name_hld'));
	    
	    if(rHelpdesk){
	    	winSubmit.bindToContainer(rHelpdesk);
	    }
	    
		return winSubmit;
	}; // newWinHelpdesk
	
	
	/* --- INIT --- */
    Exj.ui.modules.Helpdesks.superclass.constructor.call(me, {
        id: 'idHelpdesks',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.Helpdesks, Ext.Panel);
