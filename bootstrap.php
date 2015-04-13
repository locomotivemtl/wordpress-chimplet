<?php

/**
 * Plugin Name: Chimplet
 * Plugin URI: https://github.com/locomotivemtl/wordpress-chimplet/
 * Description: Automatically synchronize Users, Categories and Posts to MailChimp Lists, Groups and Campaigns.
 * Version: 1.0.0
 * Author: Locomotive
 * Author URI: http://locomotive.ca
 * License: MIT
 * Text Domain: chimplet
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) { wp_die( __( 'Cheatin&#8217; uh?' ), 403 ); }

define( 'LOCOMOTIVE_CHIMPLET_ABS', plugin_basename( __FILE__ ) );
define( 'LOCOMOTIVE_CHIMPLET_DIR', plugin_dir_path( __FILE__ ) );
define( 'LOCOMOTIVE_CHIMPLET_URL', plugin_dir_url( __FILE__ ) );

require LOCOMOTIVE_CHIMPLET_DIR . 'vendor/autoload.php';

if ( class_exists( '\Locomotive\Chimplet\Application' ) ) {
	$chimplet = new Locomotive\Chimplet\Application;
	$chimplet->initialize( __FILE__ );
}
else {
	error_log( 'Chimplet could not include Composer’s autoloader.' );
}
