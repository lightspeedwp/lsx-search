<?php
/**
 * LSX_Search_FacetWP_Hierarchy Frontend Main Class
 */

class LSX_Search_FacetWP_Hierarchy {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'facetwp_facet_html', array( $this, 'checkbox_facet_html' ), 100, 2 );
	}

	public function checkbox_facet_html( $output, $params ) {
		if ( 'checkboxes' === $params['facet']['type'] && 'yes' === $params['facet']['hierarchical'] ) {
			$output = $this->render_hierarchy( $params );
		}
		return $output;
	}

	/**
	 * Generate the facet HTML (hierarchical taxonomies)
	 */
	function render_hierarchy( $params ) {

		$output = '';
		$facet = $params['facet'];
		$selected_values = (array) $params['selected_values'];
		$values = FWP()->helper->sort_taxonomy_values( $params['values'], $facet['orderby'] );

		$init_depth = -1;
		$last_depth = -1;

		foreach ( $values as $result ) {
			$depth = (int) $result['depth'];

			/*if ( -1 == $last_depth ) {
				$init_depth = $depth;
			}
			elseif ( $depth > $last_depth ) {
				$output .= '<div class="facetwp-depth">';
			}
			elseif ( $depth < $last_depth ) {
				for ( $i = $last_depth; $i > $depth; $i-- ) {
					$output .= '</div>';
				}
			}*/

			$selected = in_array( $result['facet_value'], $selected_values ) ? ' checked' : '';
			$selected .= ( 0 == $result['counter'] && '' == $selected ) ? ' disabled' : '';

			$is_child = ( 0 == $result['parent_id'] && '0' == $result['parent_id'] ) ? ' is-child' : '';
			$depth_css = ' depth-' . $result['depth'];

			$output .= '<div class="facetwp-checkbox' . $selected . $is_child . $depth_css . '" data-parent-id="' . esc_attr( $result['parent_id'] ) . '" data-value="' . esc_attr( $result['facet_value'] ) . '">';
			$output .= esc_html( $result['facet_display_value'] ) . ' <span class="facetwp-counter">(' . $result['counter'] . ')</span>';
			$output .= '</div>';

			$last_depth = $depth;
		}

		for ( $i = $last_depth; $i > $init_depth; $i-- ) {
			$output .= '</div>';
		}

		return $output;
	}
}
