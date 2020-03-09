Exj.WinSubmit = Ext.extend(Ext.Window, {
    constructor: function (config, cfgFormPanel) {
        config = Ext.apply({
            modal: true,
            urlSubmit: '',
            hUrl: null, /* instancia de Exj.HUrl */
            idValue: 0,
            recordEditable: null,
            maximizable: false,
            width: 270,
            closable: true,
            autoHeight: true,
            plain: true,
            layout: 'fit',
            autoScroll: true,
            closeAction: 'close',
            bbar: new Array(),
            waitMsg: 'Guardando',
            withButtonOk: true,
            withButtonCancel: true,
            textOk: 'Guardar',
            textCancel: 'Cancelar',
            iconClsOk: 'app-btn-save',
            iconClsCancel: 'exj-btn-cancel',
            buttonsExtras: null,
            tooltipOk: '',
            isButtonsOkCancel: false,
            fnIsValid: null,
            fnSuccess: null,
            fnFailure: null,
            fnGetDataChangesExtras: null,
            methodToSubmit: '',
            onlyModeLocal: false,
            onlyEnabledDataChange: false,
            isReadOnlyAccess: false,
            isSuccessActionReset: false,
            fnSuccessActionReset: null,
            isSuccessActionNone: false,
            reloadStoreAppMainActionAll: false
        }, config);
        config.title = Exj.Idioma(config.title);
        var me = this;

        this.isSuccessActionReset = config.isSuccessActionReset;
        this.fnSuccessActionReset = config.fnSuccessActionReset;
        if (this.fnSuccessActionReset && Ext.isFunction(this.fnSuccessActionReset)) {
            this.isSuccessActionReset = config.isSuccessActionReset = true;
        }

        this.isSuccessActionNone = config.isSuccessActionNone;
        this.reloadStoreAppMainActionAll = config.reloadStoreAppMainActionAll;

        this.onlyModeLocal = config.onlyModeLocal;
        this.onlyEnabledDataChange = config.onlyEnabledDataChange;
        this.isReadOnlyAccessSubmit = config.isReadOnlyAccess;
        if (config.isReadOnlyAccess) {
            config.withButtonOk = false;
            config.textOk = 'No permitido!';
            config.textCancel = 'Cerrar';
            config.iconClsOk = 'app-btn-ok';
        }

        if (config.isButtonsOkCancel) {
            config.textOk = 'Aceptar';
            config.textCancel = 'Cancelar';
            config.iconClsOk = 'app-btn-ok';
        }

        this._btnOk = null;
        if (config.withButtonOk) {
            this._btnOk = Exj.newButton({
                text: config.textOk,
                disabled: false,
                tooltip: config.tooltipOk,
                iconCls: config.iconClsOk
            });

            config.bbar.push(this._btnOk);
        }

        if (config.timeOutSec) {
            this._timeOutSec = config.timeOutSec;
        }


        if (config.buttonsExtras && config.buttonsExtras.length > 0) {
            config.bbar.push('-');
            for (var indexBtn = 0, btnExtra; indexBtn < config.buttonsExtras.length; indexBtn++) {
                btnExtra = config.buttonsExtras[indexBtn];
                if (!btnExtra.handler || !Ext.isFunction(btnExtra.handler)) {
                    if (!btnExtra.menu) {
                        continue;
                    }
                }

                btnExtra.autoCloseWin = (btnExtra.autoCloseWin ? true : false);

                if (btnExtra.menu) {
                    for (var indexMnuBtn = 0, mnuExtra; indexMnuBtn < btnExtra.menu.length; indexMnuBtn++) {
                        mnuExtra = btnExtra.menu[indexMnuBtn];

                        mnuExtra._rootHandler = (mnuExtra.handler ? mnuExtra.handler.createCallback(mnuExtra.text) : null);
                        if (mnuExtra.autoCloseWin === undefined) {
                            mnuExtra.autoCloseWin = btnExtra.autoCloseWin;
                        }
                        mnuExtra.handler = function (senderBtnMnu, e) {
                            if (senderBtnMnu.autoCloseWin) {
                                me.closeWinSubmit();
                            }

                            if (senderBtnMnu._rootHandler) {
                                senderBtnMnu._rootHandler(senderBtnMnu, e, me);
                            }
                        }
                    }

                    config.bbar.push(btnExtra);
                } else {
                    config.bbar.push(Exj.newButton({
                        text: btnExtra.text,
                        tooltip: btnExtra.tooltip,
                        iconCls: btnExtra.iconCls,
                        autoCloseWin: btnExtra.autoCloseWin,
                        handler: function (senderBtnExtra, e) {
                            if (senderBtnExtra.autoCloseWin) {
                                me.closeWinSubmit();
                            }

                            btnExtra.handler(senderBtnExtra, e, me);
                        }
                    }));
                }
            }
        }

        this._btnCancel = null;
        if (config.withButtonCancel) {
            this._btnCancel = Exj.newButton({
                text: config.textCancel,
                iconCls: config.iconClsCancel,
                handler: function () {
                    if (me.fnBeforeCancel && Ext.isFunction(me.fnBeforeCancel)) {
                        if (me.fnBeforeCancel(this, me) === false) {
                            return;
                        }
                    }
                    me.closeWinSubmit();
                }
            });

            config.bbar.push(this._btnCancel);
        }

        if (!config.idValue && config.recordEditable && config.recordEditable.id) {
            config.idValue = config.recordEditable.id;
        }
        this.isNew = (config.idValue ? false : true);
        if (config.idValue && config.idValue < 0) {
            this.isNew = true;
        }

        if (!config.title && config.nameEntity) {
            config.title = (this.isNew ? 'Crear' : 'Editar') + ' ' + config.nameEntity;
            if (config.isReadOnlyAccess) {
                config.title = config.nameEntity + ' (Solo Lectura)';
            }
        }
        if (config.waitMsg && config.nameEntity) {
            config.waitMsg += ' ' + config.nameEntity;
        }

        // this.listeners = config.listeners;
        
        Exj.WinSubmit.superclass.constructor.call(this, config);

        this._initWin(cfgFormPanel);
    },
    onEnterTab: function(keyCode, e) {
        e.stopEvent();

        if (!e.target || !e.target.name) {
            // Exj.mou('ENTER ok NO TIENE name');
            return;
        }

        // Exj.mou('ENTER ok name: '+ e.target.name);
        var basicForm = this.getBasicForm();

        var fieldCurrent = basicForm.findField(e.target.name);
        if (!fieldCurrent) {
            return;
        }

        if(!fieldCurrent.isValid()){
            Exj.mou(fieldCurrent.getErrors()[0], 'ERROR');
            return;
        }


        var nodes = Ext.query('input,textarea', this.getEl().dom), nodeFocus=null;
        if (nodes && nodes.length) {
            for (var i = 0, nodex; i < nodes.length; i++) {
                nodex = nodes[i];
                if (nodex.name == e.target.name) {
                    nodeFocus = nodes[i+1];
                    if (nodeFocus && !nodeFocus.disabled) {
                        break;
                    }
                }
            }
        }

        if(nodeFocus && !nodeFocus.disabled){
            var fieldFocus = basicForm.findField(nodeFocus.name);
            if (fieldFocus) {
                fieldFocus.focus();
            }
        }
        else{
            var fieldInvalid = null;
            basicForm.items.each(function(f){
               if(!f.validate()){
                    if (!f.name || !f.getErrors().length) {
                        if(f.items && f.items.each){
                            f.items.each(function(invalidF){
                                if (!invalidF.validate()) {
                                    fieldInvalid = invalidF;
                                    return false;
                                }
                            });

                            if (fieldInvalid) {
                                return false;
                            }
                        }
                    }

                   fieldInvalid = f;
               }
            });

            if (fieldInvalid) {
                fieldInvalid.focus();
            }
            else{
                this.callSave();
            }
        }
    },
    closeWinSubmit: function (reloadStoreAppMain) {
        // a los grids quitar registros seleccionados
        var grids = this.findByType(Ext.grid.GridPanel),
                sm;
        grids.each(function (grid, indexGrid) {
            if (grid.isVisible()) {
                sm = grid.getSelectionModel();
                if (sm.clearSelections) {
                    sm.clearSelections();
                }
            }
        });

        if (this.closeAction == 'hide') {
            this.hide();
            return false;
        }

        this.isClosedWinSubmit = true;

        this.close();

        if (this.reloadStoreAppMainActionAll && reloadStoreAppMain !== false) {
            reloadStoreAppMain = true;
        }

        if (reloadStoreAppMain) {
            Exj.appMainReloadStore(this);
        }

        return true;
    },
    resetWinSubmit: function () {
        var bf = this._formW.getForm();
        bf.items.each(function (f) {
            if (f.clearValue && Ext.isFunction(f.clearValue)) {
                f.clearValue();
            }

            if (f.setValue && Ext.isFunction(f.setValue)) {
                f.originalValue = '';
            }

            f.clearInvalid();
        });

        bf.reset();

        if (this.fnSuccessActionReset) {
            this.fnSuccessActionReset(bf, this._formW, this);
        }
    },
    _initWin: function (cfgFormPanel) {
        var me = this;
        if (this.fnClose) {
            this.addListener('close', function (panel) {
                this.fnClose(this, 'close');
            });
            this.addListener('hide', function (sender) {
                this.fnClose(this, 'hide');
            });
        }

        if (this.maximizable) {
            this.addListener('maximize', function (sender) {
                sender.doLayout();
                if (this.fnResize) {
                    this.fnResize(sender, true, 'maximize');
                }
            });
            this.addListener('restore', function (sender) {
                sender.doLayout();
                if (this.fnResize) {
                    this.fnResize(sender, false, 'restore');
                }
            });
        }

        if (this._btnOk) {
            this._btnOk.addListener('click', function (senderBtn, e) {
                if (!me.isValid()) {
                    return;
                }

                me.doSubmit();
            });
        }

        this.addListener('show', function(){
            var km = this.getKeyMap();
            km.on(13, this.onEnterTab, this);
           // km.disable();
        });

        cfgFormPanel = Ext.apply({
            title: '',
            border: false,
            labelWidth: 33,
            bodyStyle: Exj.Panel.bodyStyle,
            clientValidation: true,
            layoutConfig: {
                labelSeparator: ':'
            },
            defaults: {
                msgTarget: 'qtip'
            },
            autoWidth: true,
            autoHeight: true,
            defaultType: 'textfield'
        }, cfgFormPanel);

        this._formW = new Ext.form.FormPanel(cfgFormPanel);

        this.add(this._formW);
    },
    getBasicForm: function () {
        return this._formW.getForm();
    },
    getFormPanelMain: function () {
        return this._formW;
    },
    setRawValueFieldsMoney: function(fields, rec){
        if (!fields || !fields.length || !rec || !rec.data) {
            return;
        }

        var bf = this.getBasicForm();

        for (var i = 0, nf, nuf, val; i < fields.length; i++) {
            nf = fields[i];
            nuf=bf.findField(nf);
            if (!nuf || !nuf.setRawValue) {
                Exj.mou('No existe Campo: '+nf, 'ERROR setRawValueFieldsMoney');
                continue;
            }

            val = rec.data[nf];
            if (val === undefined || Ext.num(val, null)===null) {
                continue;
            }

            nuf.setRawValue(Exj.rendererRound(val));
        }
    },
    findGridByChildKey: function(childKey){
        var gridChild = null;
        if(!childKey){
            return gridChild;
        }
        
        var grids = this.findByType('grid');
        for(var i=0; i < grids.length; i++){
            if(grids[i].childKey == childKey){
                gridChild = grids[i];
                break;
            }
        }
        
        return gridChild;
    },
    isValid: function (canShowMsg) {
        var f = this.getBasicForm();

        if (this.isReadOnlyAccessSubmit) {
            if (canShowMsg === false) {
                Exj.moi('No está permitido guardar, acceso de solo lectura.');
            }

            return false;
        }

        if (this.fnIsValidBefore) {
            if (this.fnIsValidBefore(f, this._formW) === false) {
                return false;
            }
        }


        if (!f.isValid()) {
            if (canShowMsg || canShowMsg === undefined) {
                Exj.mou('Existen errores en el formulario.<br />Por favor revizar', this.title);
            }

            if (this.fnIsValidAfter) {
                this.fnIsValidAfter(false, f, this._formW);
            }

            return false;
        }

        if (this.fnIsValidAfter) {
            if (this.fnIsValidAfter(true, f, this._formW) === false) {
                return false;
            }
        }

        if (this.fnIsValid) {
            return this.fnIsValid.call(this, f, this._formW);
        }

        return true;
    },
    _applyAutoAdjSizeComp: function (compAdjustSize) {

        this.addListener('beforeshow', function (senderWin) {
            if (!senderWin._addedListenersPanelsForLayout) {
                var pnlsInners = this.findByType(Ext.Panel);
                for (var i = 0, pnlInner = null; i < pnlsInners.length; i++) {
                    pnlInner = pnlsInners[i];
                    pnlInner.addListener('collapse', function (senderPnlx) {
                        senderWin.doLayout();
                    });
                    pnlInner.addListener('expand', function (senderPnlx) {
                        senderWin.doLayout();
                    });
                }

                senderWin._addedListenersPanelsForLayout = true;
            }
        });

        this.addListener('afterlayout', function (senderCont, layout) {
            // Exj.mou('desde base win. afterlayout');
            var heightOffset = compAdjustSize.exjAdjustSize.heightOffset;
            if (heightOffset == undefined) {
                heightOffset = 0;
            }

            var heightComps = 0;
            for (var i = 0, itemComp = null; i < compAdjustSize.exjAdjustSize.items.length; i++) {
                itemComp = compAdjustSize.exjAdjustSize.items[i];
                if (!itemComp.getResizeEl()) {
                    break;
                }

                heightOffset -= 2;

                heightComps += itemComp.getHeight();
            }

            if (!heightComps) {
                return;
            }

            var hTotal = this.getInnerHeight();
            var hToFix = hTotal - heightComps + heightOffset;
            if (hToFix < 135) {
                //  Exj.moi('Height of the window is very small');
                hToFix = 135;
            }

            //  Exj.mou('Fijando alto: '+hToFix);

            compAdjustSize.setHeight(hToFix);
            /*
             if(compAdjustSize.syncSize){
             compAdjustSize.syncSize();
             compAdjustSize.doLayout(true, true);
             }
             */
        });
    },
    addToForm: function (obj) {
        if (!obj) {
            return;
        }

        if (obj.exjAdjustSize) {
            this._applyAutoAdjSizeComp(obj);
        }

        this._formW.add(obj);
    },
    getFieldFromName: function (nameField) {
        return Exj.getFieldFromName(this._formW, nameField);
    },
    clearInvalid: function () {
        return this.getBasicForm().clearInvalid();
    },
    isDirty: function () {
        return this.getBasicForm().isDirty();
    },
    getValues: function () {
        return this.getBasicForm().getValues();
    },
    loadRecord: function (r) {
        return this.getBasicForm().loadRecord(r);
    },
    reset: function () {
        this.forceDirtyOnlyOnReadFields(false);
        return this.getBasicForm().reset();
    },
    getURLToSubmit: function () {
        if (this.onlyModeLocal) {
            return '';
        }

        if (this.urlSubmit) {
            return Exj.addParamsURL(this.urlSubmit);
        }

        if (this.hUrl) {
            if (!(this.hUrl instanceof Exj.HUrl)) {
                alert('Se ha enviado como parámetro hUrl, pero no es una instancia de la clase: Exj.HUrl');
                return null;
            }

            this.setId(this.idValue);

            if (this.isNew) {
                return this.hUrl.getActionCreate();
            } else {
                return this.hUrl.getActionUpdate();
            }
        }

        return null;
    },
    setId: function (id) {
        if (this.hUrl && this.hUrl.setId) {
            this.hUrl.setId(id);
        }

        this.isNew = !(id && (id != '0'));
        if (Ext.isNumber(id) && id < 0) {
            this.isNew = true;
        }

        if (this.idValue != id) {
            this.idValue = id;
        }
    },
    isNewData: function(){
        if (this.isNew === undefined && Ext.isNumber(this.idValue) && this.idValue > 0) {
            return false;
        }

        return this.isNew;
    },
    getFieldValues: function (dirtyOnly, onlyEnabled) {
        var o = {}, n, key, val, nProps = 0, isDirtyField;
        if (onlyEnabled === undefined) {
            onlyEnabled = false;
        }

        var bf = this.getBasicForm();

        var _fnReadField = function (f) {
            if (onlyEnabled && f.disabled) {
                return true; // continue
            }

            if (f.isFormField) {
                if (f.isComposite) {
                    f.items.each(_fnReadField);
                    return true; // continue
                }
                /*
                 else if(f instanceof Ext.form.CheckboxGroup && f.rendered){
                 f.eachItem(_fnReadField);
                 return true;
                 }
                 */
            }


            val = Exj.getValueFromCmp(f);
            isDirtyField = String(val) !== String(f.originalValue);
            /*
             if(f instanceof Ext.form.Hidden){
             isDirtyField = true;
             --nProps;
             }
             */

            if (dirtyOnly !== true || isDirtyField) {
                if (f.getName) {
                    n = f.getName();
                } else {
                    n = f.name;
                }
                if (!n) {
                    n = f.id;
                }

                key = o[n];

                if (Ext.isDefined(key)) {
                    if (Ext.isArray(key)) {
                        o[n].push(val);
                    } else {
                        o[n] = [key, val];
                    }
                } else {
                    o[n] = val;
                    ++nProps;
                }
            }
        };

        bf.items.each(_fnReadField);

        if (this.fnGetFieldsExtras) {
            var fieldsExtras = this.fnGetFieldsExtras();
            for (var i = 0, fieldExtra; i < fieldsExtras.length; i++) {
                fieldExtra = fieldsExtras[i];
                _fnReadField(fieldExtra);
            }
        }


        var childsList = this._formW.findByType(Ext.grid.GridPanel);
        if (childsList && childsList.length > 0) {
            var dataChilds = [];
            for (var i = 0, gridChild; i < childsList.length; i++) {
                gridChild = childsList[i];
                if (!gridChild.childEditable) {
                    continue;
                }

                var dataChangeChild = Exj.getDataChangesFromStore(gridChild.getStore());
                dataChangeChild.haveChanges = (dataChangeChild.haveChanges ? 1 : 0);

                if (dataChangeChild.haveChanges) {
                    dataChilds.push({
                        childKey: gridChild.childKey,
                        option: gridChild.childOption,
                        nameList: gridChild.childList,
                        nameEditable: gridChild.childEditable,
                        parentEditable: gridChild.parentEditable,
                        data: dataChangeChild
                    });

                    if (dataChangeChild.news && dataChangeChild.news.length) {
                        nProps += dataChangeChild.news.length;
                    }
                    if (dataChangeChild.edited && dataChangeChild.edited.length) {
                        nProps += dataChangeChild.edited.length;
                    }
                    if (dataChangeChild.idsDeleted && dataChangeChild.idsDeleted.length) {
                        nProps += dataChangeChild.idsDeleted.length;
                    }
                }

            }
            if (dataChilds.length > 0) {
                if (Ext.isDefined(o._dataChilds)) {
                    Exj.moe('Un componente tiene el nombre: _dataChilds.<br/>Este es un nombre revervado, se tiene que deninir otro nombre', 'ERROR EN DEFINICION DE CAMPOS');
                } else {
                    o._dataChilds = dataChilds;
                }
            }
        }

        if (nProps <= 0) {
            return null;
        }

        return o;
    },
    getFieldAllValues: function (dirtyOnly) {
        var fieldAllValues = {}, n, key, val, nProps = 0, isDirtyField, rCombo;

        var _fnReadAllField = function (f) {
            val = Exj.getValueFromCmp(f);
            isDirtyField = String(val) !== String(f.originalValue);

            if (dirtyOnly === true && !isDirtyField) {
                return true; // continue
            }

            if (f.getName) {
                n = f.getName();
            } else {
                n = f.name;
            }
            if (!n) {
                n = f.id;
            }

            if (f instanceof Ext.form.ComboBox) {
                rCombo = f.findRecord(f.valueField, val);
                if (rCombo) {
                    for (nameProp in rCombo.data) {
                        if (nameProp == 'value' || nameProp == 'text') {
                            continue;
                        }
                        fieldAllValues[nameProp] = rCombo.data[nameProp];
                        ++nProps;
                    }
                }
            }

            key = fieldAllValues[n];

            if (Ext.isDefined(key)) {
                if (Ext.isArray(key)) {
                    fieldAllValues[n].push(val);
                } else {
                    fieldAllValues[n] = val; // sobrescribe
                }
            } else {
                fieldAllValues[n] = val;
                ++nProps;
            }
        };

        this.getBasicForm().items.each(_fnReadAllField);

        if (nProps <= 0) {
            return null;
        }

        return fieldAllValues;
    },
    getAllFields: function (dirtyOnly) {
        var allFields = [], val, nProps = 0, isDirtyField;

        var _fnReadAllField = function (f) {
            val = Exj.getValueFromCmp(f);
            isDirtyField = String(val) !== String(f.originalValue);

            if (dirtyOnly === true && !isDirtyField) {
                return true; // continue
            }

            allFields.push(f);
            ++nProps;
        };

        this.getBasicForm().items.each(_fnReadAllField);

        if (nProps <= 0) {
            return null;
        }

        return allFields;
    },
    getMethodToSubmit: function () {
        if (this.methodToSubmit) {
            return this.methodToSubmit;
        }

        if (!this.isNew) {
            return 'PUT';
        }

        return 'POST';
    },
    forceDirtyOnlyOnReadFields: function (isForce) {
        if (isForce === undefined) {
            isForce = true;
        }

        this._forceDirtyOnlyOnReadFields = (isForce ? true : false);
    },
    doSubmit: function (options) {
        var me = this;
        if (!options) {
            options = {};
        }

        if (me.isReadOnlyAccessSubmit) {
            Exj.moi('No está permitido guardar es solo de lectura.');
            return false;
        }

        var dirtyOnly = !me.isNew;

        if (me._forceDirtyOnlyOnReadFields) {
            dirtyOnly = true;
        }

        var dataChanged = this.getFieldValues(dirtyOnly, me.onlyEnabledDataChange);

        if (me.fnGetDataChangesExtras && Ext.isFunction(me.fnGetDataChangesExtras)) {
            var dataChangesExtras = me.fnGetDataChangesExtras(dataChanged);
            if (dataChangesExtras && Ext.isObject(dataChangesExtras)) {
                if (!dataChanged) {
                    dataChanged = {};
                }

                dataChanged = Ext.apply(dataChangesExtras, dataChanged);
            }
        }

        // verificar si el objeto está vacio


        if (!dataChanged) {
            Exj.moi('No ha realizado ningún cambio!', this.title);
            return false;
        }

        var bf = this.getBasicForm();

        if (this.fnBeforeSubmit) {
            if (this.fnBeforeSubmit(dataChanged) === false) {
                return false;
            }
            if (!bf.isValid()) {
                Exj.moe('Existen errores en el formulario.<br/>Por favor revizar...', me.title);
                return false;
            }
        }

        options = Ext.apply({
            clientValidation: true,
            url: me.getURLToSubmit(),
            params: {}
        }, options);

        if (!options.url && !me.onlyModeLocal) {
            Exj.moe('No se ha indicado la url!', this.title);
            return false;
        }

        if (me.params && Ext.isObject(me.params)) {
            Ext.apply(options.params, me.params);
        }

        options.params.data = Ext.apply({
            isNew: (me.isNew ? 1 : 0),
            id: parseInt(me.idValue)
        }, options.params.data);

        if (me.isRestFul !== undefined) {
            options.params.isRestFul = me.isRestFul;
        }

        if (this.fnGetParamsData) {
            var paramsData = this.fnGetParamsData(this, bf);
            if (paramsData && Ext.isArray(paramsData) && paramsData.length) {
                for (var i = 0, paramData; i < paramsData.length; i++) {
                    paramData = paramsData[i];
                    // se hace merge de parametros
                    options.params.data = Ext.apply(
                            paramData,
                            options.params.data
                            );
                }
            }
        }

        options.params.data = Ext.encode(options.params.data);
        options.params.dataChanged = Ext.encode(dataChanged);
        if (options.params.dataChanged == '{}') {
            Exj.moi('No hay cambios hechos!', this.title);
            return false;
        }

        if (me._formW.fileUpload) {
            options.params.isRestFul = false;
            if (options.timeOutSec === undefined) {
                options.timeOutSec = 60;
            }
        }

        if (me._timeOutSec && !options.timeOutSec) {
            options.timeOutSec = me._timeOutSec;
        }

        if (options.timeOutSec) {
            bf.timeout = options.timeOutSec;
           /* alert('bf.timeout: '+bf.timeout); */
        }

        if (me.onlyModeLocal) {
            if (me.fnClientSuccess) {
                var dataAllChanged = me.getFieldAllValues(!me.isNew);

                if (me.fnClientSuccess.call(me, bf, dataAllChanged, dataChanged) === false) {
                    return;
                }
            }

            me.closeWinSubmit();

            return true;
        }

        me.isSaving = true;

        return bf.submit({
            clientValidation: options.clientValidation,
            method: me.getMethodToSubmit(),
            url: options.url,
            waitTitle: 'Por favor espere...',
            waitMsg: me.waitMsg,
            params: options.params,
            success: function (form, action) {
                me.isSaving = false;

                if (!Exj.isSuccessResponse(action.result)) {
                    if (me.fnFailure) {
                        me.fnFailure(form, action.result);
                    }
                    return;
                }

                if (me.isSuccessActionReset) {
                    me.resetWinSubmit();
                } else if (!me.isSuccessActionNone) {
                    me.closeWinSubmit();
                }

                // me.fireEvent('successSave', me, form, action);

                if (me.fnSuccess) {
                    me.isSaving = true;
                    setTimeout(function () {
                        me.isSaving = false;
                        me.fnSuccess(form, action.result, action);
                        if (options.fnCallbackSuccess) {
                            options.fnCallbackSuccess(form, action.result, action);
                        }
                    }, 300);
                } else if (options.fnCallbackSuccess) {
                    options.fnCallbackSuccess(form, action.result, action);
                }
            },
            failure: function (form, action) {
                me.isSaving = false;

                Exj.showMsgFailure(action);

                if (me.fnFailure) {
                    me.fnFailure(form, action.result);
                }
            }
        });
    },
    calcHeight: function (percent) {
        if (percent == undefined) {
            percent = 100;
        }

        var _h = this.height;
        if (this.isVisible()) {
            _h = this.getInnerHeight();
        }

        return Exj.round((_h * (percent / 100)), 3);
    },
    calcWidth: function (percent) {
        if (percent == undefined) {
            percent = 100;
        }
        var _w = this.width;
        if (this.isVisible()) {
            _w = this.getInnerWidth();
        }

        return Exj.round((_w * (percent / 100)), 3);
    },
    addButtonToolBar: function (btn) {
        return this.getTopToolbar().add(btn);
    },
    setDisabledCancel: function (pDisabled) {
        if (!this._btnCancel) {
            return false;
        }
        if (pDisabled === undefined) {
            pDisabled = true;
        }

        return this._btnCancel.setDisabled(pDisabled);
    },
    bindToContainer: function (record, fieldFocus) {
        if (fieldFocus == undefined) {
            fieldFocus = '';
        }

        return Exj.bindToContainer(this, record, fieldFocus, true);
    },
    callSave: function(){
        if (!this._btnOk || this._btnOk.disabled) {
            return false;
        }

        this._btnOk.fireEvent('click', this._btnOk);
        return true;
    }
});
