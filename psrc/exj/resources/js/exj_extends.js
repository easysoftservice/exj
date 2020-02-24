Exj.DataChanges = Ext.extend(Ext.util.Observable, {
    constructor: function (config) {
        config = Ext.apply({
            news: [],
            edited: [],
            idsDeleted: [],
            listeners: []
        }, config);

        this.addEvents({
            "load": true
        });
        

        // prop privadas
        this._news = config.news;
        this._edited = config.edited;
        this._idsDeleted = config.idsDeleted;

        // Copy configured listeners into *this* object so that the base class's
        // constructor will add them.
        this.listeners = config.listeners;

        // Call our superclass constructor to complete construction process.
        Exj.DataChanges.superclass.constructor.call(this, config);
    },
    setIdsDeleted: function (ids) {
        if (!ids) {
            return false;
        }

        if (!Ext.isArray(ids)) {
            if (Ext.isNumber(ids)) {
                ids = [ids];
            } else {
                return false;
            }
        }

        this._idsDeleted = ids;
        this.haveChanges = true
    },
    addNew: function (item) {
        this._news.push(item);
    },
    addEdited: function (item) {
        this._edited.push(item);
    },
    addIdDeleted: function (id) {
        this._idsDeleted.push(id);
    },
    isDirty: function () {
        return (this._idsDeleted.length > 0 || (this._news.length > 0) || (this._edited.length > 0));
    },
    getDataChange: function () {
        var dataChange = {
            news: this._news,
            edited: this._edited,
            idsDeleted: this._idsDeleted,
            haveChanges: false
        };

        if (!this.isDirty()) {
            return dataChange;
        }

        dataChange.haveChanges = true;

        return dataChange;
    },
    getItemInDataChange: function (fieldNameId, valueFieldId, refDataChange) {
        var response = {
            item: null,
            isNew: false,
            isEdit: false,
            isDelete: false
        };
        

        if (!refDataChange) {
            if (this.isDirty()) {
                refDataChange = this.getDataChange();
            } else {
                return response;
            }
        }

        if (refDataChange.news) {
            for (var i = 0, item = null; i < refDataChange.news.length; i++) {
                item = refDataChange.news[i];
                if (item[fieldNameId] === valueFieldId) {
                    response.item = item;
                    response.isNew = true;
                    break;
                }
            }
        }

        if (!response.isNew && refDataChange.edited) {
            for (var i = 0, item = null; i < refDataChange.edited.length; i++) {
                item = refDataChange.edited[i];
                if (item[fieldNameId] === valueFieldId) {
                    response.item = item;
                    response.isEdit = true;
                    break;
                }
            }
        }

        if (!response.item && refDataChange.idsDeleted) {
            for (var i = 0, itemId = null; i < refDataChange.idsDeleted.length; i++) {
                itemId = refDataChange.idsDeleted[i];
                if (itemId === valueFieldId) {
                    response.item = itemId;
                    response.isDelete = true;
                    break;
                }
            }
        }

        return response;
    },
    getDataChild: function (nameEditable, parentEditable) {
        var dataChild = {
            nameEditable: nameEditable,
            childKey: nameEditable,
            data: this.getDataChange(),
            parentEditable: ''
        };
        if (parentEditable) {
            dataChild.parentEditable = parentEditable;
        }

        return dataChild;
    },
    addDataChildToDataChange: function (params) {
        params = Ext.apply({
            dataChange: {},
            nameEditable: '',
            parentEditable: '',
            searchInParent: {
                fieldNameId: '',
                valueFieldId: 0
            },
            nameFieldKey: ''
        }, params);



        var me = this;
        var nameEditable = params.nameEditable;
        var parentEditable = params.parentEditable;

        if (!params.dataChange) {
            params.dataChange = {};
        }

        if (!params.dataChange._dataChilds) {
            params.dataChange._dataChilds = new Array();
        }

        var dc = this.getDataChild(nameEditable, parentEditable), indexFoundDC = -1;
        Ext.each(params.dataChange._dataChilds, function (item, index) {
            if (item.childKey == dc.childKey || (item.nameEditable == dc.nameEditable)) {
                indexFoundDC = index;
                return false;
            }
        });

        function _loadItemChildFromParent(itemParent, parentEditable, itemsDC) {
            if (!itemsDC || !itemsDC._dataChilds) {
                return itemParent;
            }

            if (itemParent.refItem) {
                return itemParent;
            }

            for (var i = 0, itemDC; i < itemsDC._dataChilds.length; i++) {
                itemDC = itemsDC._dataChilds[i];
                if (itemDC.childKey == parentEditable || (itemDC.nameEditable == parentEditable)) {
                    itemParent.refItem = itemDC;
                }

                if (itemParent.refItem) {
                    if (itemParent.refItem.data) {
                        if (params.searchInParent.fieldNameId) {
                            var responseItem = me.getItemInDataChange(params.searchInParent.fieldNameId, params.searchInParent.valueFieldId, itemParent.refItem.data);
                            if (responseItem.item && (responseItem.isNew || responseItem.isEdit)) {
                                itemParent.refItem = responseItem.item;
                                break;
                            } else {
                                itemParent = null;
                                break;
                            }
                        }
                    }
                } else {
                    _loadItemChildFromParent(itemParent, parentEditable, itemDC._dataChilds);
                    if (itemParent.refItem) {
                        break;
                    }
                }
            }
        }
        ;

        if (indexFoundDC == -1 || params.searchInParent.fieldNameId) {
            // buscamos a traves del padre
            var itemParent = {
                refItem: null
            };

            _loadItemChildFromParent(itemParent, parentEditable, params.dataChange);
            if (itemParent.refItem) {
                if (!itemParent.refItem._dataChilds) {
                    itemParent.refItem._dataChilds = new Array();
                }

                if (params.nameFieldKey && itemParent.refItem._dataChilds.length > 0) {
                    var foundItemDataChange = false, responseItemChild = null;
                    for (var i = 0, d = null; i < itemParent.refItem._dataChilds.length; i++) {
                        d = itemParent.refItem._dataChilds[i];

                        responseItemChild = me.getItemInDataChange(params.nameFieldKey, params.valueFieldKey, d.data);
                        // if(responseItem.item && (responseItem.isNew || responseItem.isEdit)){
                        if (responseItemChild.item) {
                            foundItemDataChange = true;
                            indexFoundDC = i;
                            break;
                        }
                    }

                    if (foundItemDataChange) {
                        // actualizar
                        if (indexFoundDC >= 0) {
                            itemParent.refItem._dataChilds[indexFoundDC] = dc;
                        }
                    } else {
                        itemParent.refItem._dataChilds.push(dc);
                    }
                } else {
                    itemParent.refItem._dataChilds.push(dc);
                }
            } else {
                if (indexFoundDC == -1) {
                    params.dataChange._dataChilds.push(dc);
                } else {
                    params.dataChange._dataChilds[indexFoundDC] = dc;
                }
            }
        } else {
            params.dataChange._dataChilds[indexFoundDC] = dc;
        }

        return params.dataChange;
    }
});

