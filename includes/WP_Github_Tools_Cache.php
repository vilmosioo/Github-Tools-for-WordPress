<?php
/*
* Cache manager
*/

require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_API.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Options.php');

class WP_Github_Tools_Cache{

	const ID = 'WP_Github_Tools';

	/**
	* Return the cache, refresh if required
	*/
	static function get_cache(){
		// TODO Temporary 
		// return array();
		$cache = self::get_transient(self::ID);
		if (empty($cache)){
			$cache = array(
				'last_update' => gmdate("Y-m-d\TH:i:s\Z", current_time( 'timestamp' )),
			);

			$options = get_option(WP_Github_Tools_Options::GENERAL);
			$github = $options['github-username'];
			$rate = $options['refresh-rate'];

			if(isset($github) && !empty($github) && WP_Github_Tools_API::can_update()){
				$cache['gists'] = WP_Github_Tools_API::get_gists($github);
				$temp = WP_Github_Tools_API::get_repos($github);
				if(!is_array($temp)) return;
				foreach ($temp as $repo) {
					$cache['repositories'][$repo['name']] = $repo;
				}
				foreach($cache['repositories'] as $repo){
					if(!is_array($repo)) return;
					$cache['repositories'][$repo['name']]['commits'] = WP_Github_Tools_API::get_commits($repo['name'], $github);
				}
				self::set_transient(self::ID, $cache, $rate);
			}
		}
		return $cache;
	}

	static function clear(){
		self::delete_transient(self::ID);
	}

	/**
	* Wrapper for transient API
	*/
	static function get_transient($key){
		if(is_multisite()){
			return get_site_transient($key);
		} else {
			return get_transient($key);
		}
	}
	
	static function set_transient($key, $value, $timeout){
		if(is_multisite()){
			return set_site_transient($key, $value, $timeout);
		} else {
			return set_transient($key, $value, $timeout);
		}
	}
	
	static function delete_transient($key){
		if(is_multisite()){
			return delete_site_transient($key);
		} else {
			return delete_transient($key);
		}
	}
}
