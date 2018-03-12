<?php
/**
 * text-domain: link2post
 */

new Dev_Get_Remote();
class Dev_Get_Remote {

	/**
	 * Add the minimum capabilities used for the plugin
	 */
	const min_caps = 'manage_options';

	protected $add_on_name;
	protected $database_names;

	public function __construct() {
		// add_action( 'admin_enqueue_scripts', array( $this, 'show_plugin_activation' ) );
		add_action( 'admin_menu', array( $this, 'create_admin_menus' ) );
		// add_action( 'admin_init', array( $this, 'l2p_welcome' ), 11 );
		// add_action( 'admin_head', array( $this, 'admin_head' ) );
		// add_action( 'admin_menu', array( $this, 'l2p_admin_help_tab' ) );
	}

	/**
	 * Add the page to the admin area
	 */
	public function show_plugin_activation() {
		?>
		<style type="text/css">
		#wpwrap {
			background-color: aliceblue;
		}
		#wpbody-content .pmpro_admin {
			min-height: 89vh;
			height: 95%;
			padding: .5rem;
		}
		</style>
		<?php
	}

	/**
	 * Add the page to the admin area
	 */
	public function create_admin_menus() {
		add_dashboard_page(
			__( __CLASS__, 'link2post' ),
			__( __CLASS__, 'link2post' ),
			self::min_caps,
			'dev-page.php',
			array( $this, 'l2p_intro_message2' )
		);

		// Remove the page from the menu
		// remove_submenu_page( 'index.php', 'l2p-page.php' );
	}

	public function gist_url_in_page_info_out( $url ) {
		$request = wp_remote_get( $url );

		if ( is_wp_error( $request ) ) {
			return false; // Bail early
		} else {
			$output = json_decode( $request['body'], true );
			$return['filename'] = array_keys( $output['files'] )[0];
			$return['description'] = $output['description'];
			$return['id'] = $output['id'];
			$return['html_url'] = $output['html_url'];
			$return['raw_url'] = $output['files'][ $filename ]['raw_url'];
			$return['updated_at'] = $output['updated_at'];
			$return['content'] = $output['files'][ $filename ]['content'];
			return $return;
		}
	}

	public function gist_url_in_print_info_out( $url ) {
		// https://pippinsplugins.com/using-wp_remote_get-to-parse-json-from-remote-apis/
		echo '<div class="wrap">';
		echo '<h2>' . __FUNCTION__ . '</h2>';
		$request = wp_remote_get( $url );

		if ( is_wp_error( $request ) ) {
			return false; // Bail early
		} else {
			$output = json_decode( $request['body'], true );
			$filename = array_keys( $output['files'] )[0];
			echo '<br>' . $filename . '<br>';
			echo '<b>description = </b>' . $output['description'] . '<br>';
			echo '<b>id = </b>' . $output['id'] . '<br>';
			echo '<b>html_url = </b>' . $output['html_url'] . '<br>';
			echo '<b>raw_url = </b>' . $output['files'][ $filename ]['raw_url'] . '<br>';
			echo '<b>updated_at = </b>' . $output['updated_at'] . '<br>';

			$content = $output['files'][ $filename ]['content'] . '<br>';
			echo wpautop( $content );
		}
		echo '</div>';
	}
	/**
	 *
	 */
	public function parse_gist_url_parts( $url ) {
		$parse = parse_url( $url );
		$explode = explode( '/', $parse['path'] );
		if ( empty( $explode[2] ) ) {
			$parse['id'] = $explode[1];
		} elseif ( 'api.github.com' === $parse['host'] ) {
			$parse['subdir'] = $explode[1];
			$parse['id'] = $explode[2];
		} else {
			$parse['user'] = $explode[1];
			$parse['id'] = $explode[2];
		}
		return $parse;
	}
	/**
	 *
	 */
	public function url_in_remote_retrieve_body( $url ) {
		$response = wp_remote_retrieve_body( $url );
		return $response;
	}
	/**
	 *
	 */
	public function api_retrieve_remote_retrieve_body() {
		$request = wp_remote_get( 'https://api.github.com/gists/f7be8cf6dea1ed8a9f08ba83f5c6f043' );

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body, true );

		echo $data['id'] . '<br>';
		echo $data['html_url'] . '<br>';
		$filename = array_keys( $data['files'] )[0];
		echo $filename . '<br>';
		echo $data['files'][ $filename ]['raw_url'] . '<br>';
		$content = $data['files'][ $filename ]['content'] . '<br>';
		echo wpautop( $content );

		echo '<pre>';
		// print_r( $data );
		echo '</pre>';

	}


	public function l2p_intro_message3() {
		echo '<div class="wrap">';
		echo '<h2>' . __FUNCTION__ . '</h2>';
		$url = 'https://gist.github.com/966a525500be3090811086ee33871fba';
		// $response = $this->url_in_remote_retrieve_body( $url );
		$response = wp_remote_get( $url );

		echo '<pre>';
		print_r( $response );
		echo '</pre>';
		/*
		 Will result in $api_response being an array of data,
		parsed from the JSON response of the API listed above * /
		$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

		echo '<pre>';
		$filename = array_keys( $api_response['files'] );
		echo '<br>' . $filename[0] . '<br>';
		print_r( array_keys( $api_response['files'] ) );
		// print_r( array_keys( $api_response->files ) );
		*/
		echo '</pre>';

	}
	/**
	 *
	 */
	public function l2p_intro_message2() {
		echo '<div class="wrap">';
		echo '<h2>' . __FUNCTION__ . '</h2>';
		// Add custom styling to your page
		$json = '{ "John":{ "gender":"male", "age":"12" }, "Jen":{ "gender":"female", "age":"13" } }';
		$url = 'https://api.github.com/gists/966a525500be3090811086ee33871fba';
		// $url = 'https://gist.github.com/966a525500be3090811086ee33871fba';
		$url = 'https://gist.github.com/strangerstudios/f7be8cf6dea1ed8a9f08ba83f5c6f043';
		echo '<span style="color:salmon;">';
		$data = $this->parse_gist_url_parts( $url );

		echo '<pre>';
		print_r( $data );
		echo '</pre>';
		echo '</span>';

		echo '<h2>=========== something ===============</h2>';
		$request = wp_remote_get( 'https://api.github.com/gists/966a525500be3090811086ee33871fba' );

		$body = wp_remote_retrieve_body( $request );

		$data = json_decode( $body, true );

		echo $data['id'] . '<br>';
		echo $data['html_url'] . '<br>';
		$filename = array_keys( $data['files'] )[0];
		echo $filename . '<br>';
		echo $data['files'][ $filename ]['raw_url'] . '<br>';
		$content = $data['files'][ $filename ]['content'] . '<br>';
		echo wpautop( $content );

		echo '</div>';
	}
	 /**
	  *
	  */
	public function l2p_intro_message1() {
		echo '<div class="wrap">';
		echo '<h2>' . __FUNCTION__ . '</h2>';
		$someJSON = '[{"name":"Jonathan Suh","gender":"male"},{"name":"William Philbin","gender":"male"},{"name":"Allison McKinnery","gender":"female"}]';

		// Convert JSON string to Array
		$someArray = json_decode( $someJSON, true );
			echo '<pre>';
		print_r( $someArray );        // Dump all data of the Array
			echo '</pre>';
		echo $someArray[0]['name']; // Access Array data

		// Convert JSON string to Object
		$someObject = json_decode( $someJSON );
			echo '<pre>';
		print_r( $someObject );      // Dump all data of the Object
			echo '</pre>';
		echo $someObject[0]->name;
		echo '</div>';
	}

	/**
	 *
	 */
	public function l2p_intro_message() {
		// Add custom styling to your page
		// https://pippinsplugins.com/using-wp_remote_get-to-parse-json-from-remote-apis/
		echo '<div class="wrap">';
		echo '<h2>' . __FUNCTION__ . '</h2>';
		$request = wp_remote_get( 'https://api.github.com/gists/966a525500be3090811086ee33871fba' );

		if ( is_wp_error( $request ) ) {
			return false; // Bail early
		} else {
			$output = json_decode( $request['body'], true );
			echo '<br>' . $output['id'] . '<br>';
			echo $output['html_url'] . '<br>';
			echo $output['description'] . '<br>';
			echo $output['updated_at'] . '<br>';
			echo $output['files']['my_pmpro_pre_handle_404.php']['filename'] . '<br>';
			echo $output['files']['my_pmpro_pre_handle_404.php']['raw_url'] . '<br>';
			echo wpautop( $output['files']['my_pmpro_pre_handle_404.php']['content'], true ) . '<br>';
			// echo $output->html_url . '<br>';
			// echo $output->files['my_pmpro_pre_handle_404.php'] . '<br>';
			// echo $output->files . '<br>';
			echo '<pre>';
			// print_r( $output );
			// print_r( $output->files );
			echo '</pre>';
		}
		echo '</div>';
	}

	/**
	 *
	 */
	public function admin_head() {
		// Add custom styling to your page
	}

	public function l2p_admin_help_tab() {
		global $l2p_help_page;
		// $l2p_help_page = add_dashboard_page( __( 'L2P Help Tab Page', 'link2post' ), __( 'L2P Help Tab Page', 'link2post' ), self::min_caps, 'link2post.php', array( $this, 'l2p_help_admin_page' ) );
		$l2p_help_page = add_submenu_page( 'options-general.php', 'Link2Post Help', 'Link2Post Help', self::min_caps, 'link2post.php', array( $this, 'l2p_help_admin_page' ) );
		add_action( 'load-' . $l2p_help_page, array( $this, 'admin_add_help_tab' ) );
	}

	public function admin_add_help_tab() {
		global $l2p_help_page;
		$screen = get_current_screen();
		$post_types = get_post_types();
		$post_type = '';
		foreach ( $post_types as $key => $value ) {
			$post_type .= $value . '<br>';
		}

		// Add my_help_tab if current screen is My Admin Page
		$screen->add_help_tab(
			array(
				'id'    => '$post_types',
				'title' => __( 'Post Types' ),
				'content'   => '<pre>' . $post_type . '</pre>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'    => 'l2p_help_tab_2',
				'title' => __( 'L2P Help Tab Two' ),
				'content'   => '<p>' . __( 'Use this field to describe to the user what text you want on the help tab.' ) . '</p>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'    => 'l2p_help_tab_3',
				'title' => __( 'L2P Help Tab Three' ),
				'content'   => '<p>' . __( 'Use this field to describe to the user what text you want on the help tab.' ) . '</p>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'    => 'l2p_help_tab_4',
				'title' => __( 'L2P Help Tab Four' ),
				'content'   => '<p>' . __( 'Use this field to describe to the user what text you want on the help tab.' ) . '</p>',
			)
		);
	}

	public function l2p_help_admin_page() {
		require_once( PMPRO_DIR . '/adminpages/admin_header.php' );
		echo '<h4>' . __FILE__ . '</h4>';
		echo '<h1 style="color:salmon;">' . __CLASS__ . '</h1>';
		// $post_types = get_post_types();
		// echo '<pre>';
		// print_r( $post_types );
		// echo '</pre>';
		// echo $form;
		$url = 'https://gist.github.com/b906f3e1566cc129657bf169d2c74df1';
		echo '<h3>Processing: <span style="color:tomato">' . $url . '</span></h3>';
		// ob_start(); // start capturing output
		// include( 'csv/return-gists-kimcoleman.csv' ); // execute the file
		// $array = ob_get_contents(); // get the contents from the buffer
		// ob_end_clean();
		// echo '$array is a ' . gettype( $array ) . '<pre> print_r() ';
		// print_r( $array );
		// echo '</pre>';
		$this->parse_this_url( $url );
		// $gist_post = $this->send_these_urls();
		// $gist_post = $this->parse_this_url( $url );
		// if ( ! empty( $gist_post ) ) {
		// echo '<h3>It did something!</h3>';
		// } else {
		// echo '<h3>It did nothing!</h3>';
		// }
		echo do_shortcode( '[pbrx-ajax-form]' );
		require_once( PMPRO_DIR . '/adminpages/admin_footer.php' );
	}

	public function send_these_urls() {
		$array = 'https://gist.github.com/6d9c51b31a5e794bfac629fd6c41897c
// https://gist.github.com/1b480f16506163d4dbf23f3d64ab0468
// https://gist.github.com/08c2d466ea370ae19bab983aeb0140cd
// https://gist.github.com/69249876ef3e5dd15699d22c2789c87d
// https://gist.github.com/94edb917c37e166c380aae6d3a4d4af6
// https://gist.github.com/b7a17d5cdaff5f5375fdec502fe00c3e
// https://gist.github.com/c961a7210549dc18b2a31db73befe5e9
// https://gist.github.com/58dc2653bde9156d98dc3b439f4faa2b
// https://gist.github.com/0528234f20dcd338933daed66a6c0a58
// https://gist.github.com/41f84b6c46189ad308209c68c7ba016d
// 









';
		$array = explode( "\n", $array );
		echo '<span style="color:tomato">$array is a ' . gettype( $array ) . '</span>';
		$i = 9;
		foreach ( $array as $key => $value ) {
			// echo '<li style="color:salmon">' . $key . ' => ' . $value . '</li>';
			if ( $i < $key ) {
				$this->parse_this_url( $value );
				sleep( 9 );
				$i++;
			}
		}
		echo '<h1>Done!!</h1>';
	}

	public function parse_this_url( $url ) {
		global $wpdb;

		// no URL, bail
		if ( empty( $url ) ) {
			die( 'Your URL is empty ' );
			// exit;
		}
		$objToReturn = new stdClass();
		$objToReturn->on_tools_page = l2p_on_tools_page();

		// Check if we've already processed this URL.
		$sqlQuery = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'l2p_url' AND meta_value = '" . esc_url_raw( $url ) . "' LIMIT 1";
		$old_post_id = $wpdb->get_var( $sqlQuery );
		if ( empty( (int) $old_post_id ) || get_post_status( (int) $old_post_id ) != 'publish' ) {
			$objToReturn->new_post_created = true;
			$objToReturn->new_post_url = l2p_update( $url, null, true );
			$JSONtoReturn = json_encode( $objToReturn );
			echo $JSONtoReturn;
			exit;
		}
		$objToReturn->new_post_created = false;
		$objToReturn->old_post_id = $old_post_id;
		$objToReturn->old_post_url = get_permalink( $old_post_id );

		$modules = l2p_get_modules();

		// Check the domain of the URL to see if it matches a module.
		$host = parse_url( $url, PHP_URL_HOST );
		$found_match = false;
		foreach ( $modules as $key => $value ) {
			if ( $host == $value['host'] && get_option( 'l2p_' . $value['quick_name'] . '_content_enabled' ) == 'enabled' ) {
				$found_match = true;
				// we found one, use the module's parse function now
				if ( empty( $value['callback'] ) || empty( $value['can_update'] ) || $value['can_update'] == false ) {
					$objToReturn->can_update = false;
				} else {
					$objToReturn->can_update = true;
				}
			}
		}
		if ( $found_match == false ) {
			$objToReturn->can_update = true;
		}
		echo '<pre>';
		print_r( $objToReturn );
		echo '</pre>';

		// $JSONtoReturn = json_encode( $objToReturn );
		// echo $JSONtoReturn;
		exit;

	}
}
