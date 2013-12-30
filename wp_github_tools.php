<?php
/*
Plugin Name: WP GitHub Tools
Plugin URI: https://github.com/vilmosioo/Github-Tools-for-WordPress
Description: A plugin that creates live updates for any GitHub repository. 
Version: 1.2
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
define('VI_VERSION', get_bloginfo( 'version' ));

require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Commits_Widget.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_API.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Options.php');
require_once(VI_GITHUB_COMMITS_DIR.'includes/WP_Github_Tools_Cache.php');

class WP_Github_Tools {
	
	static function init(){
		return new WP_Github_Tools();
	}

	const ID = 'WP_Github_Tools';
	static $INDEX = 0;

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {
		register_activation_hook(__FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook(__FILE__, array( &$this, 'deactivate' ) );

		// check for github username
		// Don't run on WP < 3.3
		if (VI_VERSION < '3.3'){
			// use standard notification
			add_action('admin_notices', array(&$this, 'check_github_field') );
			add_action('admin_init', array(&$this, 'dismiss_notification'));
		} else {
			// use new notification
			add_action('admin_enqueue_scripts', array( &$this, 'display_notice' ) );
			add_action('wp_ajax_dismiss_wp_github_tools', array( $this, 'handle_notice_dismiss' ) );
		}

		// register chart scripts
		add_action('wp_enqueue_scripts', array(&$this, 'add_chart_scripts'));
		// Add a settings link in the plugin page
		add_action('WP_Github_Tools_Activated', array(&$this, 'plugin_activated'));
		// Add a settings link in the plugin page
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'action_links'), 10, 2);
			// create gist  shortcode [gist id='#']
		add_shortcode('gist', array( &$this, 'print_gist' ));
		// create commits shortcode
		add_shortcode('commits', array( &$this, 'print_commits' ));
		// create chart shortcode
		add_shortcode('chart', array( &$this, 'display_chart' ));
		// create commits widget
		add_action( 'widgets_init', array( &$this, 'register_widgets' ) );
		
		// check to see if the user connected to github
		if(isset( $_GET['code'] )){
			if(!WP_Github_Tools_API::get_token($_GET['code'])){
				add_action('admin_notices', array(&$this, 'display_errors') );
			}
		}

		// check to see if the user requested to disconnect or refresh
		// do NOT perform any action when request is a form response
		if(isset($_GET['wp_github_tools_action']) && !isset($_GET['settings-updated'])){
			if($_GET['wp_github_tools_action'] == 'disconnect'){
				$this->clear_all();
			} else if($_GET['wp_github_tools_action'] == 'refresh'){
				WP_Github_Tools_Cache::clear();
			}
		}
		
		// add settings page
		WP_Github_Tools_Options::init();
	} 

	public function display_errors(){
		echo "<div class='error'><p><strong>Oops! Something went terribly wrong!</strong></p><p>We could not connect you to Github at this time. Please try again.</p></div>";		
	}

	/**
	* Handle the user dismiss action
	*/
	public function handle_notice_dismiss(){
		global $current_user ;
		$user_id = $current_user->ID;

		/* If user clicks to ignore the notice, add that to their user meta */
		add_user_meta($user_id, 'wp_github_tools_ignore_notice', 'true', true);

		exit;
	}

	/**
	* Check for the existence of a github username and display a notice if there isn't
	*/
	public function display_notice(){
		$data = get_option(WP_Github_Tools_Cache::DATA);
		$github = $data['access-token'];

		global $current_user ;
		$user_id = $current_user->ID;
		
		if((!isset($github) || empty($github)) && !get_user_meta($user_id, 'wp_github_tools_ignore_notice')){
			// add JavaScript for WP Pointers
			wp_enqueue_script( 'wp-pointer' );
			// add CSS for WP Pointers
			wp_enqueue_style( 'wp-pointer' );
			add_action( 'admin_print_footer_scripts', array( &$this, 'notice_footer_script' ) );
		}
	}

	/**
	* Display notice for the user
	*/
	public function notice_footer_script(){
		// Build the main content of your pointer balloon in a variable
		$pointer_content = '<h3>Connect to Github</h3>'; // Title should be <h3> for proper formatting.
		$pointer_content .= "<p>Thank you for using Github Tools for WordPress. Please go to the plugin\'s settings page to connect to Github!</p>";
		?>
		<script type="text/javascript">// <![CDATA[
		jQuery(document).ready(function($) {
		/* make sure pointers will actually work and have content */
		if(typeof(jQuery().pointer) != 'undefined') {
			$('#menu-tools').pointer({
			content: '<?php echo $pointer_content; ?>',
			position: {
				edge: 'left',
				align: 'center'
			},
			close: function() {
				$.post( ajaxurl, {
					action: 'dismiss_wp_github_tools'
				});
			}
			}).pointer('open');
		}
		});
		// ]]></script>
		<?php
	}

	function action_links($links, $file) {
		return array_merge(
			array(
				'Settings' =>  '<a href="' .admin_url('tools.php?page='.WP_Github_Tools_Options::ID).'">Settings</a>'
			),
			$links
		);	
	}

	// create custom shortcodes
	function print_gist( $atts, $content = null ) {
		extract(shortcode_atts(array('id' => ''), $atts));
		return $id ? "<script src=\"http://gist.github.com/$id.js\"></script>" : "";
	}

	// create custom shortcodes
	function print_commits( $atts, $content = null ) {
		extract(shortcode_atts(array('repository' => '', 'count' => '5', 'title' => '', 'class' => ''), $atts));
		if(!isset($repository) || empty($repository)) return;

		$s = "<ul class='github-commits github-commits-$repository $class'>";
		$s = empty($title) ? $s : "<h3>$title</h3>".$s; 
		$repositories = WP_Github_Tools_Cache::get_cache();
		$github = $repositories['user']['login'];
		if(!isset($repositories) || !is_array($repositories)) return;
		$repositories = $repositories['repositories'];
		if(!is_array($repositories)) return;
		$commits = $repositories[$repository]['commits'];
		if(!is_array($commits)) return;
		$commits = array_slice($commits, 0, $count);
		foreach($commits as $commit){
			$url = $commit['html_url'];
			$commit = $commit['commit'];
			$committer = $commit['committer'];
			$date = date("d M Y", strtotime($committer['date']));
			$msg = $commit['message'];
			
			$s .= "<li class='commit'><span class='date'>$date</span> <a href='$url' title='$msg'>$msg</a></li>";
		}	
		$s .= '</ul>';

		return $s;
	}

	// display activity chart for a repository
	function display_chart($atts, $content = null){
		extract(shortcode_atts(array('repository' => '', 'id' => 'github_chart_'.WP_Github_Tools::$INDEX++, 'title' => '', 'width' => '', 'class' => '', 'height' => '300', 'color' => '#f17f49', 'background' => 'transparent', 'count' => 30), $atts));
		if(!isset($repository) || empty($repository)) return;
		
		if (VI_VERSION > '3.3' && !is_admin()){
			wp_enqueue_script('WP_Github_Tools_D3');
			wp_enqueue_script('WP_Github_Tools_NVD3');
			wp_enqueue_style('WP_Github_Tools_NVD3_Style');
			wp_enqueue_script('WP_Github_Tools_Chart');
			wp_enqueue_style('WP_Github_Tools_Chart_Style');	
		}
		
		$s = "";
		$s .= !empty($title) ? "<h3>$title</h3>" : "";
		$s .= "<div class='github-chart $class'><svg id='$id'></div>";

		// Set JS data for the chart
		$data = array(
			'data' => array(),
			'width' => $width,
			'height' => $height,
			'background' => $background,
			'color' => $color
		);
		$temp = array();
		$repositories = WP_Github_Tools_Cache::get_cache();
		if(!isset($repositories) || !is_array($repositories)) return;
		$repositories = $repositories['repositories'];
		if(!is_array($repositories)) return;
		$commits = $repositories[$repository]['commits'];
		if(!is_array($commits)) return;

		// add number of commits for each day in a temporary array
		$min = null;
		$max = null; 

		// work only with the specified number of commits
		$commits = is_numeric($count) && $count > 0 ? array_slice($commits, 0, $count) : array_slice($commits, 0, 30);
		foreach($commits as $commit){
			$commit = $commit['commit'];
			$committer = $commit['committer'];
			$date = strtotime(date("d M Y", strtotime($committer['date'])));
			$date += ((1 - date('w', $date)) * 24 * 3600);
			$temp[$date] = empty($temp[$date]) ? 1 : $temp[$date] + 1;
			// maintain min and max dates
			if(empty($min)) $min = $date;
			if(empty($max)) $max = $date;
			$min = $date < $min ? $date : $min;
			$max = $date > $max ? $date : $max;
		}

		$data['count'] = count($commits);

		// add days that have no commits
		for($i = $min; $i < $max; $i += 3600*24*7){
			if(empty($temp[$i])){
				$temp[$i] = 0;
			}
		}	
		ksort($temp);

		// generate the JS data
		foreach ($temp as $key => $value) {
			array_push($data['data'], array(
				'date' => date("d M Y", $key),
				'value' => $value
			));
		}

    wp_localize_script( 'WP_Github_Tools_Chart', $id, $data );

		return $s;
	}

	function add_chart_scripts(){
		wp_register_script('WP_Github_Tools_D3', '//d3js.org/d3.v3.min.js', array(), '1.0', true);
		wp_register_script('WP_Github_Tools_NVD3', '//cdnjs.cloudflare.com/ajax/libs/nvd3/1.1.13-beta/nv.d3.min.js', array('WP_Github_Tools_D3'), '1.0', true);
		wp_register_style('WP_Github_Tools_NVD3_Style', '//cdnjs.cloudflare.com/ajax/libs/nvd3/1.1.13-beta/nv.d3.css');
		wp_register_script('WP_Github_Tools_Chart', plugins_url('js/chart.js', __FILE__), array('WP_Github_Tools_NVD3'), '1.0', true);
		wp_register_style('WP_Github_Tools_Chart_Style', plugins_url('css/chart.css', __FILE__), 'WP_Github_Tools_NVD3_Style');
		// we cannot enqueue scripts in shortcode for older WP
		if (VI_VERSION <= '3.3'){
			wp_enqueue_script('WP_Github_Tools_D3');
			wp_enqueue_script('WP_Github_Tools_NVD3');
			wp_enqueue_style('WP_Github_Tools_NVD3_Style');
			wp_enqueue_script('WP_Github_Tools_Chart');
			wp_enqueue_style('WP_Github_Tools_Chart_Style');	
		}		
	}

	function register_widgets(){
		register_widget( 'WP_Github_Tools_Commits_Widget' ); 
	}
		
		// Displays a welcome message to prompt the user to enter a github username
	function check_github_field(){
		$data = get_option(WP_Github_Tools_Cache::DATA);
		$github = $github['access-token'];

		global $current_user ;
		$user_id = $current_user->ID;

		if((!isset($github) || empty($github)) && !get_user_meta($user_id, 'wp_github_tools_ignore_notice')){
			echo '<div class="update-nag">You have activated "Github Tools for WordPress" plugin but have not set a github username! <a href="'.admin_url('tools.php?page='.WP_Github_Tools_Options::ID.'#github').'">Do it now</a>. | <a href="?wp_github_tools_ignore_notice=0">Hide Notice</a></div>';
		} 
	}

	/**
	* Listen to dismiss action
	*/
	function dismiss_notification() {
		global $current_user;
		$user_id = $current_user->ID;
		/* If user clicks to ignore the notice, add that to their user meta */
		if ( isset($_GET['wp_github_tools_ignore_notice']) && '0' == $_GET['wp_github_tools_ignore_notice'] ) {
			add_user_meta($user_id, 'wp_github_tools_ignore_notice', 'true', true);
		}
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function activate( $network_wide ) {
		do_action('WP_Github_Tools_Activated');
	} 
	
	public function plugin_activated(){
		global $current_user;
		$user_id = $current_user->ID;
		delete_user_meta($user_id, 'wp_github_tools_ignore_notice');
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	function deactivate( $network_wide ) {
		do_action('WP_Github_Tools_Deactivated');
	} 

	function clear_all(){
		delete_option(WP_Github_Tools_Options::GENERAL);
		delete_option(WP_Github_Tools_Cache::DATA);
		WP_Github_Tools_Cache::clear();
	}
} // end class

$GLOBALS['Github Tools'] = WP_Github_Tools::init();
?>