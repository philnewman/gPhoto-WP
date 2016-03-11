<?PHP

error_reporting(E_ERROR | E_WARNING | E_PARSE);

echo "<div class='wrap'>";
echo "<div id='icon-plugins' class='icon32'></div><h2>PWA+PHP Plugin Settings</h2><br />";
if ($_GET['loc'] == "finish") {
	echo "<div style='width: 71%; margin: 0px 0px 0px 20px; padding: 5px; background-color: #ffffcc; border: #e6e6e6 1px solid;'>Configuration is complete and PWA+PHP is ready for use. Create a page with contents \"[pwaplusphp]\" to see your albums.</div>";
}
echo "<table cellspacing=20><tr><td width='75%' valign=top>";

function step_1_create_project() {
	$site_url = site_url();
	$settings_url = $site_url . "/wp-admin/options-general.php?page=pwaplusphp";
	$site = $_SERVER['SERVER_NAME'];
        $port = ($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '';
        $self  = $_SERVER['PHP_SELF'];
        $js_origins = "http://" . $site . $port;
	echo "<h2>Install Step 1: Create Project</h2>";
	echo "<p>As of April 20th, 2015, Google no longer allows access to Picasa Web Albums using AuthSub authentication. Now we must use OAuth2, which requires you to create a project in the Google Developer Console.<p>";
	echo "<p>To create the project,<ol>";
	echo "<li>Head to the <a target='_BLANK' href='https://console.developers.google.com/project'>Google Developer Console</a> and click 'Create Project'";
	echo "<li>Give the project a name (i.e. pwaplusphp) and a project id (i.e. pwaplusphp). Click Create. Wait a few minutes.</li>";
	echo "<li>After it's done, click 'APIs & Auth' in the left sidebar, the underneath that, click 'Consent screen'.</li>";
	echo "<li>Select your email address from the dropdown and enter a Product Name, i.e. pwaplusphp. Click 'Save'.</li>";
	echo "<li>Above 'Consent Screen' in the sidebar, click 'Credentials'.</li>";
	echo "<li>On the page that comes up, click the 'Create new Client ID' button";
	echo "<li>For Application Type, select 'Web Application'.</li>";
	echo "<li>In the Authorized Javascript Origins box, enter: $js_origins </li>";
	echo "<li>In the Authorized Redirect URIs box, enter: $settings_url </li>";
	echo "<li>Click 'Create Client ID'</li>";
	echo "<li>Copy the 'Client ID' and 'Client Secret' or leave the window open</li>";
	echo "<li>Go to <a href='$settings_url&loc=step_2_project_creds'>Step 2</a>...";
	echo "</ol></p>";

}

function step_2_project_creds() {
	$site_url = site_url();
    $settings_url = $site_url . "/wp-admin/options-general.php?page=pwaplusphp";
	$next  = $settings_url . "&loc=start_oauth";
	$client_id = get_option("pwaplusphp_client_id");
    $client_secret = get_option("pwaplusphp_client_secret");
	echo "<h2>Install Step 2: Project Credentials</h2>";
	echo "<p>Now we need to enter this info for PWA+PHP to exchange it for an OAuth2 token.</p>";
	echo "<form id='project_creds' action='$next' method='GET'><table>";
	echo "<tr><td>Client ID</td><td><input style='width:400px;' name='client_id' id='client_id' value='$client_id'/></td></tr>";
	echo "<tr><td>Client Secret</td><td><input style='width:400px;' name='client_secret' id='client_secret' value='$client_secret'/></td></tr>";
	echo "</table>";
	echo "<input type='hidden' name='loc' value='step_3_start_oauth' />";
	echo "<input type='hidden' name='page' value='pwaplusphp' />";
	echo "<input type='submit' value='Go to Step 3' />";
	echo "</form>";
}


function step_3_start_oauth() {
	$site_url = site_url();
    $settings_url = $site_url . "/wp-admin/options-general.php?page=pwaplusphp";
	$client_id = $_GET['client_id'];
	$client_secret = $_GET['client_secret'];
	if ((!isset($client_id)) || (!isset($client_secret))) {
		$client_id = get_option("pwaplusphp_client_id");
		$client_secret = get_option("pwaplusphp_client_secret");
	}
	update_option("pwaplusphp_client_id",$client_id);          # save the access token
    update_option("pwaplusphp_client_secret",$client_secret);       # save the refresh token
    echo "<h2>Install Step 3: Token Generation</h2>";
    echo "<p>Generating this Google OAuth2 token is a one-time step that allows PWA+PHP to access to your private (unlisted) Picasa albums.</p>";
	echo "<p><strong>Verify the info below before clicking 'Request The Token'</strong></p>";
	echo "<table><tr><td>REDIRECT URIS:</td><td>$settings_url</td></tr>";
	echo "<tr><td>CLIENT ID:</td><td>$client_id</td></tr>";
	echo "<tr><td>CLIENT SECRET:</td><td>$client_secret</td></tr></table>";
	$settings_url = urlencode($settings_url);
	$next = "https://accounts.google.com/o/oauth2/auth?scope=https://picasaweb.google.com/data/&response_type=code&access_type=offline&redirect_uri=$settings_url&approval_prompt=force&client_id=$client_id";
        echo "<p>If this is correct, <a href='$next'>";
        echo "Request The Token</a>, then click 'Accept' on the page that comes up.</p>";
        echo "</body>\n</html>";
}

function step_4_set_token() {

    $site_url = site_url();
    $settings_url = $site_url . "/wp-admin/options-general.php?page=pwaplusphp";

    # THESE 2 COME FROM DB
    $client_id = get_option("pwaplusphp_client_id");
    $client_secret = get_option("pwaplusphp_client_secret");
    $now = date("U");
    $postBody = 'code='.urlencode($_GET['code'])
              .'&grant_type=authorization_code'
              .'&redirect_uri='.urlencode($settings_url)
              .'&client_id='.urlencode($client_id)
              .'&client_secret='.urlencode($client_secret);

    $curl = curl_init();
    curl_setopt_array( $curl,
                array( CURLOPT_CUSTOMREQUEST => 'POST'
                           , CURLOPT_URL => 'https://accounts.google.com/o/oauth2/token'
                           , CURLOPT_HTTPHEADER => array( 'Content-Type: application/x-www-form-urlencoded'
                                                         , 'Content-Length: '.strlen($postBody)
                                                         , 'User-Agent: PWA+PHP/0.1 +http://pwaplusphp.smccandl.net'
                                                         )
                           , CURLOPT_POSTFIELDS => $postBody                              
                           , CURLOPT_REFERER => $settings_url
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
	update_option("pwaplusphp_oauth_token",$response['access_token']);          # save the access token
        update_option("pwaplusphp_refresh_token",$response['refresh_token']);       # save the refresh token
        update_option("pwaplusphp_token_expires",$token_expires);                   # save the epoch when the token expires
	echo "<h2>Install Step 4: Complete!</h2>";
        echo "Token retrieved and saved in WordPress configuration database.<br />";
	$uri = $_SERVER["REQUEST_URI"];
        list($back_link,$uri_tail) = split('&',$uri);
        echo "Continue to <a href='$back_link'>the final step: Settings</a>...\n";
    } else {
	echo "<h2>Install Step 4: Failed!</h2>";
	echo "Got the following response:<br />";
	echo $orig_response;
    }
}

function get_options() {

$GDATA_TOKEN		= get_option("pwaplusphp_oauth_token");
$PICASAWEB_USER         = get_option("pwaplusphp_picasa_username");
$IMGMAX                 = get_option("pwaplusphp_image_size","640");
$GALLERY_THUMBSIZE      = get_option("pwaplusphp_thumbnail_size",160);
$ALBUM_THUMBSIZE      	= get_option("pwaplusphp_album_thumbsize",160);
$REQUIRE_FILTER         = get_option("pwaplusphp_require_filter","FALSE");
$IMAGES_PER_PAGE        = get_option("pwaplusphp_images_per_page",0);
$ALBUMS_PER_PAGE	= get_option("pwaplusphp_albums_per_page",0);
$PUBLIC_ONLY            = get_option("pwaplusphp_public_only","FALSE");
$SHOW_ALBUM_DETAILS     = get_option("pwaplusphp_album_details","TRUE");
$CHECK_FOR_UPDATES      = get_option("pwaplusphp_updates","TRUE");
$SHOW_DROP_BOX          = get_option("pwaplusphp_show_dropbox","FALSE");
$TRUNCATE_ALBUM_NAME    = get_option("pwaplusphp_truncate_names","TRUE");
$THIS_VERSION           = get_option("pwaplusphp_version");
$SITE_LANGUAGE          = get_option("pwaplusphp_language","en_us");
$PERMIT_IMG_DOWNLOAD    = get_option("pwaplusphp_permit_download","FALSE");
$SHOW_FOOTER    	= get_option("pwaplusphp_show_footer","FALSE");
$SHOW_IMG_CAPTION	= get_option("pwaplusphp_show_caption","HOVER");
$CAPTION_LENGTH         = get_option("pwaplusphp_caption_length","23");
$DESCRIPTION_LENGTH     = get_option("pwaplusphp_description_length","120");
$CROP_THUMBNAILS	= get_option("pwaplusphp_crop_thumbs","TRUE");
$DATE_FORMAT		= get_option("pwaplusphp_date_format","Y-m-d");
$HIDE_VIDEO             = get_option("pwaplusphp_hide_video","FALSE");
$CACHE_THUMBNAILS       = get_option("pwaplusphp_cache_thumbs","FALSE");
$MAIN_PHOTO_PAGE        = get_option("pwaplusphp_main_photo");
$SHOW_COMMENTS          = get_option("pwaplusphp_show_comments");
$JQ_PAGINATION_STYLE    = get_option("pwaplusphp_jq_pagination","fade");
$WHICH_JQ               = get_option("pwaplusphp_which_jq","pwaplusphp");
$ALLOW_SLIDESHOW        = get_option("pwaplusphp_allow_slideshow","TRUE");
$DESC_ON_ALBUM_PAGE     = get_option("pwaplusphp_albpage_desc","FALSE");
$SHOW_N_ALBUMS          = get_option("pwaplusphp_show_n_albums",0);
$IMAGES_ON_FRONT        = get_option("pwaplusphp_images_on_front",0);   // Rob
$SHOW_BUTTON            = get_option("pwaplusphp_show_button", "FALSE"); // Rob
$ADD_WIDGET             = get_option("pwaplusphp_add_widget", "TRUE"); // Rob

	echo "<form name=form1 action='$self?page=pwaplusphp&loc=finish' method='post'>\n";
	echo "<table class='widefat' cellspacing=5 width=700>\n";
	echo "<thead><tr><th valign=top colspan=3>Picasa Access Settings</th></tr></thead>\n";
	echo "<tr><td valign=top style='padding-top: 7px; width: 200px;'><strong>Picasaweb User</strong></td><td valign=top style='padding-top: 7px;'><input style='width: 150px;' type='text' name='pwaplusphp_picasa_username' value='$PICASAWEB_USER'></td><td valign=top style='padding-top: 8px;'><i>Enter your Picasa username.</i></td></tr>";

	echo "<tr><td valign=top style='padding-top: 5px; width: 200px;'><strong>OAuth2 Token</strong></td><td valign=top style='padding-top: 5px;'>$GDATA_TOKEN</td>";
	echo "<td valign=top style='padding-top: 5px;'><i>Allows access to unlisted Picasa albums. <a href='options-general.php?page=pwaplusphp&loc=reset'>Reset Token</a></i></td></tr>";
	echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
	echo "</table>";
	echo "<br />";
	echo "<br />";
?>
<p class="submit">
<input class='button-primary' type="submit" name="Submit" value="<?php _e('Update Options', 'pwaplusphp' ) ?>" />
</p>
<?php
	echo "</form>\n";
}

function set_options() {
	$THIS_VERSION = "0.9.14";
	update_option("pwaplusphp_picasa_username", $_POST['pwaplusphp_picasa_username']);
	update_option("pwaplusphp_version",$THIS_VERSION);
}

#
# Begin Main Program
#
if  (!(in_array  ('curl', get_loaded_extensions()))) {
	echo "<p><strong>ERROR:</strong> PWA+PHP requires cURL and it is not enabled on your webserver.  Contact your hosting provider to enable cURL support.</p>";
	echo "<p><i>More info is available on the <a href='http://groups.google.com/group/pwaplusphp/browse_thread/thread/49a198c531019706'>PWA+PHP discussion group</a>.</p>";
	exit;
}

$OAUTH_TOKEN = get_option("pwaplusphp_oauth_token","NULL");

$loc = $_GET['loc'];

if (isset($_GET['code'])) {
        step_4_set_token();
} else if ($loc == 'step_3_start_oauth') {
	step_3_start_oauth();
} else if ($loc == 'step_2_project_creds') {
        step_2_project_creds();
} else if (($OAUTH_TOKEN == "NULL") && (!isset($_GET['code'])) ) {
	step_1_create_project();
} else if ($loc == 'reset') {
	delete_option("pwaplusphp_oauth_token");
	delete_option("pwaplusphp_token_expires");	
	delete_option("pwaplusphp_refresh_token");
	step_3_start_oauth();
} else if ($loc != "finish") {
	get_options();	
} else {
        set_options();
	get_options();
} 
#else {
#	if (file_exists($cfg)) {
#		$file = file_get_contents($cfg);
#		if(strpos($file, "GDATA_TOKEN") >= 0) {
#			echo "PWP+PHP is already configured.  Delete $cfg and reload this page to reconfigure.";
#		} else {
#			get_gdata_token();
#		}
#	} else {
#		get_gdata_token();
#	}

#}

global $THIS_VERSION;
echo "</td><td width='25%' valign=top style='padding-top: 0px;'>";
?>
<script type="text/javascript">var URWidgetListener = function (event) {  if (event.data.indexOf("redirect") == 0) {    found = event.data.match(/redirect:url\(([^\)]*)\)/);    if (found.length == 2) {      location.href = found[1];    }  }};if (window.addEventListener) {  window.addEventListener("message", URWidgetListener, false);} else {  window.attachEvent("onmessage", URWidgetListener);} var head  = document.getElementsByTagName("head")[0];var link  = document.createElement("link");link.rel  = "stylesheet";link.type = "text/css";link.href = "http://pwaplusphp.smccandl.net/support/public/themes/default/assets/css/widget.css";link.media = "all";head.appendChild(link);</script><script type="text/javascript">widget = {url:'http://pwaplusphp.smccandl.net/support/'}</script><script src="http://pwaplusphp.smccandl.net/support/public/assets/modules/system/js/widget.js" type="text/javascript"></script>
<a class="widget-tab widget-tab-right w-round" style="margin-top:-52px;background-color:#67A2B7;border-color:#FFFFFF;" title="Support" href="javascript:popup('widget', 'http://pwaplusphp.smccandl.net/support/widget', 765, 405);"  >
  <img width="15" alt="" src="http://pwaplusphp.smccandl.net/support/public/files/logo/widget-text-default.png" />
</a>
<?php
echo "<table class='widefat' width='100%'>";
echo "<thead><tr><th valign=top colspan=3>Help & Support</th></tr></thead>\n";
echo "<tr><td>If you encounter any issues, head to the <strong><a href='http://pwaplusphp.smccandl.net/support/' target='_BLANK'>support site</a></strong> or click the feedback tab on the right side of this page.</td></tr>";
echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
echo "</table>";
echo "<br />";
echo "<table class='widefat' width='100%'>";
echo "<thead><tr><th valign=top colspan=3>News & Announcements</th></tr></thead>\n";
echo "<tr><td>";

	// Get RSS Feed(s) 
	include_once(ABSPATH . WPINC . '/feed.php'); 
	// Get a SimplePie feed object from the specified feed source. 
	$dateu = date("U");
	$rss = fetch_feed("http://wordpress.org/support/rss/tags/pwaplusphp&$dateu");
 	if (!is_wp_error( $rss ) ) :
 		// Checks that the object is created correctly      
		// Figure out how many total items there are, but limit it to 5.
		$count=0;      
		$maxitems = $rss->get_item_quantity(50);      
		
		// Build an array of all the items, starting with element 0 (first element).     
		$rss_items = $rss->get_items(0, $maxitems);  
		endif; ?> 
		<ul>     
		<?php 
			if ($maxitems == 0) {
				echo '<li>No items.</li>';     
			} else {     
				// Loop through each feed item and display each item as a hyperlink.     
				foreach ( $rss_items as $item ) {
					$title = $item->get_title();
					$author = substr($title,0,8);
					$title = substr($title,85);
					$title = substr($title,0,-6);	// Removes &quote; from the end
					$news = substr($title,-6);
					$title = substr($title,0,-6);
					if (($author == "smccandl") && ($count <= 5) && ($news == "[News]")) { 
					$count++;
					?>
						<li>
							<a target='_BLANK' href='<?php echo $item->get_permalink(); ?>' title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
				       		<?php echo $title ?></a>
						</li>
					<?php } 
				}
			 } ?> 
		</ul>
<?php
echo "</td></tr>";
echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
echo "</table>";
echo "<br />";
echo "<br />";

echo "<br />";
$pro_path = PWAPLUSPHP_PATH . "/pwaplusphp.php";
$plugin_data = get_plugin_data( PWAPLUSPHP_PATH . "pwaplusphp-pro.php");
$plugin_version = $plugin_data['Version'];
echo "<table class='widefat' width='100%'>";
echo "<thead><tr><th valign=top colspan=3>Server Information</th></tr></thead>\n";
echo "<tr><td>";
echo "<table cellspacing=0 width='100%'>";
echo "<tr><th>PWA+PHP</th><td>v" . $THIS_VERSION . "</td></tr>";
echo "<tr><th>Hostname</th><td>" . $_SERVER['SERVER_NAME'] . "</td></tr>";
list($ws,$os) = split(' ',$_SERVER['SERVER_SOFTWARE']);
$curlver = curl_version();
echo "<tr><th valign=top>Webserver</th><td>" . $ws . " " .$os . "</td></tr>";
echo "<tr><th valign=top>PHP/cURL</th><td>v" . phpversion() . " / v" . $curlver["version"] . "</td></tr>";
echo "</table>";
echo "<td></tr>";
echo "<tfoot><tr><th valign=top colspan=3></th></tr></tfoot>\n";
echo "</table>";
echo "<p><img src='http://code.google.com/apis/picasaweb/images/wwpicasa120x60.gif' /></p>";
echo "</td></tr></table>";
echo "</div>";
?>
