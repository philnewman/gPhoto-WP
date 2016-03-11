<?php

function ptn_postPhoto(){
	if(!isset($_POST['UPLOAD FILES'])){		
		$TOKEN_EXPIRES		= get_option("pwaplusphp_token_expires");
		$now = date("U");
		if ($now > $TOKEN_EXPIRES) {
			ptn_refreshOAuth2Token();
		}
		$albumID = $_POST['ALBUM_ID'];
		$PICASAWEB_USER	 	= get_option("pwaplusphp_picasa_username");	
		$PICASAWEB_USER = strstr($PICASAWEB_USER,'@',true);
		$albumURL = 'https://picasaweb.google.com/data/feed/api/user/'.$PICASAWEB_USER.'/albumid/'.$albumID; 
		
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
			curl_setopt($ch, CURLOPT_POST);
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
			curl_close($ch);
		}
	}
}

function ptn_uploadPhotos($album){
	echo '
	<form id="upload" method="POST" enctype="multipart/form-data">
	<fieldset>
	<legend>HTML File Upload</legend>
	<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="300000" />
	<input type="hidden" id="ALBUM_ID" name="ALBUM_ID" value='.$album.' />
	<div>
		<label for="fileselect">Files to upload:</label>
		<input type="file" id="fileselect" name="fileselect[]" multiple="multiple" accept=".jpg,.gif,.png,.bmp"/>';
	echo '</div>
	<div id="submitbutton">
		<button type="submit">Upload Files</button>
	</div>
	</fieldset>
	</form>
	<div id="messages">
	<p>Status Messages</p>
	</div>
	';
}
add_action('template_redirect', 'ptn_postPhoto');

?>