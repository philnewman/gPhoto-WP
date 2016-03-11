<?php

function ptn_refreshOAuth2Token() {
	$DEBUG = 0;
    $now = date("U");
    $clientId = get_option("pwaplusphp_client_id");
    $clientSecret = get_option("pwaplusphp_client_secret");
    $refreshToken = get_option("pwaplusphp_refresh_token");
    $postBody = 'client_id='.urlencode($clientId)
              .'&client_secret='.urlencode($clientSecret)
              .'&refresh_token='.urlencode($refreshToken)
              .'&grant_type=refresh_token';
          
    $curl = curl_init();
    curl_setopt_array( $curl,
                     array( CURLOPT_CUSTOMREQUEST => 'POST'
                           , CURLOPT_URL => 'https://www.googleapis.com/oauth2/v3/token'
                           , CURLOPT_HTTPHEADER => array( 'Content-Type: application/x-www-form-urlencoded'
                                                         , 'Content-Length: '.strlen($postBody)
                                                         , 'User-Agent: HoltstromLifeCounter/0.1 +http://holtstrom.com/michael'
                                                         )
                           , CURLOPT_POSTFIELDS => $postBody                              
                           , CURLOPT_REFERER => $GOOGLE_OAUTH2_REFERER
                           , CURLOPT_RETURNTRANSFER => 1 // means output will be a return value from curl_exec() instead of simply echoed
                           , CURLOPT_TIMEOUT => 12 // max seconds to wait
                           , CURLOPT_FOLLOWLOCATION => 0 // don't follow any Location headers, use only the CURLOPT_URL, this is for security
                           , CURLOPT_FAILONERROR => 0 // do not fail verbosely fi the http_code is an error, this is for security
                           , CURLOPT_SSL_VERIFYPEER => 1 // do verify the SSL of CURLOPT_URL, this is for security
                           , CURLOPT_VERBOSE => 0 // don't output verbosely to stderr, this is for security
                     ) );
    $orig_response = curl_exec($curl);
    $response = json_decode($orig_response, true); // convert returned objects into associative arrays
    $token_expires = $now + $response['expires_in'];
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($response['access_token']) {
		if ($DEBUG) {
			echo "DEBUG: [refreshOAuth2Token] got the following response:</p>";
			echo "DEBUG: [refreshOAuth2Token] $orig_response </p>";
			echo "DEBUG: [refreshOAuth2Token] using refreshToken $refreshToken</p>";
		}
        update_option("pwaplusphp_oauth_token",$response['access_token']);          # save the access token
        update_option("pwaplusphp_token_expires",$token_expires);                   # save the epoch when the token expires
    } else {
        echo "refreshOAuth2Token got the following response:<br />";
        echo $orig_response;
		echo "using refreshToken $refreshToken";
    }

}




?>