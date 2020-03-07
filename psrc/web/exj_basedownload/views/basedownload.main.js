/*
 * Base.
 * Descarga de Archivos.
 * Fecha: 02/09/2012
 * Autor: Byron Córdova
 */

Exj.ui.modules.Basedownload = function(senderMenu, pGen){
	var me = this;
	var pnlCriteria;
	
    function _buildVar(){
    	pnlCriteria = new Ext.Panel({
    		title: 'Filtros',
    		collapsible: true
    	});
    }; // _buildVar
    
    function _buildUI(){
    	me.add(pnlCriteria);
    	
    }; // _buildUI
    function _buildEvents(){
    	
    }; // _buildEvents
    function _run(){
    	
    }; // _run
    
	
	/* --- INIT --- */
    Exj.ui.modules.Basedownload.superclass.constructor.call(me, {
        id: 'idBasedownload',
        title: '',
        border: false
    });
    
    _buildVar();
    _buildUI();
    _buildEvents();
    _run();
};

Ext.extend(Exj.ui.modules.Basedownload, Ext.Panel);
