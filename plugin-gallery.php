<?php

// ------ Creates Gallery Post Type ------
function create_gallery_post() {
register_post_type('gallery',
			array(
			'labels' => array(
					'name' => __('Galleri'),
					'singular_name' => __('Galleri'),
          'all_items' => __('Alla album'),
          'add_new' => __( 'Lägg till nytt album' ),
          'add_new_item' => __( 'Lägg till nytt album' ),
          'edit_item' => __( 'Ändra album' ),
          'view_item' => __( 'Ändra album' ),
          'not_found' => __( 'Inga album hittade' ),
				  'not_found_in_trash' => __( 'Inga album hittade i papperskorgen' )
			),
			'public' => true,
			'rewrite' => array( 'slug' => 'gallery/album' ),
      'menu_icon' => 'dashicons-format-gallery',
			'supports' => array(
					'title',
          'editor'
			)
	));
}
add_action('init', 'create_gallery_post');

// ------ Gets The First Image From Gallery ------
function catch_that_image() {
	global $post, $posts;
	$first_img = '';
	ob_start();
	ob_end_clean();
	$transformed_content = apply_filters('the_content',$post->post_content);
	$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $transformed_content, $matches);
	$first_img = $matches [1] [0];
	return $first_img;
}

?>