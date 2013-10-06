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
		$cache = get_transient(self::ID);
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
				set_transient(self::ID, $cache, $rate);
			}
		}
		return $cache;
	}

	static function clear(){
		delete_transient(self::ID);
	}
}
