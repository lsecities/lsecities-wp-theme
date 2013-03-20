<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 *
 * WARNING: Please do not edit this file in any way
 *
 * load the theme function files
 */

/**
 * Wrapper to require
 * Just require theme function files
 * 
 * @param string $file The function file to be required
 */
function require_fragment($file) {
  require(get_stylesheet_directory() . $file);
}

require_fragment('/includes/theme_configuration.php' );
require_fragment('/includes/hooks.php' );
require_fragment('/includes/functions.php' );
require_fragment('/includes/utility_functions.php' );

// Pods function files go below
require_fragment('/includes/pods/event/pods-event.php' );
