<?php
/**
 * LSX Search Main Class.
 *
 * @package lsx-search
 */
class LSX_Search {

	/**
	 * @var LSX_Search_Admin()
	 */
	public $admin;

	/**
	 * @var LSX_Search_Frontend()
	 */
	public $frontend;

	/**
	 * @var LSX_Search_FacetWP()
	 */
	public $facetwp;

	/**
	 * @var LSX_Search_Shortcode()
	 */
	public $shortcode;

	/**
	 * LSX_Search constructor
	 */
	public function __construct() {
		require_once( LSX_SEARCH_PATH . '/classes/class-lsx-search-admin.php' );
		require_once( LSX_SEARCH_PATH . '/classes/class-lsx-search-frontend.php' );
		require_once( LSX_SEARCH_PATH . '/classes/class-lsx-search-facetwp.php' );
		require_once( LSX_SEARCH_PATH . '/classes/class-lsx-search-shortcode.php' );
		$this->admin = new LSX_Search_Admin();
		$this->frontend = new LSX_Search_Frontend();
		$this->facetwp = new LSX_Search_FacetWP();
		$this->shortcode = new LSX_Search_Shortcode();
	}

}

global $lsx_search;
$lsx_search = new LSX_Search();
