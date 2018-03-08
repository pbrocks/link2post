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

		// Add my_help_tab if current screen is My Admin Page
		$screen->add_help_tab(
			array(
				'id'    => 'l2p_help_tab_1',
				'title' => __( 'L2P Help Tab One' ),
				'content'   => '<p>' . __( 'Use this field to describe to the user what text you want on the help tab.' ) . '</p>',
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
		echo '<h2>' . __FUNCTION__ . '</h2>';
		echo '<h3>' . __FILE__ . '</h3>';
		echo '<h4>Page built with:</h4>';
		echo '<h1 style="color:salmon;">' . __CLASS__ . '</h1>';
		$post_types = get_post_types();
		echo '<pre>';
		print_r( $post_types );
		echo '</pre>';
		echo '<pre>
	add_action( \'admin_menu\', \'l2p_admin_help_tab\' );
	function l2p_admin_help_tab() {
	    $l2p_help_page = add_options_page( __( \'L2P Help Tab Page\', \'link2post\' ), __( \'L2P Help Tab Page\', \'link2post\' ),
	        \'manage_options\', \'link2post.php\', \'l2p_help_admin_page\' );
	    add_action( \'load-\' . $l2p_help_page, \'admin_add_help_tab\' );
	}</pre>';
		// function admin_add_help_tab() {
		// global $l2p_help_page;
		// $screen = get_current_screen();
		// Add my_help_tab if current screen is My Admin Page
		// $screen->add_help_tab(
		// array(
		// \'id\'    => \'l2p_help_tab\',
		// \'title\' => __( \'L2P Help Tab\' ),
		// \'content\'   => \'<p>\' . __( \'Use this field to describe to the user what text you want on the help tab.\' ) . \'</p>\',
		// )
		// );
		// }
		// function l2p_help_admin_page() {
		// echo \'<div class="wrap">\';
		// echo \'<h2>\' . __FUNCTION__ . \'</h2>\';
		// echo \'<h3>\' . __FILE__ . \'</h3>\';
		// echo \'</div>\';
		// }
		// </pre>';
		require_once( PMPRO_DIR . '/adminpages/admin_footer.php' );
	}
}
