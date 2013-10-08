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

	/*
	* Generate a slug from string (lowercase and '-' as separator)
	*/
	static function generate_slug($s = ""){
		return strtolower(str_replace(" ", "-", $s));
	}

  protected $tabs;
	protected $current;

	const ID = 'WP_Github_Tools_Settings';
	const TITLE = 'GitHub Tools';
	const GENERAL = 'WP_Github_Tools_Settingsgeneral';

	private function __construct(){
		if(!is_admin()) return;
		$this->current = ( isset( $_GET['tab'] ) ? $_GET['tab'] : '' ); 

		add_action('admin_menu', array(&$this, 'start'));
		add_action( 'admin_init', array(&$this, 'register_mysettings') );
		
		$temp = array();
		foreach (wp_get_schedules() as $key => $value) {
			$temp[$value['display']] = $value['interval'];
		}

		// determine if client details exist
		$data = get_option(WP_Github_Tools_Cache::DATA);
		$description = '<h2>Description</h2>'.
			'<p>In order to use this plugin, you must allow it to connect to Github on your behalf to retrieve all your repositories and commit history. This is only required once and the application only needs READ access. You can revoke access of this application any time you want.</p>'.
			'<h2>Steps</h2>'.
			'<ol>'.
			'<li><a href="https://github.com/settings/applications/new" title="registered a Github application">Register a new github application</a></li>'.
			'<li><strong>Make sure the redirect uri is: '.admin_url('tools.php?page='.self::ID).'</strong></li>'.
			'<li>Copy the client ID and client secret in the form below</li>'.
			'<li>Save the form</li>'.
			'<li>Once the client data is saved, you can connect this plugin to your Github account.</li>'.
			'</ol>';

		$general_options = array(
			array(
				'name' => 'Client ID',
				'description' => 'Please enter the client ID you received from GitHub when you <a href="https://github.com/settings/applications/new" title="registered a Github application">registered</a>.',
			),
			array(
				'name' => 'Client Secret',
				'description' => 'Please enter the client secret you received from GitHub when you <a href="https://github.com/settings/applications/new" title="registered a Github application">registered</a>.',
			),
			array(
				'name' => 'Refresh rate',
				'description' => 'How often to refresh to repositories.',
				'type' => 'select',
				'options' => $temp
			)
		);

		if(is_array($data) && !empty($data['access-token'])){
			// user has connected profile to github
			$cache = WP_Github_Tools_Cache::get_cache();
			if(is_array($cache)){
				$user = $cache['user']['name'];
				$login = $cache['user']['login'];
				$url = $cache['user']['html_url'];
				$avatar_url = $cache['user']['avatar_url'];
				// TODO Hide entire form if user exists
				$description = "<h2>Summary</h2><img class='wp_github_profile_img' src='$avatar_url' alt='$user'><h1>$user</h1>".
					"<p>You are connected to Gihub as <a href='$url' title='Github profile'>$login</a>.</p>".
					"<p><a class='button' href='".admin_url('tools.php?page='.self::ID)."&wp_github_tools_action=disconnect' title='Disconnect'>Disconnect</a></p>";	
				$description .= '<p class="clear">Time of last update: <strong>'.$cache['last_update'].'</strong> <br>';
				$description .= 'User: <strong>'.$cache['user']['name'].' (<a href="'.$cache['user']['html_url'].'" title="'.$cache['user']['name'].'">'.$cache['user']['login'].'</a>)</strong><br>';
				$description .= 'Saved data: <strong>'.count($cache['repositories']).' repositories</strong> (see cache) and <strong>'.count($cache['gists']).' gists.</strong></p>';
				$description .= '<p><a class="button" href="">Refresh</a></p>';
			
				// remove client-id and client-secret data
				$general_options = array(
					array(
						'name' => 'Refresh rate',
						'description' => 'How often to refresh to repositories.',
						'type' => 'select',
						'options' => $temp
					)
				);
			}
		} else {
			$options = get_option(self::GENERAL);
			if(!empty($options['client-id']) && !empty($options['client-id'])){
				// user has saved client app details, ready to connect
				$client_id = urlencode($options['client-id']);
				$url = 'https://github.com/login/oauth/authorize?client_id='.$client_id;
				$description = '<h3>Connect to Github</h3><p>Looks like you\'re ready to link your Github account!</p>'.
					'<p><a href="'.$url.'" class="button-primary">Connect to Github</a></p>';
			} 
		}
		$description .= '<h2>Settings</h2>';
		$this->addTab(array(
			'name' => 'General',
			'desc' => $description,
			'options' => $general_options
		));

		// if the user is connected to github and the cache exists, display a cache tab
		$cache = WP_Github_Tools_Cache::get_cache();
		if(is_array($cache)){
			$str = "<h2>Repositories</h2>";
			if(is_array(@$cache['repositories'])){
				foreach (@$cache['repositories'] as $name => $repository) {
					$str .= "<h2>$name</h2>";
					$str .= "<p>$repository[description]</p>";
					$str .= @do_shortcode("[commits repository='$name' count='5']");
					$str .= "<a href='".$repository['html_url']."' title='View on Github'>View on Github</a>";
				}

				$this->addTab(array(
					'name' => 'Cache',
					'desc' => $str
				));
			}
		}
		
		// initialise options
		foreach($this->tabs as $slug => $tab){
			if(!get_option(self::ID.$slug)){
				$defaults = array();
				foreach( $tab['options'] as $option){
					$name = self::generate_slug($option['name']);
					
					$defaults[$name] = '';
				}
			
				update_option( self::ID.$slug, $defaults );
			}	
		}
	}

	function page_loaded()
	{
		if(isset($_GET['settings-updated']) && $_GET['settings-updated']){
			WP_Github_Tools_Cache::clear();
		}
	}

	// Add a new tab. 
	// Parameters : tab name, description, option array
	public function addTab($args = array()){
		$args = array_merge ( array(
			"name" => 'General',
			"desc" => "",
			"options" => array()
		), $args );

		$slug = self::generate_slug($args['name']);
		$this->current = empty($this->current) ? $slug : $this->current;

		$this->tabs[$slug] = array(
			'name' => $args['name'],
			'desc' => $args['desc'],
			'options' => array()
		);

		foreach ($args['options'] as $option) {
			$this->addField(array('tab' => $slug, 'option' => $option));        	
		} 
	}


	function addField($args = array()){
		if(!is_array($args['option']) && is_string($args['option'])){
			$args['option'] = array('name' => $args['option']);
		}

		$args['option'] = array_merge ( array(
			"name" => 'Option name',
			"description" => "",
			"type" => 'text'
		), $args['option'] );

    $this->tabs[$args['tab']]['options'][self::generate_slug($args['option']['name'])] = array(
			'name' => $args['option']['name'],
			'description' => $args['option']['description'],
			'type' => $args['option']['type'],
			'options' => $args['option']['options']
		);
	}

	function start(){
		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function);
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function ); 
		$page = add_management_page(self::TITLE, self::TITLE, 'administrator', self::ID, array(&$this, 'settings_page_setup'));
		add_action( "admin_print_scripts-$page", array(&$this, 'settings_styles_and_scripts'));
		add_action('load-'.$page, array(&$this, 'page_loaded'));
	}

	function settings_styles_and_scripts(){
		wp_enqueue_script('github-tools-settings-page-script', VI_GITHUB_COMMITS_URL. 'js/admin.js');
		wp_enqueue_style('github-tools-settings-page-style', VI_GITHUB_COMMITS_URL. 'css/admin.css');
	}

	function settings_page_setup() {
		echo '<div class="wrap">';
		$this->page_tabs();
		if ( isset( $_GET['settings-updated'] ) ) {
			echo "<div class='updated'><p>Github Tools Options updated successfully.</p></div>";
		} 
		?>
		<form method="post" action="options.php">
			<?php settings_fields( self::ID.$this->current ); ?>
			<?php do_settings_sections( self::ID ); ?>
			<?php if(count($this->tabs[$this->current]['options']) > 0){?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			<?php } ?>
		</form>
		</div>
		<?php 
	} 

	/*
	* Page tabs
	*
	* Prints out the naviagtion for page tabs
	*/
	protected function page_tabs(){		
		
		$links = array();

		foreach( $this->tabs as $slug => $tab ){
			$active_class = $slug == $this->current ? "nav-tab-active" : "";
			$links[] = "<a class='nav-tab $active_class' href='?page=".self::ID."&tab=$slug'>$tab[name]</a>";
		}

		echo '<div id="icon-themes" class="icon32"><br /></div>'.
			'<h2 class="nav-tab-wrapper">';
		
		foreach ( $links as $link ){
			echo $link;
		}

		echo '</h2>';
	}

	function register_mysettings() {
		foreach($this->tabs as $slug=>$tab){
			// register_setting( $option_group, $option_name, $sanitize_callback ); 
			register_setting( self::ID.$slug, self::ID.$slug );
			if($slug != $this->current) continue;
			// add_settings_section( $id, $title, $callback, $page ); 
			add_settings_section( 'options_section_'.$slug, '', array(&$this, 'section_handler'), self::ID ); 
			foreach($tab['options'] as $key => $option){
				// add_settings_field( $id, $title, $callback, $page, $section, $args ); 
				add_settings_field( $key, $option['name'], array(&$this, 'input_handler'), self::ID, 'options_section_'.$slug, array("tab" => $slug, 'option' => array_merge(array('slug' => $key), $option)));
			}
		}
	}

	public function section_handler($args){
		$id = substr($args['id'], strlen('options_section_')); // 16 is the length of the section prefix:self::ID		
		echo $this->tabs[$id]['desc']; 
	}

	function input_handler($args){
		$option = $args['option'];
		$id = $option['slug'];
		$name = self::ID.$args['tab']."[$id]";
		$values = get_option(self::ID.$args['tab']);
		$value = $values[$id];
		$description = $option['description'];
		$options = $option['options'];
		$slug = $option['slug'];
		$type = $option['type'];

		switch($type){
			case 'select': 
				echo "<select id='$name' name='$name'>"; 
				foreach($options as $key => $option_value){
					echo "<option value='$option_value' ".($option_value == $value ? 'selected' : '').">$key</option>";
				}
				echo '</select>';
				if ( isset($description) && !empty($description) )
				echo '<br /><span class="description">' . $description . '</span>';
			break;
			default:
				echo "<input type='$type' id='$name' name='$name' value='$value'>"; 
				if ( isset($description) && !empty($description) )
				echo '<br /><span class="description">' . $description . '</span>';
		}
		
	}
}
?>