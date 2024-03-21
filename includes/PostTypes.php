<?php

namespace ObiExcludeFromSearch\Includes;

use WP_REST_Request;
use WP_REST_Response;

/**
 * PostTypes
 *
 * Class to handle post types
 *
 * @since 1.0.0
 */
class PostTypes {

	/**
	 * The PostTypes instance.
	 *
	 * @var PostTypes
	 * @access private
	 * @static
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initialize the PostTypes logic.
	 */
	private function __construct() {

		// Register post types endpoint.
		add_action( 'rest_api_init', array( __CLASS__, 'register_post_types_endpoints' ) );

		// Adjust the search query.
		add_action( 'pre_get_posts', array( __CLASS__, 'adjust_search_query' ) );

		// Handle new and removed post types.
		add_action( 'registered_post_type', array( __CLASS__, 'handle_new_post_type' ), 10, 2 );
		add_action( 'unregistered_post_type', array( __CLASS__, 'handle_removed_post_type' ), 10, 1 );
	}

	/**
	 * Get the instance of the class.
	 *
	 * @since 1.0.0
	 * @return PostTypes
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the post types endpoint.
	 *
	 * @since 1.0.0
	 * @return void
	 * @access public
	 * @static
	 */
	public static function register_post_types_endpoints() {

		/**
		 * Register the post types endpoint
		 *
		 * Usage : /wp-json/obiRCPT/v1/post-types
		 */
		register_rest_route(
			'obiRCPT/v1',
			'/post-types',
			array(
				'methods' => 'GET',
				'callback' => array( self::get_instance(), 'get_post_types_callback' ),
				'permission_callback' => '__return_true',
			)
		);

		/**
		 * Register the update post type status endpoint
		 *
		 * Usage : /wp-json/obiRCPT/v1/update-post-type-status
		 */
		register_rest_route(
			'obiRCPT/v1',
			'/update-post-type-status',
			array(
				'methods' => 'POST',
				'callback' => array( self::get_instance(), 'update_post_type_status_callback' ),
				'permission_callback' => function () {
					// Only allow administrators to update post type status!
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Get the post types callback.
	 *
	 * @since 1.0.0
	 * @return array. The post types with their corresponding statuses.
	 * @access public
	 * @static
	 */
	public function get_post_types_callback() {
		$args = array(
			'public' => true,
		);

		$post_types = get_post_types( $args, 'names', 'and' );

		// Fetch the post type statuses from the database!
		$post_type_statuses = get_option( 'obiRCPT_post_type_statuses', array() );

		// If the post type statuses have not been initialized, return the post types as is!
		if ( empty( $post_type_statuses ) ) {
			return $post_types;
		}

		// Return the post types with their corresponding statuses!
		return array_map(
			function ( $post_type ) use ( $post_type_statuses ) {
				return isset( $post_type_statuses[ $post_type ] ) ? $post_type_statuses[ $post_type ] : true;
			},
			array_flip( $post_types )
		);
	}

	/**
	 * Update the post type status callback.
	 *
	 * @param WP_REST_Request $request. The request object.
	 *
	 * @since 1.0.0
	 * @return WP_REST_Response. The response message.
	 * @access public
	 * @static
	 */
	public function update_post_type_status_callback( WP_REST_Request $request ) {
		// Extract the new post type status from the request!
		$post_type_status = $request->get_json_params();

		// Update the post type status in the database!
		update_option( 'obiRCPT_post_type_statuses', $post_type_status );

		return new WP_REST_Response( 'Post type status updated successfully.', 200 );
	}

	/**
	 * Initialize the post type statuses.
	 *
	 * This function is called when the plugin is activated.
	 * It initializes the post type statuses in the database.
	 *
	 * @since 1.0.0
	 * @return void
	 * @access public
	 * @static
	 */
	public static function initialize_post_type_statuses() {
		$initialized = get_option( 'obiRCPT_post_type_statuses_initialized', false );

		if ( ! $initialized ) {
			$args = array(
				'public' => true,
			);

			$post_types = get_post_types( $args, 'names', 'and' );

			$post_type_statuses = array();

			foreach ( $post_types as $post_type ) {
				$post_type_object = get_post_type_object( $post_type );
				$post_type_statuses[ $post_type ] = ! $post_type_object->exclude_from_search;
			}

			update_option( 'obiRCPT_post_type_statuses', $post_type_statuses );
			update_option( 'obiRCPT_post_type_statuses_initialized', true );
		}
	}

	/**
	 * Adjust the search query for post types.
	 *
	 * @param WP_Query $query. The query object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function adjust_search_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_search ) {
			$post_type_statuses = get_option( 'obiRCPT_post_type_statuses', array() );

			$searchable_post_types = array();
			foreach ( $post_type_statuses as $post_type => $included_in_search ) {
				if ( $included_in_search ) {
					$searchable_post_types[] = $post_type;
				}
			}

			$query->set( 'post_type', $searchable_post_types );
		}
	}

	/**
	 * Handle new added post types.
	 *
	 * @param string $post_type. The post type.
	 * @param array  $args. The post type arguments.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_new_post_type( $post_type, $args ) {
		$post_type_statuses = get_option( 'obiRCPT_post_type_statuses', array() );

		// Only add the new post type if it's not already in the array of existing post types.
		if ( ! isset( $post_type_statuses[ $post_type ] ) ) {
			$post_type_statuses[ $post_type ] = true;  // Or any other default value.
			update_option( 'obiRCPT_post_type_statuses', $post_type_statuses );
		}
	}

	/**
	 * Handle removed post types.
	 *
	 * @param string $post_type. The post type.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_removed_post_type( $post_type ) {
		$post_type_statuses = get_option( 'obiRCPT_post_type_statuses', array() );

		// Only update the option if the post type is in the array.
		if ( isset( $post_type_statuses[ $post_type ] ) ) {
			unset( $post_type_statuses[ $post_type ] );
			update_option( 'obiRCPT_post_type_statuses', $post_type_statuses );
		}
	}
}
