<?PHP

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$PWAPLUSPHP = 'pwaplusphp/pwaplusphp.php';
if (!is_plugin_active($PWAPLUSPHP)){
	deactivate_plugin(plugin_basename(__FILE__));
	wp_die('<a href="https://wordpress.org/plugins/pwaplusphp/"> pwaplusphp is required.</a>');
}