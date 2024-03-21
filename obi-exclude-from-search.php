<?php
/**
 * Plugin Name: Obi Exclude from Search
 * Description: Include or exclude (custom) post types from the WordPress search feature.
 * Version: 1.0.1
 * Author: Obi Juan <hola@obijuan.dev>
 * Author URI: https://obijuan.dev
 * Plugin URI: https://obijuan.dev/obi-remove-post-types-from-search
 * License: GPL2 or later
 * Textdomain: obi-exclude-from-search
 *
 * @since 1.0.0
 */

// Check if accessed directly!
if ( ! defined( 'ABSPATH' ) ) {

	exit( 'Trying what?' );
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use ObiExcludeFromSearch\Includes\Admin\AdminPage;

use ObiExcludeFromSearch\Includes\PostTypes;

/**
 * Obi_Init
 *
 * Initialize the plugin
 *
 * @since 1.0.0
 */
final class Obi_Init {


	/**
	 * Instance
	 *
	 * @var Obi_Init
	 * @access private
	 * @static
	 * @since 1.0.0
	 */
	private static $instance;


	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	private function __construct() {

		self::define_constants();

		self::check_minimum_php_version();
	}

	/**
	 * Get the instance of the class.
	 *
	 * @since 1.0.0
	 * @return Obi_Init
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Define constants
	 *
	 * @since 1.0.0
	 */
	private static function define_constants() {

		define( 'OBI_EXCLUDE_FROM_SEARCH_VERSION', '1.0.1' );
		define( 'OBI_EXCLUDE_FROM_SEARCH_TEXTDOMAIN', 'obi-exclude-from-search' );
		define( 'OBI_EXCLUDE_FROM_SEARCH_DIRNAME', plugin_basename( __DIR__ ) );
		define( 'OBI_EXCLUDE_FROM_SEARCH_FILE', __FILE__ );
		define( 'OBI_EXCLUDE_FROM_SEARCH_PREFIX', 'obi_exclude_from_search' );
		define( 'OBI_EXCLUDE_FROM_SEARCH_PATH', plugin_dir_path( OBI_EXCLUDE_FROM_SEARCH_FILE ) );
		define( 'OBI_EXCLUDE_FROM_SEARCH_URL', plugin_dir_url( OBI_EXCLUDE_FROM_SEARCH_FILE ) );
		define( 'OBI_EXCLUDE_FROM_SEARCH_MINIMUM_PHP_REQUIREMENT', '7.4' );
	}


	/**
	 * Load the plugin
	 *
	 * Load the classes we depend on and initialize the plugin.
	 *
	 * @since 1.0.0
	 */
	public static function load_obi_plugin() {
		// On plugins loaded...
		AdminPage::get_instance();
		PostTypes::get_instance();
	}


	/**
	 * Activate the plugin
	 *
	 * @since 1.0.0
	 */
	public static function activate() {

		// On plugin activation...

		// Initialize post type statuses.
		PostTypes::initialize_post_type_statuses();
	}

	/**
	 * Deactivate the plugin
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {

		// On plugin deactivation...
	}

			/**
			 * Get the minimum version of PHP required by this plugin.
			 *
			 * @return string Minimum version required.
			 */
	public static function obi_exclude_from_search_minimum_php_requirement() {
		return OBI_EXCLUDE_FROM_SEARCH_MINIMUM_PHP_REQUIREMENT;
	}

	/**
	 * Whether PHP installation meets the minimum requirements
	 *
	 * @return bool True if meets minimum requirements, false otherwise.
	 */
	public static function obi_exclude_from_search_site_meets_php_requirements() {
		return version_compare( phpversion(), self::obi_exclude_from_search_minimum_php_requirement(), '>=' );
	}


	/**
	 * Check if PHP version meets minimum requirements.
	 *
	 * Enqueue admin notice if not.
	 *
	 * @since 1.0.0
	 */
	public static function check_minimum_php_version() {

		// Ensuring our PHP version requirement is met first before loading the Obi Exclude From Search plugin.
		if ( ! self::obi_exclude_from_search_site_meets_php_requirements() ) {

			add_action(
				'admin_notices',
				array( __CLASS__, 'obi_exclude_from_search_admin_notice_php_version' )
			);

			return;
		}
	}

	/**
	 * Admin notice for PHP version requirement.
	 */
	public static function obi_exclude_from_search_admin_notice_php_version() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: Minimum required PHP version */
						__( 'Obi Exclude From Search requires PHP version %s or later. Please upgrade PHP or disable the plugin.', 'obi-exclude-from-search' ),
						esc_html( self::obi_exclude_from_search_minimum_php_requirement() )
					)
				);
				?>
			</p>
		</div>
		<?php
	}
}



/**
 * Get the instance of the class.
 *
 * @since 1.0.0
 * @return Obi_Init
 */
$obi_plugin = Obi_Init::get_instance();

/**
 * Load the classes we depend on to run the plugin.
 *
 * Load - AdminPage.
 * Load - PostTypes.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', array( $obi_plugin, 'load_obi_plugin' ) );

/**
 * Activate the plugin
 *
 * @since 1.0.0
 */
register_activation_hook( OBI_EXCLUDE_FROM_SEARCH_FILE, array( $obi_plugin, 'activate' ) );
