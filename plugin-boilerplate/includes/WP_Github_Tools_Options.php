<?php
/*
* Theme options
* 
* Loads default settings for the Hyperion theme 
*/
class WP_Github_Tools_Options{
	
    static function init(){
        new WP_Github_Tools_Options();
    } 

	private $options = array();
	private $slug = 'WP_Github_Tools_Settings';
	private $title = 'Github Tools';

	private function __construct(){
		if(!is_admin()) return;
		add_action('admin_menu', array(&$this, 'start'));
		add_action( 'admin_init', array(&$this, 'register_mysettings') );
		$this->addField(
			array(
				'slug' => 'github', 
				'name' => 'Github username',
				'description' => 'Your github\'s account username (required)',
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

		// initialise options
		if(!get_option($this->slug)){
			$temp = array();
			foreach ($this->options as $option) {
				$temp[$option['slug']] = '';
			}
			update_option($this->slug, $temp);
		}
	}

	// Parameters : slug, name, description, tab
	function addField($args = array()){
		$args = array_merge ( array(
	      "slug" => 'option',
	      "name" => 'Option name',
	      "description" => "",
	      'type' => 'text'
	    ), $args );

        $this->options[$args['slug']] = $args;
	}

	/*
	* Init function
	* 
	* Initializes the theme's options. Called on admin menu action.
	*/
	function start(){
		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function);
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function ); 
		$page = add_management_page($this->title, $this->title, 'administrator', $this->slug, array(&$this, 'settings_page_setup'));
		add_action( "admin_print_scripts-$page", array(&$this, 'settings_styles_and_scripts'));
	}

	// loads style/script on the settings page
	function settings_styles_and_scripts(){
		wp_enqueue_script('github-tools-settings-page-script', VI_GITHUB_COMMITS_URL. 'js/admin.js');
		wp_enqueue_style('github-tools-settings-page-style', VI_GITHUB_COMMITS_URL. 'css/admin.css');
	}

	/*
	* Settings page set up
	*
	* Handles the display of the Theme Options page (under Appearance)
	*/
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

	/*
	* Register settings
	* 
	* Register all settings and setting sections
	*/
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
			if ( isset($description) && !empty($description) )
			echo '<br /><span class="description">' . $description . '</span>';
		}
		
	}
}
?>