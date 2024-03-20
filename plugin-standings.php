<?php

// ------ Creates Standings Post Type ------
function create_standings_post() {
register_post_type('standings',
			array(
			'labels' => array(
					'name' => __('Speltabell'),
					'singular_name' => __('Speltabell'),
          'all_items' => __('Alla lag'),
          'add_new' => __( 'Lägg till nytt lag' ),
          'add_new_item' => __( 'Lägg till nytt lag' ),
          'edit_item' => __( 'Ändra lag' ),
          'view_item' => __( 'Ändra lag' ),
          'not_found' => __( 'Inga lag hittade' ),
				  'not_found_in_trash' => __( 'Inga lag hittade i papperskorgen' )
			),
			'public' => true,
      'menu_icon' => 'dashicons-editor-table',
			'supports' => array(
					'title',
			),
      'register_meta_box_cb' => 'add_standings_metaboxes'
	));
}
add_action('init', 'create_standings_post');

// ------ Add the Standings Meta Boxes ------
function add_standings_metaboxes() {
	add_meta_box('wpt_standings_information', 'Lagstatistik', 'wpt_standings_information', 'standings', 'normal', 'default');
}

// ------ The Standings Information Metabox ------
function wpt_standings_information() {
	global $post;
  
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="standingsmeta_noncename" id="standingsmeta_noncename" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
  
	// Get the information data if its already been entered
	$played = get_post_meta($post->ID, '_played', true);
	$difference = get_post_meta($post->ID, '_difference', true);
	$points = get_post_meta($post->ID, '_points', true);
  
	// Echo out the field
	echo '<p>Spelade matcher (S)</p>';
  echo '<input type="number" min="0" name="_played" value="' . $played  . '" class="widefat" />';
	echo '<p>Målskillnad (+/-)</p>';
  echo '<input name="_difference" value="' . $difference  . '" class="widefat" />';
  echo '<p>Poäng (P)</p>';
  echo '<input type="number" min="0" name="_points" value="' . $points  . '" class="widefat" />';
}

// ------ Save the Metabox Data ------
function wpt_save_standings_meta($post_id, $post) {
  
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['standingsmeta_noncename'], plugin_basename(__FILE__) )) {
	return $post->ID;
	}
  
	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;
    
	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	$standings_meta['_played'] = $_POST['_played'];
	$standings_meta['_difference'] = $_POST['_difference'];
  $standings_meta['_points'] = $_POST['_points'];
  
	// Add values of $events_meta as custom fields
	foreach ($standings_meta as $key => $value) { // Cycle through the $standings_meta array!
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
add_action('save_post', 'wpt_save_standings_meta', 1, 2); // save the custom fields

?>