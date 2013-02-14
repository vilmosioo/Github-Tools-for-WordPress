<?php
/*
* Widget
* 
* Creates a Widget to be added in the sidebar. This class is a template, if you wish to customize it, add more fields and change the title (among other things)
* 
*/
class Github_Commits_Widget extends WP_Widget{
	private $slug, $title, $description, $class;
	private $fields;

	function __construct($args = array()) {
		$args = array_merge ( array(
			"title" => 'Custom Widget',
			"description" => 'A widget that displays the authors name ',
			"class" => 'hyperion_widget'
	    ), $args );
		
		$this->slug = Utils::generate_slug($args['title']);
		$this->title = $args['title'];
		$this->description = $args['description'];
		$this->class = $args['class'];
		$this->fields = array('blablabla');

		parent::__construct( false, $this->title );
	}

	function Github_Commits_Widget() {
        $widget_ops = array( 
        	'classname' => $this->class, 
        	'description' => $this->description
    	);  
        $control_ops = array( 
        	'width' => 300, 
        	'height' => 350, 
        	'id_base' => $this->class 
    	);  
        $this->WP_Widget( $this->slug, $this->title, $widget_ops, $control_ops );  
    }  
	
	/*
	* Front-end view of the widget
	*/
    function widget( $args, $instance ) {
    	extract( $args );  

    	$title = apply_filters('widget_title', $instance['title'] );  
		  
		echo $before_widget;  
		  
		if ( $title )  
		    echo $before_title . $title . $after_title;  
				
		foreach($this->fields as $field){
			echo $instance[$field] ? $instance[$field] : "";
		}

		echo $after_widget;
	}

	/*
	* Update the widget 
	*/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;  
  
	    //Strip tags from title and name to remove HTML  
	    $instance['title'] = strip_tags( $new_instance['title'] );  
	    foreach($this->fields as $field){
	    	$instance[$field] = strip_tags( $new_instance[$field] );  
		}
		
	    return $instance; 
	}

	/*
	* Back-end view of the widget
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
		foreach($this->fields as $field){
		?> 
			<p>  
			    <label for="<?php echo $this->get_field_id( $field ); ?>"><?php echo $field; ?>:</label>  
			    <input id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>" value="<?php echo $instance[$field]; ?>" style="width:100%;" />  
			</p>  
		<?php
		}
	}
}