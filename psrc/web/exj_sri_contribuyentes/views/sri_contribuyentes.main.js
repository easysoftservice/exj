/*
 * Contribuyentes SRI.
 * Fecha: 11/01/2018
 * Autor: BYRON VINICIO CORDOVA MORA
 */
Exj.ui.modules.SriContribuyentes = function(senderMenu, paramsCallBack){
	var me = this;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('sri_contribuyentes');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'sri_contribuyentes',
				nameEditableModel: 'sri_contribuyente',
				nameCriteriaModel: 'sri_contribuyentes'
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
	            labelWidth: 36
			},
			items:[{
                columnWidth: 0.15,
                items: [
                    criteria.getTextField('numero_ruc')
                ]
            }, {
                columnWidth: 0.3,
                labelWidth: 78,
                items: [
                    criteria.getTextField('razon_social'),
                    criteria.getTextField('descripcion_provincia')
                ]
            }, {
                columnWidth: 0.3,
                labelWidth: 108,
                items: [
                    criteria.getTextField('nombre_comercial'),
                    criteria.getTextField('descripcion_canton')
                ]
            }, {
                columnWidth: 0.25,
                labelWidth: 102,
                items: [
                    criteria.getTextField('nombre_fantasia_comercial'),
                    criteria.getTextField('descripcion_parroquia')
                ]
            }]
		});

		return pnlCriteriaMain;
	}; // this.getContentCriteria
	
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('numero_ruc');
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
		newWinSriContribuyente().show(senderButton.getEl());
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinSriContribuyente(r).show(senderButton.getEl());
	};
	
	function newWinSriContribuyente(rSriContribuyente){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rSriContribuyente,
	    	hUrl: hUrl,
	    	nameEntity: 'Contribuyente SRI',
	        width: 510,
	        fnIsValid: function(basicForm){
	        	
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 108
    	});

        winSubmit.addToForm(editable.getTextField('numero_ruc'));
        winSubmit.addToForm(editable.getTextField('razon_social'));
        winSubmit.addToForm(editable.getTextField('nombre_comercial'));

		 winSubmit.addToForm({
             xtype: 'panel',
             layout: 'column',
             border: false,
             defaults: {
                 layout: 'form',
                 border: false,
                 labelAlign: 'top'
             },
             items: [{
                 columnWidth: 0.5,
                 items: [
                     editable.getDateField('fecha_inicio_actividades'),
                     editable.getDateField('fecha_actualizacion')
                 ]
             }, {
                 columnWidth: 0.5,
                 items: [
                     editable.getDateField('fecha_suspension_definitiva'),
                     editable.getDateField('fecha_reinicio_actividades')
                 ]
             }]
         });
		 /* winSubmit.addToForm(editable.getComboBox('estado_contribuyente')); */
		 /* winSubmit.addToForm(editable.getComboBox('clase_contribuyente')); */

		 /* winSubmit.addToForm(editable.getComboBox('tipo_contribuyente')); */

        winSubmit.addToForm({
            xtype: 'panel',
            layout: 'column',
            border: false,
            defaults: {
                layout: 'form',
                border: false
            },
            items: [{
                width: 120,
                labelWidth: 51,
                items: editable.getCheckbox('obligado')
            }, {
                columnWidth: 0.99,
                labelWidth: 120,
                items: editable.getNumberField('numero_establecimiento')
            }]
        });

		 winSubmit.addToForm(editable.getTextField('nombre_fantasia_comercial'));

        winSubmit.addToForm({
            xtype: 'panel',
            layout: 'column',
            border: false,
            defaults: {
                layout: 'form',
                border: false,
                labelAlign: 'top'
            },
            items: [{
                columnWidth: 0.4,
                items: editable.getTextField('calle')
            }, {
                columnWidth: 0.2,
                items: editable.getTextField('numero')
            }, {
                columnWidth: 0.4,
                items: editable.getTextField('interseccion')
            }]
        });

		 /* winSubmit.addToForm(editable.getComboBox('estado_establecimiento')); */

        winSubmit.addToForm({
            xtype: 'panel',
            layout: 'column',
            border: false,
            defaults: {
                layout: 'form',
                border: false,
                labelAlign: 'top'
            },
            items: [{
                columnWidth: 0.3,
                items: editable.getTextField('descripcion_provincia')
            }, {
                columnWidth: 0.3,
                items: editable.getTextField('descripcion_canton')
            }, {
                columnWidth: 0.4,
                items: editable.getTextField('descripcion_parroquia')
            }]
        });
	    
	    if(rSriContribuyente){
	    	winSubmit.bindToContainer(rSriContribuyente, 'numero_ruc');
	    }
	    
		return winSubmit;
	}; // newWinSriContribuyente
	
	
	/* --- INIT --- */
    Exj.ui.modules.SriContribuyentes.superclass.constructor.call(me, {
        id: 'idSriContribuyentes',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.SriContribuyentes, Ext.Panel);
