<?php
/*
* Gist Manager
* 
* Calls the GIthub API to retreive latest GIST and include them in the WP blog
*/
class WP_Github_Tools_Event_Manager{
	private $slug = 'WP_Github_Tools';
	function __construct(){
	   
        add_action( 'event-manager', array(&$this, 'refresh') );  
 		
        if( false !== ( $time = wp_next_scheduled( 'event-manager' ) ) ) {  
            wp_unschedule_event($time, 'event-manager' ); 
        }
        
		if( !wp_next_scheduled( 'event-manager' ) ) {  
	       wp_schedule_event( time(), 'daily', 'event-manager' ); 
	       //die('scheduled');
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
}
?>