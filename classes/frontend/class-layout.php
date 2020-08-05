<?php
namespace lsx\search\classes\frontend;

use LSX_Search;

/**
 * Houses the functions for the CMB2 Settings page.
 *
 * @package lsx-search
 */
class Layout {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx\search\classes\frontend\Layout()
	 */
	protected static $instance = null;

	/**
	 * Contructor
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'load_functions' ), 24 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\search\classes\frontend\Layout()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Check all settings.
	 */
	public function load_functions() {
		$lsx_search = LSX_Search::get_instance();
		if ( $lsx_search->frontend->search_enabled ) {
			if ( isset( $lsx_search->frontend->options['display'][ $lsx_search->frontend->search_prefix . '_layout_switcher_enable' ] ) ) {
				add_filter( 'lsx_blog_customizer_show_switcher', array( $this, 'show_layout_switcher' ), 10, 1 );
				add_filter( 'lsx_layout_switcher_options', array( $this, 'lsx_layout_switcher_options' ), 10, 1 );
				add_filter( 'lsx_layout_switcher_page_key', array( $this, 'lsx_layout_switcher_page_key' ), 10, 1 );
				add_filter( 'lsx_layout_switcher_options_default', array( $this, 'lsx_layout_switcher_options_default' ), 10, 1 );
			}
		}
	}

	/**
	 * Display the woocommerce archive swticher.
	 */
	public function show_layout_switcher( $show = false ) {
		$show = true;
		return $show;
	}

	/**
	 * Remove the default and half-grid options from the results layouts.
	 *
	 * @param  array $layout_options
	 * @return array
	 */
	public function lsx_layout_switcher_options( $layout_options ) {
		unset( $layout_options['default'] );
		unset( $layout_options['half-grid'] );
		return $layout_options;
	}

	/**
	 * Replace the key for the layout switcher.
	 *
	 * @param  string $page_key
	 * @return string
	 */
	public function lsx_layout_switcher_page_key( $page_key ) {
		$lsx_search = LSX_Search::get_instance();
		$page_key   = str_replace( '_search', '', $lsx_search->frontend->search_prefix );
		return $page_key;
	}

	/**
	 * CHange the default layout to a grid layout.
	 *
	 * @param  string $default
	 * @return string
	 */
	public function lsx_layout_switcher_options_default( $default = 'grid' ) {
		$lsx_search = LSX_Search::get_instance();
		$default    = 'grid';
		if ( isset( $lsx_search->frontend->options['display'][ $lsx_search->frontend->search_prefix . '_grid_list' ] ) && ! empty( $lsx_search->frontend->options['display'][ $lsx_search->frontend->search_prefix . '_grid_list' ] ) ) {
			$default = $lsx_search->frontend->options['display'][ $lsx_search->frontend->search_prefix . '_grid_list' ];
		}
		return $default;
	}
}
