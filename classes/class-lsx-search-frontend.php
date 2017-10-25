<?php
/**
 * LSX Search Frontend Class.
 *
 * @package lsx-search
 */
class LSX_Search_Frontend {

	public $options = false;

	public $search_enabled = false;

	public $search_core_suffix = false;

	public $search_prefix = false;

	public $post_types = false;

	public $taxonomies = false;

	public $tabs = false;

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

		add_filter( 'lsx_search_post_types', array( $this, 'register_post_types' ) );
		add_filter( 'lsx_search_taxonomies', array( $this, 'register_taxonomies' ) );
		add_filter( 'lsx_search_post_types_plural', array( $this, 'register_post_type_tabs' ) );
		add_action( 'init', array( $this, 'set_vars' ) );

		add_action( 'wp_head', array( $this, 'wp_head' ), 11 );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 999 );

		add_filter( 'lsx_layout', array( $this, 'lsx_layout' ), 20, 1 );
		add_filter( 'lsx_layout_selector', array( $this, 'lsx_layout_selector' ), 10, 4 );
	}

	/**
	 * Sets post types with active search options.
	 */
	public function register_post_types( $post_types ) {
		$post_types = array( 'project', 'service', 'team', 'testimonial', 'video' );
		return $post_types;
	}

	/**
	 * Sets taxonomies with active search options.
	 */
	public function register_taxonomies( $taxonomies ) {
		$taxonomies = array( 'project-group', 'service-group', 'team_role', 'video-category' );
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
		);

		return $post_types_plural;
	}

	/**
	 * Sets variables.
	 */
	public function set_vars() {
		$this->post_types = apply_filters( 'lsx_search_post_types', array() );
		$this->taxonomies = apply_filters( 'lsx_search_taxonomies', array() );
		$this->tabs = apply_filters( 'lsx_search_post_types_plural', array() );
	}

	/**
	 * Check all settings.
	 */
	public function wp_head() {
		if ( is_search() ) {
			$this->search_core_suffix = 'core';
			$this->search_prefix = 'search';
		} elseif ( is_post_type_archive( $this->post_types ) || is_tax( $this->taxonomies ) ) {
			$this->search_core_suffix = 'search';
			$this->search_prefix = $this->tabs[ get_post_type() ] . '_archive';
		}

		if ( ! empty( $this->options ) && ! empty( $this->options['display'][ $this->search_prefix . '_enable_' . $this->search_core_suffix ] ) ) {
			$this->search_enabled = true;

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
		}
	}

	public function assets() {
		wp_enqueue_script( 'touchSwipe', LSX_SEARCH_URL . 'assets/js/vendor/jquery.touchSwipe.min.js', array( 'jquery' ), LSX_SEARCH_VER, true );
		wp_enqueue_script( 'slideandswipe', LSX_SEARCH_URL . 'assets/js/vendor/jquery.slideandswipe.min.js', array( 'jquery', 'touchSwipe' ), LSX_SEARCH_VER, true );
		wp_enqueue_script( 'lsx-search', LSX_SEARCH_URL . 'assets/js/lsx-search.min.js', array( 'jquery', 'touchSwipe', 'slideandswipe' ), LSX_SEARCH_VER, true );

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
		if ( true === $this->search_enabled ) {
			if ( ! empty( $this->options['display'][ $this->search_prefix . '_layout' ] ) ) {
				$layout = $this->options['display'][ $this->search_prefix . '_layout' ];
			}
		}

		return $layout;
	}

	/**
	 * Change the primary and secondary column classes.
	 */
	public function lsx_layout_selector( $return_class, $class, $layout, $size ) {
		if ( true === $this->search_enabled ) {
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
		}

		return $return_class;
	}

	/**
	 * Outputs top.
	 */
	public function lsx_content_top() {
		$show_pagination     = true;
		$pagination_visible  = false;
		$show_per_page_combo = ! isset( $this->options['display'][ $this->search_prefix . '_disable_per_page' ] ) || 'on' !== $this->options['display'][ 'disable_' . $option_slug . 'per_page' ];
		$show_sort_combo     = ! isset( $this->options['display'][ $this->search_prefix . '_disable_all_sorting' ] ) || 'on' !== $this->options['display'][ 'disable_' . $option_slug . 'all_sorting' ];
		$az_pagination       = $this->options['display'][ $this->search_prefix . '_az_pagination' ];
		?>
		<div id="facetwp-top">
			<?php if ( $show_sort_combo || ( $show_pagination && $show_per_page_combo ) || $show_pagination ) { ?>
				<div class="row facetwp-top-row-1 hidden-xs">
					<div class="col-xs-12">
						<?php if ( $show_sort_combo ) { ?>
							<?php echo do_shortcode( '[facetwp sort="true"]' ); ?>
						<?php } ?>

						<?php if ( $show_pagination && $show_per_page_combo ) { ?>
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
					<div class="col-xs-12">
						<?php if ( ! empty( $az_pagination ) ) { ?>
							<?php echo do_shortcode( '[facetwp facet="' . $az_pagination . '"]' ); ?>
						<?php } ?>

						<?php if ( $show_pagination && ! $pagination_visible ) { ?>
							<?php echo do_shortcode( '[facetwp pager="true"]' ); ?>
						<?php } ?>
					</div>
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
		$az_pagination   = $this->options['display'][ $this->search_prefix . '_az_pagination' ];

		if ( $show_pagination || ! empty( $az_pagination ) ) { ?>
			<div id="facetwp-bottom">
				<div class="row facetwp-bottom-row-1">
					<div class="col-xs-12 col-lg-8 hidden-xs">
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
		?>
			<div id="secondary" class="facetwp-sidebar widget-area <?php echo esc_attr( lsx_sidebar_class() ); ?>" role="complementary">
				SIDEBAR
			</div>
		<?php
	}

}

global $lsx_search_frontend;
$lsx_search_frontend = new LSX_Search_Frontend();
