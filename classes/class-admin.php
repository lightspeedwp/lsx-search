<?php
/**
 * LSX Search Admin Class.
 *
 * @package lsx-search
 */

namespace lsx\search\classes;

/**
 * The administration class.
 */
class Admin {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx\search\classes\Admin()
	 */
	protected static $instance = null;
	/**
	 * Holds the options for the search.
	 *
	 * @var array()
	 */
	public $options = false;

	/**
	 * Holds the facetwp data for use in the fields.
	 *
	 * @var array()
	 */
	public $facet_data = false;

	/**
	 * Construct method.
	 */
	public function __construct() {
		$this->options = \lsx\search\includes\get_options();

		add_action( 'init', array( $this, 'set_vars' ) );
		add_action( 'init', array( $this, 'set_facetwp_vars' ) );
		add_action( 'cmb2_admin_init', array( $this, 'register_settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		if ( is_admin() ) {
			add_filter( 'lsx_customizer_colour_selectors_body', array( $this, 'customizer_body_colours_handler' ), 15, 2 );
			add_filter( 'lsx_customizer_colour_selectors_button', array( $this, 'customizer_button_colours' ), 10, 2 );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\member_directory\search\Admin()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Configure Business Directory custom fields for the Settings page.
	 *
	 * @return void
	 */
	public function register_settings_page() {
		$args = array(
			'id'           => 'lsx_search_settings',
			'title'        => '<h1>' . esc_html__( 'LSX Search Settings', 'lsx-search' ) . ' <span class="version">' . LSX_VERSION . '</span></h1>',
			'menu_title'   => esc_html__( 'Settings', 'search' ), // Falls back to 'title' (above).
			'object_types' => array( 'options-page' ),
			'option_key'   => 'lsx-search-settings', // The option key and admin menu page slug.
			'parent_slug'  => 'options-general.php',
			'capability'   => 'manage_options', // Cap required to view options-page.
		);
		$cmb  = new_cmb2_box( $args );
		do_action( 'lsx_search_settings_page', $cmb );
	}

	/**
	 * Sets post types with active search options.
	 */
	public function register_post_type_tabs( $post_types_plural ) {
		$post_types_plural = array(
			'project' => 'projects',
			'service' => 'services',
			'team' => 'team',
			'testimonial' => 'testimonials',
			'video' => 'videos',
			'product' => 'products', // WooCommerce
			'post' => 'posts',
		);
		return $post_types_plural;
	}

	/**
	 * Sets the FacetWP variables.
	 */
	public function set_facetwp_vars() {
		if ( class_exists( 'FacetWP' ) ) {
			$facet_data = FWP()->helper->get_facets();
		}

		$this->facet_data = array();

		$this->facet_data['search_form'] = array(
			'name' => 'search_form',
			'label' => esc_html__( 'Search Form', 'lsx-search' ),
		);

		if ( ! empty( $facet_data ) && is_array( $facet_data ) ) {
			foreach ( $facet_data as $facet ) {
				$this->facet_data[ $facet['name'] ] = $facet;
			}
		}
	}

	/**
	 * Enqueue JS and CSS.
	 */
	public function assets( $hook ) {
		wp_enqueue_script( 'lsx-search-admin', LSX_SEARCH_URL . 'assets/js/src/lsx-search-admin.js', array( 'jquery' ), LSX_SEARCH_VER, true );
		wp_enqueue_style( 'lsx-search-admin', LSX_SEARCH_URL . 'assets/css/lsx-search-admin.css', array(), LSX_SEARCH_VER );
	}

	/**
	 * Handle body colours that might be change by LSX Customiser.
	 */
	public function customizer_body_colours_handler( $css, $colors ) {
		$css .= '
			@import "' . LSX_SEARCH_PATH . '/assets/css/scss/customizer-search-body-colours";

			/**
			 * LSX Customizer - Body (LSX Search)
			 */
			@include customizer-search-body-colours (
				$bg: 		' . $colors['background_color'] . ',
				$breaker: 	' . $colors['body_line_color'] . ',
				$color:    	' . $colors['body_text_color'] . ',
				$link:    	' . $colors['body_link_color'] . ',
				$hover:    	' . $colors['body_link_hover_color'] . ',
				$small:    	' . $colors['body_text_small_color'] . '
			);
		';

		return $css;
	}

	/**
	 * Adds the lsx search buttons to the customizer plugin.
	 *
	 * @param string $css
	 * @param array $colours
	 * @return string
	 */
	public function customizer_button_colours( $css, $colours ) {
		$css .= '
			#secondary.facetwp-sidebar {
				.facetwp-item.facetwp-form {
					.search-form {
						.btn {
							&.search-submit {
								@include lsx-button-colour(' . $colours['button_text_color'] . ', ' . $colours['button_text_color_hover'] . ', ' . $colours['button_background_color'] . ', ' . $colours['button_background_hover_color'] . ', ' . $colours['button_shadow'] . ');
							}
						}
					}
				}
			}
		';
		return $css;
	}
}
