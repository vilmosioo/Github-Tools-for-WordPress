<?php
/*
Plugin Name: Github Tools for WordPress
Plugin URI: http://vilmosioo.co.uk
Description: A plugin that allows easy insert of a dinamic changelog from a github repository. Custom widgets and shortcodes.
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

if(is_admin()){
    require_once(VI_GITHUB_COMMITS_DIR.'includes/admin.php');
}    
require_once(VI_GITHUB_COMMITS_DIR.'includes/core.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Commits_Widget.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_API.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Event_Manager.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Options.php');

class WP_Github_Tools {
	 
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
	
		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );
	
		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		
		// TODO
		// add github username
		add_action( 'admin_notices', array(&$this, 'check_github_field') );
		// create gist  shortcode [gist id='#']
		add_shortcode('gist', array( &$this, 'print_gist' ));
		// create commits shortcode
		add_shortcode('commits', array( &$this, 'print_commits' ));
		// create commits widget
		add_action( 'widgets_init', array( &$this, 'register_widgets' ) );
		// schedule automatic updating
		new WP_Github_Tools_Event_Manager();
		// add settings page
		new WP_Github_Tools_Options();

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
		$commits = $repositories[$instance[$name]]['commits'];
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
			echo '<div class="update-nag">You have activated "Github Tools for WordPress" plugin but have not set a github username! <a href="'.admin_url('profile.php#github').'">Do it now</a>.</div>';
		} 
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	function activate( $network_wide ) {
	} 
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	function deactivate( $network_wide ) {
		// TODO:	Define deactivation functionality here		
	} 
	
	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	function uninstall( $network_wide ) {
		// TODO:	Define uninstall functionality here	
		// remove github option (?)	
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
	
	/**
	 * Registers and enqueues plugin-specific styles.
	 */
	function register_plugin_styles() {
		wp_enqueue_style( 'vi-github-commits-plugin-styles', VI_GITHUB_COMMITS_DIR.'css/display.css');
	} 
	
	/**
	 * Registers and enqueues plugin-specific scripts.
	 */
	function register_plugin_scripts() {
		wp_enqueue_script( 'vi-github-commits-plugin-script', VI_GITHUB_COMMITS_DIR.'js/display.js');
	} 
	
} // end class

$plugin_name = new WP_Github_Tools();