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
// Create a page with the photo upload form
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_PhotoUpload_shortcode( $atts, $content = null ) {
	
	$PICASAWEB_USER	= get_option("pwaplusphp_picasa_username");	
	$PICASAWEB_USER = strstr($PICASAWEB_USER,'@',true);
	$file = 'https://picasaweb.google.com/data/feed/api/user/'.$PICASAWEB_USER;
	
	$albums = ptn_getAlbums($file);	
	$title = get_the_title();	
	if (!ptn_duplicateAlbumCk($title, $albums)){
		echo 'Creating album: '.$title.'</br>';
		$ptn_createAlbumStatus = ptn_createAlbum($file, $title);
	}
	$albumId = ptn_getAlbumIdByName($title, $albums);
	ptn_uploadPhotos($albumId);
	
} // end shortcode

// ----------------------------------------------------------------------------------------------------------
// Create a new Google Photo Album with WP-Upload Page
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_CreateAlbum_shortcode(){
	echo "In create album shortcode";
	/* Form for input of new album
	1 - check for dups
	2 - if not dup
		a-create album
		b-create WP-page w/ [ptnpicasa] shortcode
	3 - if dup 
		a-error message
		b-reset form
	*/
}


// ----------------------------------------------------------------------------------------------------------
// gPhoto-WP-Plugin Installer
// ----------------------------------------------------------------------------------------------------------
function ptnpicasa_options() {
  echo '<div class="wrap">';
  require_once(dirname(__FILE__).'/install.php');
  echo '</div>';
}


/// ----------------------------------------------------------------------------------------------------------
// Includes
// ----------------------------------------------------------------------------------------------------------
require_once(dirname(__FILE__).'/includes/settings.php');
require_once(dirname(__FILE__)."includes/albums.php";
require_once(dirname(__FILE__).'/includes/photos.php');

// ----------------------------------------------------------------------------------------------------------
// Actions, Filters and Shortcodes
// ----------------------------------------------------------------------------------------------------------
add_action('admin_menu', 'ptnpicasa_menu');
add_filter('widget_text', 'do_shortcode');
add_shortcode('UploadPhotos', 'ptn_gPhoto_WP_PhotoUpload_shortcode');
add_shortcode('CreateAlbum', 'ptn_gPhoto_WP_CreateAlbum_shortcode');

?>
