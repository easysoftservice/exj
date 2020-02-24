<?php

class ExjStorage {
	
	public static function GetDirBase(){
		return Exj::GetPathBase(). "/storage";
	}

	public static function GetDirApp(){
		return self::GetDirBase().'/app';
	}
}
?>