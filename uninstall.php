<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
		exit();
// For Single site
if (!is_multisite()){
	delete_option('WP_Github_Tools_Settingsgeneral');
	delete_option('WP_Github_Tools_Data');
	delete_transient('WP_Github_Tools');
} 
// For Multisite
else {
	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();
	foreach ($blog_ids as $blog_id){
		switch_to_blog( $blog_id );
		delete_option('WP_Github_Tools_Settingsgeneral');
		delete_option('WP_Github_Tools_Data');
		delete_transient('WP_Github_Tools');
	}
	switch_to_blog( $original_blog_id );
}
?>