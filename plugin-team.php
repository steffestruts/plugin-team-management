<?php

// ------ Creates Team Post Type ------
function create_team_post() {
register_post_type('team',
			array(
			'labels' => array(
					'name' => __('Laget'),
					'singular_name' => __('Laget'),
          'add_new' => __( 'Lägg till ny spelare' ),
          'add_new_item' => __( 'Lägg till ny spelare' ),
          'edit_item' => __( 'Ändra spelare' ),
          'view_item' => __( 'Ändra spelare' ),
          'not_found' => __( 'Inga spelare hittade' ),
				  'not_found_in_trash' => __( 'Inga spelare hittade i papperskorgen' )
			),
			'public' => true,
      'rewrite' => array( 'slug' => 'team/player' ),
      'menu_icon' => 'dashicons-groups',
			'supports' => array(
					'title',
					'thumbnail',
          'taxonomies'
			),
      'register_meta_box_cb' => 'add_player_metaboxes'
	));
}
add_action('init', 'create_team_post');

// ------ Player Status Section ------
function create_player_status() {
  $labels = array (
    'name' => 'Kategori',
    'add_new_item' => 'Lägg till ny kategori',
    'new_item_name' => 'Lägg till ny kategori'
  );
  $args = array(
    'labels' => $labels,
    'hierarchical' => true,
    'show_admin_column' => true,
  );
  register_taxonomy( 'status', 'team', $args );
  
  function adds_status_terms() {
    $terms = array(
      'Aktiv' => array(
        'slug' => 'active',
      ),
      'Skadad' => array( 
        'slug' => 'injured',
      ),
      'Hall of Fame' => array(
        'slug' => 'legend',
      ),
    );
    foreach($terms as $term => $meta){
      wp_insert_term(
        $term, // The Term 
          'status', // The Taxonomy
        $meta
      );
    }   
  }
  add_action( 'init', 'adds_status_terms' );
}
add_action( 'init', 'create_player_status', 0 );

// ------ Add the Player Meta Boxes ------
function add_player_metaboxes() {
	add_meta_box('wpt_player_information', 'Spelar information', 'wpt_player_information', 'team', 'normal', 'default');
}

// ------ The Player Information Metabox ------
function wpt_player_information() {
	global $post;
  
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="playermeta_noncename" id="playermeta_noncename" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
  
	// Get the information data if its already been entered
	$position = get_post_meta($post->ID, '_position', true);
  $number = get_post_meta($post->ID, '_number', true);
  
	// Echo out the field
  echo '<p>Position</p>';
  echo '<input type="text" name="_position" value="' . $position  . '" class="widefat" />';
  echo '<p>Nummer</p>';
  echo '<input type="number" min="0" name="_number" value="' . $number  . '" class="widefat" />';
}

// ------ Save the Metabox Data ------
function wpt_save_player_meta($post_id, $post) {
  
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['playermeta_noncename'], plugin_basename(__FILE__) )) {
	return $post->ID;
	}
  
	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;
    
	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
  $player_meta['_position'] = $_POST['_position'];
  $player_meta['_number'] = $_POST['_number'];
  
	// Add values of $events_meta as custom fields
	foreach ($player_meta as $key => $value) { // Cycle through the $player_meta array!
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
add_action('save_post', 'wpt_save_player_meta', 1, 2); // save the custom fields

?>