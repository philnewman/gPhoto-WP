<?php
/*
Plugin Name: 	gPhoto-WP
Plugin URI:
Description:
Author: 	Phil Newman
Version:	0.1
Author URI:
*/

// ----------------------------------------------------------------------------------------------------------
// Ensure that PWA+PHP is installed
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_activate() {
	$PWAPLUSPHP = 'pwaplusphp/pwaplusphp.php';
	if (!is_plugin_active($PWAPLUSPHP)){
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die('<a href="https://wordpress.org/plugins/pwaplusphp/"> pwaplusphp is required.</a>');
	}
}
register_activation_hook( __FILE__, 'ptn_gPhoto_WP_activate' );

// ----------------------------------------------------------------------------------------------------------
// Includes
// ----------------------------------------------------------------------------------------------------------
require_once(dirname(__FILE__)."/includes/albums.php");
require_once(dirname(__FILE__).'/includes/photos.php');
require_once(dirname(__FILE__).'/includes/settings.php');

// ----------------------------------------------------------------------------------------------------------
// Actions, Filters and Shortcodes
// ----------------------------------------------------------------------------------------------------------
add_filter('widget_text', 'do_shortcode');
add_shortcode('UploadPhotos', 'ptn_gPhoto_WP_UploadPhotos_shortcode');
add_shortcode('CreateAlbum', 'ptn_gPhoto_WP_CreateAlbum_shortcode');

?>
