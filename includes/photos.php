<?php

// ----------------------------------------------------------------------------------------------------------
// Post photos to specified Google Photo album
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_postPhoto(){

// Error check here if $_POST and $_FILES are empty then
// the upload was too large for the server settings.
	echo '<pre>';
//	var_dump($_POST);
//	echo '</br>';
	var_dump($_FILES);
	echo '</pre>';


	if(!isset($_POST['UPLOAD FILES'])){
		$TOKEN_EXPIRES		= get_option("pwaplusphp_token_expires");
		$now = date("U");
		if ($now > $TOKEN_EXPIRES) {
			refreshOAuth2Token();
		}

		$albumID = $_POST['ALBUM_ID'];
		$albumURL = ptn_gPhoto_WP_getPhotoURL();
		$albumURL = $albumURL.'/albumid/'.$albumID;

		if (!empty($_FILES)){
			foreach ($_FILES['fileselect']['tmp_name'] as $imgName){
				// Get the binary image data
				$fileSize = filesize($imgName);
				$fh = fopen($imgName, 'rb');
				$imgData = fread($fh, $fileSize);
				$data = $imgData;
				fclose($fh);
				$ch = curl_init();
				$timeout = 0; // set to zero for no timeout
				curl_setopt($ch, CURLOPT_URL, $albumURL);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

				$GDATA_TOKEN = get_option("pwaplusphp_oauth_token");

				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Authorization: Bearer '. $GDATA_TOKEN,
					'Content-Type: image/jpeg',
					'Content-Length: '.$fileSize,
					'Slug: '.$imgName
					));
					$response = curl_exec($ch);
					$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					// if $code != 0 then error here
					curl_close($ch);
				}
		}
	}
}

// ----------------------------------------------------------------------------------------------------------
// Photo upload form
// ----------------------------------------------------------------------------------------------------------
function ptn_gPhoto_WP_UploadPhotos_shortcode($albumId = NULL){

//if (empty($albumId)){
		$albums = ptn_gPhoto_WP_getAlbums();
		$title = get_the_title();
		$albumId = ptn_gPhoto_WP_getAlbumIdByName($title, $albums);
//}

	echo '
	<form id="upload" method="POST" enctype="multipart/form-data">
	<fieldset>
	<legend>Upload up to 32M of photos to the '.$title.' album.</legend>
	<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="33554432" />
	<input type="hidden" id="ALBUM_ID" name="ALBUM_ID" value='.$albumId.' />
	<div>
		<label for="fileselect">Files to upload:</label>
		<input type="file" id="fileselect" name="fileselect[]" multiple="multiple" accept=".jpg,.gif,.png,.bmp"/>';
	echo '</div>
	<div id="submitbutton">
		<button type="submit">Upload Files</button>
	</div>
	</fieldset>
	</form>
	';
}

// ----------------------------------------------------------------------------------------------------------
// Redirect form POST to same page
// ----------------------------------------------------------------------------------------------------------
add_action('template_redirect', 'ptn_gPhoto_WP_postPhoto');

?>
