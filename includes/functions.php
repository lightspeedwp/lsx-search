<?php
/**
 * LSX Search functions.
 *
 * @package lsx-search
 */

namespace lsx\search\includes;

/**
 * Gets the lsx search options.
 *
 * @return array
 */
function get_options() {
	$options = array();
	if ( function_exists( 'tour_operator' ) ) {
		$options = get_option( '_lsx-to_settings', false );
	} else {
		$options = get_option( '_lsx_settings', false );

		if ( false === $options ) {
			$options = get_option( '_lsx_lsx-settings', false );
		}
	}
	return $options;
}
