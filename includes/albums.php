<?php

// ----------------------------------------------------------------------------------------------------------
// Get a list of album IDs and Names from selected Google Photo Account
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_getAlbums(){	
	$file = ptn_getPhotoURL();
	$file = $file.'?kind=album&fields=entry(id,title)';
	$xml = simplexml_load_file($file);
	foreach ($xml as $album){
		$album->id = basename($album->id);
	}
	return $xml;
}

// ----------------------------------------------------------------------------------------------------------
// Display album ID and Name in a list - primarily used for debugging
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_displayAlbums($albumArray){
	foreach($albumArray as $album){
		print $album->id.' '.$album->title;
		print '<br>';
	}
}

// ----------------------------------------------------------------------------------------------------------
// Get album ID by album name
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_getAlbumIdByName($albumName, $albumArray){
	foreach ($albumArray as $album){
		if ($album->title == $albumName){
			return $album->id;
		}
	}
	return false;
}

// ----------------------------------------------------------------------------------------------------------
// Get album name from album ID
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_getAlbumNameById($albumId, $albumArray){
	foreach ($albumArray as $album){
		if ($album->id == $albumId){
			return $album->title;
		}
	}
	return false;
}

// ----------------------------------------------------------------------------------------------------------
// Determine if album with this name already exists
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_duplicateAlbumCk($title, $albumArray){
	foreach ($albumArray as $album){
		if ($album->title == $title){
			return true;
		}
	}
	return false;
}

// ----------------------------------------------------------------------------------------------------------
// Create a new Google Photo Album
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_createAlbum($file, $title){
	$TOKEN_EXPIRES		= get_option("pwaplusphp_token_expires");
	$now = date("U");
	if ($now > $TOKEN_EXPIRES) {
		refreshOAuth2Token();
	}	
	$message_body = 	
			"<entry xmlns='http://www.w3.org/2005/Atom'
		    	xmlns:media='http://search.yahoo.com/mrss/'
		    	xmlns:gphoto='http://schemas.google.com/photos/2007'>
		  		<title type='text'>".$title."</title>
		  			<summary type='text'></summary>
		  			<gphoto:location></gphoto:location>
		  			<gphoto:access>public</gphoto:access>
		  			<gphoto:timestamp>".$now."</gphoto:timestamp>
		  			<media:group>
		    		<media:keywords></media:keywords>
		  			</media:group>
		  			<category scheme='http://schemas.google.com/g/2005#kind'
		    	term='http://schemas.google.com/photos/2007#album'></category>
				</entry>";
	$ch = curl_init();
	$timeout = 0; // set to zero for no timeout
	curl_setopt($ch, CURLOPT_URL, $file);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_POST);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $message_body);

	$GDATA_TOKEN = get_option("pwaplusphp_oauth_token");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
             'Authorization: Bearer ' . $GDATA_TOKEN, 
			 'Content-Type: application/atom+xml'
            ));
	$response = curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 	
	curl_close($ch);
	return($code);
}

// ----------------------------------------------------------------------------------------------------------
// Present from to create a new album
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_CreateAlbum_shortcode(){
		echo '
		<form id="upload" method="POST" enctype="multipart/form-data">
		<fieldset>
		<legend>Create a new Google Photo Album</legend>
		<div>
			<label for="albumname">Album name:</label>
			<input type="text" id="albumname" name="albumname" />';
		echo '</div>
		<div id="submitbutton">
			<button type="submit">Create this Album</button>
		</div>
		</fieldset>
		</form>
		';

}

function ptn_gPhoto_WP_validateAlbumForm(){
	$albums = ptn_gPhoto_WP_getAlbums();
	$title = $_POST['albumname'];
	
	if (ptn_duplicateAlbumCk($title, $albums)){
		$ptn_createAlbumStatus = ptn_gPhoto_WP_createAlbum($file, $title);
		// ERROR
		// RESET FORM
	}else{
		$file =ptn_gPhoto_WP_getPhotoURL();
		ptn_gPhoto_WP_createAlbum($file, $title);
		$post = array(
			'post_title' => $title,
			'post_type' => 'page',
			'post_content' => 'Please use this page to upload photos to the '.$title.'album.</br></br>[UploadPhotos]',
			'post_status' => 'publish'
		);
		$error = true;
		wp_insert_post($post, $error);
		$page = get_page_by_title($title, '', 'page');
		$link = get_permalink($page->ID);	
		wp_redirect($link);
	}
}

// ----------------------------------------------------------------------------------------------------------
// Redirect form POST to same page
// ----------------------------------------------------------------------------------------------------------
add_action('template_redirect', 'ptn_gPhoto_WP_validateAlbumForm');

?>
