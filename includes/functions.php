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

/**
 * Returns the Post types that are specifically not supported.
 * 
 * @return array
 */
function get_restricted_post_types() {
	$post_types = array(
		'page',
		'attachment',
		'lesson',
		'certificate',
		'envira',
		'tribe_organizer',
		'tribe_venue',
		'envira',
		'reply',
		'topic',
		'popup',
		'question',
		'certificate_template',
		'sensei_message',
		'tribe_events',
		'tip',
		'quiz',
		'forum',
	);
	$post_types = apply_filters( 'lsx_search_restricted_post_types', $post_types );
	return $post_types;
}
