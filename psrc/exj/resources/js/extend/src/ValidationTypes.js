/*
* ValidationTypes
* Validaciones de Tipos
* Autor: Byron Córdova
*/

Ext.apply(Ext.form.VTypes, {
	/* rango de fechas */
    daterange : function(val, field) {
        var date = field.parseDate(val);

        if(!date){
        	// field.reset();
            return false;
        }
        if (field.startDateField) {
            var start = Ext.getCmp(field.startDateField);
            if(!start){
            	Exj.moe('Not found: '+field.startDateField, ' Validation dates');
            	return false;
            }
            if (!start.maxValue || (date.getTime() != start.maxValue.getTime())) {
                start.setMaxValue(date);
                start.validate();
            }
        }
        else if (field.endDateField) {
            var end = Ext.getCmp(field.endDateField);
            if(!end){
            	Exj.moe('Not found: '+field.endDateField, ' Validation dates');
            	return false;
            }
            if (!end.minValue || (date.getTime() != end.minValue.getTime())) {
                end.setMinValue(date);
                end.validate();
            }
        }
        /*
         * Always return true since we're only using this vtype to set the
         * min/max allowed values (these are tested for after the vtype test)
         */
        return true;
    },

    /* verificación de passwords */
    password: function(val, field) {
        if (field.initialPassField) {
            var pwd = Ext.getCmp(field.initialPassField);
            return (val == pwd.getValue());
        }
        return true;
    },
    passwordText: 'Passwords do not match',
    
    numdoc: function(val, field) {
    	if(field.allowBlank && !val){
    		return true;
    	}
    	
    	if(val.length <= 3){
    		this.numdocText = 'Número de documento inválido';
    		return false;
    	}
    	
    	var typeDoc = '';
    	var compType = null;
		if(field.typeDocComp){
			compType = Ext.getCmp(field.typeDocComp);
			
			if(!compType.isValid()){
				this.numdocText = 'Seleccione DOCUMENTO';
				return false;
			}
			
			if(compType.getRawValue){
				typeDoc = compType.getRawValue();
			}
			else{
				typeDoc = compType.getValue();
			}
			if(!typeDoc){
				this.numdocText = 'Seleccione DOCUMENTO';
				
				Exj.moi('Seleccione el tipo de documento', function(){
					compType.focus();
				});
				field.reset();
				return false;
			}
			
			// add compotamiento de select del tipo de doc
			if(!compType.fnSelDoc){
				compType.fnSelDoc = function(senderCmb, r, index){
					field.validate();
					if(field.isValid()){
						field.focus(false, 30);
					}
					else{
						field.focus(true, 30);
					}
				};
				compType.addListener('select', compType.fnSelDoc);
			}
		}
		
		if(!field.fnSelNumDoc){
			field.fnSelNumDoc = function(senderCmb, r, index){
				field.validate();
			};
			field.addListener('select', field.fnSelNumDoc);
		}

		var me = this;
		function _setMsgInvalid(msg){
			me.numdocText = typeDoc;
			if(me.numdocText){
				me.numdocText += '. ';
			}
			me.numdocText += msg;
			
			return false;
		};
		
		var isCedula = (typeDoc == 'CEDULA');
		var isRUC = (typeDoc == 'RUC');
		var isPASAPORTE = (typeDoc == 'PASAPORTE');
		var isSSN = (typeDoc == 'SOCIAL SECURITY');
		if(typeDoc){
			if(!isCedula && !isRUC && !isPASAPORTE && !isSSN){
				return _setMsgInvalid('Unsupported document type');
			}
		}
		
		if(isSSN){
			return true;
		}
		
		if(isCedula || isRUC){
			// buscar el caracter q no es un número
			var charInvalid = val.match('[^0-9]');
			if(charInvalid !== null){
				if(charInvalid == ' '){
					return _setMsgInvalid('No está permitido espacios en blanco');
				}
				return _setMsgInvalid('No es válido el caracter: '+charInvalid);
			}
			
			if(val.length < 9 || val.length > 15){
				return _setMsgInvalid('Número de documento inválido');
			}
			
			if(isCedula && val.length != 10){
				return _setMsgInvalid('Debe tener 10 números');
			}
		}
		else{
			if(val.toUpperCase() !== val){
				val = val.toUpperCase();
				field.setRawValue(val);
			}
			// se puede admitir letras o números
			var charInvalid = val.match('[^0-9^A-Z]');
			if(charInvalid !== null){
				if(charInvalid == ' '){
					return _setMsgInvalid('No blank spaces are allowed');
				}
				return _setMsgInvalid('Not valid on character: '+charInvalid);
			}
		}
		
		if(isPASAPORTE){
			return true;
		}
		
		function _getCharDig(indexVal){
			var charx = val.substr(indexVal,1);
			if(charx === ''){
				return charx;
			}
			
			if(charx.match('[0-9]')){
				charx = parseInt(charx);
			}
			
			return charx;
		};
		
		var d1 = _getCharDig(0);
		var d2 = _getCharDig(1);
		var d3 = _getCharDig(2);
		var d4 = _getCharDig(3);
		var d5 = _getCharDig(4);
		var d6 = _getCharDig(5);
		var d7 = _getCharDig(6);
		var d8 = _getCharDig(7);
		var d9 = _getCharDig(8);
		var d10 = _getCharDig(9);
		
		/* El tercer digito es: 
			9 para sociedades privadas y extranjeros
			6 para sociedades publicas
			menor que 6 (0,1,2,3,4,5) para personas naturales
		*/
		if(isCedula || isRUC){
			if (d3==7 || d3==8){
				return _setMsgInvalid('El tercer dígito es inválido');
			}
		}
		
		/* Solo para personas naturales (modulo 10) */
		var nat = false, modulo=11;
		var pub = false;
		var pri = false;
		var p1=0, p2=0, p3=0, p4=0, p5=0, p6=0, p7=0, p8=0, p9=0;
		if (d3 < 6){
			nat = true;
			p1 = d1 * 2; if (p1 >= 10) p1 -= 9;
			p2 = d2 * 1; if (p2 >= 10) p2 -= 9;
			p3 = d3 * 2; if (p3 >= 10) p3 -= 9;
			p4 = d4 * 1; if (p4 >= 10) p4 -= 9;
			p5 = d5 * 2; if (p5 >= 10) p5 -= 9;
			p6 = d6 * 1; if (p6 >= 10) p6 -= 9;
			p7 = d7 * 2; if (p7 >= 10) p7 -= 9;
			p8 = d8 * 1; if (p8 >= 10) p8 -= 9;
			p9 = d9 * 2; if (p9 >= 10) p9 -= 9;
			modulo = 10;
		} else if(d3 == 6){
			/* 
			Solo para sociedades publicas (modulo 11) 
			Aqui el digito verficador esta en la posicion 9, 
			en las otras 2 en la pos. 10
			*/
			pub = true;
			p1 = d1 * 3;
			p2 = d2 * 2;
			p3 = d3 * 7;
			p4 = d4 * 6;
			p5 = d5 * 5;
			p6 = d6 * 4;
			p7 = d7 * 3;
			p8 = d8 * 2;
			p9 = 0;
		} else if(d3 == 9) {
			/* Solo para entidades privadas (modulo 11) */			
			pri = true;
			p1 = d1 * 4;
			p2 = d2 * 3;
			p3 = d3 * 2;
			p4 = d4 * 7;
			p5 = d5 * 6;
			p6 = d6 * 5;
			p7 = d7 * 4;
			p8 = d8 * 3;
			p9 = d9 * 2;
		}

		if(isCedula && !nat){
			if(pub){
				return _setMsgInvalid('Este número de documento es de tipo: ruc de empresa del sector público');
			}
			if(pri){
				return _setMsgInvalid('Este número de documento es de tipo: ruc de entidad privada');
			}
		}
		
		/* -------------------- */
		var suma = p1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9;
		var residuo = suma % modulo;
		
		/* Si residuo=0, dig.ver.=0, caso contrario 10 - residuo*/
		var digitoVerificador = residuo==0 ? 0: modulo - residuo;
		
		/* ahora comparamos el elemento de la posicion 10 con el dig. ver.*/
		if (pub){
			if (digitoVerificador != d9){
				return _setMsgInvalid('El ruc de la empresa del sector público es incorrecto.');
			}
			
			/* El ruc de las empresas del sector publico terminan con 0001*/
			/*
			if (val.substr(9,4) != '0001'){
				return _setMsgInvalid('El ruc de la empresa del sector público debe terminar con 0001');
			}
			*/
			
			if(isRUC && val.length != 13){
				return _setMsgInvalid('RUC de la empresa del sector público debe tener 13 dígitos y terminar en 0001, 0002, etc');
			}
		}
		else if(pri){
			if (digitoVerificador != d10){
				return _setMsgInvalid('Número de documento de la empresa del sector privado es incorrecto.');
			}
			/*
			if (val.substr(10,3) != '001' ){
				return _setMsgInvalid('El ruc de la empresa del sector privado debe terminar con 001');
			}
			*/
			
			if(isRUC && val.length != 13){
				return _setMsgInvalid('RUC de la empresa del sector privado debe tener 13 dígitos');
			}
		}
		else if(nat){
			if (digitoVerificador != d10){
				if(isCedula){
					return _setMsgInvalid('Número de cédula de la persona natural es incorrecto.');
				}
				if(isRUC){
					return _setMsgInvalid('RUC de persona natural es incorrecto.');
				}
				return _setMsgInvalid('Número de documento incorrecto!');
			}
			
			if(isRUC && val.length != 13){
				return _setMsgInvalid('RUC de persona natural debe tener 13 dígitos y terminar en 001, 002, etc');
			}
		}

    	if(!typeDoc){
    		if(nat && val.length == 10){
    			isCedula = true;
    			typeDoc = 'CEDULA';
    			// compType.setValue();
    		}
    		
    		return _setMsgInvalid('Sin tipo de doc en Construcción');
    	}
			
    	return true;
    }, /* numdoc */
	numdocText: 'Número de documento inválido'
});
