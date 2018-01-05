<?php
/**
 * Plugin Name:   WP Volunteer Plus
 * Plugin URI:    https://maddox.co.uk/volunteer-plus
 * Description:   A selection of tools for searching the Volunteer Plus Database
 * Version:       0.4.7
 * Author:        Simon Maddox
 * Author URI:    https://maddox.co.uk
 */

//Get the absolute path of the directory that contains the file, with trailing slash.
define('VOLPLUS_PATH', plugin_dir_path(__FILE__)); 
define('VOLPLUS_URL', plugin_dir_url(__FILE__)); 
define('VOLPLUS_SLUG', 'wp-volunteer-plus'); 

//Add Settings option to plugins list page
function volplus_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=volunteer-plus">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_".$plugin, 'volplus_add_settings_link' );


define( "API_URL", get_option('volplus_endpoint') );
define( "API_KEY", get_option('volplus_api_key') );


function volplus_scripts() {
	wp_register_style( 'volplus_frontend_css', VOLPLUS_URL . 'assets/css/frontend.css' );
	wp_enqueue_style('volplus_frontend_css');
	wp_enqueue_style (  'wp-jquery-ui-dialog');
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-effects-fade' );
}
add_action( 'admin_init', 'volplus_scripts' );
add_action('wp_enqueue_scripts', 'volplus_scripts');

require_once VOLPLUS_PATH . 'includes/volplus_Search_Widget.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunities_List_Widget.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunities_List_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunities_Returned_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunity_Detail_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Settings.php';
require_once VOLPLUS_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4p3_Factory::buildUpdateChecker(
	'https://github.com/smadds/wp-volunteer-plus',
	__FILE__,
	'wp-volunteer-plus'
);
//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('4046a82e545bde6ed1c1dc2ad54f4c770cccfa40');

//Optional: Set the branch that contains the stable release.
//$myUpdateChecker->setBranch('stable-branch-name');
//Optional: Load release assets
//$myUpdateChecker->getVcsApi()->enableReleaseAssets();


