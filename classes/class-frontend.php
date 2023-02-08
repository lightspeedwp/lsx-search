<?php
/**
 * LSX Search Frontend Class.
 *
 * @package lsx-search
 */

namespace lsx\search\classes;

class Frontend {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx\search\classes\Frontend()
	 */
	protected static $instance = null;

	public $options = false;

	public $tabs = false;

	public $facet_data = false;

	/**
	 * Determine weather or not search is enabled for this page.
	 *
	 * @var boolean
	 */
	public $search_enabled = false;

	public $search_core_suffix = false;

	public $search_prefix = false;

	/**
	 * Holds the post types enabled
	 *
	 * @var array
	 */
	public $post_types = array();

	/**
	 * Holds the taxonomies enabled for search
	 *
	 * @var array
	 */
	public $taxonomies = array();

	/**
	 * If the current search page has posts or not
	 *
	 * @var boolean
	 */
	public $has_posts = false;

	/**
	 * If we are using the CMB2 options or not.
	 *
	 * @var boolean
	 */
	public $new_options = false;

	/**
	 * Construct method.
	 */
	public function __construct() {
		$this->options = \lsx\search\includes\get_options();
		$this->load_classes();

		add_filter( 'wpseo_json_ld_search_url', array( $this, 'change_json_ld_search_url' ), 10, 1 );
		add_action( 'wp', array( $this, 'set_vars' ), 21 );
		add_action( 'wp', array( $this, 'set_facetwp_vars' ), 22 );
		add_action( 'wp', array( $this, 'core' ), 23 );
		add_action( 'lsx_body_top', array( $this, 'check_for_results' ) );

		add_filter( 'pre_get_posts', array( $this, 'ignore_sticky_search' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_post_types' ) );

		add_filter( 'lsx_search_post_types', array( $this, 'register_post_types' ) );
		add_filter( 'lsx_search_taxonomies', array( $this, 'register_taxonomies' ) );
		add_filter( 'lsx_search_post_types_plural', array( $this, 'register_post_type_tabs' ) );
		add_filter( 'facetwp_sort_options', array( $this, 'facetwp_sort_options' ), 10, 2 );
		add_filter( 'wp_kses_allowed_html', array( $this, 'kses_allowed_html' ), 20, 2 );
		add_filter( 'get_search_query', array( $this, 'get_search_query' ) );

		// Redirects.
		add_action( 'template_redirect', array( $this, 'pretty_search_redirect' ) );
		add_filter( 'pre_get_posts', array( $this, 'pretty_search_parse_query' ) );

		add_action( 'lsx_search_sidebar_top', array( $this, 'search_sidebar_top' ) );
		add_filter( 'facetwp_facet_html', array( $this, 'search_facet_html' ), 10, 2 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\member_directory\search\Frontend()    A single instance of this class.
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
		require_once LSX_SEARCH_PATH . 'classes/frontend/class-layout.php';
		$this->layout = frontend\Layout::get_instance();
	}

	/**
	 * Check all settings.
	 */
	public function set_vars() {
		$post_type = '';

		$this->post_types      = apply_filters( 'lsx_search_post_types', array( 'tour', 'accommodation' ) );
		$this->taxonomies      = apply_filters( 'lsx_search_taxonomies', array() );
		$this->tabs            = apply_filters( 'lsx_search_post_types_plural', array() );
		$this->options         = apply_filters( 'lsx_search_options', $this->options );
		$this->post_types      = get_post_types();
		$this->post_type_slugs = array(
			'post'        => 'posts',
			'project'     => 'projects',
			'service'     => 'services',
			'team'        => 'team',
			'testimonial' => 'testimonials',
			'video'       => 'videos',
			'product'     => 'products',
		);
		$this->set_search_prefix();
		$this->get_cmb2_options();
		$this->search_enabled = apply_filters( 'lsx_search_enabled', $this->is_search_enabled(), $this );
		$this->search_prefix  = apply_filters( 'lsx_search_prefix', $this->search_prefix, $this );
	}

	private function get_cmb2_options() {
		$cmb2_options = get_option( 'lsx-search-settings' );
		if ( false !== $cmb2_options && ! empty( $cmb2_options ) ) {
			$this->set_search_prefix( true );
			$this->options['display'] = $cmb2_options;
			foreach ( $this->options['display'] as $option_key => $option_value ) {
				if ( is_array( $option_value ) && ! empty( $option_value ) ) {
					$new_values = array();
					foreach ( $option_value as $empty_key => $key_value ) {
						$new_values[ $key_value ] = 'on';
					}
					$this->options['display'][ $option_key ] = $new_values;
				}
			}
			$this->new_options = true;
			$this->disable_to_search_actions();
		}
	}

	private function disable_to_search_actions() {
		global $lsx_to_search_fwp, $lsx_to_search;
		if ( null !== $lsx_to_search ) {
			// Redirects.
			remove_filter( 'template_include', array( $lsx_to_search, 'search_template_include' ), 99 );
			remove_action( 'template_redirect', array( $lsx_to_search, 'pretty_search_redirect' ) );
			remove_filter( 'pre_get_posts', array( $lsx_to_search, 'pretty_search_parse_query' ) );

			// Layout Filter.
			remove_filter( 'lsx_layout', array( $lsx_to_search, 'lsx_layout' ), 20, 1 );
			remove_filter( 'lsx_layout_selector', array( $lsx_to_search, 'lsx_layout_selector' ), 10, 4 );
			remove_filter( 'lsx_to_archive_layout', array( $lsx_to_search, 'lsx_to_search_archive_layout' ), 10, 2 );

			remove_action( 'lsx_search_sidebar_top', array( $lsx_to_search, 'search_sidebar_top' ) );
			remove_action( 'pre_get_posts', array( $lsx_to_search, 'price_sorting' ), 100 );

			//add_shortcode( 'lsx_search_form', array( 'LSX_TO_Search_Frontend', 'search_form' ) );
			remove_filter( 'searchwp_short_circuit', array( $lsx_to_search, 'searchwp_short_circuit' ), 10, 2 );
			remove_filter( 'get_search_query', array( $lsx_to_search, 'get_search_query' ) );
			remove_filter( 'body_class', array( $lsx_to_search, 'to_add_search_url_class' ), 20 );

			remove_filter( 'facetwp_preload_url_vars', array( $lsx_to_search, 'preload_url_vars' ), 10, 1 );
			remove_filter( 'wpseo_json_ld_search_url', array( $lsx_to_search, 'change_json_ld_search_url' ), 10, 1 );
		}
		if ( null !== $lsx_to_search_fwp ) {
			remove_filter( 'facetwp_indexer_row_data', array( $lsx_to_search_fwp, 'facetwp_index_row_data' ), 10, 2 );
			remove_filter( 'facetwp_index_row', array( $lsx_to_search_fwp, 'facetwp_index_row' ), 10, 2 );

			remove_filter( 'facetwp_sort_options', array( $lsx_to_search_fwp, 'facet_sort_options' ), 10, 2 );

			remove_filter( 'facetwp_pager_html', array( $lsx_to_search_fwp, 'facetwp_pager_html' ), 10, 2 );
			remove_filter( 'facetwp_result_count', array( $lsx_to_search_fwp, 'facetwp_result_count' ), 10, 2 );

			remove_filter( 'facetwp_facet_html', array( $lsx_to_search_fwp, 'destination_facet_html' ), 10, 2 );
			remove_filter( 'facetwp_facet_html', array( $lsx_to_search_fwp, 'slide_facet_html' ), 10, 2 );
			remove_filter( 'facetwp_facet_html', array( $lsx_to_search_fwp, 'search_facet_html' ), 10, 2 );
			remove_filter( 'facetwp_load_css', array( $lsx_to_search_fwp, 'facetwp_load_css' ), 10, 1 );

			if ( class_exists( 'LSX_Currencies' ) ) {
				remove_filter( 'facetwp_render_output', array( $lsx_to_search_fwp, 'slide_price_lsx_currencies' ), 10, 2 );
			} else {
				remove_filter( 'facetwp_render_output', array( $lsx_to_search_fwp, 'slide_price_to_currencies' ), 10, 2 );
			}
		}
	}

	/**
	 * Returns if the search is enabled.
	 *
	 * @return boolean
	 */
	public function is_search_enabled() {
		$search_enabled = false;

		if ( false === $this->new_options ) {
			if ( isset( $this->options['display'][ $this->search_prefix . '_enable_' . $this->search_core_suffix ] ) && ( ! empty( $this->options ) ) && 'on' == $this->options['display'][ $this->search_prefix . '_enable_' . $this->search_core_suffix ] ) {
				$search_enabled = true;
			}
		} else {
			$enable_prefix = $this->search_prefix;
			if ( ! empty( $this->options ) && isset( $this->options['display'] ) && isset( $this->options['display'][ $enable_prefix . '_enable' ] ) && 'on' === $this->options['display'][ $enable_prefix . '_enable' ] ) {
				$search_enabled = true;
			}
		}

		// These are specific plugin exclusions.
		if ( is_tax( array( 'wcpv_product_vendors' ) ) ) {
			$search_enabled = false;
		}
		return $search_enabled;
	}

	/**
	 * Sets the search prefix.
	 *
	 * @return void
	 */
	private function set_search_prefix( $new_prefixes = false ) {
		$page_for_posts = get_option( 'page_for_posts' );
		if ( false !== $new_prefixes ) {
			$this->taxonomies = array();
			$this->post_types = array();
		}

		if ( is_search() ) {
			if ( false === $new_prefixes ) {
				$this->search_core_suffix = 'core';
				$this->search_prefix      = 'search';
			} else {
				$this->search_core_suffix = 'enable';
				$this->search_prefix      = 'engine_search';
			}

			$engine = get_query_var( 'engine' );
			if ( '' !== $engine && false !== $engine && 'default' !== $engine ) {
				$post_type = get_query_var( 'post_type' );
				if ( is_array( $post_type ) ) {
					$post_type = $post_type[0];
				}

				$this->search_prefix = $post_type . '_search';
			}

		} elseif ( is_post_type_archive( $this->post_types ) || is_tax() || is_page( $page_for_posts ) || is_home() || is_category() || is_tag() ) {
			if ( false === $new_prefixes ) {
				$this->search_core_suffix = 'search';
			} else {
				$this->search_core_suffix = 'enable';
			}

			if ( is_tax() ) {
				$tax = get_query_var( 'taxonomy' );
				$tax = get_taxonomy( $tax );
				if ( isset( $tax->object_type[1] ) ) {
					$post_type = $tax->object_type[1];
				} else {
					$post_type = $tax->object_type[0];
				}
			} else if ( is_page( $page_for_posts ) || is_category() || is_tag() || is_home() ) {
				$post_type = 'post';
			} else {
				$post_type = get_query_var( 'post_type' );
			}

			if ( false === $new_prefixes ) {
				if ( isset( $this->tabs[ $post_type ] ) ) {
					$this->search_prefix = $this->tabs[ $post_type ] . '_archive';
				}
			} else {
				$this->search_prefix = $post_type . '_search';
			}
		}
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
	 * Check all settings.
	 */
	public function core() {

		if ( true === $this->search_enabled ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 999 );

			add_filter( 'lsx_layout', array( $this, 'lsx_layout' ), 20, 1 );
			add_filter( 'lsx_layout_selector', array( $this, 'lsx_layout_selector' ), 10, 4 );
			add_filter( 'lsx_slot_class', array( $this, 'change_slot_column_class' ) );
			add_action( 'lsx_entry_top', array( $this, 'add_label_to_title' ) );
			add_filter( 'body_class', array( $this, 'body_class' ), 10 );

			add_filter( 'lsx_blog_customizer_top_of_blog_action', array( $this, 'top_of_blog_action' ), 10, 1 );
			add_filter( 'lsx_blog_customizer_blog_description_class', array( $this, 'blog_description_class' ), 10, 1 );

			if ( class_exists( 'LSX_Videos' ) ) {
				global $lsx_videos_frontend;
				remove_action( 'lsx_content_top', array( $lsx_videos_frontend, 'categories_tabs' ), 15 );
			}

			add_filter( 'lsx_paging_nav_disable', '__return_true' );
			add_action( 'lsx_content_top', array( $this, 'facet_top_bar' ) );
			add_action( 'lsx_content_top', array( $this, 'facetwp_tempate_open' ) );
			add_action( 'lsx_content_bottom', array( $this, 'facetwp_tempate_close' ) );
			add_action( 'lsx_content_bottom', array( $this, 'facet_bottom_bar' ) );

			if ( ! empty( $this->options['display'][ $this->search_prefix . '_layout' ] ) && '1c' !== $this->options['display'][ $this->search_prefix . '_layout' ] ) {
				add_filter( 'lsx_sidebar_enable', array( $this, 'lsx_sidebar_enable' ), 10, 1 );
			}

			add_action( 'lsx_content_wrap_before', array( $this, 'search_sidebar' ), 150 );

			if ( class_exists( 'WooCommerce' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) {
				remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description' );
				remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description' );
				add_filter( 'woocommerce_show_page_title', '__return_false' );

				add_filter( 'loop_shop_columns', function() {
					return 3;
				} );

				// Actions added by LSX theme
				remove_action( 'lsx_content_wrap_before', 'lsx_global_header' );

				// Actions added be LSX theme / woocommerce.php file
				remove_action( 'woocommerce_after_shop_loop', 'lsx_wc_sorting_wrapper', 9 );
				remove_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10 );
				remove_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 20 );
				remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 30 );
				remove_action( 'woocommerce_after_shop_loop', 'lsx_wc_sorting_wrapper_close', 31 );

				// Actions added be LSX theme / woocommerce.php file
				remove_action( 'woocommerce_before_shop_loop', 'lsx_wc_sorting_wrapper', 9 );
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
				remove_action( 'woocommerce_before_shop_loop', 'lsx_wc_woocommerce_pagination', 30 );
				remove_action( 'woocommerce_before_shop_loop', 'lsx_wc_sorting_wrapper_close', 31 );
			}
		}
	}

	/**
	 * Adds a search class to the body to allow the styling of the sidebars etc.
	 *
	 * @param  array $classes The classes.
	 * @return array $classes The classes.
	 */
	public function body_class( $classes ) {
		$classes[] = 'lsx-search-enabled';
		return $classes;
	}

	/**
	 * Moves the blog description to above the content columns.
	 *
	 * @param  string $action
	 * @return string $action
	 */
	public function top_of_blog_action( $action = '' ) {
		$action = 'lsx_content_wrap_before';
		return $action;
	}

	/**
	 * Adds a class to the blog description.
	 *
	 * @param  string $action
	 * @return string $action
	 */
	public function blog_description_class( $class = '' ) {
		$class .= ' col-md-12 search-description';
		return $class;
	}

	/**
	 * Check the $wp_query global to see if there are posts in the current query.
	 *
	 * @return void
	 */
	public function check_for_results() {
		if ( true === $this->search_enabled ) {
			global $wp_query;
			if ( empty( $wp_query->posts ) ) {
				$this->has_posts = false;
				remove_action( 'lsx_content_top', array( $this, 'facet_top_bar' ) );
				remove_action( 'lsx_content_bottom', array( $this, 'facet_bottom_bar' ) );
				remove_action( 'lsx_content_wrap_before', array( $this, 'search_sidebar' ), 150 );
			} else {
				$this->has_posts = true;
			}
		}
	}

	/**
	 * Filter the post types.
	 */
	public function filter_post_types( $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
			if ( ! empty( $this->options ) && ! empty( $this->options['display']['search_enable_core'] ) ) {
				if ( ! empty( $this->options['general']['search_post_types'] ) && is_array( $this->options['general']['search_post_types'] ) ) {
					$post_types = array_keys( $this->options['general']['search_post_types'] );
					$query->set( 'post_type', $post_types );
				}
			}
		}
	}

