/*
System: GYMCloud
By: EASYSOFT SERVICE.
Date: 13/02/2020
Quito - Loja - Ecuador
Copyright(c) 2014-2015, GYM Cloud.
*/

var HandleWin = function(){
    var ventanasActivas = new Array();
    
    function add(idWin){
        ventanasActivas.push(idWin);
    };
    
    function remove(idWin, prefixExtra){
        var index = getIndex(idWin, prefixExtra);
        if (index == -1){
            return false;
        }
        
        ventanasActivas[index] = '';
        
        return true;
    };
    
    function getIndex(idSearch, prefixExtra){
        if(prefixExtra === undefined){
            prefixExtra = '';
        }
        
        var i= -1;
        while(++i < ventanasActivas.length){
            var idWin = ventanasActivas[i];
            if (prefixExtra){
                idWin = prefixExtra+idWin;
            }
            if(idWin == idSearch){
                break;
            }
        }
        
        return i;
    };
    
    function exist(idSearch, prefixExtra){
        return (getIndex(idSearch, prefixExtra) != -1)
    };
    
    function count(){
        var i= -1;
        var n = 0;
        while(++i < ventanasActivas.length){
            if(ventanasActivas[i] != ''){
                ++n;
            }
        }
        
        return n;
    };
    
    function reset(){
        ventanasActivas = new Array();
    };
    
    return {
        add: add, 
        remove: remove, 
        getIndex: getIndex,
        exist: exist,
        count: count, 
        reset: reset
    }
}; // class HandleWin


