<?php
/*
Plugin Name: WP GitHub Tools
Plugin URI: http://vilmosioo.co.uk/github-tools-for-wordpress
Description: A plugin that creates live updates for any GitHub repository. 
Version: 1.0
Author: Vilmos Ioo
Author URI: http://vilmosioo.co.uk
Author Email: ioo.vilmos@gmail.com
License: GPL2

  Copyright 2013 Vilmos Ioo  (email : ioo.vilmos@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

// Define constants
define('VI_GITHUB_COMMITS_DIR', plugin_dir_path(__FILE__));
define('VI_GITHUB_COMMITS_URL', plugin_dir_url(__FILE__));

require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Commits_Widget.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_API.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Event_Manager.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Options.php');

class WP_Github_Tools {
	 
	static function init(){
		new WP_Github_Tools();
	}

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {
		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( &$this, 'uninstall' ) );

		// Add a settings link in the plugin page
		add_filter('plugin_action_links', array(&$this, 'action_links'), 10, 2);

		// add github username
		add_action( 'admin_notices', array(&$this, 'check_github_field') );
		// create gist  shortcode [gist id='#']
		add_shortcode('gist', array( &$this, 'print_gist' ));
		// create commits shortcode
		add_shortcode('commits', array( &$this, 'print_commits' ));
		// create commits widget
		add_action( 'widgets_init', array( &$this, 'register_widgets' ) );
		// schedule automatic updating
		WP_Github_Tools_Event_Manager::init();
		// add settings page
		WP_Github_Tools_Options::init();

	} 
	
	function action_links($links, $file) {
	    static $this_plugin;

	    if (!$this_plugin) {
	        $this_plugin = plugin_basename(__FILE__);
	    }

	    if ($file == $this_plugin) {
	        // The "page" query string value must be equal to the slug
	        // of the Settings admin page we defined earlier, which in
	        // this case equals "myplugin-settings".
	        $settings_link = '<a href="' .admin_url('tools.php?page=WP_Github_Tools_Settings').'">Settings</a>';
	        array_unshift($links, $settings_link);
	    }

	    return $links;
	}

	// create custom shortcodes
	function print_gist( $atts, $content = null ) {
		extract(shortcode_atts(array('id' => ''), $atts));
		return $id ? "<script src=\"http://gist.github.com/$id.js\"></script>" : "";
	}
	// create custom shortcodes
	function print_commits( $atts, $content = null ) {
		$github = get_option('WP_Github_Tools_Settings');
        $github = $github['github'];
		if(!isset($github) || empty($github)) return;
		
		extract(shortcode_atts(array('repository' => '', 'count' => '5', 'title' => 'Latest updates'), $atts));
		if(!isset($repository) || empty($repository)) return;

		$s = "<h2>$title</h2><ul class='github-commits github-commits-$repository'>";
		$repositories = get_option('WP_Github_Tools');
        if(!isset($repositories) || !is_array($repositories)) return;
        $repositories = $repositories['repositories'];
		if(!is_array($repositories)) return;
		$commits = $repositories[$repository]['commits'];
		if(!is_array($commits)) return;
		$commits = array_slice($commits, 0, $count);
		$commits = array_slice($commits, 0, $count);
		foreach($commits as $commit){
			$url = "https://github.com/$github/$repository/commit/".$commit['sha'];
			$commit = $commit['commit'];
			$date = date("d M Y", strtotime($commit['committer']['date']));
			$msg = $commit['message'];
			$s .= "<li class='commit'><span class='date'>$date</span> <a href='$url' title='$msg'>$msg</a></li>";
		}	
		$s .= '</ul>';

		return $s;
	}

	function register_widgets(){
		register_widget( 'WP_Github_Tools_Commits_Widget' ); 
	}
	
	// Displays a welcome message to prompt the user to enter a github username
	function check_github_field(){
        $github = get_option('WP_Github_Tools_Settings');
        $github = $github['github'];
        if(!isset($github) || empty($github)){
			echo '<div class="update-nag">You have activated "Github Tools for WordPress" plugin but have not set a github username! <a href="'.admin_url('tools.php?page=WP_Github_Tools_Settings#github').'">Do it now</a>.</div>';
		} 
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	static function activate( $network_wide ) {
	} 
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	static function deactivate( $network_wide ) {
	} 
	
	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	static function uninstall( $network_wide ) {
		delete_option('WP_Github_Tools_Settings');
		delete_option('WP_Github_Tools');

		WP_Github_Tools_Event_Manager::delete_event();
	} 

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	function register_admin_styles() {
		wp_enqueue_style( 'vi-github-commits-admin-styles', VI_GITHUB_COMMITS_DIR.'css/admin.css');
	} 

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */	
	function register_admin_scripts() {
		wp_enqueue_script( 'vi-github-commits-admin-script', VI_GITHUB_COMMITS_DIR.'js/admin.js');
	}
	
} // end class

$GLOBALS['Github Tools'] = WP_Github_Tools::init();
?>