Exj.ui.Editable = Ext.extend(Ext.util.Observable, {
    constructor: function (uie) {
        uie = Ext.apply({
            items: [],
            listeners: []
        }, uie);

        if (uie.ui) {
            this.uie = uie.ui;
        } else {
            this.uie = uie;

            if (this.uie && this.uie.uie) {
                this.uie = this.uie.uie;
            }
        }

        this.addEvents({
            "load": true
        });

        // Copy configured listeners into *this* object so that the base class's
        // constructor will add them.
        this.listeners = uie.listeners;

        // Call our superclass constructor to complete construction process.
        Exj.ui.Editable.superclass.constructor.call(this, uie);
    },
    enableCache: function(enable){
        if(enable === undefined){
            enable = true;
        }
        
        this._enableCache = enable;
        return this;
    },
    getInstanceComponent: function(opts){
        var instanceCmp = null;
        if(this._enableCache){
            var nf = opts.nameField;
            
            if(!this._cache){
                this._cache = {};
            }        
            if(!this._cache[nf]){
                this._cache[nf] = opts.newInstance();
            }           
            instanceCmp = this._cache[nf];
        }
        else{
            instanceCmp = opts.newInstance();
        }

        instanceCmp.requestAjax = function(paramsRequest){
            paramsRequest = paramsRequest || {};

            if (!this._connAjax) {
                Exj.moe("requestAjax. No definido connAjax en: "+ this.name);
                return false;
            }

            if (!this._connAjax.url) {
                Exj.moe("requestAjax. No definido url en: "+ this.name);
                return false;
            }

            paramsRequest = Ext.apply(this._connAjax, paramsRequest);

            return Exj.submit(paramsRequest);
        };

        instanceCmp.getHttpProxy = function(){
            if (!this._httpProxy) {
                Exj.moe("No definido httpProxy en: "+ this.name);
                return null;
            }

            if (!this._httpProxy.conn) {
                Exj.moe("No definido conexión httpProxy en: "+ this.name);
                return null;
            }

            if (!(this._httpProxy instanceof Ext.data.HttpProxy)) {
                // this._httpProxy = new Ext.data.HttpProxy(this._httpProxy.conn);
                this._httpProxy = new Ext.data.HttpProxy(this._httpProxy);
            }

            return this._httpProxy;
        };
        
        return instanceCmp;
    },
    getPanel: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);

        if (cfg.fieldLabel == nameField) {
            delete cfg.fieldLabel;
        }

        return new Ext.Panel(cfg);
    },
    getTextField: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                return new Ext.form.TextField(cfg);
            }
        });
    },
    getNumberField: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                return (new Ext.form.NumberField(cfg));
            }
        });
    },
    getTimeField: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                return new Ext.form.TimeField(cfg);
            }
        });
    },
    getLabel: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);

        delete cfg.vtype;
        delete cfg.allowBlank;

        var lbl = new Ext.form.Label(cfg);

        lbl.setValue = function (v, encode) {
            if (!this._lastFieldLabelValue) {
                this._lastFieldLabelValue = this.label.dom.innerHTML;
            }

            var e = encode === false;
            this[!e ? 'text' : 'html'] = v;
            delete this[e ? 'text' : 'html'];

            if (this.rendered) {
                this.label.dom.innerHTML = this._lastFieldLabelValue + ' ' + v;
            }

            // this.setText(v, encode);
        };

        lbl.getValue = function () {
            if (this.text) {
                return this.text;
            }
            if (this.html) {
                return this.html;
            }

            if (this.rendered && this.el) {
                return this.el.dom.innerHTML;
            }

            return '';
        };

        return lbl;
    },
    getTextArea: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                return (new Ext.form.TextArea(cfg));
            }
        });
    },
    getHidden: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                return new Ext.form.Hidden(cfg);
            }
        });
    },
    getComboBox: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                var cmb = Exj.newComboBox(cfg);
                Exj.addLoadException(cmb);
                
                return cmb;
            }
        });
    },
    getDateField: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                var df = new Ext.form.DateField(cfg);
                //      alert('df.minText: '+df.minText);
                //      df.minText = 'La fecha para este campo debe ser después de {minValue}';
                
                return df;
            }
        });
    },
    getRadioGroup: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                return new Ext.form.RadioGroup(cfg);
            }
        });
    },
    getCheckbox: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        if (cfgDefault && !cfgDefault.fieldLabel && cfg.fieldLabel && cfg.boxLabel) {
            cfg.fieldLabel = '';
        }
        
        return this.getInstanceComponent({
            nameField: nameField,
            newInstance: function(){
                return (new Ext.form.Checkbox(cfg));
            }
        });
    },
    getGridPanel: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return (new Ext.grid.GridPanel(cfg));
    },
    getUIEditable: function (nameField, cfgDefault) {
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        return new Exj.ui.Editable(cfg);
    },
    getTreePanel: function (nameField, cfgDefault) {
        cfgDefault = cfgDefault || {};
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        cfg = Ext.apply({
            tevExpandRootNode: false,
            height: 66,
            root: {
                nodeType: 'async',
                text: 'Inicio',
                draggable: false,
                id: 'root_' + nameField
            }
        }, cfg);

        // var tree = new Ext.tree.TreePanel(cfg);
        var tree = Exj.newTreePanel(cfg);
        tree.getName = function () {
            return nameField;
        };

        Exj.catchLoadTreePanelLoader(tree, cfgDefault.fnLoadSuccess);
        var nodeRoot = tree.getRootNode();
        if (cfg.tevExpandRootNode) {
            nodeRoot.expand();
        }

        return tree;
    },
    getFileUploadField: function (nameField, cfgDefault) {
        cfgDefault = cfgDefault || {};
        var cfg = this.getCfgCmp(nameField, cfgDefault);
        cfg = Ext.apply({
            xtype: 'fileuploadfield',
            buttonCfg: {
                text: '',
                iconCls: 'app-btn-uploadfile'
            },
            listeners: {
            }
        }, cfg);

        if (!cfg.listeners.fileselected) {
            cfg.listeners.fileselected = Exj.files.onSelected;
        }

        return cfg;
  },
  getCfgCmp: function (nameField, componentDefault) {
        componentDefault = Ext.apply({
            fieldLabel: nameField,
            name: nameField
        }, componentDefault);

        var itemEditable = this.getItem(nameField);
        if (!itemEditable) {
            componentDefault.disabled = true;
            return componentDefault;
        }
        if (!itemEditable.control || !itemEditable.control.component) {
            componentDefault.disabled = true;
            return componentDefault;
        }

        var cfgCmpClone = Exj.cloneSmart(itemEditable.control.component);

        cfgCmpClone = Ext.apply(
            componentDefault,
            cfgCmpClone
        );

        return cfgCmpClone;
    },
    getItems: function () {
        if (!this.uie || !this.uie.items || !this.uie.items.length) {
            return null;
        }

        return this.uie.items;
    },
    each: function (fn) {
        var _items = this.getItems();
        if (!_items) {
            return _items;
        }

        for (var i = 0, item; i < _items.length; i++) {
            item = _items[i];
            if (fn(item.name, item, i) === false) {
                break;
            }
        }
        return this;
    },
    getItem: function (nameField) {
        if (!nameField) {
            return null;
        }

        var _items = this.getItems();
        if (!_items) {
            return _items;
        }

        var itemFound = null;
        this.each(function (name, item) {
            if (name == nameField) {
                itemFound = item;
                return false;
            }
        });

        return itemFound;
    },
    findComponent: function(nameField) {
        var item = this.getItem(nameField), cmp = null;
        if (!item) {
            return cmp;
        }

        if (item.control) {
            cmp = item.control.component;
        }

        return cmp;
    },
    getValueComponent: function(nameField, defValue) {
        var cmp = this.findComponent(nameField);
        if (cmp) {
            return cmp.value;
        }

        return (defValue === undefined ? null : defValue);
    },
    setUIEditable: function (uiEditable) {
        if (uiEditable.ui) {
            this.uie = uiEditable.ui;
        } else {
            this.uie = uiEditable;
        }
    },
    getParamUI: function(nameVar, defaultVal){
        if(!this.uie || !this.uie.params){
            return defaultVal;
        }
        if(this.uie.params[nameVar] === undefined){
            return defaultVal;
        }
        
        return this.uie.params[nameVar];
    },
    getParam: function(nameVar, defaultVal){
        return this.getParamUI(nameVar, defaultVal);
    },
    getParamGrid: function(nameVar, scope, defaultVal) {
        var listModel = this.getParam(nameVar, defaultVal);
        if (!listModel) {
            Exj.moe("No se ha definido parámetro: "+nameVar, 'ERROR PARAMETRO GRID');
            return defaultVal;
        }

        Exj.evalRendererListModel(listModel, scope);

        var grid = Exj.newGridPanelFromListModel(listModel);
        return grid;
    },
    resetItems: function (content, exceptNames) {
        var nReseted = 0;
        if (!content) {
            return nReseted;
        }

        if (exceptNames && !Ext.isArray(exceptNames)) {
            exceptNames = [exceptNames];
        }

        this.each(function (name, item, index) {
            if (exceptNames && Exj.inArray(name, exceptNames)) {
                return true;
            }

            var cmp = Exj.getFieldFromName(content, name);
            if (!cmp || !cmp.xtype) {
                return true; // continue
            }

            ++nReseted;
            if (cmp.reset) {
                cmp.reset();
                return true; // continue
            }

            cmp.setValue('');
        });

        return nReseted;
    },
    clearValuesItems: function (content, exceptNames, isExceptHidden) {
        var nOk = 0;
        if (!content) {
            return nOk;
        }

        if (exceptNames && !Ext.isArray(exceptNames)) {
            exceptNames = [exceptNames];
        }
        if (isExceptHidden === undefined) {
            isExceptHidden = true;
        }

        this.each(function (name, item, index) {
            if (exceptNames && Exj.inArray(name, exceptNames)) {
                return true; // continue
            }

            var cmp = Exj.getFieldFromName(content, name);
            if (!cmp || !cmp.xtype) {
                return true; // continue
            }

            if (isExceptHidden && cmp.hidden || (cmp instanceof Ext.form.Hidden)) {
                return true; // continue
            }


            ++nOk;

            if (Ext.isFunction(cmp.clearValue)) {
                cmp.clearValue();
                return true; // continue
            }

            if (Ext.isFunction(cmp.setValue)) {
                cmp.setValue('');
                return true; // continue
            }
        });

        return nOk;
    },
    disabledItemsToContent: function (content, disable, exceptNames) {
        var nDisabled = 0;
        if (!content) {
            return nDisabled;
        }
        if (disable === undefined) {
            disable = true;
        }

        if (exceptNames && !Ext.isArray(exceptNames)) {
            exceptNames = [exceptNames];
        }

        this.each(function (name, item, index) {
            if (exceptNames && Exj.inArray(name, exceptNames)) {
                return true; // continue
            }

            var cmp = Exj.getFieldFromName(content, name);
            if (!cmp) {
                return true; // continue
            }
            if (cmp.hidden || (cmp instanceof Ext.form.Hidden)) {
                return true; // continue
            }

            /*
             if(!(cmp instanceof Ext.form.Field)){
             return true; // continue
             }
             */

            ++nDisabled;
            if (cmp.disabledItems && Ext.isFunction(cmp.disabledItems)) {
                alert('disableItem Editable. Llamando a disabledItems del comp: ' + cmp.name);
                cmp.disabledItems(disable);
                return true; // continue
            }

            if (Ext.isFunction(cmp.setDisabled)) {
                if (disable && Ext.isFunction(cmp.clearInvalid)) {
                    cmp.clearInvalid();
                }

                cmp.setDisabled(disable);

                return true; // continue
            }
        });

        return nDisabled;
    },
    addDataToStore: function (nameField, dataToAdd) {
        var itemEditable = this.getItem(nameField);
        if (!itemEditable) {
            return false;
        }

        if (!itemEditable.control || !itemEditable.control.component) {
            return false;
        }

        var sto = itemEditable.control.component.store;
        if (!sto) {
            return false;
        }

        if (!sto.data) {
            sto.data = new Array();
        }

        if (Ext.isArray(dataToAdd)) {
            for (var i = 0; i < dataToAdd.length; i++) {
                sto.data.push(dataToAdd[i]);
            }
        } else {
            sto.data.push(dataToAdd);
        }

        return true;
    },
    getId: function(){
        var valId = 0, nameHidden='';

        if (!this.uie || !this.uie.data) {
            return valId;
        }

        if (this.uie.fieldKey) {
            nameHidden = this.uie.fieldKey
        }
        
        if (nameHidden && this.uie.data[nameHidden]) {
            valId = this.uie.data[nameHidden];
        }
        else if (this.uie.data.id) {
            valId = this.uie.data.id;
        }

        if (valId === undefined) {
            valId=0;
        }

        return valId;
    },
    getHiddenId: function(){
        if (!this.uie || !this.uie.fieldKey) {
            alert("ERROR. hiddenId. No existe uie en editableUI");
            return null;
        }

        return (new Ext.form.Hidden({
            name: this.uie.fieldKey,
            value: this.getId()
        }));
    },
    getEditableModel: function(){
        return this.uie;
    }
});
