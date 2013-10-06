<?php
/*
* Cache manager
*/
class WP_Github_Tools_Cache{
	/**
	* Return the cache, refresh if required
	*/
	static function get_cache(){
		$cache = array();
		if (false === ($cache = get_transient(self::ID))){

		}
		return $cache;
	}

	static function clear(){
		
	}
}