Exj.mail.preview = function (cfg) {
    cfg = cfg || {};
    cfg.actionSend = 'preview';

    if (!cfg.mask) {
        cfg.mask = 'Mail Preview Loading...';
    }

    cfg.fnSuccess = function (response) {
        var dataMail = response.data;
        /*
         dataMail.from
         dataMail.sender
         dataMail.email
         dataMail.subject
         dataMail.body
         dataMail.isHTML
         dataMail.cc_mail
         dataMail.attachment
         */

        var tabsContentsMail = new Ext.TabPanel({
            title: 'BODY',
            activeTab: 0,
            autoWidth: true,
            height: Exj.calcHeight(66),
            plain: true,
            defaults: {
                autoScroll: true,
                layout: 'fit',
                readOnly: true
            },
            items: [{
                    title: 'FUENTE',
                    items: [{
                            xtype: 'textarea',
                            fieldLabel: 'Body',
                            hideLabel: true,
                            value: dataMail.body,
                            flex: 1
                        }
                    ]
                }, {
                    title: 'VISTA PREVIA',
                    height: 210,
                    frame: true,
                    html: dataMail.body
                }
            ]
        });

        var formMail = new Ext.form.FormPanel({
            baseCls: 'x-plain',
            labelWidth: 55,
            height: 150,
            /*   autoHeight: true, */
            layout: 'form',
            defaults: {
                xtype: 'textfield',
                readOnly: true,
                anchor: '99%'
            },
            items: [{
                    fieldLabel: 'From',
                    value: dataMail.from
                }, {
                    fieldLabel: 'Subject',
                    value: dataMail.subject
                }, {
                    fieldLabel: 'E-mail',
                    value: dataMail.email
                }, {
                    fieldLabel: 'CC',
                    value: dataMail.cc_mail
                }, {
                    fieldLabel: 'CCO',
                    value: dataMail.bcc_mail
                }]
        });

        var winPreviewMail = Exj.newWindow({
            title: 'Vista previa de correo',
            modal: true,
            closable: true,
            maximizable: true,
            autoHeight: true,
            width: Exj.calcWidth(90),
            buttonAlign: 'center',
            fnCerrar: function () {

            },
            items: [
                formMail,
                tabsContentsMail
            ]
        });

        /*
         winPreviewMail.addListener('show', function(senderWin){
         
         });
         */

        winPreviewMail.show();
    };

    Exj.mail.send(cfg);
}; // Exj.mail.preview

Exj.mail.send = function (cfg) {
    cfg = Ext.apply({
        idMail: 0,
        mask: 'Enviando correo...',
        idMask: null,
        fnSuccess: null,
        actionSend: 'send'
    }, cfg);


    if (!cfg.idMail) {
        Exj.moe('No se guardó el correo', 'Envío de Correo');
        return false;
    }

    cfg.idMail = parseInt(cfg.idMail);
    if (!cfg.idMail) {
        Exj.moe('No se retornó id del correo');
        return false;
    }

    var hUrlMail = new Exj.HUrl({
        option: 'app_admin_mails',
        controller: 'sendmails'
    });

    Exj.submit({
        method: 'POST',
        url: hUrlMail.getActionCustom(cfg.actionSend),
        isUrlWithExtras: true,
        params: {
            id: cfg.idMail,
            isRestFul: false
        },
        idMask: cfg.idMask,
        mask: cfg.mask,
        showResult: false,
        fnSuccess: function (response) {
            if (cfg.fnSuccess) {
                cfg.fnSuccess(response);
            }
        },
        timeout: 45000,
        fnFailureShowMsg: function (response, e) {
            Exj.moi('El correo ha sido enviado.<br/>En unos instantes llegará el correo a sus destinatarios.', 'Envío de Correo...');
        }
    });

    return true;
}; // Exj.mail.send
