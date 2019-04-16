<?php
/**
 * LSX Search Shortcode Class.
 *
 * @package lsx-search
 */
class LSX_Search_Shortcode {

	/**
	 * Construct method.
	 */
	public function __construct() {
		add_shortcode( 'lsx_search_form', array( $this, 'search_form' ) );
	}

	/**
	 * Outputs the appropriate search form
	 */
	public function search_form( $atts = array() ) {
		$classes = 'search-form lsx-search-form form-inline';

		if ( isset( $atts['class'] ) ) {
			$classes .= $atts['class'];
		}

		$placeholder = __( 'Where do you want to go?', 'lsx-search' );

		if ( isset( $atts['placeholder'] ) ) {
			$placeholder = $atts['placeholder'];
		}

		$action = '/';

		if ( isset( $atts['action'] ) ) {
			$action = $atts['action'];
		}

		$method = 'get';

		if ( isset( $atts['method'] ) ) {
			$method = $atts['method'];
		}

		$button_label = __( 'Search', 'lsx-search' );

		if ( isset( $atts['button_label'] ) ) {
			$button_label = $atts['button_label'];
		}

		$button_class = 'btn cta-btn ';

		if ( isset( $atts['button_class'] ) ) {
			$button_class .= $atts['button_class'];
		}

		$engine = false;

		if ( isset( $atts['engine'] ) ) {
			$engine = $atts['engine'];
		}

		$engine_select = false;

		if ( isset( $atts['engine_select'] ) ) {
			$engine_select = true;
		}

		$display_search_field = true;

		if ( isset( $atts['search_field'] ) ) {
			$display_search_field = (boolean) $atts['search_field'];
		}

		$facets = false;

		if ( isset( $atts['facets'] ) ) {
			$facets = $atts['facets'];
		}

		$combo_box = false;

		if ( isset( $atts['combo_box'] ) ) {
			$combo_box = true;
		}

		$return = '';

		ob_start(); ?>

		<?php do_action( 'lsx_search_form_before' ); ?>

		<nav class="navbar navbar-light bg-light">

			<form class="<?php echo esc_attr( $classes ); ?>" action="<?php echo esc_attr( $action ); ?>" method="<?php echo esc_attr( $method ); ?>">

				<?php do_action( 'lsx_search_form_top' ); ?>

				<div class="input-group navbar-nav">
					<?php if ( true === $display_search_field ) : ?>
						<div class="field">
							<input class="search-field form-control" name="s" type="search" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off">
						</div>
					<?php endif; ?>

					<?php if ( false !== $engine_select && false !== $engine && 'default' !== $engine ) :
						$engines = explode( '|',$engine ); ?>
						<div class="field engine-select">
							<div class="dropdown nav-item">
								<?php
								$plural = 's';
								if ( 'accommodation' === $engine[0] ) {
									$plural = '';
								}
								?>
								<button id="engine" data-selection="<?php echo esc_attr( $engines[0] ); ?>" class="btn border-btn btn-dropdown dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo esc_html( ucwords( str_replace( '_', ' ',$engines[0] ) ) . $plural ); ?> <span class="caret"></span></button>
								<ul class="dropdown-menu">
									<?php
									foreach ( $engines as $engine ) {
										$plural = 's';
										if ( 'accommodation' === $engine ) {
											$plural = '';
										}
										echo '<li><a data-value="' . esc_attr( $engine ) . '" href="#">' . esc_html( ucfirst( str_replace( '_', ' ',$engine ) ) . $plural ) . '</a></li>';
									}
									?>
								</ul>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( false !== $facets ) {
						$facets = explode( '|',$facets );

						if ( ! is_array( $facets ) ) {
							$facets = array( $facets );
						}

						$field_class = 'field';

						if ( false !== $combo_box ) {
							$this->combo_box( $facets );
							$field_class .= ' combination-toggle hidden';
						}

						foreach ( $facets as $facet ) {
							?>
							<div class="<?php echo wp_kses_post( $field_class ); ?>">
								<?php
								$facet = FWP()->helper->get_facet_by_name( $facet );
								if ( isset( $facet['source'] ) ) {
									$values = $this->get_form_facet( $facet['source'] );
								} else {
									$values = array();
								}
								$facet_display_type = apply_filters( 'lsx_search_form_field_type', 'select', $facet );
								$this->display_form_field( $facet_display_type,$facet,$values,$combo_box );
								?>
							</div>
							<?php
						}
					} ?>

					<div class="field submit-button">
						<button class="<?php echo esc_attr( $button_class ); ?>" type="submit"><?php echo wp_kses_post( $button_label ); ?></button>
					</div>

					<?php if ( false === $engine_select && false !== $engine && 'default' !== $engine ) : ?>
						<input name="engine" type="hidden" value="<?php echo esc_attr( $engine ); ?>">
					<?php endif; ?>
				</div>

				<?php do_action( 'lsx_search_form_bottom' ); ?>

			</form>

		</nav>

		<?php do_action( 'lsx_search_form_after' ); ?>
		<?php
		$return = ob_get_clean();

		$return = preg_replace( '/[\n]+/', ' ', $return );
		$return = preg_replace( '/[\t]+/', ' ', $return );

		return $return;
	}

