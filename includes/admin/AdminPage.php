<?php
/**
 * AdminPage
 */

namespace ObiExcludeFromSearch\Includes\Admin;

/**
 * Check if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying what?' );
}

/**
 * AdminPage
 *
 * Admin page class
 *
 * @since 1.0.0
 * @access public
 * @package ObiExcludeFromSearch
 * @subpackage ObiExcludeFromSearch/includes
 * @author Obi Juan <hola@obi.dev>
 * @link https://obijuan.dev
 */
class AdminPage {

	/**
	 * Instance
	 *
	 * @var AdminPage
	 * @access private
	 * @static
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Admin page slug
	 *
	 * Property to store the slug of the admin page.
	 *
	 * @var string
	 * @access private
	 * @since 1.0.0
	 * @static
	 */
	private static $admin_slug;


	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		/**
		 * Register the options page
		 *
		 * Establish the admin page slug
		 */
		add_action( 'admin_menu', array( __CLASS__, 'register_options_page' ) );

		/**
		 * Maybe enqueue the admin scripts and styles
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'obi_handle_admin_scripts' ) );
	}

	/**
	 * Get the instance of the class.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the options page
	 *
	 * @since 1.0.0
	 * @return void
	 * @access public
	 * @static
	 */
	public static function register_options_page() {

		/**
		 * Register the admin page
		 *
		 * Save the admin page slug in a static property
		 * for later use in the enqueue handler.
		 */
		self::$admin_slug = add_menu_page(
			__( 'Exclude from Search', 'obi-exclude-from-search' ),
			__( 'Exclude from Search', 'obi-exclude-from-search' ),
			'manage_options',
			'obi-exclude-from-search',
			array( __CLASS__, 'obi_options_page_callback' )
		);
	}

	/**
	 * Options page callback
	 *
	 * Establishes the HTML element our react component will be rendered in
	 * for the options page.
	 *
	 * @since 1.0.0
	 * @return void
	 * @access public
	 * @static
	 */
	public static function obi_options_page_callback() {

		echo '<div id="obi-exclude-from-search-options"></div>';
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook_suffix The current admin page slug.
	 * @return void
	 * @access public
	 * @static
	 * @since 1.0.0
	 */
	public static function obi_handle_admin_scripts( $hook_suffix ) {

		/**
		 * Check if we're on the admin page
		 */
		if ( ! is_admin() ) {

			return;

		}

		/**
		 * Check if we're on the correct admin page
		 *
		 * Prevent enqueuing the script if we're not on our admin page.
		 */
		if ( $hook_suffix !== self::$admin_slug ) {

			return;
		}

		/**
		 * Enqueue the script
		 */
		self::obi_enqueue_admin_scripts();

		/**
		 * Enqueue the styles
		 */
		self::obi_enqueue_admin_styles();
	}

	public static function obi_enqueue_admin_scripts() {

		/**
		 * Enqueue the script that will render the React component.
		 */
		wp_enqueue_script( 'obi-options-scripts', OBI_EXCLUDE_FROM_SEARCH_URL . 'dist/js/obi-options.js', array( 'wp-element', 'wp-api' ), OBI_EXCLUDE_FROM_SEARCH_VERSION, true );

		/*
		*   Localize right after enqueuing the script.
		*
		*   Pass PHP data from the plugin to the JS script.
		*   E.g., the plugin version, the REST URL, nonce, etc.
		*
		*   https://developer.wordpress.org/reference/functions/wp_localize_script/
		*   https://codex.wordpress.org/Function_Reference/wp_localize_script
		*
		*   @param string $handle
		*   @param string $object
		*   @param array $data
		*/
		wp_localize_script(
			'obi-options-scripts',
			'obiOptions',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'root' => esc_url_raw( rest_url() ),
				'version' => OBI_EXCLUDE_FROM_SEARCH_VERSION,
			)
		);
	}

	public static function obi_enqueue_admin_styles() {

		/**
		 * Enqueue the styles
		 */
		wp_enqueue_style( 'admin_scss', OBI_EXCLUDE_FROM_SEARCH_URL . 'dist/css/styles.css', false, OBI_EXCLUDE_FROM_SEARCH_VERSION );
	}
}
