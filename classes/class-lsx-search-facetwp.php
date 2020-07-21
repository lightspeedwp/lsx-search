<?php
/**
 * LSX Search FacetWP filters and actions
 *
 * @package lsx-search
 */
class LSX_Search_FacetWP {

	/**
	 * @var object \lsx\search\classes\facetwp\Hierarchy()
	 */
	public $hierarchy;

	/**
	 * @var object \lsx\search\classes\facetwp\Post_Connections()
	 */
	public $post_connections;

	/**
	 * Class Constructor.
	 */
	public function __construct() {

		require_once LSX_SEARCH_PATH . '/classes/facetwp/class-hierarchy.php';
		$this->hierarchy = lsx\search\classes\facetwp\Hierarchy::get_instance();

		require_once LSX_SEARCH_PATH . '/classes/facetwp/class-post-connections.php';
		$this->post_connections = lsx\search\classes\facetwp\Post_Connections::get_instance();

		add_filter( 'facetwp_pager_html', array( $this, 'facetwp_pager_html' ), 10, 2 );
		add_filter( 'facetwp_result_count', array( $this, 'facetwp_result_count' ), 10, 2 );
		add_filter( 'facetwp_facet_html', array( $this, 'facetwp_slide_html' ), 10, 2 );
		add_filter( 'facetwp_load_css', array( $this, 'facetwp_load_css' ), 10, 1 );
		add_filter( 'facetwp_index_row', array( $this, 'index_row' ), 10, 2 );
	}

	/**
	 * Change FaceWP pagination HTML to be equal LSX pagination.
	 */
	public function facetwp_pager_html( $output, $params ) {
		$output = '';
		$page = (int) $params['page'];
		$per_page = (int) $params['per_page'];
		$total_pages = (int) $params['total_pages'];

		if ( 1 < $total_pages ) {
			$output .= '<div class="lsx-pagination-wrapper facetwp-custom">';
			$output .= '<div class="lsx-pagination">';
			// $output .= '<span class="pages">Page '. $page .' of '. $total_pages .'</span>';

			if ( 1 < $page ) {
				$output .= '<a class="prev page-numbers facetwp-page" rel="prev" data-page="' . ( $page - 1 ) . '">«</a>';
			}

			$temp = false;

			for ( $i = 1; $i <= $total_pages; $i++ ) {
				if ( $i == $page ) {
					$output .= '<span class="page-numbers current">' . $i . '</span>';
				} elseif ( ( $page - 2 ) < $i && ( $page + 2 ) > $i ) {
					$output .= '<a class="page-numbers facetwp-page" data-page="' . $i . '">' . $i . '</a>';
				} elseif ( ( $page - 2 ) >= $i && $page > 2 ) {
					if ( ! $temp ) {
						$output .= '<span class="page-numbers dots">...</span>';
						$temp = true;
					}
				} elseif ( ( $page + 2 ) <= $i && ( $page + 2 ) <= $total_pages ) {
					$output .= '<span class="page-numbers dots">...</span>';
					break;
				}
			}

			if ( $page < $total_pages ) {
				$output .= '<a class="next page-numbers facetwp-page" rel="next" data-page="' . ( $page + 1 ) . '">»</a>';
			}

			$output .= '</div>';
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Change FaceWP result count HTML.
	 */
	public function facetwp_result_count( $output, $params ) {
		$output = $params['total'];
		return $output;
	}

	/**
	 * Change FaceWP slider HTML.
	 */
	public function facetwp_slide_html( $html, $args ) {
		if ( 'slider' === $args['facet']['type'] ) {
			$html = str_replace( 'class="facetwp-slider-reset"', 'class="btn btn-md facetwp-slider-reset"', $html );
		}

		return $html;
	}

	/**
	 * Change FaceWP slider HTML.
	 */
	public function facetwp_counts_html( $html, $args ) {
		if ( 'slider' === $args['facet']['type'] ) {
			$html = str_replace( 'class="facetwp-slider-reset"', 'class="btn btn-md facetwp-slider-reset"', $html );
		}
		return $html;
	}

	/**
	 * Disable FacetWP styles.
	 */
	public function facetwp_load_css( $boolean ) {
		$boolean = false;
		return $boolean;
	}

	/**
	 * Get the price including the tax
	 * @param $params
	 * @param $class
	 *
	 * @return mixed
	 */
	public function index_row( $params, $class ) {
		// Custom woo fields
		if ( 0 === strpos( $params['facet_source'], 'woo' ) ) {
			$product = wc_get_product( $params['post_id'] );

			// Price
			if ( in_array( $params['facet_source'], array( 'woo/price', 'woo/sale_price', 'woo/regular_price' ) ) ) {
				$price = $params['facet_value'];
				if ( $product->is_taxable() ) {
					$price = wc_get_price_including_tax( $product );
				}
				$params['facet_value']    = $price;
				$params['facet_display_value'] = $price;

			}
		}
		return $params;
	}

}
