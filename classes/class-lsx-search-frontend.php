<?php
/**
 * LSX Search Frontend Class.
 *
 * @package lsx-search
 */
class LSX_Search_Frontend {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 999 );
	}

	public function assets() {
		wp_enqueue_script( 'lsx-search', LSX_SEARCH_URL . 'assets/js/lsx-search.min.js', array( 'jquery' ), LSX_SEARCH_VER, true );

		$params = apply_filters( 'lsx_search_js_params', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		));

		wp_localize_script( 'lsx-search', 'lsx_customizer_params', $params );

		wp_enqueue_style( 'lsx-search', LSX_SEARCH_URL . 'assets/css/lsx-search.css', array(), LSX_SEARCH_VER );
		wp_style_add_data( 'lsx-search', 'rtl', 'replace' );
	}

}

global $lsx_search_frontend;
$lsx_search_frontend = new LSX_Search_Frontend();