	/**
	 * Sets post types with active search options.
	 */
	public function register_post_types( $post_types ) {
		$post_types = array( 'post', 'project', 'service', 'team', 'testimonial', 'video', 'product' );
		return $post_types;
	}

	/**
	 * Sets taxonomies with active search options.
	 */
	public function register_taxonomies( $taxonomies ) {
		$taxonomies = array( 'category', 'post_tag', 'project-group', 'service-group', 'team_role', 'video-category', 'product_cat', 'product_tag' );
		return $taxonomies;
	}

	/**
	 * Sets post types with active search options.
	 */
	public function register_post_type_tabs( $post_types_plural ) {
		$post_types_plural = array(
			'post' => 'posts',
			'project' => 'projects',
			'service' => 'services',
			'team' => 'team',
			'testimonial' => 'testimonials',
			'video' => 'videos',
			'product' => 'products', // WooCommerce
		);
		return $post_types_plural;
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function assets() {
		add_filter( 'lsx_defer_parsing_of_js', array( $this, 'skip_js_defer' ), 10, 4 );
		wp_enqueue_script( 'touchSwipe', LSX_SEARCH_URL . 'assets/js/vendor/jquery.touchSwipe.min.js', array( 'jquery' ), LSX_SEARCH_VER, true );
		wp_enqueue_script( 'slideandswipe', LSX_SEARCH_URL . 'assets/js/vendor/jquery.slideandswipe.min.js', array( 'jquery', 'touchSwipe' ), LSX_SEARCH_VER, true );
		wp_enqueue_script( 'lsx-search', LSX_SEARCH_URL . 'assets/js/src/lsx-search.js', array( 'jquery', 'touchSwipe', 'slideandswipe', 'jquery-ui-datepicker' ), LSX_SEARCH_VER, true );

		$params = apply_filters( 'lsx_search_js_params', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		));

		wp_localize_script( 'lsx-search', 'lsx_customizer_params', $params );

		wp_enqueue_style( 'lsx-search', LSX_SEARCH_URL . 'assets/css/lsx-search.css', array(), LSX_SEARCH_VER );
		wp_style_add_data( 'lsx-search', 'rtl', 'replace' );

		if ( true === $this->new_options ) {
			wp_deregister_style( 'lsx_to_search' );
			wp_deregister_script( 'lsx_to_search' );
		}
	}

