<?php
/*
Plugin Name: 	gPhoto-WP
Plugin URI: 	
Description:	
Author: 	Phil Newman
Version:	0.1
Author URI: 	
*/

define( 'WP_DEBUG', true );
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// ----------------------------------------------------------------------------------------------------------
// Ensure that PWA+PHP is installed
// ----------------------------------------------------------------------------------------------------------

function gPhoto_WP_activate() {
	$PWAPLUSPHP = 'pwaplusphp/pwaplusphp.php';
	if (!is_plugin_active($PWAPLUSPHP)){
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die('<a href="https://wordpress.org/plugins/pwaplusphp/"> pwaplusphp is required.</a>');
	}
}
register_activation_hook( __FILE__, 'gPhoto_WP_activate' );


// ----------------------------------------------------------------------------------------------------------
// Create a page with the photo upload form
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_PhotoUpload_shortcode( $atts, $content = null ) {
		
	$albums = ptn_getAlbums();	
	$title = get_the_title();	
	$albumId = ptn_getAlbumIdByName($title, $albums);
	ptn_uploadPhotos($albumId);
	
} // end shortcode

// ----------------------------------------------------------------------------------------------------------
// Create a new Google Photo Album with WP-Upload Page
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_CreateAlbum_shortcode(){
	echo "In create album shortcode";
/*
		b-create WP-page w/ [ptnpicasa] shortcode
	3 - if dup 
		a-error message
		b-reset form
	*/
}

// ----------------------------------------------------------------------------------------------------------
// Includes
// ----------------------------------------------------------------------------------------------------------
require_once(dirname(__FILE__)."/includes/albums.php");
require_once(dirname(__FILE__).'/includes/photos.php');
require_once(dirname(__FILE__).'/includes/settings.php');

// ----------------------------------------------------------------------------------------------------------
// Actions, Filters and Shortcodes
// ----------------------------------------------------------------------------------------------------------
add_action('admin_menu', 'ptnpicasa_menu');
add_filter('widget_text', 'do_shortcode');
add_shortcode('UploadPhotos', 'ptn_gPhoto_WP_PhotoUpload_shortcode');
//add_shortcode('CreateAlbum', 'ptn_gPhoto_WP_CreateAlbum_shortcode');
add_shortcode('CreateAlbum', 'ptn_createAlbumForm');


?>