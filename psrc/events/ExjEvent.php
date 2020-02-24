<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class ExjEvent {
	private static $_events = array();

	public static function On($nameEvent, $func, $classScope='.'){
		if (!isset(self::$_events[$classScope])) {
			self::$_events[$classScope] = array();
		}

		self::$_events[$classScope][$nameEvent][] = $func;
	}

	public static function Fire($nameEvent, $args = array(), $senderObj=null){
		// echo " -> $nameEvent";

		if (empty(self::$_events)) {
			return;
		}

		if ($senderObj && is_object($senderObj)) {
			$classScope = get_class($senderObj);
		}
		else{
			$classScope = '.';
		}

		if (!isset(self::$_events[$classScope]) || 
			!isset(self::$_events[$classScope][$nameEvent]))
		{
			return;
		}

		$funcs = self::$_events[$classScope][$nameEvent];

		$params = null;
		foreach ($funcs as $func) {

			if (!$params) {
				$params = array_merge(array($senderObj), $args);
			}

			if ($func instanceof Closure) {
				call_user_func_array($func, $params);
				continue;
			}

			if (is_string($func)) {
				$func($params);
			}
			/*
			else{
				echo "ERROR. EV. FUNC INCORRECTO. ";
				exit();
			}
			*/
		}
	}
}

?>