<?php
/*
* Github API helper class
* 
* Uses the github api to retrieve public gists, repos or commits for a specific user
*/
class WP_Github_Tools_API{
	static function get_data($url){
        $base = "https://api.github.com/";
        $response = wp_remote_get($base . $url, array( 'sslverify' => false ));
        $response = json_decode($response['body'], true);
        return $response;
    }

    static function can_update(){
        // check for rate limit
        $base = "https://api.github.com/";
        $rate = wp_remote_get($base . "rate_limit", array( 'sslverify' => false ));
        if($rate['response']['code'] == 200) return true;
        return false;
    }

    static function get_repos($user) {
    	return self::get_data("users/$user/repos");
    }

    static function get_user($user){
        return self::get_data("users/$user");
    }

    static function get_commits($repo, $user){
        return self::get_data("repos/$user/$repo/commits");
    }

    static function get_gists($user){
        return self::get_data("users/$user/gists");
    }
}
?>