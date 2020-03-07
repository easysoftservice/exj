/*
 * Ayuda.
 * Fecha: 10/07/2012
 * Autor: Byron Córdova
 */
Exj.ui.modules.AppHelp = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('helps');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'helps',
				nameEditableModel: 'help',
				nameCriteriaModel: 'helps'
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
	            	criteria.getTextField('name_help')
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
		me.criteriaFocus('name_help');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		
		Exj.action.grid.onCustom(me.gridMainList, _onViewHelp, 'viewhlp', true);
		
	}; // buildListUI
	
	/* --------AREA DE RENDERS---------- */
	
	this.renderDataCats = function(valueDataCats, data){
		if(!valueDataCats){
			return '';
		}
		
		var titsCats = new Array(), itemHTML;
		for(var i=0, itemCat;i< valueDataCats.length; i++){
			itemCat = valueDataCats[i];
			
			itemHTML = '<h3>'+ itemCat.titCat+'</h3>';
			if(itemCat.titsCnt && itemCat.titsCnt.length){
				itemHTML += ' '+ itemCat.titsCnt.join(' | ');
			}
			
			titsCats.push(itemHTML);
		}
		
		titsCats = titsCats.join('<br/>');
		
	//	titsCats = '<div class="'+data.iconCls+'">'+titsCats+'</div>';
		
	    return titsCats;
	};
	
	/* --------FIN AREA DE RENDERS---------- */
	
	this.onActionNew = function(senderButton, e){
		Exj.moi('Adicione la ayuda por el backend', 'No soportado');
	};
	this.onActionEdit = function(senderButton, e, r){
		Exj.moi('Edite la ayuda por el backend', 'No soportado');
	};
	
	
	function _onViewHelp(btn, e, r){
		if(!r.data.moduleName){
			Exj.moi('Ayuda no disponible', r.data.nameMnu+' - Ayuda');
			return;
		}
		
		Exj.showHelp({
			url: me.hUrl.getActionHelpViewCmp({
					nameCmp: r.data.moduleName
				}, 
				me.hUrl
			),
			titleModule: r.data.nameMnu,
			iconCls: r.data.iconCls
		});
	};
	
	/* --- INIT --- */
    Exj.ui.modules.AppHelp.superclass.constructor.call(me, {
        id: 'idAppHelp',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.AppHelp, Ext.Panel);
