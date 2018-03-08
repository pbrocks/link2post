<?php

/*
	DON'T FORGET TO REQUIRE THIS MODULE IN LINK2POST.PHP
printf( '<a href="%s" class="your-class">' . __( 'Your link text here' ) . '</a>',  __( 'http://example.com' ) );
*/

function l2p_add_pmpro_prod_module_module( $modules ) {
	/*
		Replace 'pmpro-prod-module.php' with the name of this file
		Replace 'samplemodule.com' with the host of the website this module will parse
		Replace 'l2p_pmpro_prod_module_callback' with the name of the callback function declared below
		Replace 'l2p_create_pmpro_prod_module_cpt' with the name of the create CPT function declared at the bottom of this file
		Replace 'pmpro-prod-module' with how you would like the name of the module to be displayed to the user, but no spaces since this is also used in sql option names
		If the module can update(ie. the final else statement in the callback function is filled out), change false to true
	*/
	$modules['pmpro_prod_module.php'] = array(
		'host' => 'www.paidmembershipspro.com',
		'callback' => 'l2p_pmpro_prod_module_callback',
		'create_cpt' => 'l2p_create_pmpro_prod_module_cpt',
		'quick_name' => 'PMPro Production URL',
		// 'quick_name' => sprintf( '<a target="_blank" href="%s">' . __( 'PMPro Production Site', 'link2post' ) . '</a>', 'https://www.paidmembershipspro.com' ),
		// 'quick_name' => sprintf( __( "<a target=\"_blank\" href=\"%1$s\">%2$s</a>", 'link2post' ), 'https://www.paidmembershipspro.com', 'PMPro Production Site' ),
		'can_update' => false,
	);
	return $modules;
}
add_filter( 'l2p_modules', 'l2p_add_pmpro_prod_module_module' );

/*
	DON'T FORGET TO REQUIRE THIS MODULE IN LINK2POST.PHP
*/

function l2p_pmpro_prod_module_callback( $url, $old_post_id = null, $return_result = false ) {
	global $current_user;

	// Set up selector
	require_once( L2P_DIR . '/lib/selector.php' );
	$html = wp_remote_retrieve_body( wp_remote_get( $url ) );

	/*
		Change 'title' to target the element where the title for the post should be
	*/
	$title = l2p_SelectorDOM::select_element( 'title', $html );
	if ( ! empty( $title ) && ! empty( $title['text'] ) ) {
		$title = sanitize_text_field( $title['text'] );
	}
	$objToReturn->title = $title;

	/*
		Change '#description_id' to target the element where the main content for the post should be
	*/
	$description = l2p_SelectorDOM::select_element( '#description_id', $html );
	if ( ! empty( $description ) && ! empty( $description['text'] ) ) {
		$description = sanitize_text_field( $description['text'] );
	}

	/*
		Repeat description step above for any other information you may want
	*/

	/*
		Format post content using the information found above
	*/
	$post_content = $description;

	// check if the pmpro-prod-module CPT exists
	/*
		Check if the CPT for this module is enabled
		Change 'pmpro-prod-module' to the first argument passed into the register_post_type function below
	*/
	$post_type = (post_type_exists( 'pmpro_prod_module' ) ? 'pmpro_prod_module' : 'post');

	// This runs if it is a new post
	if ( empty( $old_post_id ) ) {
		$postarr = array(
			'post_type' => $post_type,
			'post_title' => $title,
			'post_content' => $post_content,
			'post_author' => $current_user->ID,
			'post_status' => 'publish',
			'meta_input' => array(
				'l2p_url' => esc_url_raw( $url ),
			),
		);
		$post_id = wp_insert_post( $postarr );
		$post_url = get_permalink( $post_id );
		if ( $return_result == true ) {
			return $post_url;
		}
		$objToReturn->url = $post_url;
		$JSONtoReturn = json_encode( $objToReturn );
		echo $JSONtoReturn;
		exit;
	} // This runs if the post is being updated
	else {
		$postarr = array(
			'ID' => $old_post_id,
			'post_type' => $post_type,
			'post_title' => $title,
			'post_content' => $post_content,
			'post_author' => $current_user->ID,
		);
		wp_update_post( $postarr );
		$post_url = get_permalink( $old_post_id );
		$objToReturn->url = $post_url;
		$JSONtoReturn = json_encode( $objToReturn );
		echo $JSONtoReturn;
		exit;
	}
}

/*
	DON'T FORGET TO REQUIRE THIS MODULE IN LINK2POST.PHP
*/

/*
	Add a Sample Module CPT
	Replace all 'sample module' with information for your new CPT
*/
function l2p_create_pmpro_prod_module_cpt() {
	register_post_type(
		'pmpro_prod_module',
		array(
			'labels' => array(
				'name' => __( 'PMPro Prod URLs', 'link2post' ),
				'singular_name' => __( 'PMPro Prod URL', 'link2post' ),
				'add_new_item' => __( 'Add New PMPro Prod URL', 'link2post' ),
				'edit_item' => __( 'Edit PMPro Prod URL', 'link2post' ),
				'new_item' => __( 'New PMPro Prod URL', 'link2post' ),
				'view_item' => __( 'View PMPro Prod URL', 'link2post' ),
				'search_items' => __( 'Search PMPro Prod URLs', 'link2post' ),
				'not_found' => __( 'No PMPro Prod URLs Found', 'link2post' ),
				'not_found_in_trash' => __( 'No PMPro Prod URLs Found In Trash', 'link2post' ),
				'all_items' => __( 'All PMPro Prod URLs', 'link2post' ),
			),
			'public' => true,
			'has_archive' => true,
		)
	);
}

/*
	DON'T FORGET TO REQUIRE THIS MODULE IN LINK2POST.PHP
*/
