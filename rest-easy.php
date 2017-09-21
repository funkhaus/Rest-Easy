<?php
/**
 * Plugin Name: Rest Easy
 * Plugin URI:  http://funkhaus.us/
 * Description: Rest-ify your site with zero effort and powerful customization.
 * Version:     1.0
 * Author:      John Robson, Funkhaus
 * Author URI:  http://funkhaus.us
 */

! defined( 'ABSPATH' ) and exit;

// Helper function to always reference this directory (rest-easy plugin directory)
if ( ! function_exists( 're_pd' ) ) {
    function re_pd() {
        return trailingslashit( dirname( __FILE__ ) );
    }
}

// include core functionality
include_once re_pd() . 'utils.php';
include_once re_pd() . 'builders.php';
include_once re_pd() . 'serializers/index.php';
include_once re_pd() . 'core.php';
