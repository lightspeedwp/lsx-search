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
		$this->load_vendors();

		require_once LSX_SEARCH_PATH . '/classes/class-admin.php';
		require_once LSX_SEARCH_PATH . '/classes/class-lsx-search-frontend.php';
		require_once LSX_SEARCH_PATH . '/classes/class-lsx-search-facetwp.php';
		require_once LSX_SEARCH_PATH . '/classes/class-lsx-search-shortcode.php';

		$this->admin     = \lsx\search\classes\Admin::get_instance();
		$this->frontend  = new LSX_Search_Frontend();
		$this->facetwp   = new LSX_Search_FacetWP();
		$this->shortcode = new LSX_Search_Shortcode();
	}

	/**
	 * Loads the plugin functions.
	 */
	private function load_vendors() {
		// Configure custom fields.
		if ( ! class_exists( 'CMB2' ) ) {
			require_once LSX_SEARCH_PATH . 'vendor/CMB2/init.php';
		}
	}
}

global $lsx_search;
$lsx_search = new LSX_Search();