	/**
	 * Adds the to-search.min.js and the to-search.js
	 *
	 * @param boolean $should_skip
	 * @param string  $tag
	 * @param string  $handle
	 * @param string  $href
	 * @return boolean
	 */
	public function skip_js_defer( $should_skip, $tag, $handle, $href ) {
		if ( ! is_admin() && ( false !== stripos( $href, 'lsx-search.min.js' ) || false !== stripos( $href, 'lsx-search.js' ) ) ) {
			$should_skip = true;
		}
		return $should_skip;
	}

	/**
	 * Redirect wordpress to the search template located in the plugin
	 *
	 * @param	$template
	 * @return	$template
	 */
	public function search_template_include( $template ) {
		if ( is_main_query() && is_search() ) {
			if ( file_exists( LSX_SEARCH_PATH . 'templates/search.php' ) ) {
				$template = LSX_SEARCH_PATH . 'templates/search.php';
			}
		}

		return $template;
	}

	/**
	 * Ignore sticky posts on Blog search.
	 *
	 * @param [type] $query
	 * @return void
	 */
	public function ignore_sticky_search( $query ) {
		if ( $query->is_main_query() && is_home() ) {
			$query->set( 'ignore_sticky_posts', true );
		}
	}

	/**
	 * Rewrite the search URL
	 */
	public function pretty_search_redirect() {
		global $wp_rewrite,$wp_query;

		if ( ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->using_permalinks() ) {
			return;
		}

		$search_base = $wp_rewrite->search_base;

		if ( is_search() && ! is_admin() && strpos( $_SERVER['REQUEST_URI'], "/{$search_base}/" ) === false ) {
			$search_query = get_query_var( 's' );
			if ( empty( $search_query ) && isset( $_GET['s'] ) ) {
				$search_query = $_GET['s'];
			}
			$engine = '';

			// If the search was triggered by a supplemental engine.
			if ( isset( $_GET['engine'] ) && 'default' !== $_GET['engine'] ) {
				$engine = sanitize_text_field( wp_unslash( $_GET['engine'] ) );
				$index  = array_search( $engine, $this->post_type_slugs, true );
				if ( false !== $index ) {
					$engine = $index;
				}
				$engine = $engine . '/';
			}

			$get_array = $_GET;

			if ( is_array( $get_array ) && ! empty( $get_array ) ) {
				$vars_to_maintain = array();

				foreach ( $get_array as $ga_key => $ga_value ) {
					if ( false !== strpos( $ga_key, 'fwp_' ) ) {
						$vars_to_maintain[] = $ga_key . '=' . $ga_value;
					}
				}
			}

			$redirect_url = home_url( "/{$search_base}/" . $engine . urlencode( $search_query ) );

			if ( ! empty( $vars_to_maintain ) ) {
				$redirect_url .= '?' . implode( '&', $vars_to_maintain );
			}
			wp_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Parse the Query and trigger a search
	 */
	public function pretty_search_parse_query( $query ) {
		$this->post_type_slugs = array(
			'post' => 'posts',
			'project' => 'projects',
			'service' => 'services',
			'team' => 'team',
			'testimonial' => 'testimonials',
			'video' => 'videos',
			'product' => 'products', // WooCommerce
			'tour' => 'tours',
			'accommodation' => 'accommodation',
			'destination' => 'destinations',
		);
		if ( $query->is_search() && ! is_admin() && $query->is_main_query() ) {
			$search_query = $query->get( 's' );
			$keyword_test = explode( '/', $search_query );

			$index = array_search( $keyword_test[0], $this->post_type_slugs, true );
			
			if ( false !== $index ) {
				$engine = $this->post_type_slugs[ $index ];

				$query->set( 'post_type', $index );
				$query->set( 'engine', $engine );
				$query->set( 'searchwp', $engine );

				if ( count( $keyword_test ) > 1 ) {
					$query->set( 's', $keyword_test[1] );
				} elseif ( post_type_exists( $index ) ) {
					$query->set( 's', '' );
				}

			} else {
				if ( isset( $this->options['general']['search_post_types'] ) && is_array( $this->options['general']['search_post_types'] ) ) {
					$post_types = array_keys( $this->options['general']['search_post_types'] );
					$query->set( 'post_type', $post_types );
				}
			}
		}
		return $query;
	}

	/**
	 * Change the search slug to /search/ for the JSON+LD output in Yoast SEO
	 *
	 * @return url
	 */
	public function change_json_ld_search_url() {
		return trailingslashit( home_url() ) . 'search/{search_term_string}';
	}

	/**
	 * A filter to set the layout to 2 column.
	 */
	public function lsx_layout( $layout ) {
		if ( ! empty( $this->options['display'][ $this->search_prefix . '_layout' ] ) ) {
			if ( false === $this->has_posts ) {
				$layout = '1c';
			} else {
				$layout = $this->options['display'][ $this->search_prefix . '_layout' ];
			}
		}
		return $layout;
	}

	/**
	 * Outputs the Search Title Facet
	 */
	public function search_sidebar_top() {
		if ( ! empty( $this->options['display'][ $this->search_prefix . '_facets' ] ) && is_array( $this->options['display'][ $this->search_prefix . '_facets' ] ) && true !== apply_filters( 'lsx_search_hide_search_box', false ) ) {

			if ( ! is_search() ) {

				foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {

					if ( isset( $this->facet_data[ $facet ] ) && 'search' === $this->facet_data[ $facet ]['type'] ) {
						echo wp_kses_post( '<div class="row">' );
							$this->display_facet_default( $facet );
						echo wp_kses_post( '</div>' );
						unset( $this->options['display'][ $this->search_prefix . '_facets' ][ $facet ] );
					}
				}
			} else {
				echo wp_kses_post( '<div class="row">' );
					$this->display_facet_search();
				echo wp_kses_post( '</div>' );
			}
		}
	}

	/**
	 * Overrides the search facet HTML
	 * @param $output
	 * @param $params
	 *
	 * @return string
	 */
	public function search_facet_html( $output, $params ) {
		if ( 'search' == $params['facet']['type'] ) {

			$value = (array) $params['selected_values'];
			$value = empty( $value ) ? '' : stripslashes( $value[0] );
			$placeholder = isset( $params['facet']['placeholder'] ) ? $params['facet']['placeholder'] : __( 'Search...', 'lsx-search' );
			$placeholder = facetwp_i18n( $placeholder );

			ob_start();
			?>
			<div class="col-xs-12 facetwp-item facetwp-form">
				<div class="search-form lsx-search-form 2">
					<div class="input-group facetwp-search-wrap">
						<div class="field">
							<input class="facetwp-search search-field form-control" type="text" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off" value="<?php echo esc_attr( $value ); ?>">
						</div>

						<div class="field submit-button">
							<button class="search-submit btn facetwp-btn" type="submit"><?php esc_html_e( 'Search', 'lsx-search' ); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php
			$output = ob_get_clean();
		}
		return $output;
	}

	/**
	 * Change the primary and secondary column classes.
	 */
	public function lsx_layout_selector( $return_class, $class, $layout, $size ) {
		if ( ! empty( $this->options['display'][ $this->search_prefix . '_layout' ] ) ) {

			if ( '2cl' === $layout || '2cr' === $layout ) {
				$main_class    = 'col-sm-8 col-md-9';
				$sidebar_class = 'col-sm-4 col-md-3';

				if ( '2cl' === $layout ) {
					$main_class    .= ' col-sm-pull-4 col-md-pull-3 search-sidebar-left';
					$sidebar_class .= ' col-sm-push-8 col-md-push-9';
				}

				if ( 'main' === $class ) {
					return $main_class;
				}

				if ( 'sidebar' === $class ) {
					return $sidebar_class;
				}
			}
		}

		return $return_class;
	}

	/**
	 * Displays the Alphabet sorter above the facets.
	 *
	 * @return void
	 */
	public function display_alphabet_facet() {
		if ( isset( $this->options['display'][ $this->search_prefix . '_az_pagination' ] ) ) {
			$az_pagination = $this->options['display'][ $this->search_prefix . '_az_pagination' ];
		} else {
			$az_pagination = false;
		}
		$az_pagination = apply_filters( 'lsx_search_top_az_pagination', $az_pagination );
		if ( false !== $az_pagination && '' !== $az_pagination ) {
			echo do_shortcode( '[facetwp facet="' . $az_pagination . '"]' );
		}
	}

	/**
	 * Outputs top.
	 */
	public function facet_top_bar() {
		if ( true === apply_filters( 'lsx_search_hide_top_bar', false ) ) {
			return;
		}

		$show_pagination     = true;
		$pagination_visible  = false;
		$show_per_page_combo = empty( $this->options['display'][ $this->search_prefix . '_disable_per_page' ] );
		$show_sort_combo     = empty( $this->options['display'][ $this->search_prefix . '_disable_sorting' ] );

		$show_pagination     = apply_filters( 'lsx_search_top_show_pagination', $show_pagination );
		$pagination_visible  = apply_filters( 'lsx_search_top_pagination_visible', $pagination_visible );
		$show_per_page_combo = apply_filters( 'lsx_search_top_show_per_page_combo', $show_per_page_combo );
		$show_sort_combo     = apply_filters( 'lsx_search_top_show_sort_combo', $show_sort_combo );
		$facet_row_classes   = apply_filters( 'lsx_search_top_facetwp_row_classes', '' );
		?>
		<div id="facetwp-top">
			<?php if ( $show_sort_combo || ( $show_pagination && $show_per_page_combo ) ) { ?>
				<div class="row facetwp-top-row-1 hidden-xs <?php echo esc_attr( $facet_row_classes ); ?>">
					<div class="col-xs-12">

						<?php if ( ! empty( $this->options['display'][ $this->search_prefix . '_display_result_count' ] ) && false === apply_filters( 'lsx_search_hide_result_count', false ) ) { ?>
							<div class="row">
								<div class="col-md-12 facetwp-item facetwp-results">
									<h3 class="lsx-search-title lsx-search-title-results"><?php esc_html_e( 'Results', 'lsx-search' ); ?> <?php echo '(' . do_shortcode( '[facetwp counts="true"]' ) . ')&nbsp;'; ?>
									<?php if ( false !== $this->options && isset( $this->options['display'] ) && ( ! empty( $this->options['display'][ $this->search_prefix . '_display_clear_button' ] ) ) && 'on' === $this->options['display'][ $this->search_prefix . '_display_clear_button' ] ) { ?>
										<span class="clear-facets hidden">- <a title="<?php esc_html_e( 'Clear the current search filters.', 'lsx-search' ); ?>" class="facetwp-results-clear" type="button" onclick="<?php echo esc_attr( apply_filters( 'lsx_search_clear_function', 'lsx_search.clearFacets(this);' ) ); ?>"><?php esc_html_e( 'Clear', 'lsx-search' ); ?></a></span>
									<?php } ?>
									</h3>
								</div>
							</div>
						<?php } ?>

						<?php do_action( 'lsx_search_facetwp_top_row' ); ?>

						<?php $this->display_alphabet_facet(); ?>

						<?php
						if ( $show_sort_combo ) { 
							$new_sorter = $this->has_facet( 'sort' );
							if ( false !== $new_sorter ) {
								echo do_shortcode( '[facetwp facet="' . $new_sorter . '"]' );
							} else {
								echo do_shortcode( '[facetwp sort="true"]' );
							}	
						}
						?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	public function has_facet( $type ) {
		$has_facet = false;
		if ( ! empty( $this->options['display'][ $this->search_prefix . '_facets' ] ) && is_array( $this->options['display'][ $this->search_prefix . '_facets' ] ) ) {
			foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {
				if ( isset( $this->facet_data[ $facet ] ) &&  $this->facet_data[ $facet ]['type'] === $type ) {
					$has_facet = $facet;
				}
			}
		}
		return $has_facet;
	}

	/**
	 * Outputs bottom.
	 */
	public function facet_bottom_bar() {
		if ( true === apply_filters( 'lsx_search_hide_bottom_bar', false ) ) {
			return;
		}
		$show_pagination    = true;
		$pagination_visible = false;
		if ( isset( $this->options['display'][ $this->search_prefix . '_az_pagination' ] ) ) {
			$az_pagination = $this->options['display'][ $this->search_prefix . '_az_pagination' ];
		} else {
			$az_pagination = false;
		}

		$show_per_page_combo = empty( $this->options['display'][ $this->search_prefix . '_disable_per_page' ] );
		$show_sort_combo     = empty( $this->options['display'][ $this->search_prefix . '_search_disable_sorting' ] );

		$show_pagination     = apply_filters( 'lsx_search_bottom_show_pagination', $show_pagination );
		$pagination_visible  = apply_filters( 'lsx_search_bottom_pagination_visible', $pagination_visible );
		$show_per_page_combo = apply_filters( 'lsx_search_bottom_show_per_page_combo', $show_per_page_combo );
		$show_sort_combo     = apply_filters( 'lsx_search_bottom_show_sort_combo', $show_sort_combo );

		if ( $show_pagination || ! empty( $az_pagination ) ) { ?>
			<div id="facetwp-bottom">
				<div class="row facetwp-bottom-row-1">
					<div class="col-xs-12">
						<?php do_action( 'lsx_search_facetwp_bottom_row' ); ?>

						<?php //if ( $show_sort_combo ) { ?>
							<?php //echo do_shortcode( '[facetwp sort="true"]' ); ?>
						<?php //} ?>

						<?php //if ( ( $show_pagination && $show_per_page_combo ) || $show_per_page_combo ) { ?>
							<?php //echo do_shortcode( '[facetwp per_page="true"]' ); ?>
						<?php //} ?>

						<?php
						if ( $show_pagination ) {
							$output_pagination = do_shortcode( '[facetwp pager="true"]' );
							if ( ! empty( $this->options['display'][ $this->search_prefix . '_facets' ] ) && is_array( $this->options['display'][ $this->search_prefix . '_facets' ] ) ) {
								foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {
									if ( isset( $this->facet_data[ $facet ] ) && in_array( $this->facet_data[ $facet ]['type'], array( 'pager' ) ) ) {
										$output_pagination = do_shortcode( '[facetwp facet="pager_"]' );
									}
								}
							}
							echo wp_kses_post( $output_pagination );
						?>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php }
	}

	/**
	 * Adds in the closing facetwp div
	 *
	 * @return void
	 */
	public function facetwp_tempate_open() {
		?>
		<div class="facetwp-template">
		<?php
	}

	/**
	 * Adds in the closing facetwp div
	 *
	 * @return void
	 */
	public function facetwp_tempate_close() {
		?>
		</div>
		<?php
	}

	/**
	 * Disables default sidebar.
	 */
	public function lsx_sidebar_enable( $sidebar_enabled ) {
		$sidebar_enabled = false;
		return $sidebar_enabled;
	}

	/**
	 * Outputs custom sidebar.
	 */
	public function search_sidebar() {

		$this->options = apply_filters( 'lsx_search_sidebar_options', $this->options );
		?>
			<?php do_action( 'lsx_search_sidebar_before' ); ?>

			<div id="secondary" class="facetwp-sidebar widget-area <?php echo esc_attr( lsx_sidebar_class() ); ?>" role="complementary">

				<?php do_action( 'lsx_search_sidebar_top' ); ?>

				<?php if ( ! empty( $this->options['display'][ $this->search_prefix . '_facets' ] ) && is_array( $this->options['display'][ $this->search_prefix . '_facets' ] ) ) { ?>
					<div class="row facetwp-row lsx-search-filer-area">
						<h3 class="facetwp-filter-title"><?php echo esc_html_e( 'Refine by', 'lsx-search' ); ?></h3>
						<div class="col-xs-12 facetwp-item facetwp-filters-button hidden-sm hidden-md hidden-lg">
							<button class="ssm-toggle-nav btn btn-block" rel="lsx-search-filters"><?php esc_html_e( 'Filters', 'lsx-search' ); ?> <i class="fa fa-chevron-down" aria-hidden="true"></i></button>
						</div>

						<div class="ssm-overlay ssm-toggle-nav" rel="lsx-search-filters"></div>

						<div class="col-xs-12 facetwp-item-wrap facetwp-filters-wrap" id="lsx-search-filters">
							<div class="row hidden-sm hidden-md hidden-lg ssm-row-margin-bottom">
								<div class="col-xs-12 facetwp-item facetwp-filters-button">
									<button class="ssm-close-btn ssm-toggle-nav btn btn-block" rel="lsx-search-filters"><?php esc_html_e( 'Close Filters', 'lsx-search' ); ?> <i class="fa fa-times" aria-hidden="true"></i></button>
								</div>
							</div>

							<div class="row">
								<?php
								// Slider.
								foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {
									if ( isset( $this->facet_data[ $facet ] ) && ! in_array( $this->facet_data[ $facet ]['type'], array( 'alpha', 'search', 'pager', 'sort', 'autocomplete' ) ) ) {
										$this->display_facet_default( $facet );
									}
								}
								?>
							</div>

							<div class="row hidden-sm hidden-md hidden-lg ssm-row-margin-top">
								<div class="col-xs-12 facetwp-item facetwp-filters-button">
									<button class="ssm-apply-btn ssm-toggle-nav btn btn-block" rel="lsx-search-filters"><?php esc_html_e( 'Apply Filters', 'lsx-search' ); ?> <i class="fa fa-check" aria-hidden="true"></i></button>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>

				<?php do_action( 'lsx_search_sidebar_bottom' ); ?>
			</div>

			<?php do_action( 'lsx_search_sidebar_after' ); ?>
		<?php
	}

	/**
	 * Check if the pager facet is on
	 *
	 * @return void
	 */
	public function pager_facet_enabled() {

		$pager_facet_off = false;

		if ( ! empty( $this->options['display'][ $this->search_prefix . '_facets' ] ) && is_array( $this->options['display'][ $this->search_prefix . '_facets' ] ) ) {
			foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {
				if ( isset( $this->facet_data[ $facet ] ) && ! in_array( $this->facet_data[ $facet ]['type'], array( 'pager' ) ) ) {
					$pager_facet_off = true;
				}
			}
		}

		return $pager_facet_off;
	}

	/**
	 * Display facet search.
	 */
	public function display_facet_search() {
		?>
		<div class="col-xs-12 facetwp-item facetwp-form">
			<form class="search-form lsx-search-form" action="<?php echo esc_attr( home_url() ); ?>" method="get">
				<div class="input-group">
					<div class="field">
						<input class="facetwp-search search-field form-control" name="s" type="search" placeholder="<?php esc_html_e( 'Search', 'lsx-search' ); ?>..." autocomplete="off" value="<?php echo get_search_query() ?>">
					</div>

					<div class="field submit-button">
						<button class="search-submit btn" type="submit"><?php esc_html_e( 'Search', 'lsx-search' ); ?></button>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Display facet default.
	 */
	public function display_facet_default( $facet ) {

		$show_collapse = ! isset( $this->options['display'][$this->search_prefix . '_collapse'] ) || 'on' !== $this->options['display'][$this->search_prefix . '_collapse'];
		$col_class = '';

		if ( 'search' === $this->facet_data[ $facet ]['type'] ) : ?>
			<?php echo do_shortcode( '[facetwp facet="' . $facet . '"]' ); ?>
		<?php else : ?>
			<div class="col-xs-12 facetwp-item parent-facetwp-facet-<?php echo esc_html( $facet ); ?> <?php echo esc_attr( $col_class ); ?>">
				<?php if ( ! $show_collapse ) { ?>
					<div class="facetwp-collapsed">
						<h3 class="lsx-search-title"><?php echo wp_kses_post( $this->facet_data[ $facet ]['label'] ); ?></h3>
						<button title="<?php echo esc_html_e( 'Click to Expand', 'lsx-search' ); ?>" class="facetwp-collapse" type="button" data-toggle="collapse" data-target="#collapse-<?php echo esc_html( $facet ); ?>" aria-expanded="false" aria-controls="collapse-<?php echo esc_html( $facet ); ?>"></button>
					</div>
					<div id="collapse-<?php echo esc_html( $facet ); ?>" class="collapse">
						<?php echo do_shortcode( '[facetwp facet="' . $facet . '"]' ); ?>
					</div>
				<?php } else { ?>
					<h3 class="lsx-search-title"><?php echo wp_kses_post( $this->facet_data[ $facet ]['label'] ); ?></h3>
					<?php echo do_shortcode( '[facetwp facet="' . $facet . '"]' ); ?>
				<?php } ?>
			</div>
		<?php
		endif;
	}

	/**
	 * Changes slot column class.
	 */
	public function change_slot_column_class( $class ) {
		if ( is_post_type_archive( 'video' ) || is_tax( 'video-category' ) ) {
			$column_class = 'col-xs-12 col-sm-4';
		}

		return $column_class;
	}

	/**
	 * Add post type label to the title.
	 */
	public function add_label_to_title() {
		if ( is_search() ) {
			if ( ! empty( $this->options['display']['engine_search_enable_pt_label'] ) ) {
				$post_type = get_post_type();
				$post_type = str_replace( '_', ' ', $post_type );
				$post_type = str_replace( '-', ' ', $post_type );
				if ( 'tribe events' === $post_type ) {
					$post_type = 'Events';
				}
				echo wp_kses_post( ' <span class="label label-default lsx-label-post-type">' . $post_type . '</span>' );
			}
		}
	}

	/**
	 * Changes the sort options.
	 */
	public function facetwp_sort_options( $options, $params ) {
		$this->set_vars();

		if ( true === $this->search_enabled ) {
			if ( 'default' !== $params['template_name'] && 'wp' !== $params['template_name'] ) {
				return $options;
			}

			if ( ! empty( $this->options['display'][ $this->search_prefix . '_disable_date' ] ) ) {
				unset( $options['date_desc'] );
				unset( $options['date_asc'] );
			}

			if ( ! empty( $this->options['display'][ $this->search_prefix . '_disable_az_sorting' ] ) ) {
				unset( $options['title_desc'] );
				unset( $options['title_asc'] );
			}


			$engine = get_query_var( 'engine' );
			if ( false !== $engine && 'default' !== $engine && '' !== $engine ) {
				$search_slug = $engine;
			} else {
				$search_slug = 'display';
			}

			if ( is_post_type_archive( array( 'tour', 'accommodation' ) ) || is_tax( array_keys( $this->taxonomies ) ) ) {
				$obj = get_queried_object();
				if ( isset( $obj->name ) && in_array( $obj->name, array( 'tour', 'accommodation' ) ) ) {
					$search_slug = $obj->name;
				}
			}

			if ( 'tours' === $search_slug || 'tour' === $search_slug || 'accommodation' === $search_slug ) {
				$options['price_asc'] = array(
					'label' => __( 'Price (Highest)', 'lsx' ),
					'query_args' => array(
						'orderby' => 'meta_value_num',
						'meta_key' => 'price',
						'order' => 'DESC',
					),
				);
	
				$options['price_desc'] = array(
					'label' => __( 'Price (Lowest)', 'lsx' ),
					'query_args' => array(
						'orderby' => 'meta_value_num',
						'meta_key' => 'price',
						'order' => 'ASC',
					),
				);
			}

		}

		return $options;
	}

	/**
	 * @param $allowedtags
	 * @param $context
	 *
	 * @return mixed
	 */
	public function kses_allowed_html( $allowedtags, $context ) {
		$allowedtags['a']['data-value'] = true;
		$allowedtags['a']['data-selection']  = true;
		$allowedtags['button']['data-toggle'] = true;
		return $allowedtags;
	}

	/**
	 * Change FaceWP result count HTML
	 */
	public function get_search_query( $keyword ) {
		global $wp_rewrite,$wp_query;

		if ( empty( $keyword ) ) {
			if ( ! isset( $wp_rewrite ) || ! is_object( $wp_rewrite ) || ! $wp_rewrite->using_permalinks() ) {
				return;
			}
			$search_base = $wp_rewrite->search_base;
			if ( strpos( $_SERVER['REQUEST_URI'], "/{$search_base}/" ) !== false ) {
				$words = explode( "/{$search_base}/", $_SERVER['REQUEST_URI'] );
				$limit = count( $words );
				if ( isset( $words[ $limit - 1 ] ) ) {
					$keyword = $words[ $limit - 1 ];
				}
			}
		}

		$needle = trim( '/ ' );
		$words = explode( $needle, $keyword );
		if ( is_array( $words ) && ! empty( $words ) ) {
			$keyword = $words[ count( $words ) - 1 ];
		}
		$keyword = str_replace( '+', ' ', $keyword );
		$keyword = str_replace( '%20', ' ', $keyword );
		return $keyword;
	}
}
