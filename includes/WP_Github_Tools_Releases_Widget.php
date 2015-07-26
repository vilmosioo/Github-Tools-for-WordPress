<?php
/*
* Widget
* 
* Creates a Widget to be added in the sidebar. This class is a template, if you wish to customize it, add more fields and change the title (among other things)
* 
*/
class WP_Github_Tools_Releases_Widget extends WP_Widget{
	private $slug, $title, $description, $class;
	private $fields;
	private $github_username;

	function __construct($args = array()) {
		$args = array_merge ( array(
			"slug" => 'wp_github_tools_releases_widget',
			"title" => 'GitHub Releases',
			"description" => 'Use this widget to displays a list of the latest releases from your GitHub repository.',
			"class" => 'wp_github_tools_widget'
			), $args );
		
		$this->slug = $args['slug'];
		$this->title = $args['title'];
		$this->description = $args['description'];
		$this->class = $args['class'];
		$this->fields = array( 
			'repository' => array(
				'name' => 'Repository',
				'type' => 'select',
				'options' => array()
			),
			'count' => array(
				'name' => 'Count',
				'type' => 'number',
				'min' => "1"
			),
		);
		parent::__construct(
			$this->slug, // Base ID
			$this->title, // Name
			array( 'description' => $this->description, 'class' => $this->class ) // Args
		);

		$data = get_option(WP_Github_Tools_Cache::DATA);
		$github = $data['access-token'];
		if(isset($github) && !empty($github)){
			$this->github_username = $github;
			$repositories = WP_Github_Tools_Cache::get_cache();
			if(!isset($repositories) || !is_array($repositories)) return;
			$repositories = $repositories['repositories'];
			if(!is_array($repositories)) return;
			foreach($repositories as $repo){
				$this->fields['repository']['options'][$repo['name']] = $repo['name'];
			}
		}
	}
	
	/**
	* Front-end display of widget.
	*
	* @see WP_Widget::widget()
	*
	* @param array $args     Widget arguments.
	* @param array $instance Saved values from database.
	*/
		function widget( $args, $instance ) {
			extract( $args );  

			$title = apply_filters('widget_title', $instance['title'] );  
			
		echo $before_widget;  
			
		if($title){  
				echo $before_title . $title . $after_title;  
		}
		// add count variable 
		$field = $this->fields['repository'];
		$name = $field['name'];
		$name = $instance[$name];
		$count = $this->fields['count']['name'];
		$count = $instance[$count] ? $instance[$count] : 5;
		if($this->github_username){
			echo @do_shortcode("[releases repository='$name' count='$count']");
		}
		echo $after_widget;
	}

	/**
	* Sanitize widget form values as they are saved.
	*
	* @see WP_Widget::update()
	*
	* @param array $new_instance Values just sent to be saved.
	* @param array $old_instance Previously saved values from database.
	*
	* @return array Updated safe values to be saved.
	*/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;  
	
			//Strip tags from title and name to remove HTML  
			$instance['title'] = strip_tags( $new_instance['title'] );  
			foreach($this->fields as $field){
				if(is_array($field)) $field = $field['name'];
				$instance[$field] = strip_tags( $new_instance[$field] );  
		}
		
			return $instance; 
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {
		//Set up some default widget settings.  
			$instance = (array) $instance; 
		?> 
		<p>  
				<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>  
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />  
		</p>  
			
	<?php

		if(!$this->github_username){
			echo "<p class='error'>Please <a href='".admin_url('profile.php#github')."'>enter your GitHub username</a> to retrieve the list of repositories.</p>";
		}
		foreach($this->fields as $field){
			$name = is_array($field) ? $field['name'] : $field;
			$type = is_array($field) ? $field['type'] : 'text';
			switch ($type) {
					case "select":
					 ?>
						<p>  
							<label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $name; ?>:</label>  
							<select id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" style="width:100%;">
							<?php foreach ($field['options'] as $option) { ?>
								<option value='<?php echo $option; ?>' <?php if($instance[$name] == $option) echo "selected"; ?>>
									<?php echo $option; ?>
								</option>		
							<?php } ?>
							</select>
					</p>  
					<?php     
							break;
					default:
					?> 
				<p>  
						<label for="<?php echo $this->get_field_id( $name ); ?>"><?php echo $name; ?>:</label>  
						<input type='<?php echo $type; ?>' <?php echo $field['min'] ? 'min='.$field['min'] : ''; ?> id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" value="<?php echo $instance[$name]; ?>" style="width:100%;" >  
				</p>  
			<?php
			}
		}
	}
}