Ext.onReady(function(){
    var vpApp;
    var pInfo;
    var _tabInfo=null;
    var _tpMain=null;
    var _cmbEmpresa=null;
    var pNorte;
 //   var _USE_TABS_MAIN = true;
    var _USE_TABS_MAIN = false;
    
    
    Ext.QuickTips.init(); /* ESTO ACTIVA A TODO EL SISTEMA LOS TOOLTIPS */
    Exj.loadDataGlobal();
    
    var _hw = new HandleWin();
	var _huBaseui = new Exj.HUrl({
		option: 'exj_baseui',
		controller: 'baseui'
	});
    
	function _onAddOptionMenu(scope){
		if(_cmbEmpresa){
        	_cmbEmpresa.setDisabled(true);
    	}
    	
    	/*
		if(_USE_TABS_MAIN){
			pNorte.getTopToolbar().setVisible(false);
		}
		else{
			
			
			pNorte.getBottomToolbar().setVisible(false);
		}
		*/
		
		pNorte.getTopToolbar().setVisible(false);
    	
        vpApp.doLayout();
	};
	function _onResetOptionMenu(scope, disableInfoHeader){
		if(disableInfoHeader === undefined){
			disableInfoHeader = false;
		}
		
		
		
		if(_USE_TABS_MAIN){
			if(!disableInfoHeader){
	    		pNorte.getTopToolbar().setVisible(!disableInfoHeader);
	    		vpApp.doLayout();
	    	}
		}
		
    	
    	if(_cmbEmpresa){
    		_cmbEmpresa.setDisabled(disableInfoHeader);
    	}			
	};
    // create some portlet tools using built in Ext tool ids
    var callBackTools = new Object();
    
    Exj.loadDataApp({
    	success: function(dataGlobal){
		    // nota: cargar el idioma, por lo pronto, sin idioma
            var dataIdioma = dataGlobal.dataIdioma;
        	var dataMenusMain = dataGlobal.dataMenusMain;
        	
            loadMainApp({
                dataIdioma: dataIdioma,
                dataMenusMain: dataMenusMain,
                dataMenusOpcGen: dataGlobal.dataMenusOpcGen,
                itemsModulesMains: dataGlobal.itemsModulesMains,
                itemsCmpAutoLoad: dataGlobal.itemsCmpAutoLoad
            });
    	}
    });
    
    function loadMainApp(paramsApp){
    /* 
        paramsApp.dataIdioma
        paramsApp.dataMenusMain
        paramsApp.itemsModulesMains
        paramsApp.itemsCmpAutoLoad
    */
        var dataIdiomaApp = paramsApp.dataIdioma;
    
        callBackTools.refresh = function(){
            Exj.moi('No implementado: refresh');
        }
        callBackTools.close = function(){
            // Exj.moi('No implementado: close');
            return true;
        }
        
        Ext.MessageBox.buttonText.cancel = Exj.Idioma('Cancelar');
        Ext.MessageBox.buttonText.ok = Exj.Idioma('Aceptar');    
        
        var tools = [ {
            id:'refresh',
            handler: function(e, target, panel){
                panel.doLayout();
                
                callBackTools.refresh(e, target, panel);
            }
        }, {
            id:'help',
            handler: function(e, target, panel){
                Exj.moi('Ayuda no disponible para: ' + panel.title);
            }
        },{
            id:'close',
            /* tooltip : 'Cierraxx', */
            handler: function(e, target, panel){
                if(!callBackTools.close(e, target, panel)){
                    return;
                }
                panel.ownerCt.remove(panel, true);
                
                idContainner = panel.el.id;
                // está adicionado al inicio la palabra: _cont_
                
                _hw.remove(idContainner, '_cont_');
                if(_hw.count() <= 0){
                    if(!pInfo.ownerCt){
                        
                    }
                }
                
                
                
                var _pi = getPanelIzq();
                _pi.expand(true);
                
                var _ps = getPanelSup();
                _ps.doLayout();
                
    //            vpApp.doLayout();
            }
        }];
        
        var _margenes = new Object();
        _margenes._sup = '0';
        _margenes.centro     =  _margenes._sup+' 0 0 0';
        _margenes.izquierda  =  _margenes._sup+' 0 2 2';
        _margenes.cizquierda  = _margenes._sup+' 2 2 2';
        
        function _buildBubbleContent(senderContainer, layout){
			var title = new Ext.ux.BubblePanel({
				bodyStyle: 'color: gray; background: transparent; text-align: right;',
				renderTo: senderContainer.getEl(),
				/* html: '<h3>GYM Cloud.</h3><span>SVU!</span>', */
				html: '<h3>GYM Cloud.</h3>',
				/* width: 200, */
				autoHeight: true
		    });
        };
        
        
        function NewPanelInfo(){
            var PanelInfoMain = function(config){
                /*
                    config.callBackTools
                */
                
                var u = Exj.getUsuario();
                
                var htmlInfoUsr = '<div id="bubble-markup" class="headerTitle">';
                
                // htmlInfoUsr += '<div class="exj-title-main">'+Exj.Global.infoUser.name_company+'</div>';
                
                if(!Exj.Global.itemsDisplay){
                    htmlInfoUsr += '<p><b>'+Exj.Global.infoUser.usertype.toUpperCase() + ':</b> '+Exj.Global.infoUser.apes_noms_persona + ' - '+ Exj.Global.infoUser.nro_doc_persona;
                    if(Exj.isModeDebug){
                        htmlInfoUsr += ' <div style="color:red">(<b>MODO DEBUG ESTA ACTIVO</b>)</div>';
                    }
                    
                    htmlInfoUsr += '</p>';

                	var _htmlInfoEmpresa = '';
                	// aaa 
                	var attrsExtrasHTML = '';
                	if(Exj.Global.infoUser.usertype == 'CLIENTE'){
                		attrsExtrasHTML = 'style="display:none;"';
                	}
                	
                	_htmlInfoEmpresa += '<div ' + attrsExtrasHTML + '>';
                	
                	if(Exj.Global.infoUser.is_main_empresa == 1){
                		_htmlInfoEmpresa += '<b>'+'<span id="exjInfoMain_prefixOfc">'+Exj.Idioma('EMPRESA') + '</span>' +':</b>';
                	}
                	else{
                		_htmlInfoEmpresa += '<b>'+'<span id="exjInfoMain_prefixOfc">'+Exj.Idioma('EMPRESA') + '</span>' +':</b>';
                	}
                	
                	_htmlInfoEmpresa += ' <span id="exjInfoMain_nom_empresa">'+Exj.Global.infoUser.nom_empresa+'</span>';
                	
                	_htmlInfoEmpresa += '</div>';
                	
                	htmlInfoUsr += _htmlInfoEmpresa;
                }

                if(Exj.Global.infoUser.id_guser != 0){
                	if(!Exj.Global.itemsDisplay){
                		/* var htmlInfoNameCiu = '<span id="exjInfoMain_name_ciu_com">'+Exj.Global.infoUser.name_ciu_com+'</span>'; */
                		var htmlInfoNameCiu = '<span id="exjInfoMain_name_ciu_com">'+Exj.Global.infoUser.name_city_prs+'</span>';
		                if(Exj.Global.infoUser.is_capital == 1){
			                htmlInfoUsr += '<p><b>CAPITAL:</b> '+htmlInfoNameCiu+'</p>';
		                }
		                else{
			                htmlInfoUsr += '<p><b>'+Exj.Idioma('CIUDAD')+':</b> '+htmlInfoNameCiu;
			                htmlInfoUsr += ' <b>'+'<span id="exjInfoMain_name_sit_main">'+Exj.Idioma(Exj.Global.infoUser.name_sit_main.toUpperCase())+'</span>'+'</b>: '+ '<span id="exjInfoMain_name_state">'+ Exj.Global.infoUser.name_state_prs+'</span>';
			                htmlInfoUsr += '</p>';
		                }
                	}

	                if(Exj.Global.infoUser.nam_loc_custom != null){
		                htmlInfoUsr += '<p>'+Exj.Global.infoUser.nam_custom_group_single+': '+Exj.Global.infoUser.nam_loc_custom+'</p>';
	                }

                    /*
                    if (Exj.Global.emisor) {
                        htmlInfoUsr += '<p>';
                        htmlInfoUsr += '<b>EMISOR</b>: '+ Exj.Global.emisor.ruc+' Punto de emisión: '+Exj.Global.emisor.cod_establecimieto+'-'+Exj.Global.emisor.cod_punto_emision;
                        htmlInfoUsr += '</p>';
                    }
                    */
					
                    /*
	                htmlInfoUsr += '<p><b>'+Exj.Idioma('LENGUAJE')+'</b>: '+Exj.Global.infoUser.name_lang+'</p>';
	                if(Exj.Global.infoUser.usertype == "Super Administrator"){
	                	htmlInfoUsr += '<p><b>Navegador</b>: '+ navigator.userAgent+'</p>';
	                	htmlInfoUsr += '<p><b>Tamaño de Pantalla (w*h)</b>: '+ Exj.calcWidth(100)+' * '+Exj.calcHeight(100)+'</p>';
	                }
                    */
                }
                
                htmlInfoUsr += '</div>';
                
                var tableInfoExtra = null;
                if(Exj.Global.itemsDisplay && Exj.Global.itemsDisplay.length > 0){
                	// htmlInfoUsr += '<div>';
                	
                	var itemsTable = new Array();
                	for(var i=0, item; i < Exj.Global.itemsDisplay.length; i++){
                		item = Exj.Global.itemsDisplay[i];
                		
                		itemsTable.push({
                			html: '<b>'+item.label+'</b>',
                			cellCls: 'highlight'
                		}, {
                			html: item.value
                		});
                	}
                	
					tableInfoExtra = new Ext.Panel({
					    title: 'INFORMACION GENERAL GYM Cloud!',
					    layout:'table',
					    border: true,
					    style: 'padding: 3px 0px',
					    defaults: {
					        bodyStyle:'padding:3px',
					        border: false
					    },
					    layoutConfig: {
					        columns: 2
					    },
					    items: itemsTable
					});                	
                	
                	// xxx
                	// htmlInfoUsr += ;
                	
                	// htmlInfoUsr += '</div>';
                }
                
                Exj.Global.itemsDisplay = null;
                
                htmlInfoUsr += '<img unselectable="on" class="vu-img-logo-app" src="'+Exj.Global.infoUser.uri_logo_frontal+'"/>';
              //  htmlInfoUsr += '<div id="date_info_ride"></div>';
                
                /* dato del Usuario con: Exj.Global.infoUser */
				var htmlUser = '';
				htmlUser += '<p class="headerUsuario">' + u.GRUPO_USUARIO+'</p>';
				if(Exj.Global.infoUser.firstname && Exj.Global.infoUser.lastname){
					htmlUser += '<p class="headerUsuario">';
					htmlUser += '<b>'+Exj.Idioma('NOMBRES')+':</b> '+Exj.Global.infoUser.firstname + ' '+ Exj.Global.infoUser.lastname;
					htmlUser += '</p>';
					
				}
				else{
					htmlUser += '<div class="headerAgente">'+u.NOMBRE_USUARIO +  '</div>';
				}
				if((Exj.Global.infoUser.avatarapproved == 1) && (Exj.Global.infoUser.avatar)){
					htmlUser += '<img class="cbThumbPict" title="'+u.NOMBRE_USUARIO+'" alt="' + u.NOMBRE_USUARIO + '"';
					htmlUser += 'src="' + Exj.getPathImageUserAvatar() + '"';
					htmlUser += '/>';
				}
				
				var itemsPanelInfo = new Array();

				
				itemsPanelInfo.push(new Ext.form.Label({
                    html: htmlInfoUsr 
                }));
                
                if(tableInfoExtra){
                	itemsPanelInfo.push(tableInfoExtra);
                }
                
				itemsPanelInfo.push(new Ext.form.Label({
                     html: htmlUser
                }));

                /* xxx test */
                /*
				itemsPanelInfo.push({
					id: 'btnFullScreenApp',
					xtype: 'button',
					text: 'Full Screen',
					listeners: {
						click: function(){
							Exj.fullScreen();
						}
					}
				});
				*/
				
                var p =  new Ext.Panel({
                    id: 'idPanelInfo',
                    border: false,
                    style: 'padding: 6px',
                    items: itemsPanelInfo
                });
               
                config.callBackTools.refresh = function(sender){
                    p.doLayout();
                }
                
                
                PanelInfoMain.superclass.constructor.call(this, {
                    id: 'idPanelInfoMain',
                    title: '',
                    bodyStyle: 'padding: 6px;',
                    border: false,
                    items: [
                        p
                    ]
                }); // PanelInfoMain.superclass.constructor
                
            }; /* PanelInfoMain */
            
            Ext.extend(PanelInfoMain, Ext.ux.BubblePanel);
            
            panelInfoMain = new PanelInfoMain({
                callBackTools: callBackTools,
                bodyStyle: 'padding-left: 8px;color: #0d2a59',
                style: 'color: red;'
            });
            panelInfoMain.addListener('afterrender', function(senderContainer, layout){
            	_buildBubbleContent(senderContainer, layout);
            });
            
            return panelInfoMain;
        }; // NewPanelInfo
        
        
        
        Exj.addComponentMain = function (content, idPanelToRenderVU, closableTab){
            var icon = 'icon-app';
            if(content.iconClsTab){
                icon = content.iconClsTab;
            }
            
            if(closableTab === undefined){
            	closableTab = true;
            }
            
            var panelX='', id, i=0;
            while(++i <= countIndexTab){
                id = 'tabMain' + content.id + i;
                var panelX = _tpMain.getItem(id);
                if(panelX){
                    break;
                }
                
               // alert('panelX: '+panelX.id+' content.id: '+content.id);
            }
            
            if(panelX){
                Exj.mou(Exj.Idioma('Está activa la opción')+ ':<br />'+content.titleTab);
                _tpMain.activate(panelX);
                return null;
            }
            
            if(idPanelToRenderVU){
            	var pnlToRenderVU = Ext.getCmp(idPanelToRenderVU);
            	if(pnlToRenderVU && (pnlToRenderVU instanceof Ext.Panel)){
            		content.idPanelToRenderVU = idPanelToRenderVU;
            		pnlToRenderVU.removeAll();
            		pnlToRenderVU.add(content);
            		
            		pnlToRenderVU.doLayout();
            		
            		return pnlToRenderVU;
            	}
            }

            
            ++countIndexTab;
            var panelTab = _tpMain.add({
                id: 'tabMain'+content.id+countIndexTab,
                title: content.titleTab,
                iconCls: icon,
                items: [content],
                closable: closableTab,
                bodyCfg: {
                	align: 'center'
                }
            });

            // comboStyle.setDisabled(true);
            panelTab.show();
            return panelTab;
        }; /* Exj.addComponentMain */
        
        // xxx
        _tpMain = new Ext.TabPanel({
            resizeTabs:true, /* turn on tab resizing */
            minTabWidth: 180,
            tabWidth: 150,
            enableTabScroll:false,
            width: '100%',
            height: 510,
            defaults: {autoScroll:false},
            region: 'center',
            plugins: new Ext.ux.TabCloseMenu(callBackTools)
        });
        
        var countIndexTab=0;
        callBackTools.showPanelInfoOnly = function(tab){
            countIndexTab=0;
            
            // comboStyle.setDisabled(false);
            
            
            var _pi = getPanelIzq();
            _pi.expand(true);
            
            return true;
        };
        
       
                
        function getDataProcess(dataMenu){
            var dataProcess = new Object();
            dataProcess.id = 0;
            dataProcess.nameModule='';
            
        	if(dataMenu.id){
	            dataProcess.id = dataMenu.id;
	            dataProcess.nameModule = dataMenu.nameModule;
        	}
        	else{
        		dataProcess.id = dataMenu;
        	}
        	if(!dataProcess.nameModule){
        		dataProcess.nameModule = dataProcess.id;
        	}
        	
            
            dataProcess.idSeccionIdioma = '';
            
            dataProcess.idAccess = -1; // sin definir
            dataProcess.msgAccess = ''; // si se asigna este valor, cuando se deniega el acceso, se emite este mensaje
            
            // alert('dataProcess.id: '+dataProcess.id);
             

            if(dataProcess.id == 'mnuConfigurarCotizMon' || (dataProcess.id == 'Cotizaciones de monedas')){
                dataProcess.id = 'ConfigurarCotizMon';
                dataProcess.idSeccionIdioma = 'CotizMonGeneral';
                dataProcess.idAccess = 24;
            }
            else if(dataProcess.id == 'mnuAgentesCuentas' || (dataProcess.id == 'CuentasAgentes')){
                dataProcess.id = 'CuentasAgentes';
                dataProcess.idAccess = 14;
            }
            else if(dataProcess.id == 'mnuConfigurarPaises' || (dataProcess.id == 'ConfigurarPaises')){
                dataProcess.id = 'ConfigurarPaises';
                dataProcess.idSeccionIdioma = 'PaisesTarifas';
                dataProcess.idAccess = 25;
            }
            else if(dataProcess.id == 'mnuConfigurarPtosVenta' || (dataProcess.id == 'ConfigurarPtosVenta')){
                dataProcess.id = 'ConfigurarPtosVenta';
                dataProcess.idSeccionIdioma = 'Puntos de Venta';
                dataProcess.idAccess = 13;
            }
            
            
            if(dataProcess.idAccess == -1){
            	if(Exj.accessModuleAprobe(dataProcess.id)){
            		dataProcess.idAccess = 1;
            	}
            	else{
            		dataProcess.idAccess = 0;
            	}
            }
            
           // alert('dataProcess.idSeccionIdioma: '+dataProcess.idSeccionIdioma+' dataProcess.id: '+dataProcess.id);
            return dataProcess;
        }; // getDataProcess
        
        function getPanelIzq(){
            return vpApp.getComponent('west-panel');
        };
        
        function getPanelSup(){
            return vpApp.getComponent('norte-panel');
        };
        
        
        // menu principal ***************************************
        onItemClickMenu = function(senderMenu, e, isMenuIzq, closableTab){
            if (! Exj.dataOk()){
                return ;
            }
            if(isMenuIzq === undefined){
                isMenuIzq = false;
            }
            
            if(closableTab === undefined){
            	closableTab = true;
            }

            var dataProcess = getDataProcess(senderMenu);
            
             // var _idProcess = dataProcess.id;
            var _idProcess = dataProcess.nameModule;
            
            if (_hw.exist(_idProcess)){
                // Exj.moi('La opción ya está activa. opción: '+ btn.text);
                // return;
            }

            var _pma = '';
            var _titleGen = senderMenu.text + ' - '+Exj.TITLE;
            var _titleTab = senderMenu.page_title; // senderMenu.text;
            var _iconClsTab = senderMenu.iconCls;
            
			var _handlerUrl = new Exj.HUrl({
				option: senderMenu.access.moduleName
			});
			
            var paramsCallBack = {
                title: _titleGen,
                callBackTools: callBackTools,
                dataIdioma: '',
                iconClsTab: _iconClsTab,
                getHandlerUrl: function(){
                	return _handlerUrl;
                }
            };
		    
		    if(!showModuleApp('', senderMenu.idPanelToRenderVU, closableTab)){
		    	return;
		    }
		    
		    if(!paramsCallBack.getUI){
		    	return;
		    }
		    var _uiCallBack = paramsCallBack.getUI({
		    	titleModule: (senderMenu.page_title ? senderMenu.page_title: senderMenu.text)
		    });
		    if(!_uiCallBack){
		    	return;
		    }
		    if(!_uiCallBack.actions){
		    	return;
		    }
		    
		    var paramsSubmit = {
		    	idMenu: senderMenu.id,
		    	nameComponent: senderMenu.access.moduleName
		    };
		    paramsSubmit = Ext.apply(_uiCallBack.actions, paramsSubmit);
		    if(_uiCallBack.params){
		    	if(Ext.isObject(_uiCallBack.params)){
		    		// paramsSubmit = Ext.apply(_uiCallBack.params, paramsSubmit);
		    		paramsSubmit = Ext.apply(paramsSubmit, _uiCallBack.params);
		    	}
		    	else{
		    		Exj.moe('El parámetro params de paramsCallBack no es un objeto.', 'ERROR DE IMPLEMENTACION');
		    	}
		    }
		    
		    if(paramsSubmit.namesListsModels && Ext.isArray(paramsSubmit.namesListsModels)){
		    	paramsSubmit.namesListsModels = Ext.encode(paramsSubmit.namesListsModels);
		    }

		    _pma.titleModule = senderMenu.page_title;
		    _pma.editableModel = null;
			_pma.editable = null;
			_pma.criteria = null;
			_pma.uiEditableFooter = null;
			_pma.paramsGenerals = (_uiCallBack.params ? _uiCallBack.params: null);
			
			// Exj.fullScreen();
	
		    Exj.submit({
		        url: _huBaseui.getActionView('getDataUI'),
		        method: 'GET',
		        isUrlWithExtras: true,
		        params: paramsSubmit, 
		        /* idMask: _pma.getEl(), */
		        mask: 'Cargando '+ senderMenu.page_title+' ...',
		        fnSuccess: function(response){
					_successLoadModuleApp(response, paramsSubmit);
		        }
		    });

function _successLoadModuleApp(response, paramsSubmit){
	/* paramsSubmit.nameComponent */
	var dataResponse = response.data;
	
	var uiList = dataResponse.list;
	/* var uiLists = dataResponse.lists; */
	var uiEditable = dataResponse.editable;
	var dataIdioma = dataResponse.dataIdioma;
	var uiCriteria = null;
	var uiFooter = dataResponse.footer;
	var uiReadOnly = dataResponse.readonly;
	var uiPanelMain = dataResponse.panelmain;
	
	if(dataResponse.criteria){
		uiCriteria = dataResponse.criteria;
	}
	
	_pma.editableModel = dataResponse.editable;
	
	if(uiList){
		Exj.evalRendererListModel(uiList, _pma);
	}
	
	/*
	if(uiLists && uiLists.length > 0){
		for(var indexList=0, itemList; indexList < uiLists.length; indexList++){
			itemList = uiLists[indexList];
			if(itemList.list){
				Exj.evalRendererListModel(itemList.list, _pma);
			}
		}
	}
	*/
	
	var fnbuildListUI = _pma.buildListUI;
	var fnBuildListsSecsUI = _pma.buildListsSecsUI;
	if(!fnbuildListUI){
		fnbuildListUI = _uiCallBack.buildListUI;
	}
	if(!fnBuildListsSecsUI){
		fnBuildListsSecsUI = _uiCallBack.buildListsSecsUI;
	}

	if(!fnbuildListUI){
		Exj.moe('Fallo de construcción no se ha definido. buildListUI en: '+senderMenu.access.moduleName);
		return;
	}
	
	_pma.editable = new Exj.ui.Editable(uiEditable);
	if(uiCriteria){
		_pma.criteria = new Exj.ui.Editable(uiCriteria);
	}
	
	if(uiFooter){
		_pma.uiEditableFooter = new Exj.ui.Editable(uiFooter);
	}
	
	_pma.readonly = new Exj.ui.Editable(uiReadOnly);
	
	_pma._fixHeigthComponentMain = function(pnlSenderCriteria){
		if(_pma.gridsSecLists){
			// Note: Pendiente
			return;
		}
		
		var componentMain = _pma.gridMainList;
		if(!componentMain && _pma.getPanelMainModel){
			componentMain = _pma.getPanelMainModel();
			
			/*
			if(!componentMain){
				componentMain = _pma;
			}
			*/
		}
		
		if(!componentMain){
			return;
		}
		
		if(componentMain.isVisible && !componentMain.isVisible()){
			/* Exj.mou('comp no esta visible', componentMain.title); */
			return;
		}
		
		if(!pnlSenderCriteria && (this.formPanelCriteria && this.formPanelCriteria.getOuterSize)){
			pnlSenderCriteria = this.formPanelCriteria;
		}
		
		if(_tpMain){
			_pma._sizeModuleApp = Exj.getSizeLayout(_tpMain.getActiveTab());
		}
		
		if(pnlSenderCriteria){
			_pma._sizeCriteria = pnlSenderCriteria.getOuterSize();
			// Exj.mou('height. moduleApp: '+_pma._sizeModuleApp.height+'<br/>Criteria: '+_pma._sizeCriteria.height);
		}
		else{
			_pma._sizeCriteria = null;
		}
		
		var newHeightCompMain = _pma._sizeModuleApp.height;
		if(_pma._sizeCriteria){
			newHeightCompMain -= _pma._sizeCriteria.height-1;
			if(!_tpMain){
				newHeightCompMain -= 1;
			}
		}
		
		if(_pma.formPanelFooter){
			// newHeightCompMain -= _pma.formPanelFooter.getSize().height;
			newHeightCompMain -= _pma.formPanelFooter.getOuterSize().height;
		}
		
		if(componentMain.exjOffsetHeight){
			componentMain.exjOffsetHeight = Ext.num(componentMain.exjOffsetHeight, 0);
			newHeightCompMain += componentMain.exjOffsetHeight;
		}
		
		if(_pma.idPanelToRenderVU){
			newHeightCompMain -= 30;
		}
		
		if(newHeightCompMain <= 0){
			return;
		}
		
		if(componentMain.getHeight() == newHeightCompMain){
		//	Exj.mou('Evitando fijar alto, ya tiene el mismo alto el componentMain');
			return;
		}
		
		// sss
		// Exj.mou('Fijando alto al componentMain newHeightCompMain: '+newHeightCompMain+ ' HeightLast: '+componentMain.getHeight());
		componentMain.setHeight(newHeightCompMain);
	/*	componentMain.doLayout(); */
	};
	
	_pma.getPanelMainModel = function(){
		var panelsMainsModels = this.find('typeModel', 'ExjPanelMainModel');
		if(!panelsMainsModels || panelsMainsModels.length == 0){
			return null;
		}
		
		return panelsMainsModels[0];
	};
					
	if(uiCriteria && uiCriteria.cfgFormPanel){
		if(!uiCriteria.cfgFormPanel.items){
			uiCriteria.cfgFormPanel.items = new Array();
		}
		
		if(_pma.getContentCriteria){
			var items = _pma.getContentCriteria(_pma.criteria);
			if(items){
				if(Ext.isArray(items)){
					for(var i=0, item; i < items.length; i++){
						uiCriteria.cfgFormPanel.items.push(items[i]);
					}
				}
				else{
					uiCriteria.cfgFormPanel.items.push(items);
				}
			}
		}
		
		// uiCriteria.cfgFormPanel.region = 'north';
		// uiCriteria.cfgFormPanel.flex = 1;
		// uiCriteria.cfgFormPanel.autoHeight = false;
		
		_pma.formPanelCriteria = new Ext.form.FormPanel(uiCriteria.cfgFormPanel);
		
		
		
		_pma.formPanelCriteria.addListener('expand', function(senderContent){
			_pma._fixHeigthComponentMain(senderContent);
		});
		_pma.formPanelCriteria.addListener('collapse', function(senderContent){
			_pma._fixHeigthComponentMain(senderContent);
		});
		_pma.formPanelCriteria.addListener('afterlayout', _pma._fixHeigthComponentMain);
		
		_pma.add(_pma.formPanelCriteria);
		
		_pma.formPanelCriteria.addListener('afterrender', function(senderContent){
			if(senderContent.collapsible){
				var fb = senderContent.getForm();
				if(fb.isValid()){
					if(_pma.isCollapsibleInitialCriteria === undefined){
						_pma.isCollapsibleInitialCriteria = true;
					}
					if(_pma.isCollapsibleInitialCriteria){
						senderContent.collapse(false);
					}
					else{
						if(_pma.autoFocusToField === undefined){
							_pma.autoFocusToField = true;
						}
						
						if(_pma.autoFocusToField){
							var fieldFocusx=null;
							fb.items.each(function(fieldx) {
					            if (fieldx.focus && Ext.isFunction(fieldx.focus)) {
					                fieldFocusx = fieldx;
					                return false;
					            }
					        });
					        
					        if(fieldFocusx){
					        	fieldFocusx.focus(false, 120);
					        }
						}
					}
				}
				else{
					var fInvalid = null;
					fb.items.each(function(f) {
			            if (!f.disabled) {
			                if(!f.isValid()){
			                	fInvalid = f;
			                	return false; // break
			                }
			            }
			        });
					
					if(fInvalid){
						fInvalid.focus(false, 120);
					}
				}
			}
		});
		
		
		var buttonsCriteria = _pma.formPanelCriteria.getBottomToolbar();
		
		var btnReset = buttonsCriteria.find('exjAction', 'reset');
		if(btnReset.length > 0){
			btnReset = btnReset[0];
			btnReset.addListener('click', function(senderButton, e){
				_pma.formPanelCriteria.getForm().reset();
				if(_pma.onAfterReset){
					_pma.onAfterReset(senderButton, _pma.formPanelCriteria);
				}
			});
		}
						

	_pma._getParamsCriteria = function(){
		var fb = _pma.formPanelCriteria.getForm();
		if(!_pma.isValidCriteria(fb)){
			return false;
		}
		
		return fb.getFieldValues();
	};
	
	_pma.getFieldValuesCriteria = function(){
		return _pma._getParamsCriteria();
	};	

	_pma._getValuesCriteria = function(){
		var fb = _pma.formPanelCriteria.getForm();
		if(!_pma.isValidCriteria(fb)){
			return false;
		}
		
		return fb.getValues();
	};
	
	

	_pma.isValidCriteria = function(formBasic, showMsgInfo){
		if(showMsgInfo === undefined){
			showMsgInfo = true;
		}
		
		if(!formBasic){
			formBasic = _pma.formPanelCriteria.getForm();	
		}
		
		if(!formBasic.isValid()){
			if(showMsgInfo){
				Exj.mou('Existen datos inválidos.<br/>Revize por favor...');
			}
			
			return false;
		}
		
		return true;
	};
	
	_pma.criteriaFocus = function(nameComponent){
		var cmp = Exj.getFieldFromName(_pma.formPanelCriteria, nameComponent);
		if(!cmp){
			Exj.moe('En filtros. Intentando fijar el foco.<br/>No se encuentra el componente:<br/>'+nameComponent, 'ERROR DE IMPLEMENTACION');
			return false;
		}
		cmp.focus();
		return true;
	};
	
	_pma.callSearch = function(){
		Exj.moe('No implementado el botón Buscar!');
	};
	
	_pma.setVisiblePanelMainModel = function(isVisible){
		if(isVisible == undefined){
			isVisible = true;
		}
		
		var pnlMainModel = this.getPanelMainModel();
		if(!pnlMainModel){
			return false;
		}
		
		return pnlMainModel.setVisible(isVisible);
	};
	
						
	var btnSearch = buttonsCriteria.find('exjAction', 'search');
	if(btnSearch.length > 0){
		btnSearch = btnSearch[0];

		_pma.callSearch = function(){
			btnSearch.fireEvent('click', btnSearch, {});
		};
		
		Exj.applyActionPressEnter(_pma.formPanelCriteria, _pma.callSearch);
		
		btnSearch.addListener('click', function(senderButton, e){
			var paramsCriteria = _pma._getParamsCriteria();
			if(paramsCriteria === false){
				return;
			}
			
			if(_pma.onBeforeSearch){
				if(_pma.onBeforeSearch(paramsCriteria, _pma.formPanelCriteria) === false){
					return;
				}
			}
			
			var compsMains = [];
			if(_pma.gridMainList){
				compsMains.push(_pma.gridMainList);
			}
			
			if(compsMains.length == 0){
				var panelsMainsModels = _pma.find('typeModel', 'ExjPanelMainModel');
            	if(panelsMainsModels && panelsMainsModels.length > 0){
            		for(var indexMainModel=0; indexMainModel < panelsMainsModels.length; indexMainModel++){
            			compsMains.push(panelsMainsModels[indexMainModel]);
            		}
            	}
			}
			
			if(compsMains.length == 0){
				Exj.moe('No se ha definido contenedor principal gridMainList o panelmain', 'Error en Implementación UI');			    					
				return;
			}
			
			for(var i=0, compMain, storeToCall; i < compsMains.length; i++){
				compMain = compsMains[i];
				storeToCall = null;
				if(compMain.store){
					storeToCall = compMain.store;
				}
				else if(compMain.exjStore || (compMain.dataModel && compMain.dataModel.cfgStore)){
					if(!compMain.exjStore){
						compMain.exjStore = Exj.newJsonStore(compMain.dataModel.cfgStore, compMain.dataModel.cfgStore.url);
					}
					
					storeToCall = compMain.exjStore;
				}
				
				if(!storeToCall){
					Exj.moe('No se ha definido store en gridMainList o panelmain', 'Error en Implementación UI');			    					
					continue;
				}
				
				btnSearch.setDisabled(true);
				
				storeToCall.baseParams = (_pma.gridMainList ? (_pma.gridMainList.store.baseParams || {}): {});
				storeToCall.baseParams.criteria = Ext.encode(paramsCriteria);
				
				if(storeToCall.baseParams.start === undefined){
					storeToCall.baseParams.start = 0;
				}
				if(storeToCall.baseParams.limit === undefined){
					storeToCall.baseParams.limit = 30;
				}
				
				storeToCall.load({
					callback: function(records, options, success){
						btnSearch.setDisabled(false);
						if(!success){
							this.removeAll();
							// Exj.moe('Huvieron problemas!', 'Error buscando...');
							return;
						}
						if(_pma.onAfterSearch){
							_pma.onAfterSearch(records, options, _pma.formPanelCriteria);
						}
						
						if(_pma.onLoadFromStore){
							_pma.onLoadFromStore(records, options, compMain, _pma.formPanelCriteria);
						}
						
					}
				});
			}
		});
	}
	
	}
	
	fnbuildListUI(uiList, dataIdioma, dataResponse);
	if(uiPanelMain){
		if(_pma.autoCreateStorePanelMain){
			if(uiPanelMain.exjStore || (uiPanelMain.dataModel && uiPanelMain.dataModel.cfgStore)){
				if(!uiPanelMain.exjStore){
					uiPanelMain.exjStore = Exj.newJsonStore(uiPanelMain.dataModel.cfgStore, uiPanelMain.dataModel.cfgStore.url);
					
					if(uiPanelMain.dataModel.dataResponse && uiPanelMain.dataModel.dataResponse.DataTopics){
						uiPanelMain.exjStore.loadData(uiPanelMain.dataModel.dataResponse);
					}
				}
				
				// storeToCall = uiPanelMain.exjStore;
			}
		}
		
		if(_pma.beforeBuildPanelMain){
			_pma.beforeBuildPanelMain(uiPanelMain, dataIdioma, dataResponse);
		}
		
		if(!uiPanelMain.listeners){
			uiPanelMain.listeners = new Object();
		}
		
		if(uiPanelMain.listeners.show){
			uiPanelMain._extraEvShow = uiPanelMain.listeners.show;
		}
		uiPanelMain.listeners.show = function(senderCompPanelMain){
			if (senderCompPanelMain._extraEvShow){
				senderCompPanelMain._extraEvShow(senderCompPanelMain);
			}
			
			_pma._fixHeigthComponentMain();
		};
		
	//	_pma._exjPanelMainInner = new Ext.Panel(uiPanelMain);
	// 	_pma._exjPanelMainInner = Exj.newPanel(uiPanelMain);
		// _pma._exjPanelMainInner = uiPanelMain;
	}
	
	if(uiList && uiList.listsSecModels && Ext.isArray(uiList.listsSecModels)){
		if(fnBuildListsSecsUI){
			_pma.gridsSecLists = new Array();
			
			for(var indexSec = 0, itemSecList; indexSec < uiList.listsSecModels.length; indexSec++){
				itemSecList = uiList.listsSecModels[indexSec];
				// itemSecList.listModel.cfgGrid
				// itemSecList.nameController
				// itemSecList.nameModel
				var hURLSec = new Exj.HUrl({
					option: paramsSubmit.nameComponent,
					controller: itemSecList.nameController
				});
				
				fnBuildListsSecsUI(itemSecList.listModel, dataIdioma, dataResponse, hURLSec);
			}
		}
		else{
			Exj.moe('No se ha definido función callback buildListsSecsUI en módulo UI.', 'Error de implementación');
		}
	}
	
	if(uiPanelMain){
		if(_pma.getContentMainList){
			_pma.add(_pma.getContentMainList(uiPanelMain));
		}
		else{
			_pma.add(uiPanelMain);
		}
		
		_pma.doLayout();
	}
	
	if(_pma.gridMainList){
		if(_pma.gridMainList.title){
			_pma.gridMainList.headerCssClass = 'exj-grid-header-title';
		}
		
		if(!_pma.criteria){
			_pma.addListener('afterlayout', function(){
				_pma._fixHeigthComponentMain(null);
			});
		}
		
		if(_pma.getContentMainList){
			_pma.add(_pma.getContentMainList(_pma.gridMainList));
		}
		else{
			// var _pnlContent 
			// _pma.add(_pma.gridMainList);
			
			if(_pma.gridsSecLists && _pma.gridsSecLists.length > 0){
				var itemsGrids = new Array();
				_pma.gridMainList.autoHeight = true;
				_pma.gridMainList.height = 'auto';
				_pma.gridMainList.boxMinHeight = 300;
				_pma.gridMainList.boxMinHeight = 300;
			//	_pma.gridMainList.width = '93%';
				// itemsGrids.push(Exj.getContainerGridsCenter(_pma.gridMainList));
				itemsGrids.push(_pma.gridMainList);
			
				itemsGrids.push({
					xtype: 'container',
					style: 'padding: 6px;'
				});
				
				for(var indexGridSec=0, gridSec; indexGridSec < _pma.gridsSecLists.length; indexGridSec++){
					gridSec = _pma.gridsSecLists[indexGridSec];
					
					gridSec.autoHeight = true;
					gridSec.height = 'auto';
					gridSec.boxMinHeight = 300;
				//	gridSec.width = '93%';
					
				//	itemsGrids.push(Exj.getContainerGridsCenter(gridSec));
					itemsGrids.push(gridSec);
				}
				
				_pma.add({
					xtype: 'panel',
					autoHeight: false,
					height: 450,
					autoScroll: true,
					/*
					layout: {
                        type:'vbox',
                        padding:'3',
                        pack:'center',
                        align:'stretch'
                    },
                    */
					/*
                    layout: 'table',
                	layoutConfig: {
                		tableAttrs: {
				            style: {
				                width: '100%',
				                height: 'auto'
				            }
				        },
                		columns:1
                	},
                	*/
					items: itemsGrids
				});
			}
			else{
				/* _pma.add(Exj.getContainerGridsCenter(_pma.gridMainList)); */
				/* adiciona al tab */
				_pma.add(_pma.gridMainList);
			}
			
		}

		_pma.doLayout();
		
						
		if(_pma.onAfterAddContentCriteria){
			_pma.onAfterAddContentCriteria(_pma.formPanelCriteria);
		}
						
		if(!_pma.onActionDel && _pma.hUrl){
			_pma.onActionDel = function(senderButton, e, r){
				
				if(_pma.onBeforeDel){
					if(_pma.onBeforeDel(senderButton, e, r) === false){
						return; // cancela: _pma.onActionDel
					}
				}
				
				Exj.executeDelete(_pma.gridMainList, _pma.hUrl, r);
			};
		}

		if(!_pma.onActionViewLogPers && _pma.hUrl){
			_pma.onActionViewLogPers = function(senderButton, e, r){
				
				if(_pma.onBeforeViewLogPers){
					if(_pma.onBeforeViewLogPers(senderButton, e, r) === false){
						return; // cancela
					}
				}
				
				if(!r.id){
					Exj.moe('No definido propiedad IdProperty!');
					return;
				}
				
				var hUrl = new Exj.HUrl({
					option: 'exj_sys_log_pers',
					controller: 'sys_log_pers'
				});
				
				// xxx
				var nameEditableModel = '';
				if(_uiCallBack.actions && _uiCallBack.actions.nameEditableModel){
					nameEditableModel = _uiCallBack.actions.nameEditableModel;
				}
				
				Exj.showListModel({
					hUrl: hUrl,
					/* title: '', */
					params: {
						id_primary_key_current: r.id,
						name_comp_log: _pma.hUrl.getOption(),
						nameEditableModel: nameEditableModel
					},
					senderButton: senderButton,
					width: Exj.calcWidth(69),
					componentTop: null,
					componentBottom: null
				}, _pma);
			};
		}
		
		if(_pma.gridMainList){
			Exj.action.grid.onNew(_pma.gridMainList, _pma.onActionNew);
			Exj.action.grid.onEdit(_pma.gridMainList, _pma.onActionEdit);
			Exj.action.grid.onDel(_pma.gridMainList, _pma.onActionDel);
			
			Exj.action.grid.onSave(_pma.gridMainList, _pma.onActionSave);
			Exj.action.grid.onCancel(_pma.gridMainList, _pma.onActionCancel);
			
			if(_pma.onActionReadOnly){
				_pma.onActionView = _pma.onActionReadOnly;
			}
			Exj.action.grid.onView(_pma.gridMainList, _pma.onActionView);
			Exj.action.grid.onViewLogPers(_pma.gridMainList, _pma.onActionViewLogPers);
			
			Exj.action.grid.onPrints(_pma);
			Exj.action.grid.onHelp(_pma);
		}
		
		if(_pma.getMsgQuestionDel && Ext.isFunction(_pma.getMsgQuestionDel)){
			var buttonsActions = _pma.gridMainList.getTopToolbar().find('exjAction', 'del');
			if(buttonsActions && buttonsActions.length >= 1){
				var btnDelete = buttonsActions[0];
				btnDelete.getMsgQuestion = function(recordSelected, oldMsg){
					return _pma.getMsgQuestionDel(recordSelected, oldMsg);
				};
			}
		}
	}
	
	_pma.formPanelFooter = null;
	if(uiFooter && uiFooter.cfgFormPanel){
		if(!uiFooter.cfgFormPanel.items){
			uiFooter.cfgFormPanel.items = new Array();
		}
		
		if(_pma.getContentFooter && _pma.uiEditableFooter){
			var items = _pma.getContentFooter(_pma.uiEditableFooter);
			if(items){
				if(Ext.isArray(items)){
					for(var i=0, item; i < items.length; i++){
						uiFooter.cfgFormPanel.items.push(items[i]);
					}
				}
				else{
					uiFooter.cfgFormPanel.items.push(items);
				}
			}
		}
		
		_pma.formPanelFooter = new Ext.form.FormPanel(uiFooter.cfgFormPanel);
		_pma.add(_pma.formPanelFooter);
		_pma.doLayout();
		
		
		if(_pma.gridMainList && _pma.gridMainList.getStore){
			_pma.gridMainList.getStore().addListener('load', function(sto, records, options){
				if(!sto.reader.jsonData.data || !sto.reader.jsonData.data.dataFooter){
					return;
				}
				
				var df = sto.reader.jsonData.data.dataFooter;
				
				 _pma.formPanelFooter.getForm().setValues(df);
				// Exj.bindToContainer(_pma.formPanelFooter.getForm(), df);
			});
		}
		
	}
	
	
}; // _successLoadModuleApp
		   
            
            function showModuleApp(dataIdioma, idPanelToRenderVU, closableTab){
	            // opciones del sistema ********************************************************
	            
	            Exj._langCurrent = dataIdioma;
	            /*
	            if(Exj._langCurrent){
	            	alert('Exj._langCurrent: '+Ext.encode(Exj._langCurrent));
	            }
	            */
	
	            var claseX = _idProcess;
	            if(!claseX || (claseX == 'Exj.ui.modules.NoDefinido')){
	            	Exj.moe('No se ha especificado el componente a ejecutar!');
	            	return false;
	            }
	            var dinamicGen = '_pma = new '+claseX+'(senderMenu, paramsCallBack)';
	            var infoError='';
	            try{
	                eval(dinamicGen);
	            }
	            catch(e){
	               // claseX = '';
	               // alert(e);
	               infoError = e;
	            }
	            
	            // *****************************************************************************
	            if (_pma){
	            	if(!(_pma instanceof Ext.Panel)){
	            		return false;
	            	}
	                _pma.titleTab = _titleTab;
	                _pma.iconClsTab = _iconClsTab;
	                
	                _pma.addListener('afterlayout', function(senderPanelPMA){
	                	/* gridMainList */
	                	var gridsPMA = senderPanelPMA.findByType(Ext.grid.GridPanel);
	                	if(gridsPMA && gridsPMA.length > 0){
	                		for(var indexGrid=0, gridPMA; indexGrid < gridsPMA.length; indexGrid++){
	                			gridPMA = gridsPMA[indexGrid];
	                			
	                			if(gridPMA._isAppliedRendererColsExj){
	                				continue;
	                			}
	                			gridPMA._isAppliedRendererColsExj = true;

	                			var hiddenBtnActionEdit = false, hiddenBtnActionView = false;
	                			
	                			for(var indexCol=0, colPMA; indexCol < gridPMA.colModel.config.length; indexCol++){
	                				colPMA = gridPMA.colModel.config[indexCol];
	                				if(colPMA.isColumn && colPMA.dataAction && colPMA.dataAction.exjActions){
	                					for(var indexAction =0, strAction; indexAction < colPMA.dataAction.exjActions.length; indexAction++){
	                						strAction = colPMA.dataAction.exjActions[indexAction];
	                						if(strAction == 'edit'){
	                							hiddenBtnActionEdit = true;
	                						}
	                						else if(strAction == 'view'){
	                							hiddenBtnActionView = true;
	                						}
	                					}
	                					
	                					colPMA._rendererOriginal = colPMA.renderer;
	                					colPMA.renderer = function(value, p, r, rowIndex, colIndex, store){
	                						var valueReturn = value;
	                						if(this._rendererOriginal && Ext.isFunction(this._rendererOriginal)){
	                							valueReturn = this._rendererOriginal(value, p, r, rowIndex, colIndex, store);
	                							if(valueReturn && Ext.isString(valueReturn)){
	                								if(valueReturn.indexOf('Exj.execActionEditViewFromNode') > 0){
	                									return valueReturn;
	                								}
	                							}
	                						}
	                						
	                						if(r.data.isData === false || r.data.isHeader === true || r.data.isRowSummary === true){
	                							return valueReturn;
	                						}
	                						
	                						valueReturn = Exj.renderCSSText(valueReturn, p, {
												css: (this.dataAction ? this.dataAction.css : ''),
												converToButton: true
											});
	                						
	                						return valueReturn;
	                					};
	                				}
	                			}
	                			
	                			if(hiddenBtnActionEdit || hiddenBtnActionView){
									var tbGrid = gridPMA.getTopToolbar();
		                			if(tbGrid){
		                				if(hiddenBtnActionEdit){
		                					var buttonsEdit = tbGrid.find('exjAction', 'edit');
		                					if(buttonsEdit && buttonsEdit.length > 0){
			                					var buttonEdit = buttonsEdit[0];
			                					buttonEdit.setVisible(false);
			                				}
		                				}
		                				
		                				if(hiddenBtnActionView){
		                					var buttonsView = tbGrid.find('exjAction', 'view');
		                					if(buttonsView && buttonsView.length > 0){
			                					var buttonView = buttonsView[0];
			                					if(buttonView){
			                						buttonView.setVisible(false);
			                					}
			                				}
		                				}
		                			}                				
	                			}
	                		}
	                	}
	                	
	                	/* Ver si hay un panelmain */
	                	if(!senderPanelPMA._isAppliedActions){
		                	var panelsMainsModels = senderPanelPMA.find('typeModel', 'ExjPanelMainModel');
		                	if(panelsMainsModels && panelsMainsModels.length > 0){
		                		senderPanelPMA._isAppliedActions = true;
		                		
		                		for(var indexMainModel=0, pnlMM; indexMainModel < panelsMainsModels.length; indexMainModel++){
		                			pnlMM = panelsMainsModels[indexMainModel];
		                			
		                			Exj.action.grid.onSave(pnlMM, _pma.onActionSave);
									Exj.action.grid.onCancel(pnlMM, _pma.onActionCancel);
		                		}
		                	}
	                	}
	                	
	                	if(senderPanelPMA._fixHeigthComponentMain){
	                		senderPanelPMA._fixHeigthComponentMain();
	                	}	                	
	                }); /* afterlayout */
	                
	               // _pma.dataIdioma = dataIdioma;
	                
	               /*
	                if(pInfo.ownerCt){
	                    pInfo.ownerCt.remove(pInfo, true);
	                }
	                */
	
	                var _pi = getPanelIzq();
	                _pi.collapse(true);
	                
	                
	                Exj.addComponentMain(_pma, idPanelToRenderVU, closableTab);
	                
	                if(!idPanelToRenderVU){
	                	 _pma.doLayout();
	                }
	                
	             //   alert('_idProcess: '+_idProcess);
	                _hw.add(_idProcess);
	                
	                return _pma;
	            }
	            
	            var msgInfoError='';
	            
	            if(Exj.isModeDebug){
		            msgInfoError += 'En construcción.<br/>Opción: '+ _titleTab+ '. Menú izquierdo es: '+ isMenuIzq+'.';
		            if(claseX){
		            	msgInfoError += '<br />Por implementar la clase: '+claseX;
		            }
		            if(infoError){
		            	msgInfoError += '<br />Error: '+infoError;
		            }
	            }
	            else{
	            	msgInfoError += 'No autorizado a ingresar:<br/>'+_titleTab+'.';
	            }
	        
	            Exj.moi(msgInfoError);
	            return false;
            }; // showModuleApp

           // _pi.expand(true);
        }; // onItemClickMenu
        
        function renderCall(items){
        	if(!items){
        		return;
        	}
	        var i= -1;
	        var item;
	        while(++i < items.length){
	        	item = items[i];
	        	
	        	if(item.handler == 'onItemClickMenu'){
	        		item.handler = onItemClickMenu;
	        	}
	        	
	        	if(item.menu){
	        		if(item.menu.items){
	        			renderCall(item.menu.items);
	        		}
	        	}
	        	
	        	if(item.text){
	        		item.text = Exj.Idioma(item.text);
	        	}
	        	
	        }
        }; // renderCall
        
        renderCall(paramsApp.dataMenusMain.items);
        renderCall(paramsApp.dataMenusOpcGen.items);
        
        Exj.itemsMenu = paramsApp.dataMenusMain.items;
        Exj.itemsOpcGen = paramsApp.dataMenusOpcGen.items;
        Exj.loadModulesSystem();
        
        // alert('xxx Test Paso 1');
        
        Exj.__tpMain = _tpMain;
        
        vpApp = new Ext.Viewport({
            layout:'border',
            monitorResize: true, 
            listeners: {
            	'afterrender': function(senderVP){
            		Exj._setViewportMain(senderVP);
            		
            		
            		/*
            		var pnlNorte = Ext.getCmp('norte-panel');
            		
            		var tbNorte = pnlNorte.getTopToolbar();
            		
            		tbNorte.items.each(function(itemCmp){
            			if(itemCmp.children){
            				
            			}
            		});
            		*/
            		
            		/*
            		var cmpMnuX = Ext.getCmp('174');
            		cmpMnuX.fireEvent('click', cmpMnuX, {});
            		*/
            		
            		// nameModule == 'Exj.ui.modules.RepFacturas'
            		// 'nameModule', 'Exj.ui.modules.RepFacturas'
            		//var itemsMnuSersCli = senderVP.items.items[0].getTopToolbar().items.items[0].children;
            		// var itemBozon = itemsMnuSersCli[0];
            		// Exj._refVPMain.items.items[0].getTopToolbar().items.items[0].children[0].handler
            		// onItemClickMenu
            		// ttt
            	}
            },
            items:[{
	                region:'north',
	                id:'norte-panel',
	                /*
	                //minSize: 175,
	                //maxSize: 400,
	                //margins: _margenes.izquierda,
	                //cmargins:_margenes.cizquierda,
	                */
	                margins: '0 0 3 0',
	               /* tbar: paramsApp.dataMenusMain.tbHeaderMain, */
	                tbar: {
	                	xtype: 'toolbar',
	                	cls: 'vu-toolbar-mainmenu',
	                	items: paramsApp.dataMenusMain.items /* menu principal */
	                }, 
	                autoHeight: true
	        }, {
	                region:'west',
	                id:'west-panel',
	                title: Exj.Idioma('Opciones Generales'),
	                split:true,
	                width: 150,
	                minSize: 90,
	                maxSize: 400,
	                collapsible: true,
	                margins:  _margenes.izquierda,
	                cmargins: _margenes.cizquierda,
	                hidden: (paramsApp.dataMenusOpcGen.items.length == 0),
	                xtype: 'treepanel',
	                width: 200,
	                autoScroll: true,
	                split: true,
	                loader: new Ext.tree.TreeLoader(),
	                root: new Ext.tree.AsyncTreeNode({
	                    expanded: true,
	                    singleClickExpand: true,
	                    children: paramsApp.dataMenusOpcGen.items
	                }),
	                rootVisible: false,
	                listeners: {
	                    click: function(n, e) {
	                        attr = n.attributes;
	                        //TEST_TREE = attr;
	                        
	                        if(!attr.leaf){
	                            return;
	                        }
	                        
	                        onItemClickMenu(attr, e, true);
	                    }
	                }
	            },
            	_tpMain
            ]
        }); // vpApp
        
       // alert('xxx Test Paso 2');

        // Ext.util.MixedCollection
        var _mcApp = vpApp.items;
        
        _mcApp.each(function(item, index, length){
        	if(item.id == 'norte-panel'){
        		var i=-1;
        		var mnuMain;
        		
        		if(!item.topToolbar){
        			// xxx
        			return;
        		}
        		
        		var combosEmpresas = item.topToolbar.find('exjAction', 'selectEmpresa');
        		if(combosEmpresas && combosEmpresas.length > 0){
        			_cmbEmpresa = combosEmpresas[0];
        			_cmbEmpresa.addListener('select', _onSelectEmpresa);
        		}
        		
        		itemsMnu = item.topToolbar.items.items;
        		while(++i < itemsMnu.length){
        			mnuMain = itemsMnu[i];
        			
        			// alert(i+' mnuMain.id: '+mnuMain.id);
        			 // var subsMenu = mnuMain.menu.items.items;
        			 // var subsMenu = mnuMain.menu.items.items;
        			 var nActive = 0;
        			 var menu_sep_open = false;
        			 
					subMenu = mnuMain;
    			 	
    			 	if(subMenu.itemCls == 'x-menu-sep'){
    			 		if(nActive == 0){
    			 			subMenu.setVisible(false);
    			 			menu_sep_open = false;
    			 			continue;
    			 		}
    			 		else{
    			 			menu_sep_open = !menu_sep_open;
    			 		}
    			 		if(!menu_sep_open){
    			 			subMenu.setVisible(false);
    			 		}
    			 		continue;
    			 	};
        		}
        	}
        });
        

        pNorte = vpApp.getComponent('norte-panel');
		var _hUrlGlobal = new Exj.HUrl({
			option: 'exj_global',
			controller: 'globals'
		});
        
        // pNorte.tbar.addField(comboStyle);
		function _onSelectEmpresa(senderCbmMunicipo, rEmpresa, index){
            Exj.submit({
                url: _hUrlGlobal.getActionCustom('changeEmpresa'),
                params: {
					id_empresa: rEmpresa.data.value
                }, 
                idMask : (_tabInfo ? _tabInfo.getEl() : null),
                mask: 'Cambiando de empresa. Espere por favor...',
                fnSuccess: function(response){
                	if(!response || !response.data){
                		// Ext.get('form-login').dom.Submit.click();
                		
                		if(document.location.href && document.location.href.replace){
                			document.location = document.location.href.replace('#', '');
                		}
                		else{
                			document.location = document.location;
                		}
                		
                		return;
                	}
                	
                	// actualizamos el obj global...
                	if(Exj.Global.infoUser && response && response.data.id_empresa){
						Exj.Global.infoUser.id_empresa = parseInt(response.data.id_empresa);
						Exj.Global.infoUser.is_main_empresa = parseInt(response.data.is_main_empresa);
						
						Exj.Global.infoUser.nom_empresa = response.data.nom_empresa;
						Exj.Global.infoUser.name_state = response.data.name_state;
						Exj.Global.infoUser.name_sit_main = response.data.name_sit_main;
						Exj.Global.infoUser.name_ciu_com = response.data.name_ciu_com;
						
						if(!Exj.Global.infoUser.name_sit_main){
							Exj.Global.infoUser.name_sit_main = 'Error Undefined';
						}
						
						Exj.setterInnerHTML('exjInfoMain_nom_empresa', Exj.Global.infoUser.nom_empresa);
						Exj.setterInnerHTML('exjInfoMain_name_state', Exj.Global.infoUser.name_state);
						Exj.setterInnerHTML('exjInfoMain_name_sit_main', Exj.Idioma(Exj.Global.infoUser.name_sit_main).toUpperCase());
						
						var lblInfoMHNO = Ext.getCmp('lblInfoMainHeaderNameMunic');
						if(lblInfoMHNO){
							lblInfoMHNO.setText(Exj.Global.infoUser.nom_empresa);
						}
						
						Exj.setterInnerHTML('exjInfoMain_name_ciu_com', Exj.Global.infoUser.name_ciu_com);
						
						if(Exj.Global.infoUser.is_main_empresa == 1){
							Exj.setterInnerHTML('exjInfoMain_prefixOfc', Exj.Idioma('EMPRESA'));
						}
						else{
							Exj.setterInnerHTML('exjInfoMain_prefixOfc', Exj.Idioma('EMPRESA'));
						}
						
                	}
                	
                	if(response && response.data.msgUI){
	                	Exj.moi(response.data.msgUI);
                	}
                	else{
                		alert('sin respuesta!');
                	}
                }
            });
		}; // _onSelectEmpresa
		
		pInfo = NewPanelInfo();
		
		_tpMain._itemsCmpAutoLoad = paramsApp.itemsCmpAutoLoad;
        var indexActiveTabDef = -1;
		
		_tabInfo = _tpMain.add({
            title: Exj.Idioma('Información General'),
            iconCls: 'exj-icon-app',
            items: [pInfo],
            closable: false,
            listeners: {
            	afterrender: function(){
            		/// yyy
            		if(_tpMain._itemsCmpAutoLoad){
            			for(var indexCmpLoad=0, itemCmpLoad, cmpMnuX; indexCmpLoad < _tpMain._itemsCmpAutoLoad.length; indexCmpLoad++){
            				itemCmpLoad = _tpMain._itemsCmpAutoLoad[indexCmpLoad];
            				cmpMnuX = Ext.getCmp(itemCmpLoad.id);
            				if(cmpMnuX){
            					cmpMnuX.fireEvent('click', cmpMnuX, {}, false, false);
            					// onItemClickMenu
            				}
            			}
            		}
            		
            		// _tpMain.setActiveTab(1);
            	}
            }
         }).show();
        
        
        _tpMain.nroModulesMains = 0;
        if(paramsApp.itemsModulesMains){
        	for(var indexModMain=0, itemModMain; indexModMain < paramsApp.itemsModulesMains.length; indexModMain++){
        		itemModMain = paramsApp.itemsModulesMains[indexModMain];
        		
        		if(itemModMain.items){
        			for(var indexCntMain=0, itemCntMain; indexCntMain < itemModMain.items.length; indexCntMain++){
	        			itemCntMain = itemModMain.items[indexCntMain];
	        			if(itemCntMain.isVUContentMain){
	        				if(itemCntMain.tbar && itemCntMain.tbar.items){
	        					renderCall(itemCntMain.tbar.items);
	        				}
	        				
	        				itemCntMain = new Ext.Panel(itemCntMain);
	        				
	        				itemModMain.items[indexCntMain] = itemCntMain;
	        				continue;
	        			}
	        		}
        		}
        		
    			_tpMain.add(itemModMain);
        		_tpMain.nroModulesMains += 1;
        		
        		if(itemModMain.isActiveTabDefault){
        			indexActiveTabDef = _tpMain.nroModulesMains;
        		}
        	}
        }
        
        
        
        /*
        _tpMain.addListener('afterrender', function(senderCmpTpMain){
        	Exj.mou('afterrender. nroModulesMains: '+ senderCmpTpMain.nroModulesMains);
        });
        */
       
        _tpMain.addListener('add', function(senderCmp, ownerCt, index){
        	if(index == 0){
        		return;
        	}
        	
        //	_onAddOptionMenu(this);
        	
        //	Exj.mou('add. index: '+index);
        });
        
        _tpMain.addListener('remove', function(senderContainer, cmpDeleted){
        	var disableInfoHeader = false;
        	
        	if(_tpMain.nroModulesMains){
        		disableInfoHeader = (_tpMain.items.getCount() > (_tpMain.nroModulesMains+1));
        	}
        	else{
        		disableInfoHeader = (_tpMain.items.getCount() > 1);
        	}
        	
        	_onResetOptionMenu(this, disableInfoHeader);
        	
        	// alert('remove. nro: '+ _tpMain.items.getCount());
        });
        
        if(indexActiveTabDef >= 0){
        	_tpMain.setActiveTab(indexActiveTabDef);
        }
        
        _tpMain.doLayout();
        
    }; /* loadMainApp */
    
    /*
	Ext.EventManager.on(window, 'load', function(){
	    Ext.select('#app-spacer').remove();
	
	    setTimeout(function(){
	        Ext.get('_loading').remove();
	        Ext.get('_loading-mask').fadeOut({remove:true});
	    }, 333);
	
	    if(window.console && window.console.firebug){
	        Ext.Msg.alert('Advertencia', 'Firebug está activo, con Ext JS.');
	    }
	});
    */
});
