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
		$cache = self::get_transient(self::ID);
		if (empty($cache)){
			$cache = array(
				'last_update' => gmdate("Y-m-d\TH:i:s\Z", current_time( 'timestamp' )),
			);

			$options = get_option(WP_Github_Tools_Options::GENERAL);
			$rate = $options['refresh-rate'];
			$access_token = $options['access-token'];

			if(!empty($access_token)){
				$cache['gists'] = WP_Github_Tools_API::get_gists($access_token);
				$cache['user'] = WP_Github_Tools_API::get_user($access_token);
				$temp = WP_Github_Tools_API::get_repos($access_token);
				if(!is_array($temp)) return;
				foreach ($temp as $repo) {
					$cache['repositories'][$repo['name']] = $repo;
				}
				foreach($cache['repositories'] as $repo){
					if(!is_array($repo)) return;
					$commits = WP_Github_Tools_API::get_commits($cache['user']['login'], $repo['name'], $access_token);
					$cache['repositories'][$repo['name']]['commits'] = $commits;
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
