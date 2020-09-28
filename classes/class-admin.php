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
	 * Holds the Alpha betical facetwp data for use in the fields.
	 *
	 * @var array()
	 */
	public $az_facets = array();

	/**
	 * Holds the settings page theme functions
	 *
	 * @var object \lsx\search\classes\admin\Settings_Theme();
	 */
	public $settings_theme;

	/**
	 * Construct method.
	 */
	public function __construct() {
		$this->load_classes();
		add_action( 'cmb2_admin_init', array( $this, 'register_settings_page' ) );
		add_action( 'lsx_search_settings_page', array( $this, 'configure_settings_search_engine_fields' ), 15, 1 );
		add_action( 'lsx_search_settings_page', array( $this, 'configure_settings_search_archive_fields' ), 15, 1 );
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
	 * Loads the variable classes and the static classes.
	 */
	private function load_classes() {
		require_once LSX_SEARCH_PATH . 'classes/admin/class-settings-theme.php';
		$this->settings_theme = admin\Settings_Theme::get_instance();
	}

	/**
	 * Configure Business Directory custom fields for the Settings page.
	 *
	 * @return void
	 */
	public function register_settings_page() {
		$args = array(
			'id'           => 'lsx_search_settings',
			'title'        => '<h1>' . esc_html__( 'LSX Search Settings', 'lsx-search' ) . ' <span class="version">' . LSX_SEARCH_VER . '</span></h1>',
			'menu_title'   => esc_html__( 'LSX Search', 'search' ), // Falls back to 'title' (above).
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
	 * Gets the Tour Operator Post Types.
	 *
	 * @return array
	 */
	public function get_to_post_types() {
		$to_types = array(
			'accommodation',
			'tour',
			'destination',
			'review',
			'activity',
			'special',
			'vehicle',
		);
		return $to_types;
	}

	/**
	 * Sets the FacetWP variables.
	 *
	 * @return  void
	 */
	public function set_facetwp_vars() {
		if ( function_exists( '\FWP' ) ) {
			$facet_data = \FWP()->helper->get_facets();
		}
		$this->facet_data = array();
		$this->az_facets  = array(
			'' => __( 'Do not show', 'lsx-search' ),
		);
		if ( ! empty( $facet_data ) && is_array( $facet_data ) ) {
			foreach ( $facet_data as $facet ) {
				if ( 'alpha' === $facet['type'] ) {
					$this->az_facets[ $facet['name'] ] = $facet['label'] . '(' . $facet['name'] . ')';
				} else {
					$this->facet_data[ $facet['name'] ] = $facet['label'] . '(' . $facet['name'] . ')';
				}
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
	 * Enable Business Directory Search settings only if LSX Search plugin is enabled.
	 *
	 * @return  void
	 */
	public function configure_settings_search_engine_fields( $cmb ) {
		$global_args = array(
			'title' => __( 'Global', 'lsx-search' ),
			'desc'  => esc_html__( 'Control the filters which show on your WordPress search results page.', 'lsx-search' ),
		);
		$this->search_fields( $cmb, 'engine', $global_args );
	}

	/**
	 * Enable Business Directory Search settings only if LSX Search plugin is enabled.
	 *
	 * @param object $cmb The CMB2() class.
	 * @param string $position either top of bottom.
	 * @return void
	 */
	public function configure_settings_search_archive_fields( $cmb ) {
		$archives       = array();
		$post_type_args = array(
			'public' => true,
		);
		$post_types     = get_post_types( $post_type_args );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type_key => $post_type_value ) {
				switch ( $post_type_key ) {
					case 'post':
						$page_url      = home_url();
						$page_title    = __( 'Home', 'lsx-search' );
						$show_on_front = get_option( 'show_on_front' );
						if ( 'page' === $show_on_front ) {
							$page_for_posts = get_option( 'page_for_posts' );
							if ( '' !== $page_for_posts ) {
								$page_title   = get_the_title( $page_for_posts );
								$page_url     = get_permalink( $page_for_posts );
							}
						}
						$description = sprintf(
							/* translators: %s: The subscription info */
							__( 'Control the filters which show on your <a target="_blank" href="%1$s">%2$s</a> page.', 'lsx-search' ),
							$page_url,
							$page_title
						);
						$archives[ $post_type_key ] = array(
							'title' => __( 'Blog', 'lsx-search' ),
							'desc'  => $description,
						);
						break;

					case 'product':
						$page_url = home_url();
						$page_title    = __( 'Shop', 'lsx-search' );
						if ( function_exists( 'wc_get_page_id' ) ) {
							$shop_page  = wc_get_page_id( 'shop' );
							$page_url   = get_permalink( $shop_page );
							$page_title = get_the_title( $shop_page );
						}
						$description = sprintf(
							/* translators: %s: The subscription info */
							__( 'Control the filters which show on your <a target="_blank" href="%1$s">%2$s</a> page.', 'lsx-search' ),
							$page_url,
							$page_title
						);
						$archives[ $post_type_key ] = array(
							'title' => __( 'Shop', 'lsx-search' ),
							'desc'  => $description,
						);
						break;

					default:
						if ( ! in_array( $post_type_key, \lsx\search\includes\get_restricted_post_types() ) ) {
							$temp_post_type = get_post_type_object( $post_type_key );
							if ( ! is_wp_error( $temp_post_type ) ) {
								$page_url    = get_post_type_archive_link( $temp_post_type->name );
								$description = sprintf(
									/* translators: %s: The subscription info */
									__( 'Control the filters which show on your <a target="_blank" href="%1$s">%2$s</a> archive.', 'lsx-search' ),
									$page_url,
									$temp_post_type->label
								);

								$archives[ $post_type_key ] = array(
									'title' => $temp_post_type->label,
									'desc'  => $description,
								);
							}
						}
						break;
				}
			}
		}
		if ( ! empty( $archives ) ) {
			foreach ( $archives as $archive_key => $archive_args ) {
				$this->search_fields( $cmb, $archive_key, $archive_args );
			}
		}
	}

	/**
	 * Enable Business Directory Search settings only if LSX Search plugin is enabled.
	 *
	 * @param object $cmb The CMB2() class.
	 * @param string $section either engine,archive or single.
	 * @return void
	 */
	public function search_fields( $cmb, $section, $args ) {
		$this->set_facetwp_vars();
		$cmb->add_field(
			array(
				'id'          => 'settings_' . $section . '_search',
				'type'        => 'title',
				'name'        => $args['title'],
				'default'     => $args['title'],
				'description' => $args['desc'],
			)
		);
		do_action( 'lsx_search_settings_section', $cmb, 'top' );
		$cmb->add_field(
			array(
				'name'        => esc_html__( 'Enable Search Filters', 'lsx-search' ),
				'id'          => $section . '_search_enable',
				'description' => esc_html__( 'Display FacetWP filters on your search results page.', 'lsx-search' ),
				'type'        => 'checkbox',
			)
		);

		$cmb->add_field(
			array(
				'name'    => esc_html__( 'Page Layout', 'lsx-search' ),
				'id'      => $section . '_search_layout',
				'type'    => 'select',
				'options' => array(
					''    => esc_html__( 'Follow the theme layout', 'lsx-search' ),
					'2cr' => esc_html__( 'Sidebar on left', 'lsx-search' ),
					'2cl' => esc_html__( 'Sidebar on right', 'lsx-search' ),
				),
				'default' => '',
			)
		);

		if ( 'product' === $section ) {
			$cmb->add_field(
				array(
					'name'             => esc_html__( 'Results Layout', 'lsx-search' ),
					'id'               => $section . '_search_grid_list',
					'type'             => 'select',
					'show_option_none' => false,
					'description'      => __( 'Set a default layout for the search results.', 'lsx-search' ),
					'options'          => array(
						'grid' => esc_html__( 'Grid', 'lsx-search' ),
						'list' => esc_html__( 'List', 'lsx-search' ),
					),
					'default' => 'grid',
				)
			);
			$cmb->add_field(
				array(
					'name'        => esc_html__( 'Layout Switcher', 'lsx-search' ),
					'id'          => $section . '_search_layout_switcher_enable',
					'type'        => 'checkbox',
					'description' => __( 'Display the layout switcher to allow the user to toggle between the list and grid layouts.', 'lsx-search' ),
				)
			);
		}
		if ( 'engine' === $section && function_exists('is_plugin_active') && is_plugin_active( 'tour-operator/tour-operator.php' ) ) {
			$cmb->add_field(
				array(
					'name'    => esc_html__( 'List layout images', 'lsx-search' ),
					'id'      => $section . '_search_list_layout_image_style',
					'type'    => 'select',
					'options' => array(
						''           => esc_html__( 'Full Height', 'lsx-search' ),
						'max-height' => esc_html__( 'Max Height', 'lsx-search' ),
					),
					'default' => '',
				)
			);
		}
		if ( 'engine' === $section ) {
			$cmb->add_field(
				array(
					'name'        => esc_html__( 'Display Excerpt', 'lsx-search' ),
					'id'          => $section . '_excerpt_enable',
					'type'        => 'checkbox',
					'description' => __( 'Display the excerpt of a listing.', 'lsx-search' ),
				)
			);
			$cmb->add_field(
				array(
					'name'        => esc_html__( 'Enable Post Type Label', 'lsx-search' ),
					'id'          => $section . '_search_enable_pt_label',
					'type'        => 'checkbox',
					'description' => __( 'This enables the post type label from entries on search results page.', 'lsx-search' ),
				)
			);
			if ( function_exists('is_plugin_active') &&  is_plugin_active( 'tour-operator/tour-operator.php' ) ) {
				$cmb->add_field(
					array(
						'name'        => esc_html__( 'Enable Continent Filter', 'lsx-search' ),
						'id'          => $section . '_search_enable_continent_filter',
						'type'        => 'checkbox',
						'description' => __( 'This enables the continent filter in FacetWP destinations filter.', 'lsx-search' ),
					)
				);
				$cmb->add_field(
					array(
						'name'        => esc_html__( 'Enable Continental Regions', 'lsx-search' ),
						'id'          => $section . '_search_enable_continental_regions',
						'type'        => 'checkbox',
						'description' => __( 'This disable continents and enabled the sub regions.', 'lsx-search' ),
					)
				);
			}
		}

		if ( function_exists('is_plugin_active') && is_plugin_active( 'tour-operator/tour-operator.php' ) && 'accommodation' === $section ) {
			$cmb->add_field(
				array(
					'name'    => esc_html__( 'Results Layout - list vs map', 'lsx-search' ),
					'id'      => $section . '_search_results_layout',
					'type'    => 'select',
					'options' => array(
						'list_map'    => esc_html__( 'List and Map', 'lsx-search' ),
						'list'        => esc_html__( 'List only', 'lsx-search' ),
					),
					'default' => '',
				)
			);
		}

		$cmb->add_field(
			array(
				'name'        => esc_html__( 'Enable Collapse', 'lsx-search' ),
				'id'          => $section . '_search_collapse',
				'type'        => 'checkbox',
				'description' => __( 'Enable collapsible filters on search results.', 'lsx-search' ),
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Disable Sorting', 'lsx-search' ),
				'id'   => $section . '_search_disable_sorting',
				'type' => 'checkbox',
				'description' => __( 'Toggle the sorting drop down menu on your search results.', 'lsx-search' ),
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Disable the Date Sorting Option', 'lsx-search' ),
				'id'   => $section . '_search_disable_date',
				'type' => 'checkbox',
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Display Clear Button', 'lsx-search' ),
				'id'   => $section . '_search_display_clear_button',
				'type' => 'checkbox',
				'description' => __( 'Check this to turn on a button that will clear your search results.', 'lsx-search' ),
			)
		);

		$cmb->add_field(
			array(
				'name' => esc_html__( 'Display Result Count', 'lsx-search' ),
				'id'   => $section . '_search_display_result_count',
				'type' => 'checkbox',
			)
		);
		if ( function_exists('is_plugin_active') && is_plugin_active( 'facetwp-alpha/index.php' ) ) {
			$cmb->add_field(
				array(
					'name'        => esc_html__( 'Alphabet Facet', 'lsx-search' ),
					'description' => esc_html__( 'Select the alphabetical sorter facet.', 'lsx-search' ),
					'id'          => $section . '_search_az_pagination',
					'type'        => 'select',
					'options'     => $this->az_facets,
				)
			);
		}
		$cmb->add_field(
			array(
				'name'        => esc_html__( 'Facets', 'lsx-search' ),
				'description' => esc_html__( 'Choose the filters to display in the sidebar. Edit FacetWP filters to change individual filters.', 'lsx-search' ),
				'id'          => $section . '_search_facets',
				'type'        => 'multicheck',
				'options'     => $this->facet_data,
			)
		);
		do_action( 'lsx_search_settings_section', $cmb, 'bottom' );
		$cmb->add_field(
			array(
				'id'   => 'settings_' . $section . '_search_closing',
				'type' => 'tab_closing',
			)
		);
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
