<?php
/**
 * LSX Search Admin Class.
 *
 * @package lsx-search
 */
class LSX_Search_Admin {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	public function assets() {
		//wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_script( 'lsx-search-admin', LSX_SEARCH_URL . 'assets/js/lsx-search-admin.min.js', array( 'jquery' ), LSX_SEARCH_VER, true );
		wp_enqueue_style( 'lsx-search-admin', LSX_SEARCH_URL . 'assets/css/lsx-search-admin.css', array(), LSX_SEARCH_VER );
	}

}

global $lsx_search_admin;
$lsx_search_admin = new LSX_Search_Admin();
