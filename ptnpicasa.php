<?php
/*
Plugin Name: 	PTNPicasa
Plugin URI: 	
Description:	
Author: 	Phil Newman
Version:	0.1
Author URI: 	
*/

/* Hook to delete PWA+PHP */
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'pwaplusphp_remove');
 
// ----------------------------------------------------------------------------------------------------------
// Setup the shortcode
// ----------------------------------------------------------------------------------------------------------
function ptnpicasa_shortcode( $atts, $content = null ) {
	
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
function ptnpicasaCreateAlbum_shortcode(){
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

/* Installer / Options page */
function ptnpicasa_options() {
  echo '<div class="wrap">';
  require_once(dirname(__FILE__).'/install.php');
  echo '</div>';
}

/* Define Option settings */
function ptnpicasa_menu() {
  add_options_page('PTNpicasa Options', 'PTN Picasa', 'administrator', 'ptnpicasa', 'ptnpicasa');
}

/* Includes */
require_once(dirname(__FILE__).'/includes/settings.php');
require_once plugin_dir_path(__FILE__)."includes/albums.php";
require_once(dirname(__FILE__).'/includes/photos.php');

/* Add actions, filters and shortcodes */
add_action('admin_menu', 'ptnpicasa_menu');
add_filter('widget_text', 'do_shortcode');
add_shortcode('ptnpicasa', 'ptnpicasa_shortcode');
add_shortcode('ptnpicasaCreateAlbum', 'ptnpicasaCreateAlbum_shortcode');

?>
