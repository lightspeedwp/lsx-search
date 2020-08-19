<?php
/**
 * LSX Search Main Class.
 *
 * @package lsx-search
 */
class LSX_Search {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object LSX_Search()
	 */
	protected static $instance = null;

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
		require_once LSX_SEARCH_PATH . '/classes/class-frontend.php';
		require_once LSX_SEARCH_PATH . '/classes/class-lsx-search-facetwp.php';
		require_once LSX_SEARCH_PATH . '/classes/class-lsx-search-shortcode.php';

		$this->admin     = \lsx\search\classes\Admin::get_instance();
		$this->frontend  = \lsx\search\classes\Frontend::get_instance();
		$this->facetwp   = new LSX_Search_FacetWP();
		$this->shortcode = new LSX_Search_Shortcode();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object LSX_Search()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
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

/**
 * Initiates the LSX Search Plugin
 * 
 * @return object LSX_Search();
 */
function lsx_search() {
	global $lsx_search;
	$lsx_search = LSX_Search::get_instance();
	return $lsx_search;
}
lsx_search();
