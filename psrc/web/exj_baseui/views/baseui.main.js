/*
 * Administración.
 * Parámetros Generales.
 * Fecha: 20/05/2012
 * Autor: Byron Córdova
 */

Exj.ui.modules.Baseui = function(senderMenu, pGen){
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
    Exj.ui.modules.Baseui.superclass.constructor.call(me, {
        id: 'idBaseui',
        title: '',
        border: false
    });
    
    _buildVar();
    _buildUI();
    _buildEvents();
    _run();
};

Ext.extend(Exj.ui.modules.Baseui, Ext.Panel);
