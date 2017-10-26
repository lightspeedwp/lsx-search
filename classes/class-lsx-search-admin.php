<?php
/**
 * LSX Search Admin Class.
 *
 * @package lsx-search
 */
class LSX_Search_Admin {

	public $options = false;

	public $tabs = false;

	public $facet_data = false;

	/**
	 * Construct method.
	 */
	public function __construct() {
		if ( ! class_exists( 'CMB_Meta_Box' ) ) {
			require_once( LSX_SEARCH_PATH . '/vendor/Custom-Meta-Boxes/custom-meta-boxes.php' );
		}

		if ( function_exists( 'tour_operator' ) ) {
			$this->options = get_option( '_lsx-to_settings', false );
		} else {
			$this->options = get_option( '_lsx_settings', false );

			if ( false === $this->options ) {
				$this->options = get_option( '_lsx_lsx-settings', false );
			}
		}

		add_action( 'init', array( $this, 'set_vars' ) );
		add_action( 'init', array( $this, 'set_facetwp_vars' ) );

		add_filter( 'lsx_search_post_types_plural', array( $this, 'register_post_type_tabs' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		if ( is_admin() ) {
			add_filter( 'lsx_customizer_colour_selectors_body', array( $this, 'customizer_body_colours_handler' ), 15, 2 );
		}

		add_action( 'init', array( $this, 'create_settings_page' ), 100 );
		add_filter( 'lsx_framework_settings_tabs', array( $this, 'register_tabs' ), 100, 1 );
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
		$this->tabs = apply_filters( 'lsx_search_post_types_plural', array() );
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
		//wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_script( 'lsx-search-admin', LSX_SEARCH_URL . 'assets/js/lsx-search-admin.min.js', array( 'jquery' ), LSX_SEARCH_VER, true );
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
	 * Returns the array of settings to the UIX Class.
	 */
	public function create_settings_page() {
		if ( is_admin() && class_exists( 'FacetWP' ) ) {
			if ( ! class_exists( '\lsx\ui\uix' ) && ! function_exists( 'tour_operator' ) ) {
				include_once LSX_SEARCH_PATH . 'vendor/uix/uix.php';
				$pages = $this->settings_page_array();
				$uix = \lsx\ui\uix::get_instance( 'lsx' );
				$uix->register_pages( $pages );
			}

			if ( function_exists( 'tour_operator' ) ) {
				add_action( 'lsx_to_framework_dashboard_tab_content', array( $this, 'general_settings' ), 25 );
				add_action( 'lsx_to_framework_display_tab_content', array( $this, 'display_settings' ), 35 );
				add_action( 'lsx_to_framework_display_tab_content', array( $this, 'archive_settings' ), 45 );
			} else {
				add_action( 'lsx_framework_dashboard_tab_content', array( $this, 'general_settings' ), 25 );
				add_action( 'lsx_framework_display_tab_content', array( $this, 'display_settings' ), 35 );
				add_action( 'lsx_framework_display_tab_content', array( $this, 'archive_settings' ), 45 );
			}
		}
	}

	/**
	 * Returns the array of settings to the UIX Class.
	 */
	public function settings_page_array() {
		$tabs = apply_filters( 'lsx_framework_settings_tabs', array() );

		return array(
			'settings'  => array(
				'page_title'  => esc_html__( 'Theme Options', 'lsx-search' ),
				'menu_title'  => esc_html__( 'Theme Options', 'lsx-search' ),
				'capability'  => 'manage_options',
				'icon'        => 'dashicons-book-alt',
				'parent'      => 'themes.php',
				'save_button' => esc_html__( 'Save Changes', 'lsx-search' ),
				'tabs'        => $tabs,
			),
		);
	}

	/**
	 * Register tabs.
	 */
	public function register_tabs( $tabs ) {
		if ( class_exists( 'FacetWP' ) ) {
			$default = true;

			if ( false !== $tabs && is_array( $tabs ) && count( $tabs ) > 0 ) {
				$default = false;
			}

			if ( ! function_exists( 'tour_operator' ) ) {
				if ( ! array_key_exists( 'general', $tabs ) ) {
					$tabs['general'] = array(
						'page_title'        => '',
						'page_description'  => '',
						'menu_title'        => esc_html__( 'General', 'lsx-search' ),
						'template'          => LSX_SEARCH_PATH . 'includes/settings/general.php',
						'default'           => $default,
					);

					$default = false;
				}

				if ( ! array_key_exists( 'display', $tabs ) ) {
					$tabs['display'] = array(
						'page_title'        => '',
						'page_description'  => '',
						'menu_title'        => esc_html__( 'Display', 'lsx-search' ),
						'template'          => LSX_SEARCH_PATH . 'includes/settings/display.php',
						'default'           => $default,
					);

					$default = false;
				}

				if ( ! array_key_exists( 'api', $tabs ) ) {
					$tabs['api'] = array(
						'page_title'        => '',
						'page_description'  => '',
						'menu_title'        => esc_html__( 'API', 'lsx-search' ),
						'template'          => LSX_SEARCH_PATH . 'includes/settings/api.php',
						'default'           => $default,
					);

					$default = false;
				}
			}
		}

		return $tabs;
	}

	/**
	 * Outputs the general tabs settings.
	 */
	public function general_settings( $tab = 'general' ) {
		if ( 'search' === $tab ) :
			$post_types = get_post_types( array(
				'public' => true,
			) );

			$key = array_search( 'attachment', $post_types, true );

			if ( false !== $key ) {
				unset( $post_types[ $key ] );
			}
			?>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Post types', 'lsx-search' ); ?></label>
				</th>
				<td>
					<ul>
						<?php
							$active_post_types = array();

							if ( isset( $this->options['general']['search_post_types'] ) && is_array( $this->options['general']['search_post_types'] ) ) {
								$active_post_types = $this->options['general']['search_post_types'];
							}

							foreach ( $post_types as $key => $value ) {
								?><li>
									<input type="checkbox" <?php if ( array_key_exists( $key, $active_post_types ) ) { echo 'checked="checked"'; } ?> name="search_post_types[<?php echo esc_attr( $key ); ?>]" /> <label><?php echo esc_html( ucwords( $key ) ); ?></label>
								</li><?php
							}
						?>
					</ul>
				</td>
			</tr>
			<?php
		endif;
	}

	/**
	 * Outputs the display tabs settings.
	 */
	public function display_settings( $tab = 'display' ) {
		if ( 'search' === $tab ) :
			?>
			<tr class="form-field">
				<th scope="row">
					<label><?php esc_html_e( 'Enable Search', 'lsx-search' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if search_enable_core}} checked="checked" {{/if}} name="search_enable_core" />
					<small><?php esc_html_e( 'This adds the facet shortcodes to the search results template.', 'lsx-search' ); ?></small>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label><?php esc_html_e( 'Enable Post Type Label', 'lsx-search' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if search_enable_pt_label}} checked="checked" {{/if}} name="search_enable_pt_label" />
					<small><?php esc_html_e( 'This enables the post type label from entries on search results page.', 'lsx-search' ); ?></small>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Columns Layout', 'lsx-search' ); ?></label>
				</th>
				<td>
					<select name="search_layout">
						<option value="" {{#is search_layout value=""}}selected="selected"{{/is}}><?php esc_html_e( 'Follow the theme layout', 'lsx-search' ); ?></option>
						<option value="1c" {{#is search_layout value="1c"}} selected="selected"{{/is}}><?php esc_html_e( '1 column', 'lsx-search' ); ?></option>
						<option value="2cr" {{#is search_layout value="2cr"}} selected="selected"{{/is}}><?php esc_html_e( '2 columns / Content on right', 'lsx-search' ); ?></option>
						<option value="2cl" {{#is search_layout value="2cl"}} selected="selected"{{/is}}><?php esc_html_e( '2 columns / Content on left', 'lsx-search' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Per Page', 'lsx-search' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if search_disable_per_page}} checked="checked" {{/if}} name="search_disable_per_page" /> <label><?php esc_html_e( 'Disable Per Page', 'lsx-search' ); ?></label>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Sorting', 'lsx-search' ); ?></label>
				</th>
				<td>
					<ul>
						<li><input type="checkbox" {{#if search_disable_all_sorting}} checked="checked" {{/if}} name="search_disable_all_sorting" /> <label><?php esc_html_e( 'Disable Sorting', 'lsx-search' ); ?></label></li>
						<li><input type="checkbox" {{#if search_disable_az_sorting}} checked="checked" {{/if}} name="search_disable_az_sorting" /> <label><?php esc_html_e( 'Disable Title (A-Z)', 'lsx-search' ); ?></label></li>
						<li><input type="checkbox" {{#if search_disable_date_sorting}} checked="checked" {{/if}} name="search_disable_date_sorting" /> <label><?php esc_html_e( 'Disable Date', 'lsx-search' ); ?></label></li>
					</ul>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label><?php esc_html_e( 'Display Result Count', 'lsx-search' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if search_display_result_count}} checked="checked" {{/if}} name="search_display_result_count" />
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Alphabetical Facet', 'lsx-search' ); ?></label>
				</th>
				<td>
					<?php
						if ( is_array( $this->facet_data ) && ! empty( $this->facet_data ) ) {
							$active_facet = $this->options['display']['search_az_pagination'];
							?>
							<select name="search_az_pagination">
								<option <?php if ( empty( $active_facet ) ) { echo 'selected="selected"'; } ?> value=""><?php esc_html_e( 'None', 'lsx-search' ); ?></option>

								<?php foreach ( $this->facet_data as $facet ) {
									if ( 'alpha' === $facet['type'] ) { ?>
										<option <?php if ( $active_facet === $facet['name'] ) { echo 'selected="selected"'; } ?> value="<?php echo esc_attr( $facet['name'] ); ?>"><?php echo esc_html( $facet['label'] ) . ' (' . esc_html( $facet['name'] ) . ')'; ?></option>
									<?php }
								} ?>
							</select>
							<?php
						} else {
							esc_html_e( 'You have no Facets setup.', 'lsx-search' );
						}
					?>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Facets', 'lsx-search' ); ?></label>
				</th>
				<td>
					<ul>
						<?php
							if ( is_array( $this->facet_data ) && ! empty( $this->facet_data ) ) {
								$active_facets = array();

								if ( isset( $this->options['display']['search_facets'] ) && is_array( $this->options['display']['search_facets'] ) ) {
									$active_facets = $this->options['display']['search_facets'];
								}

								foreach ( $this->facet_data as $facet ) {
									if ( 'alpha' !== $facet['type'] ) { ?>
										<li>
											<input type="checkbox" <?php if ( array_key_exists( $facet['name'], $active_facets ) ) { echo 'checked="checked"'; } ?> name="search_facets[<?php echo esc_attr( $facet['name'] ); ?>]" /> <label><?php echo esc_html( $facet['label'] ) . ' (' . esc_html( $facet['name'] ) . ')'; ?></label>
										</li>
									<?php }
								}
							} else {
								?>
									<li><?php esc_html_e( 'You have no Facets setup.', 'lsx-search' ); ?></li>
								<?php
							}
						?>
					</ul>
				</td>
			</tr>
			<?php
		endif;
	}

	/**
	 * Outputs the archive tabs settings.
	 */
	public function archive_settings( $tab = 'display' ) {
		if ( in_array( $tab, array_values( $this->tabs ) ) ) :
			?>
			<tr class="form-field">
				<th scope="row" colspan="2"><label><h3><?php esc_html_e( 'Search Settings', 'lsx-search' ); ?></h3></label></th>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label><?php esc_html_e( 'Enable Filtering', 'lsx-search' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if <?php echo esc_attr( $tab ); ?>_archive_enable_search}} checked="checked" {{/if}} name="<?php echo esc_attr( $tab ); ?>_archive_enable_search" />
					<small><?php esc_html_e( 'This adds the facet shortcodes to the post type archive and taxonomy templates.', 'lsx-search' ); ?></small>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Columns Layout', 'lsx-search' ); ?></label>
				</th>
				<td>
					<select name="<?php echo esc_attr( $tab ); ?>_archive_layout">
						<option value="" {{#is <?php echo esc_attr( $tab ); ?>_archive_layout value=""}}selected="selected"{{/is}}><?php esc_html_e( 'Follow the theme layout', 'lsx-search' ); ?></option>
						<option value="1c" {{#is <?php echo esc_attr( $tab ); ?>_archive_layout value="1c"}} selected="selected"{{/is}}><?php esc_html_e( '1 column', 'lsx-search' ); ?></option>
						<option value="2cr" {{#is <?php echo esc_attr( $tab ); ?>_archive_layout value="2cr"}} selected="selected"{{/is}}><?php esc_html_e( '2 columns / Content on right', 'lsx-search' ); ?></option>
						<option value="2cl" {{#is <?php echo esc_attr( $tab ); ?>_archive_layout value="2cl"}} selected="selected"{{/is}}><?php esc_html_e( '2 columns / Content on left', 'lsx-search' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Per Page', 'lsx-search' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if <?php echo esc_attr( $tab ); ?>_archive_disable_per_page}} checked="checked" {{/if}} name="<?php echo esc_attr( $tab ); ?>_archive_disable_per_page" /> <label><?php esc_html_e( 'Disable Per Page', 'lsx-search' ); ?></label>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Sorting', 'lsx-search' ); ?></label>
				</th>
				<td>
					<ul>
						<li><input type="checkbox" {{#if <?php echo esc_attr( $tab ); ?>_archive_disable_all_sorting}} checked="checked" {{/if}} name="<?php echo esc_attr( $tab ); ?>_archive_disable_all_sorting" /> <label><?php esc_html_e( 'Disable Sorting', 'lsx-search' ); ?></label></li>
						<li><input type="checkbox" {{#if <?php echo esc_attr( $tab ); ?>_archive_disable_az_sorting}} checked="checked" {{/if}} name="<?php echo esc_attr( $tab ); ?>_archive_disable_az_sorting" /> <label><?php esc_html_e( 'Disable Title (A-Z)', 'lsx-search' ); ?></label></li>
						<li><input type="checkbox" {{#if <?php echo esc_attr( $tab ); ?>_archive_disable_date_sorting}} checked="checked" {{/if}} name="<?php echo esc_attr( $tab ); ?>_archive_disable_date_sorting" /> <label><?php esc_html_e( 'Disable Date', 'lsx-search' ); ?></label></li>
					</ul>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label><?php esc_html_e( 'Display Result Count', 'lsx-search' ); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if <?php echo esc_attr( $tab ); ?>_archive_display_result_count}} checked="checked" {{/if}} name="<?php echo esc_attr( $tab ); ?>_archive_display_result_count" />
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Alphabetical Facet', 'lsx-search' ); ?></label>
				</th>
				<td>
					<?php
						if ( is_array( $this->facet_data ) && ! empty( $this->facet_data ) ) {
							$active_facet = $this->options['display'][ $tab . '_archive_az_pagination' ];
							?>
							<select name="<?php echo esc_attr( $tab ); ?>_archive_az_pagination">
								<option <?php if ( empty( $active_facet ) ) { echo 'selected="selected"'; } ?> value=""><?php esc_html_e( 'None', 'lsx-search' ); ?></option>

								<?php foreach ( $this->facet_data as $facet ) {
									if ( 'alpha' === $facet['type'] ) { ?>
										<option <?php if ( $active_facet === $facet['name'] ) { echo 'selected="selected"'; } ?> value="<?php echo esc_attr( $facet['name'] ); ?>"><?php echo esc_html( $facet['label'] ) . ' (' . esc_html( $facet['name'] ) . ')'; ?></option>
									<?php }
								} ?>
							</select>
							<?php
						} else {
							esc_html_e( 'You have no Facets setup.', 'lsx-search' );
						}
					?>
				</td>
			</tr>
			<tr class="form-field-wrap">
				<th scope="row">
					<label><?php esc_html_e( 'Facets', 'lsx-search' ); ?></label>
				</th>
				<td>
					<ul>
						<?php
							if ( is_array( $this->facet_data ) && ! empty( $this->facet_data ) ) {
								$active_facets = array();

								if ( isset( $this->options['display'][ $tab . '_archive_facets' ] ) && is_array( $this->options['display'][ $tab . '_archive_facets' ] ) ) {
									$active_facets = $this->options['display'][ $tab . '_archive_facets' ];
								}

								foreach ( $this->facet_data as $facet ) {
									if ( 'alpha' !== $facet['type'] ) { ?>
										<li>
											<input type="checkbox" <?php if ( array_key_exists( $facet['name'], $active_facets ) ) { echo 'checked="checked"'; } ?> name="<?php echo esc_attr( $tab ); ?>_archive_facets[<?php echo esc_attr( $facet['name'] ); ?>]" /> <label><?php echo esc_html( $facet['label'] ) . ' (' . esc_html( $facet['name'] ) . ')'; ?></label>
										</li>
									<?php }
								}
							} else {
								?>
									<li><?php esc_html_e( 'You have no Facets setup.', 'lsx-search' ); ?></li>
								<?php
							}
						?>
					</ul>
				</td>
			</tr>
			<?php
		endif;
	}

}

global $lsx_search_admin;
$lsx_search_admin = new LSX_Search_Admin();
