<?php
/**
 * LSX Search Frontend Class.
 *
 * @package lsx-search
 */
class LSX_Search_Frontend {

	public $options = false;

	public $tabs = false;

	public $facet_data = false;

	public $search_enabled = false;

	public $search_core_suffix = false;

	public $search_prefix = false;

	public $post_types = false;

	public $taxonomies = false;

	/**
	 * Construct method.
	 */
	public function __construct() {
		if ( function_exists( 'tour_operator' ) ) {
			$this->options = get_option( '_lsx-to_settings', false );
		} else {
			$this->options = get_option( '_lsx_settings', false );

			if ( false === $this->options ) {
				$this->options = get_option( '_lsx_lsx-settings', false );
			}
		}

		add_action( 'wp', array( $this, 'set_vars' ), 11 );
		add_action( 'wp', array( $this, 'set_facetwp_vars' ), 12 );
		add_action( 'wp', array( $this, 'core' ), 13 );

		add_action( 'pre_get_posts',  array( $this, 'filter_post_types' ) );

		add_filter( 'lsx_search_post_types', array( $this, 'register_post_types' ) );
		add_filter( 'lsx_search_taxonomies', array( $this, 'register_taxonomies' ) );
		add_filter( 'lsx_search_post_types_plural', array( $this, 'register_post_type_tabs' ) );
		add_filter( 'facetwp_sort_options', array( $this, 'facetwp_sort_options' ), 10, 2 );
		add_filter( 'wp_kses_allowed_html', array( $this, 'kses_allowed_html' ), 20, 2 );
	}

	/**
	 * Check all settings.
	 */
	public function set_vars() {
		$post_type = '';

		$this->post_types = apply_filters( 'lsx_search_post_types', array() );
		$this->taxonomies = apply_filters( 'lsx_search_taxonomies', array() );
		$this->tabs = apply_filters( 'lsx_search_post_types_plural', array() );
		$this->options = apply_filters( 'lsx_search_options', $this->options );

		if ( is_search() ) {
			$this->search_core_suffix = 'core';
			$this->search_prefix = 'search';
		} elseif ( is_post_type_archive( $this->post_types ) || is_tax( $this->taxonomies ) ) {
			$this->search_core_suffix = 'search';

			if ( is_tax( $this->taxonomies ) ) {
				$tax = get_query_var( 'taxonomy' );
				$tax = get_taxonomy( $tax );
				$post_type = $tax->object_type[0];
			} else {
				$post_type = get_query_var( 'post_type' );
			}

			$this->search_prefix = $this->tabs[ $post_type ] . '_archive';
		}

		if ( ! empty( $this->options ) && ! empty( $this->options['display'][ $this->search_prefix . '_enable_' . $this->search_core_suffix ] ) ) {
			$this->search_enabled = true;
		}

		$this->search_enabled = apply_filters( 'lsx_search_enabled', $this->search_enabled, $this );
		$this->search_prefix = apply_filters( 'lsx_search_prefix', $this->search_prefix, $this );
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

			if ( class_exists( 'LSX_Videos' ) ) {
				global $lsx_videos_frontend;
				remove_action( 'lsx_content_top', array( $lsx_videos_frontend, 'categories_tabs' ), 15 );
			}

			add_filter( 'lsx_paging_nav_disable', '__return_true' );
			add_action( 'lsx_content_top', array( $this, 'lsx_content_top' ) );
			add_action( 'lsx_content_bottom', array( $this, 'lsx_content_bottom' ) );

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
				add_action( 'lsx_content_wrap_before', array( $this, 'wc_archive_header' ), 140 );

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
		$post_types = array( 'project', 'service', 'team', 'testimonial', 'video', 'product' );
		return $post_types;
	}

	/**
	 * Sets taxonomies with active search options.
	 */
	public function register_taxonomies( $taxonomies ) {
		$taxonomies = array( 'project-group', 'service-group', 'team_role', 'video-category', 'product_cat', 'product_tag' );
		return $taxonomies;
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
		);