	/**
	 * Outputs the combination selector
	 */
	public function combo_box( $facets ) {
		?>
		<div class="field combination-dropdown">
			<div class="dropdown">
				<button data-selection="0" class="btn border-btn btn-dropdown dropdown-toggle btn-combination" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					<?php esc_attr_e( 'Select', 'lsx-search' ); ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">

					<li style="display: none;"><a class="default" data-value="0" href="#"><?php esc_attr_e( 'Select ', 'lsx-search' ); ?></a></li>

					<?php foreach ( $facets as $facet ) {
						$facet = FWP()->helper->get_facet_by_name( $facet );
						?>
						<li><a data-value="fwp_<?php echo wp_kses_post( $facet['name'] ); ?>" href="#"><?php echo wp_kses_post( $facet['label'] ); ?></a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Grabs the Values for the Facet in Question.
	 */
	protected function get_form_facet( $facet_source = false ) {
		global $wpdb;

		$values = array();
		$select = 'f.facet_value, f.facet_display_value';
		$from = "{$wpdb->prefix}facetwp_index f";
		$where = "f.facet_source = '{$facet_source}'";

		//Check if the current facet is showing destinations.
		if ( stripos( $facet_source, 'destination_to' ) ) {
			$from .= " INNER JOIN {$wpdb->posts} p ON f.facet_value = p.ID";
			$where .= " AND p.post_parent = '0'";

		}

		$response = $wpdb->prepare( "SELECT {$select} FROM {$from} WHERE {$where}" );// WPCS: unprepared SQL OK.

		if ( ! empty( $response ) ) {
			foreach ( $response as $re ) {
				$display_value = $re->facet_display_value;
				if ( function_exists( 'pll_translate_string' ) ) {
					$current_lang = pll_current_language();
					$display_value = pll_translate_string( $display_value, $current_lang );
				}
				$display_value = apply_filters( 'lsx_search_facetwp_display_value', $display_value, $re->facet_value );
				$values[ $re->facet_value ] = $display_value;
			}
		}

		asort( $values );
		return $values;
	}

	/**
	 * Change FaceWP pagination HTML to be equal main pagination (WP-PageNavi)
	 */
	public function display_form_field( $type = 'select', $facet = array(), $values = array(), $combo = false ) {
		if ( empty( $facet ) ) {
			return;
		}

		$source = 'fwp_' . $facet['name'];

		switch ( $type ) {

			case 'select': ?>
				<div class="dropdown nav-item <?php if ( true === $combo ) { echo 'combination-dropdown'; } ?>">
					<button data-selection="0" class="btn border-btn btn-dropdown dropdown-toggle" type="button" id="<?php echo wp_kses_post( $source ); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<?php echo esc_attr( apply_filters( 'lsx_search_facet_label', __( 'Select', 'lsx-search' ) . ' ' . wp_kses_post( $facet['label'] ) ) ); ?>
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" aria-labelledby="<?php echo wp_kses_post( $source ); ?>">
						<?php if ( ! empty( $values ) ) { ?>

							<li style="display: none;">
								<a class="default" data-value="0" href="#">
									<?php
										$facet_label = __( 'Select ', 'lsx-search' ) . ' ' . wp_kses_post( $facet['label'] );
										$facet_label = apply_filters( 'lsx_search_facet_label', $facet_label );
										echo esc_attr( $facet_label );
									?>
								</a>
							</li>

							<?php foreach ( $values as $key => $value ) { ?>
								<li><a data-value="<?php echo wp_kses_post( $key ); ?>" href="#"><?php echo wp_kses_post( $value ); ?></a></li>
							<?php } ?>
						<?php } else { ?>
							<li><a data-value="0" href="#"><?php esc_attr_e( 'Please re-index your facets.', 'lsx-search' ); ?></a></li>
						<?php } ?>
					</ul>
				</div>
				<?php
				break;

			case 'datepicker': ?>
				<div class="datepicker nav-item">
					<input autocomplete="off" class="datepicker-value" placeholder="<?php echo wp_kses_post( apply_filters( 'lsx_search_facet_label' , $facet['label'] ) ); ?>" name="<?php echo wp_kses_post( $source ); ?>"  id="<?php echo wp_kses_post( $source ); ?>" type="text" value="" />
				</div>
			<?php
				break;
		}

		?>

	<?php }
}
