<?php

// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Plugin para presentación en la UI
 *
 */
class ExjPluginDisplay extends ExjPlugin {
	private $_itemsDisplay = null;
	
	public function __construct(&$scope){
		parent::__construct($scope);
		
		$this->loadInfoExtraDisplay();
	}
	
	public static function NewItem($label, $value){
		$item = new stdClass();
		$item->label = $label;
		$item->value = $value;
		
		return $item;
	}
	
	public function addItemDisplay($label, $value){
		if (!$this->_itemsDisplay) {
			$this->_itemsDisplay = array();
		}
		
		$this->_itemsDisplay[] = self::NewItem($label, $value);

		return $this;
	}
	
	public function getItemsDisplay(){
		return $this->_itemsDisplay;
	}
	
	/**
	 * Carga de items para presentación
	 *
	 */
	protected function loadInfoExtraDisplay(){
		
	}
}
?>