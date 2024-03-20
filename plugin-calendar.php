<?php

//------ Creates Calendar Post Type ------
function create_calendar_post() {
register_post_type('calendar',
			array(
			'labels' => array(
					'name' => __('Kalender'),
					'singular_name' => __('Kalender'),
          'all_items' => __('Alla aktiviteter'),
          'add_new' => __( 'Ny aktivitet' ),
          'add_new_item' => __( 'Ny aktivitet' ),
          'edit_item' => __( 'Ändra aktivitet' ),
          'view_item' => __( 'Ändra aktivitet' ),
          'not_found' => __( 'Inga aktiviteter hittade' ),
				  'not_found_in_trash' => __( 'Inga aktiviteter hittade i papperskorgen' )
			),
			'public' => true,
      'menu_icon' => 'dashicons-calendar-alt',
			'supports' => array(
					'title'
			),
      'register_meta_box_cb' => 'add_calendar_metaboxes'
	));
}
add_action('init', 'create_calendar_post');

// ------ Add the Calendar Meta Boxes ------
function add_calendar_metaboxes() {
	add_meta_box('wpt_calendar_information', 'Information om matchen', 'wpt_calendar_information', 'calendar', 'normal', 'default');
}

// ------ The Calendar Information Metabox ------
function wpt_calendar_information() {
	global $post;
	
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="calendarmeta_noncename" id="calendarmeta_noncename" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	
	// Get the information data if its already been entered
	$opponent = get_post_meta($post->ID, '_opponent', true);
  $datetime = get_post_meta($post->ID, '_datetime', true);
  $location = get_post_meta($post->ID, '_location', true);
	$hometeam = get_post_meta($post->ID, '_hometeam', true);
	$homegoals = get_post_meta($post->ID, '_homegoals', true);
	$awayteam = get_post_meta($post->ID, '_awayteam', true);
	$awaygoals = get_post_meta($post->ID, '_awaygoals', true);
	
	// Echo out the field
	echo '<p>Hemmalag</p>';
  echo '<input type="text" name="_hometeam" value="' . $hometeam  . '" />';
	echo '<p>Gjorda mål av hemmalaget</p>';
  echo '<input type="number" min="0" name="_homegoals" value="' . $homegoals  . '" />';
	
	echo '<p>Bortalag</p>';
  echo '<input type="text" name="_awayteam" value="' . $awayteam  . '" />';
	echo '<p>Gjorda mål av bortalaget</p>';
  echo '<input type="number" min="0" name="_awaygoals" value="' . $awaygoals  . '" />';
	
	echo '<p>Motståndare</p>';
  echo '<input type="text" name="_opponent" value="' . $opponent  . '" />';
  echo '<p>Datum & Tid</p>';
  echo '<input class="datetimepicker" type="text" name="_datetime" value="' . $datetime  . '" class="widefat" />';
  echo '<p>Plats</p>';
	echo '<input type="text" name="_location" value="' . $location  . '" class="widefat" />';
}

// ------ Add Datetime Picker To Admin ------
add_action('admin_enqueue_scripts', 'enqueue_datetimepicker_scripts');
function enqueue_datetimepicker_scripts($hook){
	if ($hook == 'post-new.php' || $hook == 'post.php' ) { // Add datepicker to new post & edit post screen 
		wp_enqueue_style('datepicker', get_template_directory_uri() . '/css/jquery-ui-timepicker-addon.min.css');
		wp_enqueue_style('jquery-ui', get_template_directory_uri() . '/css/jquery-ui.min.css');	
		wp_enqueue_script('jquery-ui-theme', get_template_directory_uri() . '/js/jquery-ui.min.js', array('jquery','jquery-ui-core'));
		// Date picker
		wp_enqueue_script('jquery-ui-timepicker-addon', get_template_directory_uri() . '/js/jquery-ui-timepicker-addon.min.js', array('jquery','jquery-ui-core'));
	}
}

// Settings for Datetime Picker
add_action( 'admin_head', 'add_datetimepicker_script' );
function add_datetimepicker_script(){
	global $post;
?>
	<script type="text/javascript">
		jQuery(document).ready(function() { 
			if(jQuery('.datetimepicker').length > 0){
				// Add any datepickers to all fields with the class
				jQuery('.datetimepicker').datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm'});
			}	 
		});
	</script>
<?php
}

// ------ Save the Metabox Data ------
function wpt_save_calendar_meta($post_id, $post) {
  
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['calendarmeta_noncename'], plugin_basename(__FILE__) )) {
	return $post->ID;
	}
  
	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;
    
	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	$calendar_meta['_opponent'] = $_POST['_opponent'];
	$calendar_meta['_datetime'] = $_POST['_datetime'];
  $calendar_meta['_location'] = $_POST['_location'];
  $calendar_meta['_hometeam'] = $_POST['_hometeam'];
	$calendar_meta['_homegoals'] = $_POST['_homegoals'];
	$calendar_meta['_awayteam'] = $_POST['_awayteam'];
	$calendar_meta['_awaygoals'] = $_POST['_awaygoals'];
  
	// Add values of $calendar_meta as custom fields
	foreach ($calendar_meta as $key => $value) { // Cycle through the $calendar_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}
}
add_action('save_post', 'wpt_save_calendar_meta', 1, 2); // save the custom fields

?>