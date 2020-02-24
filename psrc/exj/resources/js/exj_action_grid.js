Exj.action.grid.onCustom = function (grid, fnAction, nameAction, requiereSelection, requiereConfirmation) {
    if (!grid) {
        return;
    }
    if (requiereSelection === undefined) {
        requiereSelection = false;
    }

    var toptb = grid.getTopToolbar();
    if (!toptb) {
        return false;
    }

    var buttons = toptb.find('exjAction', nameAction);
    if (!buttons || !buttons.length) {
        return false;
    }
    var btn = buttons[0];
    if (!btn.addListener) {
        return false;
    }
    if (!fnAction) {
        fnAction = function (senderButton, e) {
            Exj.moi('En construcción: ' + senderButton.text);
        };
    }

    if (btn.applyToItems) {
        var items = btn.items;
        if (!items) {
            if (btn.menu && btn.menu.items) {
                items = btn.menu.items;
            }
        }

        if (!items) {
            Exj.moe('No se pudo aplicar acciones para: ' + btn.text, 'ERROR 001');
            return false;
        }

        // aplicar solo a los items del objeto
        if (!items.each) {
            Exj.moe('No se pudo aplicar acciones para: ' + btn.text, 'ERROR 002');
            return false;
        }

        items.each(function (item, index) {
            _applyActionClick(item);
        });

        return false;
    }

    _applyActionClick(btn);

    function _applyActionClick(component) {
        if (!component.addListener) {
            return false;
        }

        component.addListener('click', function (senderButton, e) {
            if (!requiereSelection) {
                fnAction(senderButton, e);
                return;
            }

            if (grid.store && grid.store.getCount() == 0) {
                Exj.moi('No existen elementos en la lista!');
                return;
            }

            var sm = grid.getSelectionModel();
            if (!Ext.isFunction(sm.getCount)) {
                Exj.moi('El modo de selección no soporta esta acción!');
                return;
            }

            if (sm.getCount() == 0) {
                Exj.moi('Debe seleccionar de la lista para:<br/>' + senderButton.text);
                return;
            }

            var rSel = sm.getSelected(),
                    indexRow = grid.store.indexOfId(rSel.id);

            if (indexRow == -1) {
                sm.clearSelections();
                Exj.moi('Debe seleccionar de la lista para:<br/>' + senderButton.text);
                return;
            }

            // comprovar si esta seleccionado visualmente el grid

            if (indexRow >= 0) {
                var gv = grid.getView(),
                        row = gv.getRow(indexRow);

                if (row) {
                    if (gv.fly(row).dom.className.indexOf(gv.selectedRowClass) == -1) {
                        sm.selectRow(indexRow);
                        rSel = sm.getSelected();
                    }
                }
            }

            if (!requiereConfirmation) {
                fnAction(senderButton, e, rSel);
                return;
            }

            
            var msgConfirmQuestion = 'Está seguro de ' + senderButton.text;
            if (senderButton.getMsgQuestion && Ext.isFunction(senderButton.getMsgQuestion)) {
                msgConfirmQuestion = senderButton.getMsgQuestion(rSel, msgConfirmQuestion);
                if (msgConfirmQuestion === false) {
                    msgConfirmQuestion = 'Está seguro de ' + senderButton.text;
                }
            }

            // confirmacion
            Exj.msgQuestion({
                msg: msgConfirmQuestion,
                fnYes: function () {
                    fnAction(senderButton, e, rSel);
                }
            });
        });
    }; /* _applyActionClick */

    return true;
};

Exj.action.grid.onNew = function (grid, fnAction) {
    if (!fnAction && grid.onActionNew) {
        fnAction = grid.onActionNew;
    }

    return Exj.action.grid.onCustom(grid, fnAction, 'add');
};
Exj.action.grid.onEdit = function (grid, fnAction) {
    return Exj.action.grid.onCustom(grid, fnAction, 'edit', true);
};
Exj.action.grid.onDel = function (grid, fnAction) {
    return Exj.action.grid.onCustom(grid, fnAction, 'del', true, true);
};

Exj.action.grid.onView = function (grid, fnAction) {
    return Exj.action.grid.onCustom(grid, fnAction, 'view', true);
};

Exj.action.grid.onViewLogPers = function (grid, fnAction) {
    return Exj.action.grid.onCustom(grid, fnAction, 'viewlogpers', true);
};

Exj.action.grid.onSave = function (grid, fnAction) {
    return Exj.action.grid.onCustom(grid, fnAction, 'save');
};
Exj.action.grid.onCancel = function (grid, fnAction) {
    return Exj.action.grid.onCustom(grid, fnAction, 'cancel');
};

