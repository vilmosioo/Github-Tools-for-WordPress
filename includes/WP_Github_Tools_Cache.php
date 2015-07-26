<?php
/*
* Cache manager
*/

require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_API.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Options.php');

class WP_Github_Tools_Cache{

	const ID = 'WP_Github_Tools';
	const DATA = 'WP_Github_Tools_Data';

	/**
	* Return the cache, refresh if required
	*/
	static function get_cache(){
		$cache = get_transient(self::ID);
		if (empty($cache)){
			$cache = array(
				'last_update' => gmdate("Y-m-d H:i:s", current_time( 'timestamp' )),
			);

			$options = get_option(WP_Github_Tools_Options::GENERAL);
			$rate = $options['refresh-rate'];
			$data = get_option(self::DATA);
			$access_token = $data['access-token'];
			if(!empty($access_token)){
				// $cache['gists'] = WP_Github_Tools_API::get_gists($access_token);
				$cache['user'] = WP_Github_Tools_API::get_user($access_token);
				$temp = WP_Github_Tools_API::get_repos($access_token);
				if(!is_array($temp)) return;
				foreach ($temp as $repo) {
					$cache['repositories'][$repo['name']] = $repo;
				}
				foreach($cache['repositories'] as $repo){
					if(!is_array($repo)) return;
					$commits = WP_Github_Tools_API::get_commits($cache['user']['login'], $repo['name'], $access_token);
					$releases = WP_Github_Tools_API::get_releases($cache['user']['login'], $repo['name'], $access_token);
					$cache['repositories'][$repo['name']]['commits'] = $commits;
					$cache['repositories'][$repo['name']]['releases'] = $releases;
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
