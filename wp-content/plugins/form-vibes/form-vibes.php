<?php

/*
Plugin Name: Form Vibes
Plugin URI: https://formvibes.com
Description: Lead Management and Graphical Reports for Elementor Pro, Contact Form 7 & Caldera form submissions.
Author: WPVibes
Version: 1.4.9
Author URI: https://wpvibes.com/
Text Domain: wpv-fv
License: GPLv2
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !defined( 'WPV_FV__PATH' ) ) {
    define( 'WPV_FV__VERSION', '1.4.9' );
    // recommended pro version for free
    // define( 'WPV_FV__PRO_RECOMMENDED_VERSION', '0.5' );
    define( 'WPV_FV__URL', plugins_url( '/', __FILE__ ) );
    define( 'WPV_FV__PATH', plugin_dir_path( __FILE__ ) );
    define( 'WPV_FV_PLUGIN_BASE', plugin_basename( __FILE__ ) );
    
    if ( !defined( 'WPV_PRO_FV_VERSION' ) ) {
        // maintain
        define( 'WPV_PRO_FV_VERSION', '1.4.7' );
        define( 'WPV_FV_MIN_VERSION', '1.3.6' );
    }

}

add_action( 'plugins_loaded', function () {
    require_once WPV_FV__PATH . '/vendor/autoload.php';
    require_once WPV_FV__PATH . '/inc/bootstrap.php';
    FormVibes\Classes\DbTables::fv_plugin_activated();
} );