Exj.action.grid.onPrints = function (pnlModuleApp, gridListModel) {
    var grid = null;
    if (gridListModel) {
        grid = gridListModel;
    }
    if (!grid) {
        grid = pnlModuleApp.gridMainList;
    }

    var buttons = grid.getTopToolbar().find('exjAction', 'rep_mnu');
    if (!buttons || !buttons.length) {
        // Exj.moe('No se ha encontrado el botón Reporte!');
        return;
    }

    var rSel = null, _requiereSelection = false;
    var btnMnu = buttons[0];
    _requiereSelection = btnMnu.requiereSelection;

    /*
     btnMnu.addListener('click', function(senderButton, e){
     rSel = null;
     if(_requiereSelection){
     var sm = grid.getSelectionModel();
     if(sm.getCount() == 0){
     Exj.mou('You must select from the list to:<br/>'+ senderButton.text);
     return;
     }
     rSel = sm.getSelected();
     }
     });
     */

    function _fnPrint(senderButton, e, rSel) {

        if (senderButton instanceof Ext.menu.CheckItem) {
            e.stopPropagation();
            e.browserEvent.stopImmediatePropagation();
            e.stopEvent();

            if (senderButton.isLoaderClickCustom) {
                return;
            }

            senderButton.isLoaderClickCustom = true;
            senderButton.handleClick = function (compCheck) {
                senderButton.setChecked(!senderButton.checked);

                senderButton.parentMenu.sendToMailFile = senderButton.checked;

                /*
                 if(!senderButton.checked){
                 senderButton.stopEvent = e.stopEvent;
                 Ext.menu.CheckItem.superclass.handleClick.apply(senderButton, e);
                 //Ext.menu.CheckItem.superclass.handleClick(e);
                 }
                 */
            };

            return;
        }


        var paramsCriteria = null;
        var valuesCriteria = null;

        if (pnlModuleApp._getParamsCriteria) {
            paramsCriteria = pnlModuleApp._getParamsCriteria();
        }

        if (paramsCriteria === false) {
            return;
        }

        if (pnlModuleApp._getValuesCriteria) {
            valuesCriteria = pnlModuleApp._getValuesCriteria();
        }

        var _id = 0;
        if (rSel && rSel.id) {
            _id = rSel.id;
        }

        if (!paramsCriteria && grid.store.baseParams) {
            paramsCriteria = Ext.applyIf(
                    paramsCriteria,
                    grid.store.baseParams
                    );
        }

        if (!paramsCriteria) {
            paramsCriteria = new Object();
        }

        var paramsReport = new Object();

        if (pnlModuleApp.paramsGenerals && Ext.isObject(pnlModuleApp.paramsGenerals)) {
            paramsReport = Ext.apply(paramsReport, pnlModuleApp.paramsGenerals);
        }

        paramsReport.id = _id;
        paramsReport.criteria = Ext.encode(paramsCriteria);
        if (valuesCriteria) {
            paramsReport.valuesCriteria = Ext.encode(valuesCriteria);
        } else {
            paramsReport.valuesCriteria = null;
        }

        var ss = grid.store.getSortState();
        paramsReport.dir = ss.direction;
        paramsReport.sort = ss.field;
        if (pnlModuleApp.getParamsExtrasReport) {
            var peReport = pnlModuleApp.getParamsExtrasReport();
            if (peReport) {
                paramsReport = Ext.applyIf(
                        paramsReport,
                        peReport
                        );
            }
        }
        
        paramsReport.cols = null;
        if(grid.colModel && grid.colModel.config){
	        for(var i = 0, c; (c = grid.colModel.config[i]); i++){
	            if(!c.hidden){
		            if(!paramsReport.cols){
		            	paramsReport.cols = new Array();
		            }
		            
		            paramsReport.cols.push({
		            	id: c.id,
		            	dataIndex: c.dataIndex,
		            	width: c.width
		            });
	            }
	        }
        }
        
        if(paramsReport.cols){
        	paramsReport.cols = Ext.encode(paramsReport.cols);
        }

        var paramsDownloadFile = {
            hUrl: pnlModuleApp.hUrl,
            idMask: grid.getEl(),
            senderButton: senderButton,
            params: paramsReport
        };

        if (senderButton.parentMenu.sendToMailFile) {
            paramsDownloadFile.isDownloadFile = false;
            paramsDownloadFile.fnSuccess = function (idFile, dataDownload, response) {
                Exj.mail.showTo({
                    attachs: [{
                            dataDownload: dataDownload
                        }],
                    scope: pnlModuleApp
                });
            };
        }

        Exj.downloadReportModel(paramsDownloadFile);
    }
    ;

    Exj.action.grid.onCustom(grid, _fnPrint, 'rep_mnu', _requiereSelection);

    /*
     Exj.action.grid.onCustom(grid, _fnPrint, 'rep_pdf', _requiereSelection);
     Exj.action.grid.onCustom(grid, _fnPrint, 'rep_excelxlsx', _requiereSelection);
     Exj.action.grid.onCustom(grid, _fnPrint, 'rep_excelxls', _requiereSelection);
     Exj.action.grid.onCustom(grid, _fnPrint, 'rep_html', _requiereSelection);
     */
};

Exj.action.grid.onHelp = function (pnlModuleApp, gridListModel) {
    var grid = null;
    if (gridListModel) {
        grid = gridListModel;
    }
    if (!grid) {
        grid = pnlModuleApp.gridMainList;
    }

    var _titleModule = pnlModuleApp.titleModule;
    if (!_titleModule) {
        _titleModule = 'Help';
    }

    function _fnShowHelp(senderButton, e) {
        Exj.showHelp({
            url: pnlModuleApp.hUrl.getActionHelpViewCmp({
                format: 'htmlx'
            }),
            titleModule: _titleModule,
            iconCls: senderButton.iconCls
        });
    }
    ;

    return Exj.action.grid.onCustom(grid, _fnShowHelp, 'hlp');
};
