Ext.onReady(function () {

    /*
     Ext.override(Ext.grid.Column, {
     renderer: function(value){
     Exj.mou('value:'+value);
     return value;
     }
     });
     */

    Ext.override(Ext.form.ComboBox, {
        onSelect: function (record, index) {
            /* ---> Código adicionado */
            if (record.data.exjDisableSelect !== undefined) {
                if (record.data.exjDisableSelect) {
                    if (record.data.exjDisableMsg) {
                        Exj.mou(record.data.exjDisableMsg, 'No seleccionable');
                    }

                    return;
                }
            }

            if (this.valueField) {
                var valueRecord = record.data[this.valueField];

                if (valueRecord === '') {
                    this.clearValue();
                    this.collapse();
                    return;
                }
            }
            /* <--- Código adicionado */

            /* Ext.form.ComboBox.superclass.onSelect.call(this, record, index); */
            if (this.fireEvent('beforeselect', this, record, index) !== false) {
                this.setValue(record.data[this.valueField || this.displayField]);
                this.collapse();
                this.fireEvent('select', this, record, index);
            }
        },
        setValue: function (v) {
            var text = v;
            if (this.valueField) {
                var r = this.findRecord(this.valueField, v);
                if (r) {
                    text = r.data[this.displayField];
                    /* ---> Código adicionado */
                    if (v === '' || r.data.exjDisableSelect) {
                        if (!v) {
                            this.clearValue();
                            return this;
                        }

                        if (this.lastSelectionText != undefined) {
                            if (this.originalValue != v) {
                                this.reset();
                                return this;
                            }
                        }
                    }
                    /* <--- Código adicionado */

                } else if (Ext.isDefined(this.valueNotFoundText)) {
                    text = this.valueNotFoundText;
                }
            }

            this.lastSelectionText = text;
            if (this.hiddenField) {
                this.hiddenField.value = Ext.value(v, '');
            }
            Ext.form.ComboBox.superclass.setValue.call(this, text);
            this.value = v;
            return this;
        }
        /*
         validateBlur : function(){
         return !this.list || !this.list.isVisible();
         }
         */
    });

    /*
     Override to fix  box-sizing problem in chrome latest versions
     */
    if (!Ext.isDefined(Ext.webKitVersion)) {
        Ext.webKitVersion = Ext.isWebKit ? parseFloat(/AppleWebKit\/([\d.]+)/.exec(navigator.userAgent)[1], 10) : NaN;
    }

    /*
     Box-sizing was changed beginning with Chrome v19.  For background information, see:
     http://code.google.com/p/chromium/issues/detail?id=124816
     https://bugs.webkit.org/show_bug.cgi?id=78412
     https://bugs.webkit.org/show_bug.cgi?id=87536
     http://www.sencha.com/forum/showthread.php?198124-Grids-are-rendered-differently-in-upcoming-versions-of-Google-Chrome&p=824367
     
     */
    if (Ext.isWebKit && Ext.webKitVersion >= 535.2) {
        /* probably not the exact version, but the issues started appearing in chromium 19 */

        Ext.override(Ext.grid.ColumnModel, {
            getTotalWidth: function (includeHidden) {
                if (!this.totalWidth) {
                    var boxsizeadj = 2;
                    /* var boxsizeadj = 1; */
                    this.totalWidth = 0;
                    for (var i = 0, len = this.config.length; i < len; i++) {
                        if (includeHidden || !this.isHidden(i)) {
                            this.totalWidth += (this.getColumnWidth(i) + boxsizeadj);
                        }
                    }
                }
                return this.totalWidth;
            }
        });
        /* alert('overrride grid para calc total width'); */

        Ext.get(document.body).addClass('ext-chrome-fixes');
        Ext.util.CSS.createStyleSheet('@media screen and (-webkit-min-device-pixel-ratio:0) {.x-grid3-cell{box-sizing: border-box !important;}}', 'chrome-fixes-box-sizing');
    }

    Exj.addListenerGlobal = function (nameEvent, scopeComponent, fnEventGlobal) {
        var refDocumentMain = Ext.getDoc();
        if (!refDocumentMain || !refDocumentMain.addListener) {
            return;
        }

        if (!scopeComponent.id) {
            return false;
        }

        if (refDocumentMain._exjScopeComponent) {
            if (refDocumentMain._exjScopeComponent.id == scopeComponent.id) {
                if (refDocumentMain._exjNameEvToGlobal == nameEvent) {
                    refDocumentMain._exjScopeComponent = scopeComponent;
                    return true;
                }
            }
        }

        refDocumentMain._exjScopeComponent = scopeComponent;
        refDocumentMain._exjNameEvToGlobal = nameEvent;
        refDocumentMain._fnCallbackEventGlobal = fnEventGlobal;

        refDocumentMain._exjHandlerFnGlobal = function (e, scopeTarget, p) {
            if (!this._exjScopeComponent) {
                return;
            }

            if (this._exjScopeComponent.isDestroyed) {
                if (this.removeListener && this._exjNameEvToGlobal) {
                    this.removeListener(this._exjNameEvToGlobal, this._exjHandlerFnGlobal);
                }

                delete this._exjScopeComponent;
                delete this._exjNameEvToGlobal;
                delete this._fnCallbackEventGlobal;

                return;
            }

            if (!this._fnCallbackEventGlobal) {
                return;
            }

            this._fnCallbackEventGlobal(e, scopeTarget, p, this._exjScopeComponent);
        };

        refDocumentMain.addListener(nameEvent, refDocumentMain._exjHandlerFnGlobal);

        return true;
    };

    Exj.buildClaveCatastral = function (params) {
        params = Ext.apply({
            zona: 0,
            sector: 0,
            manz: 0,
            predio: 0,
            ph: '00'
        }, params);

        if (!params.ph) {
            params.ph = '00';
        }

        var valueCC = [
            params.zona,
            params.sector,
            params.manz,
            params.predio,
            params.ph
        ];

        return valueCC.join('.');
    };

    /*
     Ext.override(Ext.form.Radio, {
     checkedCls: 'x-form-radio-checked'
     });
     */

    /* Se sobrescribe para centrar los grids */
    Ext.override(Ext.grid.GridView, {
        getScrollOffsetVisible: function () {
            if (this.scroller && !this.scroller.isScrollable()) {
                // alert('scroll no visible');
                return 0;
            }

            if (!this.scroller || !this.scroller.dom || !this.scroller.dom.offsetWidth || (this.scroller.dom.offsetWidth <= this.scroller.dom.clientWidth)) {
                return Ext.num(this.scrollOffset, Ext.getScrollBarWidth());
            }

            return (this.scroller.dom.offsetWidth - this.scroller.dom.clientWidth);
        },
        disabledFixAdjCenter: false,
        focusRow: function (row) {
            this.disabledFixAdjCenter = true;

            this.focusCell(row, 0, false);

            this.disabledFixAdjCenter = false;
        },
        syncFocusEl: function (row, col, hscroll) {
            var xy = row;

            if (!Ext.isArray(xy)) {
                row = Math.min(row, Math.max(0, this.getRows().length - 1));

                if (isNaN(row)) {
                    return;
                }

                xy = this.getResolvedXY(this.resolveCell(row, col, hscroll));
            }

            this.focusEl.setXY(xy || this.scroller.getXY());

            //   alert('syncFocusEl xy: '+xy);
            this.adjUICenter();
        },
        afterRender: function () {
            if (!this.ds || !this.cm) {
                return;
            }

            this.mainBody.dom.innerHTML = this.renderBody() || ' ';
            this.processRows(0, true);

            if (this.deferEmptyText !== true) {
                this.applyEmptyText();
            }

            this.grid.fireEvent('viewready', this.grid);

            this.adjUICenter();
        },
        updateColumnHidden: function (col, hidden) {
            var totalWidth = this.getTotalWidth(),
                    display = hidden ? 'none' : '',
                    headerCell = this.getHeaderCell(col),
                    nodes = this.getRows(),
                    nodeCount = nodes.length,
                    row, rowFirstChild, i;

            this.updateHeaderWidth();
            headerCell.style.display = display;

            for (i = 0; i < nodeCount; i++) {
                row = nodes[i];
                row.style.width = totalWidth;
                rowFirstChild = row.firstChild;

                if (rowFirstChild) {
                    rowFirstChild.style.width = totalWidth;
                    rowFirstChild.rows[0].childNodes[col].style.display = display;
                }
            }

            this.onColumnHiddenUpdated(col, hidden, totalWidth);
            delete this.lastViewWidth; //recalc
            this.layout();

            //   alert('updateColumnHidden');
            this.adjUICenter();
        },
        updateColumnWidth: function (column, width) {
            var columnWidth = this.getColumnWidth(column),
                    totalWidth = this.getTotalWidth(),
                    headerCell = this.getHeaderCell(column),
                    nodes = this.getRows(),
                    nodeCount = nodes.length,
                    row, i, firstChild;

            this.updateHeaderWidth();
            headerCell.style.width = columnWidth;

            for (i = 0; i < nodeCount; i++) {
                row = nodes[i];
                firstChild = row.firstChild;

                row.style.width = totalWidth;
                if (firstChild) {
                    firstChild.style.width = totalWidth;
                    firstChild.rows[0].childNodes[column].style.width = columnWidth;
                }
            }

            this.onColumnWidthUpdated(column, columnWidth, totalWidth);

            //   alert('updateColumnWidth');
            this.adjUICenter();
        },
        getCenterLeftOffset: function () {
            var centerLeftOffset = this.mainWrap.getWidth() / 2;
            centerLeftOffset -= this.cm.getTotalWidth() / 2;
            if (centerLeftOffset > 0) {
                centerLeftOffset -= this.getScrollOffsetVisible() - 1;
            }

            if (centerLeftOffset < 0) {
                centerLeftOffset = 0;
            } else if (centerLeftOffset > 0) {
                centerLeftOffset = parseInt(centerLeftOffset);
            }

            centerLeftOffset += 'px';
            return centerLeftOffset;
        },
        adjUICenter: function () {
            if (this.disabledFixAdjCenter) {
                return;
            }

            // 	alert('1. ejecutar: adjUICenter');

            if (!this.domOffsetHd) {
                this.domOffsetHd = this.mainHd.child('div.x-grid3-header-offset').dom;

                /*
                 this.scroller.addListener('resize', function(e, t, obj){
                 alert('adjUICenter. resize ');
                 });
                 */
            }

            var paddingLeft = this.getCenterLeftOffset();
            if (this._lastPaddingLeft == paddingLeft) {
                return;
            }
            this._lastPaddingLeft = paddingLeft;

            if (this.mainHd.isVisible()) {
                this.domOffsetHd.style.paddingLeft = paddingLeft;
            }

            this.mainBody.dom.style.paddingLeft = paddingLeft;

            if (!this._isAppliedFloatLeft) {
                this._isAppliedFloatLeft = true;

                if (this.mainHd.isVisible()) {
                    this.domOffsetHd.setStyle('float', 'left');
                }

                this.mainBody.dom.setStyle('float', 'left');
                ;
            }

            //	alert('2. ejecutar: adjUICenter. paddingLeft: ' + paddingLeft);
        }
    });


    Exj.ui.GridColumnModel = Ext.extend(Ext.grid.ColumnModel, {
        constructor: function (config) {
            var i = -1;
            var colItem;
            while (++i < config.length) {
                colItem = config[i];
                colItem.header = Exj.Idioma(colItem.header);
                if (colItem.sortable === undefined) {
                    colItem.sortable = true;
                }

                if (colItem.dataIndex == 'date_registry' || (colItem.dataIndex == 'modificado_dt')) {
                    if (!colItem.renderer) {
                        colItem.renderer = Exj.rendererFormatDateTime;
                    }
                }

                if (colItem.renderer == undefined) {
                    colItem.renderer = Exj.rendererText;
                }
            }

            Exj.ui.GridColumnModel.superclass.constructor.call(this, config);
        }
    }); // Exj.ui.GridColumnModel

// bco xxx
    Exj.ui.GridHTMLView = Ext.extend(Ext.grid.GridView, {
        initElements: function () {
            var Element = Ext.Element,
                    el = Ext.get(this.grid.getGridEl().dom.firstChild),
                    mainWrap = new Element(el.child('div.x-grid3-viewport')),
                    mainHd = new Element(mainWrap.child('div.x-grid3-header')),
                    scroller = new Element(mainWrap.child('div.x-grid3-scroller'));


            Ext.apply(this, {
                el: el,
                mainWrap: mainWrap,
                scroller: scroller,
                mainHd: mainHd,
                innerHd: mainHd.child('div.x-grid3-header-inner').dom,
                mainBody: new Element(Element.fly(scroller).child('div.x-grid3-body')),
                focusEl: new Element(Element.fly(scroller).child('a')),
                resizeMarker: new Element(el.child('div.x-grid3-resize-marker')),
                resizeProxy: new Element(el.child('div.x-grid3-resize-proxy'))
            });
        },
        render: function () {
            if (this.autoFill) {
                var ct = this.grid.ownerCt;

                if (ct && ct.getLayout()) {
                    ct.on('afterlayout', function () {
                        this.fitColumns(true, true);
                        this.updateHeaders();
                        this.updateHeaderSortState();
                    }, this, {single: true});
                }
            } else if (this.forceFit) {
                this.fitColumns(true, false);
            } else if (this.grid.autoExpandColumn) {
                this.autoExpand(true);
            }

            this.grid.getGridEl().dom.innerHTML = '<div align="center">' + this.renderUI() + '</div>';

            this.afterRenderUI();
        },
        renderUI: function () {
            return 'view renderUI poner el html';

            /*
            var templates = this.templates;

            return templates.master.apply({
                body: templates.body.apply({rows: ' '}),
                header: this.renderHeaders(),
                ostyle: 'width:' + this.getOffsetWidth() + ';',
                bstyle: 'width:' + this.getTotalWidth() + ';'
            });
            */
        },
        doRender: function (columns, records, store, startRow, colCount, stripe) {
            var templates = this.templates,
                    cellTemplate = templates.cell,
                    rowTemplate = templates.row,
                    last = colCount - 1,
                    tstyle = 'width:' + this.getTotalWidth() + ';',
                    /* buffers */
                    rowBuffer = [],
                    colBuffer = [],
                    rowParams = {tstyle: tstyle},
            meta = {},
                    len = records.length,
                    alt,
                    column,
                    record, i, j, rowIndex;

            for (j = 0; j < len; j++) {
                record = records[j];
                colBuffer = [];

                rowIndex = j + startRow;

                //build up each column's HTML
                for (i = 0; i < colCount; i++) {
                    column = columns[i];

                    meta.id = column.id;
                    meta.css = i === 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
                    meta.attr = meta.cellAttr = '';
                    meta.style = column.style;
                    meta.value = column.renderer.call(column.scope, record.data[column.name], meta, record, rowIndex, i, store);

                    if (Ext.isEmpty(meta.value)) {
                        meta.value = ' ';
                    }

                    if (this.markDirty && record.dirty && typeof record.modified[column.name] != 'undefined') {
                        meta.css += ' x-grid3-dirty-cell';
                    }

                    colBuffer[colBuffer.length] = cellTemplate.apply(meta);
                }

                alt = [];
                //set up row striping and row dirtiness CSS classes
                if (stripe && ((rowIndex + 1) % 2 === 0)) {
                    alt[0] = 'x-grid3-row-alt';
                }

                if (record.dirty) {
                    alt[1] = ' x-grid3-dirty-row';
                }

                rowParams.cols = colCount;

                if (this.getRowClass) {
                    alt[2] = this.getRowClass(record, rowIndex, rowParams, store);
                }

                rowParams.alt = alt.join(' ');
                rowParams.cells = colBuffer.join('');

                rowBuffer[rowBuffer.length] = rowTemplate.apply(rowParams);
            }

            //   rowBuffer.push('hola xxx');
            //	return 'test xxx';

            //	return '<div align="center">'+ rowBuffer.join('') + '</div>';
            return rowBuffer.join('');
        },
        afterRender: function () {
            if (!this.ds || !this.cm) {
                return;
            }

            this.mainBody.dom.innerHTML = this.renderBody() || ' ';
            //   this.mainBody.dom.innerHTML = ' hola vacio';
            this.processRows(0, true);

            if (this.deferEmptyText !== true) {
                this.applyEmptyText();
            }

            this.grid.fireEvent('viewready', this.grid);
        },
        afterRenderUI: function () {

        },
        refresh: function (headersToo) {
            this.fireEvent('beforerefresh', this);

            this.grid.stopEditing(true);

            if (!this.mainBody) {
                return;
            }

            var result = this.renderBody();
            this.mainBody.update(result).setWidth(this.getTotalWidth());
            if (headersToo === true) {
                this.updateHeaders();
                this.updateHeaderSortState();
            }
            this.processRows(0, true);
            this.layout();
            this.applyEmptyText();
            this.fireEvent('refresh', this);
        }
    });

// Exj.ui.GridHTMLPanel = Ext.extend(Ext.grid.GridPanel, {
    Exj.ui.GridHTMLPanel = Ext.extend(Ext.Panel, {
        loadMask: false,
        minColumnWidth: 24,
        view: null,
        rendered: false,
        viewReady: false,
        initComponent: function () {
            Exj.ui.GridHTMLPanel.superclass.initComponent.call(this);
            if (Ext.isArray(this.columns)) {
                this.colModel = new Ext.grid.ColumnModel(this.columns);
                delete this.columns;
            }

            if (this.ds) {
                this.store = this.ds;
                delete this.ds;
            }
            if (this.cm) {
                this.colModel = this.cm;
                delete this.cm;
            }
            if (this.sm) {
                this.selModel = this.sm;
                delete this.sm;
            }
            this.store = Ext.StoreMgr.lookup(this.store);
        },
        constructor: function (config) {
            config.title = Exj.Idioma(config.title);

            if (config.trackMouseOver === undefined) {
                config.trackMouseOver = false;
            }
            if (config.sm == undefined) {
                config.sm = new Ext.grid.RowSelectionModel({
                    singleSelect: true
                });
            }

            if (config.loadMask == undefined) {
                config.loadMask = Exj._buildLoadMask(config.title);
            }

            Exj.ui.GridHTMLPanel.superclass.constructor.call(this, config);
        },
        getView: function () {
            if (!this.view) {
                this.view = new Exj.ui.GridHTMLView(this.viewConfig);
            }

            return this.view;
        },
        getGridEl: function () {
            return this.body;
        },
        stopEditing: Ext.emptyFn,
        getStore: function () {
            return this.store;
        },
        getColumnModel: function () {
            return this.colModel;
        },
        onRender: function (ct, position) {
            Exj.ui.GridHTMLPanel.superclass.onRender.apply(this, arguments);
            //	return;

            var c = this.getGridEl();

            this.el.addClass('x-grid-panel');

            /*
             this.mon(c, {
             scope: this,
             mousedown: this.onMouseDown,
             click: this.onClick,
             dblclick: this.onDblClick,
             contextmenu: this.onContextMenu
             });
             
             this.relayEvents(c, ['mousedown','mouseup','mouseover','mouseout','keypress', 'keydown']);
             */

            var view = this.getView();
            view.init(this);
            view.render();
            this.getSelectionModel().init(this);
        },
        getSelectionModel: function () {
            if (!this.selModel) {
                this.selModel = new Ext.grid.RowSelectionModel(
                        this.disableSelection ? {selectRow: Ext.emptyFn} : null);
            }
            return this.selModel;
        }
    }); // Exj.ui.GridHTMLPanel

    Exj.ui.GridPanel = Ext.extend(Ext.grid.GridPanel, {
        constructor: function (config) {
            config.title = Exj.Idioma(config.title);

            if (config.trackMouseOver === undefined) {
                config.trackMouseOver = false;
            }
            if (config.sm == undefined) {
                config.sm = new Ext.grid.RowSelectionModel({
                    singleSelect: true
                });
            }

            if (config.viewConfig == undefined) {
                if (config.autoExpandColumn) {
                    config.viewConfig = {
                        forceFit: false
                    };
                } else {
                    config.viewConfig = {
                        forceFit: true
                    };
                }
            }
            if (config.loadMask == undefined) {
                config.loadMask = Exj._buildLoadMask(config.title);
            }

            // alert('tipo cm: '+ typeof config.cm+' ');

            Exj.ui.GridPanel.superclass.constructor.call(this, config);
        }
    }); // Exj.ui.GridPanel

    Exj.ui.GridEditor = Ext.extend(Ext.grid.EditorGridPanel, {
        constructor: function (config) {
            config.title = Exj.Idioma(config.title);

            if (config.clicksToEdit == undefined) {
                config.clicksToEdit = 1;
            }
            if (config.trackMouseOver === undefined) {
                config.trackMouseOver = false;
            }
            if (config.viewConfig == undefined) {
                if (config.autoExpandColumn) {
                    config.viewConfig = {
                        forceFit: false
                    };
                } else {
                    config.viewConfig = {
                        forceFit: true
                    };
                }
            }

            if (config.loadMask == undefined) {
                config.loadMask = Exj._buildLoadMask(config.title);
            }
            if (config.sm == undefined) {
                config.sm = new Ext.grid.RowSelectionModel({
                    singleSelect: true
                });
            }

            Exj.ui.GridEditor.superclass.constructor.call(this, config);
        }
    });

}); // Ext.onReady
