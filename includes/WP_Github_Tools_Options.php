<?php
/*
* Plugin options
* 
* Allows the user to set a GitHub username and refresh rate for the plugin.
*/
class WP_Github_Tools_Options{
	
    static function init(){
        new WP_Github_Tools_Options();
    } 

	private $options = array();
	private $slug = 'WP_Github_Tools_Settings';
	private $title = 'GitHub Tools';

	private function __construct(){
		if(!is_admin()) return;
		add_action('admin_menu', array(&$this, 'start'));
		add_action( 'admin_init', array(&$this, 'register_mysettings') );
		$this->addField(
			array(
				'slug' => 'github', 
				'name' => 'GitHub username',
				'description' => 'Your GitHub\'s account username (required)',
			)
		);

		$temp = array();
		foreach (wp_get_schedules() as $key => $value) {
			$temp[$key] = $value['display'];
		}

		$this->addField(
			array(
				'slug' => 'refresh', 
				'name' => 'Refresh rate',
				'description' => 'How often to refresh to repositories.',
				'type' => 'select',
				'options' => $temp
			)
		);

		if(!get_option($this->slug)){
			$temp = array();
			foreach ($this->options as $option) {
				$temp[$option['slug']] = '';
			}
			update_option($this->slug, $temp);
		}

		add_action('wp_ajax_verify_github_username', array(&$this, 'verify_github_username'));	
	}

	function verify_github_username() {
		global $wpdb; // this is how you get access to the database

		if(!WP_Github_Tools_API::can_update()){
			$msg['message'] = "API limit reached";
			echo json_encode($msg);
			die();
		}
		
		$github = $_POST['github'];
		echo json_encode(WP_Github_Tools_API::get_user($github));

		die(); // this is required to return a proper result
	}

	function addField($args = array()){
		$args = array_merge ( array(
	      "slug" => 'option',
	      "name" => 'Option name',
	      "description" => "",
	      'type' => 'text'
	    ), $args );

        $this->options[$args['slug']] = $args;
	}

	function start(){
		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function);
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function ); 
		$page = add_management_page($this->title, $this->title, 'administrator', $this->slug, array(&$this, 'settings_page_setup'));
		add_action( "admin_print_scripts-$page", array(&$this, 'settings_styles_and_scripts'));
	}

	function settings_styles_and_scripts(){
		wp_enqueue_script('github-tools-settings-page-script', VI_GITHUB_COMMITS_URL. 'js/admin.js');
		wp_enqueue_style('github-tools-settings-page-style', VI_GITHUB_COMMITS_URL. 'css/admin.css');
	}

	function settings_page_setup() {
		echo '<div class="wrap">';
		if ( isset( $_GET['settings-updated'] ) ) {
			echo "<div class='updated'><p>Github Tools Options updated successfully.</p></div>";
		} 
		?>
		<form method="post" action="options.php">
			<?php settings_fields( $this->slug ); ?>
			<?php do_settings_sections( $this->slug ); ?>
	    	<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		</div>
		<?php 
	} 

	function register_mysettings() {
		// register_setting( $option_group, $option_name, $sanitize_callback ); 
		register_setting( $this->slug, $this->slug );
		// add_settings_section( $id, $title, $callback, $page ); 
		add_settings_section( $this->slug, '', array(&$this, 'section_handler'), 'WP_Github_Tools_Settings' ); 
		foreach($this->options as $option){
			extract($option);
			// add_settings_field( $id, $title, $callback, $page, $section, $args ); 
			add_settings_field( $slug, $name, array(&$this, 'input_handler'), $this->slug, $this->slug, $option );
		}
	}

	function section_handler($args){
		echo "<div id=\"icon-options-general\" class=\"icon32\"><br></div><h2>$this->title</h2>";
		echo "<div id='github-tools-information-bar' class='error'></div>";
	}

	function input_handler($args){
		extract($args);
		$value = get_option($this->slug);
		$value = $value[$slug];
		$slug = $this->slug."[$slug]";
		switch($type){
			case 'select': 
				echo "<select id='$slug' name='$slug'>"; 
				foreach($options as $key => $option_value){
					echo "<option value='$key' ".($key == $value ? 'selected' : '').">$option_value</option>";
				}
				echo '</select>';
				if ( isset($description) && !empty($description) )
				echo '<br /><span class="description">' . $description . '</span>';
			break;
			default:
				echo "<input type='$type' id='$slug' name='$slug' value='$value'>"; 
				echo "<img alt=\"\" id='github-tools-yes' class='github-tools-image' src=\"".admin_url()."images/yes.png\">";
				echo "<img alt=\"\" id='github-tools-no' class='github-tools-image' src=\"".admin_url()."images/no.png\">";
				echo "<img alt=\"\" id='github-tools-loading' class='github-tools-image' src=\"".admin_url()."images/wpspin_light.gif\">";
				echo "<span id='github-tools-feedback'></span>";

				if ( isset($description) && !empty($description) )
				echo '<br /><span class="description">' . $description . '</span>';
		}
		
	}
}
?>