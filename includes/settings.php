<?php

	function ptn_getPhotoURL(){
		$PICASAWEB_USER	= get_option("pwaplusphp_picasa_username");	
		$PICASAWEB_USER = strstr($PICASAWEB_USER,'@',true);
		$file = 'https://picasaweb.google.com/data/feed/api/user/'.$PICASAWEB_USER;
		return $file;
	}
?>