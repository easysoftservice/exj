/*
 * Usuario Perfil
 * Fecha: 08/06/2015
 * Autor: Byron Córdova
 */

Exj.ui.modules.UsrPerfil = function(senderMenu, paramsCallBack){
	var me = this;
	var winSubmit;
	var hUrl = paramsCallBack.getHandlerUrl();
	hUrl.setController('usr_perfil');
	
	this.hUrl = hUrl;
	
	function _getItemsUsrPerfil(editable, editableModel){
		var itemsUI = new Array();
		
		/*
		var hPersonas = new Exj.ui.helpers.Personas(editable.getCfgCmp('id_persona'), false);
		me._pnlPersona = hPersonas.getPanelPersona();
		me._cmbEmpresa = editable.getComboBox('id_empresa');
		
	    itemsUI.push(me._pnlPersona);
	    itemsUI.push(me._cmbEmpresa);
	    */
	    
	    itemsUI.push(editable.getHidden('id_persona'));
	    
	    itemsUI.push({
	    	xtype: 'panel',
	    	layout: 'form',
	    	title: 'Datos Personales',
	    	labelWidth: 192,
	    	items: [
	    		editable.getTextField('nro_doc_persona'),
	    		editable.getTextField('noms_apes'),
	    		editable.getTextField('tlf_persona'),
	    		editable.getComboBox('id_sit'),
	    		editable.getTextArea('dir_person'),
	    		editable.getTextField('user_email')
	    	]
	    });
	    
	    itemsUI.push({
	    	xtype: 'panel',
	    	layout: 'form',
	    	title: 'Cambiar Contraseña',
	    	labelWidth: 171,
	    	items: [
	    		editable.getTextField('user_pwd_current'),
	    		editable.getTextField('user_pwd1'),
	    		editable.getTextField('user_pwd2')
	    	]
	    });
	    
		return itemsUI;
	};
	
	Exj.showEditableModel({
		title: senderMenu.text,
		iconCls: senderMenu.iconCls,
		recordEditable: null,
		idValue: 0,
		hUrl: me.hUrl,
		width: 450,
		/* idMask: gridTpl.getEl(), */
		labelWidth: 60,
        params: {
        },
        fnGetParamsData: function(senderWin, basicForm){
        	var id_persona = basicForm.findField('id_persona').getValue();
        	if(id_persona){
        		id_persona = parseInt(id_persona);
        	}
        	
        	senderWin.setId(id_persona);
        	
        	return [{
        		id_persona: id_persona
        	}]
        	
        },
		getItemsUI: _getItemsUsrPerfil,
		fnSuccessSave: function(form, result, action){
			
		},
		fnIsValid: function(basicForm, formPnl){
			
			
			
			return true;
		},
		fnSuccess: function(editable, editableModel){
			
		},
		fnAfterShowWin: function(senderWin, isNew){
			setTimeout(function(){
				var bf = senderWin.getBasicForm();
				bf.findField('user_pwd_current').setValue('');
				bf.findField('user_pwd1').setValue('');
				bf.findField('user_pwd2').setValue('');
				
				bf.findField('noms_apes').focus();
		    }, 330);
		}
	}, me);
	
	return false;
};
