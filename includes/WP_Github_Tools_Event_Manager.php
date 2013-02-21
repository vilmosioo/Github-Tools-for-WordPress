<?php
/*
* Event Manager
* 
* It's purpose is to cache github data in the wp database and update it periodically as set by the user
*/
class WP_Github_Tools_Event_Manager{
    private $slug = 'WP_Github_Tools';
    private $hook = 'event-manager';

	private function __construct(){
	   
        add_action( $this->hook, array(&$this, 'refresh') );  
        $set_schedule = get_option('WP_Github_Tools_Settings');
        $set_schedule = $set_schedule['refresh'];
            
        if( !wp_next_scheduled( $this->hook ) ) { 
            if(!$set_schedule) $set_schedule = 'daily'; 
	        wp_schedule_event( time(), $set_schedule, $this->hook ); 
        } else {
            // check if event schedule has changed
            $current_schedule = wp_get_schedule($this->hook);
            if($set_schedule && $current_schedule != $set_schedule){
                // reset event
                $next_occurence = wp_next_scheduled($this->hook);
                self::delete_event();
                wp_schedule_event( $next_occurence, $set_schedule, $this->hook ); 
            }
        }
	}

    static function delete_event($hook = 'event-manager'){
        if( false !== ( $time = wp_next_scheduled( $hook ) ) ) {  
            wp_unschedule_event($time, $hook ); 
        }
    }

	function refresh(){
        $options = array(
            'last_update' => gmdate("Y-m-d\TH:i:s\Z", current_time( 'timestamp' )),
        );
        
        if(!get_option($this->slug)){
            update_option($this->slug, $options);
        }
        
        $github = get_option('WP_Github_Tools_Settings');
        $github = $github['github'];

        if(isset($github) && !empty($github) && WP_Github_Tools_API::can_update()){
            $options['gists'] = WP_Github_Tools_API::get_gists($github);
            $temp = WP_Github_Tools_API::get_repos($github);
            if(!is_array($temp)) return;
            foreach ($temp as $repo) {
                $options['repositories'][$repo['name']] = $repo;
            }
            foreach($options['repositories'] as $repo){
                if(!is_array($repo)) return;
                $options['repositories'][$repo['name']]['commits'] = WP_Github_Tools_API::get_commits($repo['name'], $github);
            }
            update_option( $this->slug, $options);
        } 
  	}

    static function init(){
        new WP_Github_Tools_Event_Manager();
    } 
}
?>