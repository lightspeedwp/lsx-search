<?php
/*
 * Plugin Name:	LSX Search
 * Plugin URI:	https://github.com/lightspeeddevelopment/lsx-search
 * Description:	The LSX Search extension improves the search result pages for LSX Theme.
 * Author:		LightSpeed
 * Version: 	1.5.7
 * Author URI: 	https://www.lsdev.biz/
 * License: 	GPL3
 * Text Domain: lsx-search
 * Domain Path: /languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'LSX_SEARCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSX_SEARCH_CORE', __FILE__ );
define( 'LSX_SEARCH_URL', plugin_dir_url( __FILE__ ) );
define( 'LSX_SEARCH_VER', '1.5.7' );

/* ======================= Below is the Plugin Class init ========================= */

require_once LSX_SEARCH_PATH . '/includes/template-tags.php';
require_once LSX_SEARCH_PATH . '/includes/functions.php';
require_once LSX_SEARCH_PATH . '/classes/class-lsx-search.php';
