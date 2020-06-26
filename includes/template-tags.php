<?php
/**
 * LSX Search functions.
 *
 * @package lsx-search
 */

/**
 * Adds text domain.
 */
function lsx_search_load_plugin_textdomain() {
	load_plugin_textdomain( 'lsx-search', false, basename( LSX_SEARCH_PATH ) . '/languages' );
}
add_action( 'init', 'lsx_search_load_plugin_textdomain' );
