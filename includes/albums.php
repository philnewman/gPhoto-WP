<?php

/*

- Get album list
- Create new album
- Lookup name by ID
- Lookup ID by name 

*/

function ptn_getAlbums($file){
	$file = $file.'?kind=album&fields=entry(id,title)';
	$xml = simplexml_load_file($file);
	foreach ($xml as $album){
		$album->id = basename($album->id);
	}
	return $xml;
}

function ptn_displayAlbums($albumArray){
	foreach($albumArray as $album){
		print $album->id.' '.$album->title;
		print '<br>';
	}
}

function ptn_getAlbumIdByName($albumName, $albumArray){
	foreach ($albumArray as $album){
		if ($album->title == $albumName){
			return $album->id;
		}
	}
	return false;
}

function ptn_getAlbumNameById($albumId, $albumArray){
	foreach ($albumArray as $album){
		if ($album->id == $albumId){
			return $album->title;
		}
	}
	return false;
}

function ptn_duplicateAlbumCk($title, $albumArray){
	foreach ($albumArray as $album){
		if ($album->title == $title){
			return true;
		}
	}
	return false;
}

function ptn_createAlbum($file, $title){
	$TOKEN_EXPIRES		= get_option("pwaplusphp_token_expires");
	$now = date("U");
	if ($now > $TOKEN_EXPIRES) {
		ptn_refreshOAuth2Token();
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

?>
