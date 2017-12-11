<?php
/**
 * Plugin Name:   WP Volunteer Plus
 * Plugin URI:    https://maddox.co.uk/volunteer-plus
 * Description:   A selection of tools for searching the Volunteer Plus Database
 * Version:       0.4.1
 * Author:        Simon Maddox
 * Author URI:    https://maddox.co.uk
 */

//Get the absolute path of the directory that contains the file, with trailing slash.
define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__)); 
define('MY_PLUGIN_URL', plugin_dir_url(__FILE__)); 
define('MY_PLUGIN_SLUG', 'wp-volunteer-plus'); 

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


require_once MY_PLUGIN_PATH . 'includes/volplus_Search_Widget.php';
require_once MY_PLUGIN_PATH . 'includes/volplus_Opportunities_List_Widget.php';
require_once MY_PLUGIN_PATH . 'includes/volplus_Opportunities_List_Shortcode.php';
require_once MY_PLUGIN_PATH . 'includes/volplus_Opportunities_Returned_Shortcode.php';
require_once MY_PLUGIN_PATH . 'includes/volplus_Opportunity_Detail_Shortcode.php';
require_once MY_PLUGIN_PATH . 'includes/volplus_Settings.php';
require_once MY_PLUGIN_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';

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


function volplus_load_plugin_css() {

    wp_enqueue_style( 'volplus_frontend', MY_PLUGIN_URL . 'assets/css/frontend.css' );
}
add_action( 'wp_enqueue_scripts', 'volplus_load_plugin_css' );