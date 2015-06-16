<?php
/*
Plugin Name: Lazy Widgets
Version: 0.1-alpha
Description: Lazy load widgets
Author: Dan Bissonnet <dan@danisadesigner.com>
Author URI: http://danisadesigner.com
Plugin URI: http://danisadesigner.com
Text Domain: dbisso-lazy-widgets
Domain Path: /languages
*/

if ( version_compare( phpversion(), '5.3', '<' ) ) {
	wp_die( 'This plugin requires PHP version 5.3 or higher' );
} else {
	$loader = include_once __DIR__ . '/vendor/autoload.php';

	// Call the bootstrap.
	include_once __DIR__ . '/src/Bootstrap.php';
}


