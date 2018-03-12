<?php
/**
 * text-domain: link2post
 */

new Help_Welcome_Menus();
class Help_Welcome_Menus {

	/**
	 * Add the minimum capabilities used for the plugin
	 */
	const min_caps = 'manage_options';

	protected $add_on_name;
	protected $database_names;

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'show_plugin_activation' ) );
		add_action( 'admin_menu', array( $this, 'create_admin_menus' ) );
		add_action( 'admin_init', array( $this, 'l2p_welcome' ), 11 );
		// add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_menu', array( $this, 'l2p_admin_help_tab' ) );
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
			__( 'Link2Post splash page', 'link2post' ),
			__( 'Link2Post splash page', 'link2post' ),
			self::min_caps,
			'l2p-page.php',
			array( $this, 'l2p_intro_message' )
		);

		// Remove the page from the menu
		remove_submenu_page( 'index.php', 'l2p-page.php' );
	}

	/**
	 * Display the plugin l2p message
	 */
	public function l2p_intro_message() {
		// echo '<div class="wrap">';
		require_once( PMPRO_DIR . '/adminpages/admin_header.php' );
		echo '<h2>' . __FUNCTION__ . '</h2>';
		echo '<p class="description">' . __( 'Link2Post splash page is created in the following file. This text can be edited in the method above.', 'link2post' ) . '</p>';
		echo '<p class="description">' . __( 'We know the Link2Post welcome class is active when the background-color of the dashboard changes to aliceblue.', 'link2post' ) . '</p>';
		echo '<h4>' . __FILE__ . '</h4>';
		echo '<a class="button button-primary" href="' . admin_url( 'options-general.php?page=link2post.php' ) . '" >Custom Button 1</a>';

			require_once( PMPRO_DIR . '/adminpages/admin_footer.php' );
		// echo '</div>';
	}

	/**
	 * Check the plugin activated transient exists if does then redirect
	 */
	public function l2p_welcome() {
		if ( ! get_transient( 'l2p_activated' ) ) {
			return;
		}

		// Delete the plugin activated transient
		delete_transient( 'l2p_activated' );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => 'l2p-page.php',
				), admin_url( 'index.php' )
			)
		);
		exit;
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
