/*
 * Acerca de..
 * Fecha: 19/01/2015
 * Autor: Byron Córdova
 */

Exj.ui.modules.AcercadeApp = function(senderMenu, pGen){
	var hUrl = new Exj.HUrl({
        controller: 'acercade_apps'
    });

	Exj.submitAction({
		method: 'GET',
        mask: 'Por favor espere...',
        url: hUrl.getActionView('getDataInfo'),
        withParams: false,
        fnSuccess: function (response) {
        	var dataWin = response.data;
        	// console.log('dataWin: ', dataWin);
        	if (!dataWin || (!dataWin.html && !dataWin.items)) {
        		return;
        	}

        	if (!dataWin.title) {
        		dataWin.title = senderMenu.text+' '+ Exj.TITLE;
        	}

        	if (!dataWin.height) {
        		dataWin.height = Exj.calcHeight(54);
        	}

        	if (!dataWin.width) {
        		dataWin.width = Exj.calcWidth(36);
        	}

        	if (dataWin.maximizable === undefined) {
        		dataWin.maximizable = false;
        	}

        	if (dataWin.html) {
                dataWin.html = Exj.replaceVarsGenerals(dataWin.html);
        	}
        	
        	Exj.showHTML(dataWin);
        }
	});
	
	return false;
};
