<?php
/**
 * text-domain: collect-snippets
 */

class Collect_PMPro_Gists {

	/**
	 * Add the minimum capabilities used for the plugin
	 */
	const min_caps = 'manage_options';

	protected $add_on_name;
	protected $database_names;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_admin_menus' ) );
		add_shortcode( 'ajax-submitting-form', array( $this, 'ajax_submitting_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'ajax_submitting_scripts' ) );
		add_action( 'wp_ajax_tie_into_php213', array( $this, 'ajax_returning_function' ) );
		add_action( 'wp_ajax_nopriv_tie_into_php213', array( $this, 'ajax_returning_function' ) );
		add_action( 'wp_footer', array( $this, 'add_sidebar_reference_menus' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_sidebar_reference_scripts' ) );
	}

	/**
	 * [parse_gist_url_parts description]
	 *
	 * https://bitbucket.org/snippets/pbrocks/qegXkx
	 *
	 * @param  [type] $url Various expressions of gist URLs
	 * @return array      Returns array of parsed URL constiutents
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
	 * Parse a gist URL and return its ID
	 *
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public function retrieve_gist_url_parts( $url ) {
		$url_array = $this->parse_gist_url_parts( $url );
		return $url_array['id'];
	}
	public function api_retrieve_gist_body( $url ) {
		$url_array = $this->parse_gist_url_parts( $url );
		$gist_id = $url_array['id'];

		$request = wp_remote_get( 'https://api.github.com/gists/' . $gist_id );

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

	/**
	 * Inspiration for this method came from
	 * https://pippinsplugins.com/using-wp_remote_get-to-parse-json-from-remote-apis/
	 *
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public function gist_url_in_page_info_out( $url ) {
		$url_array = $this->parse_gist_url_parts( $url );
		$gist_id = $url_array['id'];
		$user_id = $url_array['user'];
		// if ( )
		$check_transient = $this->check_if_in_transient( $gist_id );
		if ( 'not-found' !== $check_transient ) {
			return 'It is already present';
		}
		$data = wp_remote_get( 'https://api.github.com/gists/' . $gist_id );

		$body = wp_remote_retrieve_body( $data );

		if ( is_wp_error( $body ) ) {
			return false; // Bail early
		} else {
			$output = json_decode( $body, true );
			$return['filename'] = array_keys( $output['files'] )[0];
			$return['description'] = $output['description'];
			$return['user'] = $user_id;
			$return['id'] = $output['id'];
			$return['html_url'] = $output['html_url'];
			$return['updated_at'] = $output['updated_at'];
		}
		$post_content = "\n" . $return['html_url'] . "\n\n" . sprintf( __( 'Originally posted at %s.', 'link2post' ), '<a href="' . esc_url_raw( $return['html_url'] ) . '">' . $url_array['host'] . '</a> by ' . $url_array['user'] );
		// Create post object
		$new_gist = array(
			'post_title'    => $return['description'],
			'post_content'  => ' <br> ' . esc_url_raw( $url ) . ' <br> ' . $post_content,
			// 'post_author' => $current_user->ID,
			'post_status'   => 'publish',
			'post_date'     => $return['updated_at'],
			'meta_input'    => array(
				'l2p_url'   => esc_url_raw( $url ),
				'gist_id'   => $gist_id,
			),
			'post_type'     => 'gist',
		);

		// Insert the post into the database
		$return['post_id'] = wp_insert_post( $new_gist );
		$return['post_url'] = get_permalink( $return['post_id'] );
		return $return;
	}


	/**
	 * [build_gist_transient description]
	 *
	 * @return [type] [description]
	 */
	public function build_gist_transient() {
		$all_gists = $this->check_posts_for_gist_ids( 'gist' );

		// echo '<pre>';
		$transient[] = count( $all_gists );
		// echo '</pre>';
		foreach ( $all_gists as $key => $gist ) {
			$meta = get_post_meta( $gist->ID );
			if ( $meta['gist_id'][0] ) {
				$transient[] = array(
					'key' => $key,
					'post_id' => $gist->ID,
					'gist_id' => $meta['gist_id'][0],
				);
			} else {
				// echo "<li>$key " . $gist->ID . ' = no => ' . $gist->post_title . '</li>';
				$gist_url_parsed = $this->parse_gist_url_parts( $meta['l2p_url'][0] );
				update_post_meta( $gist->ID, 'gist_id', $gist_url_parsed['id'] );
				$transient[] = array(
					'key' => $key,
					'post_id' => $gist->ID,
					'gist_id' => $meta['gist_id'][0],
				);
			}
		}
		set_transient( 'existing_gists', $transient );
		return $transient;
	}

	/**
	 * [check_for_transient description]
	 *
	 * @return [type] [description]
	 */
	public function check_for_transient() {
		$transient = get_transient( 'existing_gists' );
		if ( ! empty( $transient ) ) {
			return $transient;
		} else {
			$transient = $this->build_gist_transient();
			return $transient;
		}
	}

	/**
	 * [check_if_in_transient description]
	 *
	 * @return [type] [description]
	 */
	public function check_if_in_transient( $gist_id ) {
		$gist_check = $this->check_for_transient();
		$search = $gist_id;
		$array = wp_list_pluck( $gist_check, 'gist_id' );
		if ( in_array( $search, $array ) ) {
			return $search . ' match found';
		} else {
			return 'not-found';
		}
	}

	/**
	 * [check_for_id_meta description]
	 *
	 * @return [type] [description]
	 */
	public function insert_gist_info_to_posttype( $url ) {
		$gist_array = $this->parse_gist_url_parts( $url );
		$title = $gist_array['description'];
		$post_content = "\n\n" . sprintf( __( 'Originally posted at %s.', 'link2post' ), '<a href="' . esc_url_raw( $gist_array['html_url'] ) . '">' . $url_array['host'] . '</a> by ' . $url_array['user'] );

		// Create post object
		$new_gist = array(
			'post_title'    => $gist_array['description'],
			'post_content'  => $gist_array['html_url'] . '<br>' . $post_content,
			// 'post_author' => $current_user->ID,
			'post_status'   => 'publish',
			'post_date'     => $gist_array['updated_at'],
			'meta_input'    => array(
				'l2p_url'   => esc_url_raw( $url ),
			),
			'post_type'     => 'gist',
		);

		// Insert the post into the database
		$post['id'] = wp_insert_post( $new_gist );
		$post['url'] = get_permalink( $post['id'] );

		// if ( true === $post_url ) {
		return $post;
		// }
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
	public function admin_head() {
		// Add custom styling to your page
	}

	public function check_posts_for_gist_ids( $post_type ) {
		$args = array(
			'post_type'   => $post_type,
			'posts_per_page'   => -1,
		);

		$all_posts = get_posts( $args );

		return $all_posts;

	}

	public function ajax_submitting_shortcode() {
		wp_enqueue_script( 'submitting-ajax' );
		?>
		<style type="text/css">
		#ajax-submitting {
			padding: 2rem;
		}
		input#gisturl {
			width: 85%;
		}
		</style>
		<div id="ajax-submitting">
		<form name="ajaxform" id="ajaxform">
			Gist URL: <input type="text" name="gisturl" id="gisturl" placeholder="Paste the Gist URL here"/> <br/>
		</form>
		<input type="button"  id="simple-click" value="Run Code" /><br/>
		<div id="simple-msg"></div>
		</div>
		<?php

	}

	public function ajax_submitting_scripts() {
		wp_register_script( 'submitting-ajax', plugins_url( '/js/ajax-submitting.js', __FILE__ ), array( 'jquery' ), time() );
		wp_localize_script(
			'submitting-ajax', 'submitting_ajax_object', array(
				'submitting_ajax_ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'submitting_ajax_nonce' => wp_create_nonce( 'submitting-ajax-nonce' ),
			)
		);
	}

	public function ajax_returning_function() {
		$return = $_POST;
		$url = $_POST['gisturl'];

		$gist_id = $this->retrieve_gist_url_parts( $url );
		$check_gist = $this->check_if_in_transient( $gist_id );
		$return['id_chk'] = $check_gist;

		$create_gist = $this->gist_url_in_page_info_out( $url );
		if ( ! empty( $create_gist ) ) {
			$return['gist_created'] = $create_gist['post_id'];
		}
		echo '<pre>';
		print_r( $return );
		echo '</pre>';

		exit;
	}

	public function submit_ajax_scripts() {
		wp_enqueue_style( 'submit-ajax', plugins_url( '/css/submit-ajax.css', __FILE__ ) );
		wp_register_script( 'submit-ajax', plugins_url( '/js/submit-ajax.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script(
			'submit-ajax', 'submit_ajax_object', array(
				'submit_ajax_ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'submit_ajax_nonce' => wp_create_nonce( 'submit-ajax-nonce' ),
				'other_url' => home_url( 'submit-ajax-nonce' ),
			// 'get_notes' => json_encode( get_transient( 'notes' ) ),
			)
		);
	}

	public function delete_gists_on_website_planning() {

		return 'DONE';
	}

	public function create_gists_admin_page() {
		require_once( PMPRO_DIR . '/adminpages/admin_header.php' );
		echo '<h2>' . __FUNCTION__ . '</h2>';
		// $transient = get_transient( 'values' );
		// $transient = $this->build_gist_transient();
		// $transient = $this->check_for_transient();
		// $transient = get_transient( 'existing_gists' );
		// $transient = get_transient( 'values' );
		$gist_id = 'd473abb787ada311bd777e6ae0f587e6';
		// $array = wp_list_pluck( $transient, 'gist_id' );
		// if ( in_array( $search, $array ) ) {
		// echo $search . ' match found';
		// } else {
		// echo $search . ' match not found';
		// }
		$check_transient = $this->check_if_in_transient( $gist_id );
		if ( 'not-found' !== $check_transient ) {
			echo '<h3 style="color:green;">It is in the check_if_in_transient ARRAY</h3>';
			echo '<pre>';
			print_r( $check_transient );
			echo '</pre>';
		} else {
			echo '<h3 style="color:tomato;">It is NOT in the ARRAY</h3>';
		}
		$got_transient = $this->check_for_transient();
		echo '<h3 style="color:tomato;">' . gettype( $got_transient ) . '</h3>';

		if ( 'array' === gettype( $got_transient ) ) {
			echo '<h3 style="color:tomato;">I said ARRAY</h3>';
			echo '<pre>';
			// print_r( $got_transient );
			echo '</pre>';
		} else {
			echo $got_transient;
		}
		$meta = get_post_meta( 79 );
		// $gist_url_parsed = $this->parse_gist_url_parts( $meta['l2p_url'][0] );
		echo '<h4 style="color:salmon">' . $meta['l2p_url'][0] . '</h4>';
		echo '<pre>';
		// print_r( $got_transient );
		echo '</pre>';
		?>
		<h3>morning</h3>
		<li>Check why creating dups</li>
		<h3>Plan</h3>
		<li>Take ID and get info from Github</li>
		<li>Take user and create, if doesn't exist</li>
		<li>Create post</li>
		<li>Assign user to post</li>
		<h3>Bonus</h3>
		<li>add to json file</li>
		<li>copy permalink</li>
		<?php
		$class_methods = get_class_methods( __CLASS__ );
		foreach ( $class_methods as $method_name ) {
			echo '<h4>' . $method_name . '</h4>';
		}
		require_once( PMPRO_DIR . '/adminpages/admin_footer.php' );
	}

	/**
	 * Add the page to the admin area
	 */
	public function create_admin_menus() {
		add_dashboard_page(
			__( __CLASS__, 'collect-snippets' ),
			__( __CLASS__, 'collect-snippets' ),
			self::min_caps,
			'dev-page.php',
			array( $this, 'create_gists_admin_page' )
		);
	}

	public function add_sidebar_reference_scripts() {
		wp_register_script( 'show-stuff', plugins_url( '/js/show-stuff.js', __FILE__ ), array( 'jquery' ), time() );
		wp_enqueue_script( 'show-stuff' );
		wp_register_script( 'sidebar-reference', plugins_url( '/js/sidebar-reference.js', __FILE__ ), array( 'jquery' ), time() );
		wp_enqueue_script( 'sidebar-reference' );
		wp_register_style( 'sidebar-reference', plugins_url( '/css/sidebar-reference.css', __FILE__ ), time() );
		wp_enqueue_style( 'sidebar-reference' );
	}
		/**
		 * Add the page to the admin area
		 */
	public function add_sidebar_reference_menus1() {
	?>
	<style type="text/css">
		button#sidebar-trigger {
			position: absolute;
			top: 9rem;
			left: 3rem;
		}
		section.sidebar-reference {
			width: 34%;
			position: absolute;
			top: 1rem;
			right: -40%;
			background: rgba(40,170,210,.7);
			height: 100%;
			padding: 2rem;
		}
		section.sidebar-reference.open {
			right: 1rem;
		}
	</style>
	<button id="sidebar-trigger">Open</button>
	<section class="sidebar-reference">
		<div class="referent">
			<h2>Big Story here</h2>
		</div>
	</section>
	<?php
	}

	/**
	 * Add the page to the admin area
	 */
	public function add_sidebar_reference_menus() {
	?>
	<style type="text/css">
		button#sidebar-trigger {
			position: absolute;
			top: 9rem;
			left: 3rem;
		}
		section.sidebar-reference {
			width: 34%;
			position: absolute;
			top: 1rem;
			right: -40%;
			background: rgba(40,170,210,.7);
			height: 100%;
			padding: 2rem;
		}
		section.sidebar-reference.open {
			right: 1rem;
		}
	</style>
	<button id="sidebar-trigger">Open</button>

	<section id="sidebar-reference">
		<input class="sidebar-reference" type="checkbox" id="menu"/>
		<nav class="sidebar-reference">

			<div class="w3-bar w3-pmpro">
				<button class="w3-bar-item w3-button" onclick="openReference('Gists')">Gists</button>
				<button class="w3-bar-item w3-button" onclick="openReference('Hooks')">Hooks</button>
				<button class="w3-bar-item w3-button" onclick="openReference('Documentation')">Documentation</button>
			</div>

			<div id="Gists" class="tab-container refSource">
				<h2>Gists</h2>
				<p>Bacon ipsum dolor sit amet landjaeger sausage brisket, jerky drumstick fatback boudin ball tip turducken.
					<?php echo do_shortcode( '[ajax-submitting-form]' ); ?>
				</p>
			</div>

			<div id="Hooks" class="tab-container refSource" style="display:none">
				<h2>Hooks</h2>
				<p>Capicola shank pig ribeye leberkas filet mignon brisket beef kevin tenderloin porchetta. Capicola fatback venison shank kielbasa, drumstick ribeye landjaeger beef kevin tail meatball pastrami prosciutto pancetta. Tail kevin spare ribs ground round ham ham hock brisket shoulder.</p> 
			</div>

			<div id="Documentation" class="tab-container refSource" style="display:none">
				<h2>Documentation</h2>
				<p>Brisket meatball turkey short loin boudin leberkas meatloaf chuck andouille pork loin pastrami spare ribs pancetta rump. Frankfurter corned beef beef tenderloin short loin meatloaf swine ground round venison.</p>
			</div>


			<!--label is inside the drawer but drawer is moved to left(-100%) when checkbox is not checked but the label is moved from left to 200px so it will look like when drawer is closed the label is there to open a drawer-->
			<label class="sidebar-reference" for="menu">
				<!--burger menu without cheese-->
				<span></span>
				<span></span>
			</label>
			<li><a href="#">One</a></li>
			<li><a href="#">Two</a></li>
			<li><a href="#">Three</a></li>
			<li><a href="#">Four</a></li>
			<li><a href="#">Five</a></li>

			<script>
				function openReference(refSourceName) {
					var i;
					var x = document.getElementsByClassName("refSource");
					for (i = 0; i < x.length; i++) {
						x[i].style.display = "none";  
					}
					document.getElementById(refSourceName).style.display = "block";  
				}
			</script>

		</nav>
</section>
	<?php
	}
}
// new Collect_PMPro_Gists();
