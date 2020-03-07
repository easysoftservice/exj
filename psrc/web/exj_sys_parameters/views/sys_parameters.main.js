/*
 * Administración.
 * Parámetros del Sistema.
 * Fecha: 05/12/2014
 * Autor: Byron Córdova
 */

Exj.ui.modules.SysParameters = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('sys_parameters');
	
	this.hUrl = hUrl;
		
	paramsCallBack.getUI = function(){
		return {
			actions: {
				nameListModel: 'sys_parameters',
				nameEditableModel: 'sys_parameter',
				nameCriteriaModel: 'sys_parameters'
			}
		}
	};
	
	this.getContentCriteria = function(criteria){
		var criteria = me.criteria;
		
		var pnlCriteriaMain = Exj.newPanelCols({
			defaults: {
				border: false,
	            xtype: 'fieldset',
	            labelWidth: 45
			},
			items:[{
	            columnWidth: 0.25,
	            items: [
	            	criteria.getTextField('code_param')
	            ]
	        }, {
	            columnWidth: 0.25,
	            items: [
	            	criteria.getTextField('name_param')
	            ]
	        }, {
	            columnWidth: 0.25,
	            labelWidth: 78,
	            items: [
	            	criteria.getComboBox('type_param')
	            ]
	        }, {
	            columnWidth: 0.25,
	            items: [
	            	criteria.getTextField('value_param')
	            ]
	        }]
		});

		return pnlCriteriaMain;
		/*
		return [
			pnlCriteriaMain
		];
		*/
		
	}; // this.getContentCriteria
	
	this.onAfterReset = function(btnReset, formPanel){
		me.criteriaFocus('code_param');
	};
	
	
	/**
	* Construye la UI. Según el modelo list
	* Llamado desde la base de la aplicación, y trae los moledos del servidor.
	* Note: No cambiar el nombre de la función. Técnica ORM
	*/
	this.buildListUI = function(listModel, dataIdioma, dataResponse){
		me.gridMainList = Exj.newGridPanelFromListModel(listModel, hUrl.getActionView());
		
		Exj.action.grid.onCustom(me.gridMainList, _onActionFixTime, 'fix_time_srv', false);
	}; // buildListUI
	
	/* --------AREA DE RENDERS---------- */
	
	/* --------FIN AREA DE RENDERS---------- */
	
	function _getHUrlCouParams(){
    	var hUrlTrx = new Exj.HUrl({
    		option: hUrl.getOption(),
    		controller: 'cou_params'
    	});
		
    	return hUrlTrx;
	};
	
	
	function _onActionFixTime(senderButton, e){
		var _tifSrv;
		Exj.showEditableModel({
			title: 'Fijar tiempo del servidor',
			senderButton: senderButton,
			hUrl: _getHUrlCouParams(),
			idMask: me.gridMainList.getEl(),
			width: 300,
			labelWidth: 99,
	        params: {
	        	
	        },
	        fnGetParamsData: function(senderWin, basicForm){
	        	return {
	        	};
	        },
			getItemsUI: _getItemsUIWin,
			fnSuccessSave: function(form, result, action){
				// me.gridMainList.store.reload();
			},
			fnIsValid: function(){
				// alert(_tifSrv.getValue());
				
				return true;
			}
		}, this);
		
		function _getItemsUIWin(editable, editableModel){
	    	_tifSrv = editable.getTimeField('offset_time');
	    	// _tifSrv.setRawValue(_tifSrv.tiempoActual);
	    	
	    	var txfInfoRegister = Exj.newTextField({
	    		fieldLabel: 'Ult Cambio',
	    		anchor: '90%',
	    		value: editableModel.data.modificado_dt,
	    		disabled: true
	    	});
	    	
	    	var txfInfoTimeAct = Exj.newTextField({
	    		fieldLabel: 'Tiempo Actual',
	    		width: _tifSrv.width,
	    		value: _tifSrv.tiempoActual,
	    		readOnly: true
	    	});
	    	
	    	var txfInfoTimeSrv = Exj.newTextField({
	    		fieldLabel: 'Tiempo del Servidor',
	    		width: _tifSrv.width,
	    		value: _tifSrv.tiempoSrv,
	    		readOnly: true
	    	});
	    	
	    	return[
	    		txfInfoRegister,
	    		txfInfoTimeSrv,
	    		txfInfoTimeAct,
	    		_tifSrv
	    	]
		};
		
		
	}; // _onActionFixTime	
	
	this.onActionNew = function(senderButton, e){
		Exj.moi('No disponible para crear parámetros del sistema.');
	};
	this.onActionEdit = function(senderButton, e, r){
		newWinParameter(r).show(senderButton.getEl());
	};
	
	/*
	* Antes de eliminar
	*/
	this.onBeforeDel = function(senderButton, e, r){
		Exj.moi('No se permite elimnar parámetros del sistema.');
		return false;
	};
	
	function newWinParameter(rParameter){
	    var editable = me.editable; // shortcut
		
	    var winSubmit = new Exj.WinSubmit({
	    	recordEditable: rParameter,
	    	hUrl: hUrl,
	    	nameEntity: 'Parámetro del Sistema',
	        width: 333,
	        fnIsValid: function(basicForm){
	        	return true;
	        },
	        fnSuccess: function(){
	        	me.gridMainList.store.reload();
	        }
	    }, {
    		labelWidth: 63
    	});
	    
	    if(rParameter){
			winSubmit.fnGetParamsData = function(senderWin, basicForm){
	        	return [{
	        		id_empresa: rParameter.data.id_empresa
	        	}]
	        };
	    }
	    
	    var cmbTypeParam = editable.getComboBox('type_param');
	    var txaValueParam = editable.getTextArea('value_param');
	    
	    winSubmit.addToForm(editable.getTextField('code_param'));
	    winSubmit.addToForm(editable.getTextField('name_param'));
	    winSubmit.addToForm(cmbTypeParam);
	    winSubmit.addToForm(txaValueParam);
	    
	    
	    txaValueParam._typeParam = (rParameter ? rParameter.data.type_param: '');
	    
	    cmbTypeParam.addListener('select', function(senderCombo, rType, index){
	    	txaValueParam._typeParam = rType.data.value;
	    	
	    	if(!txaValueParam.isValid()){
	    //	if(txaValueParam.getActiveError()){
	    		Exj.moe(txaValueParam.getActiveError(), 'ERROR', function(){
	    			txaValueParam.focus();
	    		});
	    	}
	    });
	    
	    
	    if(rParameter){
	    	winSubmit.bindToContainer(rParameter);
	    }

    	txaValueParam.validator = function(value){
    		var responseValidator = true, testValue = null;
    		
    	//	Exj.mou('txaValueParam.validator value: '+ value);
    		
    		if(!this._typeParam){
    			responseValidator = 'Por favor seleccione el tipo de dato';
    			return responseValidator;
    		}
    		
    		if(value.trimLeft){
    			testValue = value.trimLeft();
    		}
    		else{
    			testValue = value;
    		}
    		
    		if(value && testValue.length != value.length){
    			value = testValue;
    			this.setValue(testValue);
    		}
    		
    		if(!value){
    			return responseValidator;
    		}
    		
    		switch(this._typeParam){
    			case 'int':
    			case 'float':
    				testValue = Ext.num(value, null);
    				if(testValue === null){
    					responseValidator = value+' no es un valor numérico';
    				}
    				
    				if(this._typeParam == 'int' && responseValidator === true){
    					if(parseInt(testValue) != testValue){
    						responseValidator = value+' no es un valor int - es tipo float';
    					}
    					else{
    						testValue = parseInt(testValue);
    					}
    				}
    				
    				if(responseValidator === true && value != testValue || (value.length != (testValue+'').length)){
    					this.setValue(testValue);
    				}
    				
    			break;

    			case 'date':
    				testValue = Date.parseDate(value, Exj.FormatDate);
    				if(!testValue){
    					responseValidator = value+' no es una fecha válida - el formato es dd/mm/YYYY';
    				}
    				else{
    					testValue = testValue.dateFormat(Exj.FormatDate);
    					if(testValue != value){
    						this.setValue(testValue);
    					}
    				}
    				
    			break;
    			
    			case 'datetime':
    				testValue = Date.parseDate(value, Exj.FormatDateTime);
    				if(!testValue){
    					responseValidator = value+' no es una fecha válida - el formato es dd/mm/YYYY HH:ii:ss';
    				}
    				else{
    					testValue = testValue.dateFormat(Exj.FormatDateTime);
    					if(testValue != value){
    						this.setValue(testValue);
    					}
    				}
    			break;

    			case 'object':
    				var firstChar = value.charAt(0);
    				if(firstChar != "{" && firstChar != "["){
    					responseValidator = value+' no es un objeto';
    				}
    				
					if(responseValidator === true){
						try{
							testValue = eval(value);
							if(Ext.isFunction(testValue)){
								responseValidator = value+' es una función';
							}
							/*
							if(responseValidator === true){
								testValue = Ext.decode(value);
							}
							*/
						}
						catch(ex){
							if(ex && ex.message){
								responseValidator = ex.message;
							}
							else{
								responseValidator = ex;
							}    					
						}
						
					}
    			break;
    		}
    		
    		return responseValidator;
    	};
    		    
		return winSubmit;
	}; // newWinParameter
	
	
	/* --- INIT --- */
    Exj.ui.modules.SysParameters.superclass.constructor.call(me, {
        id: 'idSysParameters',
        title: '',
        border: false
    });
};
Ext.extend(Exj.ui.modules.SysParameters, Ext.Panel);
