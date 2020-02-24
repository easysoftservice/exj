Ext.layout.ExjCenterToolbarLayout = Ext.extend(Ext.layout.ContainerLayout, {
    monitorResize: true,
    onLayout: function (ct, target) {
        Ext.layout.ExjCenterToolbarLayout.superclass.onLayout.call(this, ct, target);
        if (!this.container.collapsed) {
            var sz = (Ext.isIE6 && Ext.isStrict && target.dom == document.body) ? target.getViewSize() : target.getStyleSize();
            this.setItemSize(this.activeItem || ct.items.itemAt(0) || this.container, sz);
        }
    },
    setItemSize: function (item, size) {
        if (item) {
            var formEl = item.getEl();

            if (formEl.findParent) {
                var nodeFullSize = formEl.findParent('table.x-toolbar-ct');
                if (nodeFullSize && nodeFullSize.getSize) {
                    var newSize = nodeFullSize.getSize().size;
                    size.width = newSize.x;
                }
            }
            

            if (!size.width) {
                return;
            }

            var widthCenter = (size.width / 2) - (formEl.getWidth() / 2);

            if (widthCenter <= 0) {
                return;
            }

            if (this._lastLeftFixed && (this._lastLeftFixed == widthCenter)) {
                return;
            }

            formEl.setStyle({
                left: widthCenter + 'px',
                top: '0px',
                position: 'absolute',
            });

            this._lastLeftFixed = widthCenter;
        }
    }
});
Ext.Container.LAYOUTS['exj_centertb'] = Ext.layout.ExjCenterToolbarLayout;

Ext.layout.ExjGridCenterLayout = Ext.extend(Ext.layout.ContainerLayout, {
    monitorResize: true,
    onLayout: function (ct, target) {
        Ext.layout.ExjGridCenterLayout.superclass.onLayout.call(this, ct, target);

        if (!this.container.collapsed) {
            var sz = (Ext.isIE6 && Ext.isStrict && target.dom == document.body) ? target.getViewSize() : target.getStyleSize();

            if (this.activeItem) {
                this.setItemSize(this.activeItem, sz);
            } else {
                for (var indexGrid = 0; indexGrid < ct.items.getCount(); indexGrid++) {
                    this.setItemSize(ct.items.itemAt(indexGrid), sz);
                }
            }
        }
    },
    setItemSize: function (item, size) {
        if (!item) {
            return;
        }



        var componentEl = item.getEl(), totalWidth = 'auto';

        var widthCenter = (size.width / 2);
        if (item.view && item.view.cm && item.view.cm.getTotalWidth) {
            widthCenter -= (item.view.cm.getTotalWidth() / 2);
            totalWidth = item.view.getTotalWidth(); // retorna con px
        } else {
            widthCenter -= (componentEl.getWidth() / 2);
        }

        if (widthCenter <= 0) {
            return;
        }

        var componentsToFix = new Array(), nodesToFixAutoWidth = new Array();

        if (item.view && item.view.innerHd) {
            nodesToFixAutoWidth.push(item.view.mainHd.dom);
            componentsToFix.push({
                node: item.view.mainHd.dom,
                style: {
                    position: 'relative'
                }
            });

            nodesToFixAutoWidth.push(item.view.innerHd);
            var nodeHeaderOffset = item.view.innerHd.childNodes[0];
            if (nodeHeaderOffset) {
                if (nodeHeaderOffset.style) {
                    nodeHeaderOffset.style.paddingLeft = '0';
                }
                nodesToFixAutoWidth.push(nodeHeaderOffset);
            }

            componentsToFix.push({
                node: item.view.innerHd,
                style: {
                    position: ''
                }
            });

        }

        if (item.view && item.view.mainBody) {
            nodesToFixAutoWidth.push(item.view.mainBody.dom);
            componentsToFix.push({
                node: item.view.mainBody.dom
            });
        }

        /*
         if(item.bbar && item.bbar.dom){
         if(item.bbar.dom.childNodes[0]){
         nodesToFixAutoWidth.push(item.bbar.dom.childNodes[0]);
         }
         
         componentsToFix.push({
         node: item.bbar.dom,
         style: {
         position: 'relative'
         }
         });
         }
         */

        if (!componentsToFix.length) {
            return;
        }

        for (var i = 0, nodeToFixAutoWidth; i < nodesToFixAutoWidth.length; i++) {
            nodeToFixAutoWidth = nodesToFixAutoWidth[i];
            if (!nodeToFixAutoWidth || !nodeToFixAutoWidth.style) {
                continue;
            }

            nodeToFixAutoWidth.style.width = 'auto';
        }

        for (var i = 0, c = null, nodeX = null; i < componentsToFix.length; i++) {
            c = componentsToFix[i];
            if (!c || !c.node) {
                // alert('No hay nodo!');
                continue;
            }
            /*
             if(!c.node.setStyle){
             alert('componente no tiene setStyle. id: '+c.id);
             continue;
             }
             */

            if (c.node.dom) {
                nodeX = c.node.dom;
            } else {
                nodeX = c.node;
            }

            if (!nodeX.style) {
                nodeX.style = new Object();
            }

            c.style = Ext.apply({
                'float': 'left',
                left: widthCenter + 'px',
                position: 'absolute',
                width: totalWidth
            }, c.style ? c.style : {});

            Ext.apply(nodeX.style, c.style);
        }
    }
});
Ext.Container.LAYOUTS['exj_gridcenter'] = Ext.layout.ExjGridCenterLayout;
