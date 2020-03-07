/*
 * Base.
 * Reportes.
 * Fecha: 25/01/2015
 * Autor: Byron Córdova
 */

Exj.ui.modules.BaseReports = function(senderMenu, pGen){
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
    Exj.ui.modules.BaseReports.superclass.constructor.call(me, {
        id: 'idBaseReports',
        title: '',
        border: false
    });
    
    _buildVar();
    _buildUI();
    _buildEvents();
    _run();
};

Ext.extend(Exj.ui.modules.BaseReports, Ext.Panel);