		return $post_types_plural;
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function assets() {
		wp_enqueue_script( 'touchSwipe', LSX_SEARCH_URL . 'assets/js/vendor/jquery.touchSwipe.min.js', array( 'jquery' ), LSX_SEARCH_VER, true );
		wp_enqueue_script( 'slideandswipe', LSX_SEARCH_URL . 'assets/js/vendor/jquery.slideandswipe.min.js', array( 'jquery', 'touchSwipe' ), LSX_SEARCH_VER, true );
		wp_enqueue_script( 'lsx-search', LSX_SEARCH_URL . 'assets/js/src/lsx-search.js', array( 'jquery', 'touchSwipe', 'slideandswipe', 'jquery-ui-datepicker' ), LSX_SEARCH_VER, true );

		$params = apply_filters( 'lsx_search_js_params', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		));

		wp_localize_script( 'lsx-search', 'lsx_customizer_params', $params );

		wp_enqueue_style( 'lsx-search', LSX_SEARCH_URL . 'assets/css/lsx-search.css', array(), LSX_SEARCH_VER );
		wp_style_add_data( 'lsx-search', 'rtl', 'replace' );
	}

	/**
	 * A filter to set the layout to 2 column.
	 */
	public function lsx_layout( $layout ) {
		if ( ! empty( $this->options['display'][ $this->search_prefix . '_layout' ] ) ) {
			$layout = $this->options['display'][ $this->search_prefix . '_layout' ];
		}

		return $layout;
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
					$main_class    .= ' col-sm-pull-4 col-md-pull-3';
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
	 * Outputs top.
	 */
	public function lsx_content_top() {
		$show_pagination     = true;
		$pagination_visible  = false;
		$show_per_page_combo = empty( $this->options['display'][ $this->search_prefix . '_disable_per_page' ] );
		$show_sort_combo     = empty( $this->options['display'][ $this->search_prefix . '_disable_all_sorting' ] );
		if ( isset( $this->options['display'][ $this->search_prefix . '_az_pagination' ] ) ) {
			$az_pagination       = $this->options['display'][ $this->search_prefix . '_az_pagination' ];
		} else {
			$az_pagination = false;
		}

		$show_pagination     = apply_filters( 'lsx_search_top_show_pagination', $show_pagination );
		$pagination_visible  = apply_filters( 'lsx_search_top_pagination_visible', $pagination_visible );
		$show_per_page_combo = apply_filters( 'lsx_search_top_show_per_page_combo', $show_per_page_combo );
		$show_sort_combo     = apply_filters( 'lsx_search_top_show_sort_combo', $show_sort_combo );
		$az_pagination       = apply_filters( 'lsx_search_top_az_pagination', $az_pagination );

		$facet_row_classes = apply_filters( 'lsx_search_top_facetwp_row_classes', '' );

		?>
		<div id="facetwp-top">
			<?php if ( $show_sort_combo || ( $show_pagination && $show_per_page_combo ) ) { ?>
				<div class="row facetwp-top-row-1 hidden-xs <?php echo esc_attr( $facet_row_classes ); ?>">
					<div class="col-xs-12">

						<?php do_action( 'lsx_search_facetwp_top_row' ); ?>

						<?php if ( $show_sort_combo ) { ?>
							<?php echo do_shortcode( '[facetwp sort="true"]' ); ?>
						<?php } ?>

						<?php if ( ( $show_pagination && $show_per_page_combo ) || $show_per_page_combo ) { ?>
							<?php echo do_shortcode( '[facetwp per_page="true"]' ); ?>
						<?php } ?>

						<?php if ( $show_pagination ) { ?>
							<?php
								$pagination_visible = true;
								echo do_shortcode( '[facetwp pager="true"]' );
							?>
						<?php } ?>
					</div>
				</div>
			<?php } ?>

			<?php if ( ! empty( $az_pagination ) || ( $show_pagination && ! $pagination_visible ) ) { ?>
				<div class="row facetwp-top-row-2 hidden-xs">
					<div class="col-xs-12 col-lg-8">
						<?php if ( ! empty( $az_pagination ) ) { ?>
							<?php echo do_shortcode( '[facetwp facet="' . $az_pagination . '"]' ); ?>
						<?php } ?>
					</div>

					<?php if ( $show_pagination && ! $pagination_visible ) { ?>
						<div class="col-xs-12 col-lg-4">
							<?php echo do_shortcode( '[facetwp pager="true"]' ); ?>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

		<div class="facetwp-template">
		<?php
	}

	/**
	 * Outputs bottom.
	 */
	public function lsx_content_bottom() {
		?>
		</div>
		<?php
		$show_pagination = true;
		if ( isset( $this->options['display'][ $this->search_prefix . '_az_pagination' ] ) ) {
			$az_pagination       = $this->options['display'][ $this->search_prefix . '_az_pagination' ];
		} else {
			$az_pagination = false;
		}

		if ( $show_pagination || ! empty( $az_pagination ) ) { ?>
			<div id="facetwp-bottom">
				<div class="row facetwp-bottom-row-1">
					<div class="col-xs-12 col-lg-8 hidden-xs">
						<?php do_action( 'lsx_search_facetwp_bottom_row' ); ?>
						<?php if ( ! empty( $az_pagination ) ) {
							echo do_shortcode( '[facetwp facet="' . $az_pagination . '"]' );
						} ?>
					</div>

					<?php if ( $show_pagination ) { ?>
						<div class="col-xs-12 col-lg-4">
							<?php echo do_shortcode( '[facetwp pager="true"]' ); ?>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php }
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

				<?php if ( ! empty( $this->options['display'][ $this->search_prefix . '_display_result_count' ] ) ) { ?>
					<div class="row hidden-xs">
						<div class="col-xs-12 facetwp-item facetwp-results">
							<h3 class="lsx-search-title lsx-search-title-results"><?php esc_html_e( 'Results', 'lsx-search' ); ?> (<?php echo do_shortcode( '[facetwp counts="true"]' ); ?>)

							<?php if ( false !== $this->options && isset( $this->options['display'] ) && ( 'on' === $this->options['display'][ $this->search_prefix . '_display_clear_button' ] || 'on' === $this->options['display']['products_search_display_clear_button'] ) ) { ?>
								<span class="clear-facets hidden">- <a title="<?php esc_html_e( 'Clear the current search filters.', 'lsx-search' ); ?>" class="facetwp-results-clear" type="button" onclick="<?php echo esc_attr( apply_filters( 'lsx_search_clear_function', 'lsx_search.clearFacets(this);' ) ); ?>"><?php esc_html_e( 'Clear', 'lsx-search' ); ?></a></span>
							<?php } ?>
							</h3>
						</div>
					</div>
				<?php } ?>

				<?php if ( ! empty( $this->options['display'][ $this->search_prefix . '_facets' ] ) && is_array( $this->options['display'][ $this->search_prefix . '_facets' ] ) ) { ?>
					<div class="row">
						<div class="col-xs-12 facetwp-item facetwp-filters-button hidden-sm hidden-md hidden-lg">
							<button class="ssm-toggle-nav btn btn-block" rel="lsx-search-filters"><?php esc_html_e( 'Filters', 'lsx-search' ); ?> <i class="fa fa-chevron-down" aria-hidden="true"></i></button>
						</div>

						<div class="ssm-overlay ssm-toggle-nav" rel="lsx-search-filters"></div>

						<div class="col-xs-12 facetwp-item facetwp-filters-wrap" rel="lsx-search-filters">
							<div class="row hidden-sm hidden-md hidden-lg ssm-row-margin-bottom">
								<div class="col-xs-12 facetwp-item facetwp-filters-button">
									<button class="ssm-close-btn ssm-toggle-nav btn btn-block" rel="lsx-search-filters"><?php esc_html_e( 'Close Filters', 'lsx-search' ); ?> <i class="fa fa-times" aria-hidden="true"></i></button>
								</div>
							</div>

							<div class="row">
								<?php
									// Search
									foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {
										if ( 'search_form' === $facet ) {
											$this->display_facet_search();
										}

										if ( 'search' === $facet ) {
											$this->display_facet_default( $facet );
										}
									}
								?>

								<?php
									// Slider
									foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {
										if ( isset( $this->facet_data[ $facet ] ) && 'search_form' !== $facet && 'search' !== $facet && 'slider' === $this->facet_data[ $facet ]['type'] ) {
											$this->display_facet_default( $facet );
										}
									}
								?>

								<?php
									// Others
									foreach ( $this->options['display'][ $this->search_prefix . '_facets' ] as $facet => $facet_useless ) {
										if ( isset( $this->facet_data[ $facet ] ) && 'search_form' !== $facet && 'search' !== $facet && ! in_array( $this->facet_data[ $facet ]['type'], array( 'alpha', 'slider' ) ) ) {
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
	 * Display WooCommerce archive title.
	 */
	public function wc_archive_header() {
		$default_size   = 'sm';
		$size           = apply_filters( 'lsx_bootstrap_column_size', $default_size );
		?>
			<div class="archive-header-wrapper col-<?php echo esc_attr( $size ); ?>-12">
				<header class="archive-header">
					<h1 class="archive-title"><?php woocommerce_page_title(); ?></h1>
				</header>

				<?php lsx_global_header_inner_bottom(); ?>
			</div>
		<?php
	}

	/**
	 * Display facet search.
	 */
	public function display_facet_search() {
		?>
		<div class="col-xs-12 facetwp-item facetwp-form">
			<form class="search-form lsx-search-form" action="/" method="get">
				<div class="input-group">
					<div class="field">
						<input class="search-field form-control" name="s" type="search" placeholder="<?php esc_html_e( 'Search', 'lsx-search' ); ?>..." autocomplete="off" value="<?php echo get_search_query() ?>">
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
		$col_class = '';

		if ( 'search' === $facet ) {
			$col_class = 'facetwp-form';
		}
		?>
		<div class="col-xs-12 facetwp-item <?php echo esc_attr( $col_class ); ?>">
			<?php if ( 'search' === $facet ) : ?>
				<div class="search-form lsx-search-form">
					<div class="input-group">
						<div class="field">
							<?php echo do_shortcode( '[facetwp facet="' . $facet . '"]' ); ?>
						</div>

						<div class="field submit-button">
							<button class="search-submit search-submit-facetwp btn" type="button"><?php esc_html_e( 'Search', 'lsx-search' ); ?></button>
						</div>
					</div>
				</div>
			<?php else : ?>
				<h3 class="lsx-search-title"><?php echo wp_kses_post( $this->facet_data[ $facet ]['label'] ); ?></h3>
				<?php echo do_shortcode( '[facetwp facet="' . $facet . '"]' ); ?>
			<?php endif; ?>
		</div>
		<?php
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
			if ( ! empty( $this->options['display']['search_enable_pt_label'] ) ) {
				echo wp_kses_post( ' <span class="label label-default lsx-label-post-type">' . ucwords( get_post_type() ) . '</span>' );
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

			if ( ! empty( $this->options['display'][ $this->search_prefix . '_disable_date_sorting' ] ) ) {
				unset( $options['date_desc'] );
				unset( $options['date_asc'] );
			}

			if ( ! empty( $this->options['display'][ $this->search_prefix . '_disable_az_sorting' ] ) ) {
				unset( $options['title_desc'] );
				unset( $options['title_asc'] );
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

}
