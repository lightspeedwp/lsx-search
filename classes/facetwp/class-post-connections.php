<?php
/**
 * LSX_Search_FacetWP_Hierarchy Frontend Main Class
 */

namespace lsx\search\classes\facetwp;

class Post_Connections {

	/**
	 * Holds class instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object \lsx\search\classes\facetwp\Post_Connections()
	 */
	protected static $instance = null;

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object \lsx\search\classes\facetwp\Post_Connections()    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
