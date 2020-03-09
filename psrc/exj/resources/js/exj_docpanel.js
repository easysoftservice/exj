Exj.DocPanel = Ext.extend(Ext.Panel, {
    closable: true,
    autoScroll: true,
    initComponent: function () {
        if (this.optsBar && this.optsBar.length) {
            var _tbar = ['->'];
            for (var i = 0, optBar; i < this.optsBar.length; i++) {
                optBar = this.optsBar[i];
                _tbar.push({
                    text: optBar.text,
                    handler: this.scrollToMember.createDelegate(this, [optBar.member]),
                    iconCls: optBar.iconCls
                });
                if (i < this.optsBar.length - 1) {
                    _tbar.push('-');
                }
            }

            Ext.apply(this, {
                tbar: _tbar
            });
        }

        Exj.DocPanel.superclass.initComponent.call(this);
    },
    scrollToMember: function (member) {
        var el = Ext.fly(member);
        if (el) {
            var top = (el.getOffsetsTo(this.body)[1]) + this.body.dom.scrollTop;
            this.body.scrollTo('top', top - 25, {duration: 0.75, callback: this.hlMember.createDelegate(this, [member])});
        }
    },
    scrollToSection: function (id) {
        var el = Ext.getDom(id);
        if (el) {
            var top = (Ext.fly(el).getOffsetsTo(this.body)[1]) + this.body.dom.scrollTop;
            this.body.scrollTo('top', top - 25, {duration: 0.5, callback: function () {
                    Ext.fly(el).next('h2').pause(0.2).highlight('#8DB2E3', {attr: 'color'});
                }});
        }
    },
    hlMember: function (member) {
        var el = Ext.fly(member);
        if (el) {
            if (tr = el.up('tr')) {
                tr.highlight('#cadaf9');
            }
        }
    }
});
