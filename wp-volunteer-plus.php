<?php
/**
 * Plugin Name:   WP Volunteer Plus
 * Plugin URI:    https://maddox.co.uk/volunteer-plus
 * Description:   A selection of tools for interacting with the Volunteer Plus Database
 * Version:       1.1.1
 * Author:        Simon Maddox
 * Author URI:    https://maddox.co.uk
 */

//Get the absolute path of the directory that contains the file, with trailing slash.
define('VOLPLUS_PATH', plugin_dir_path(__FILE__)); 
define('VOLPLUS_URL', plugin_dir_url(__FILE__)); 
define('VOLPLUS_SLUG', 'wp-volunteer-plus'); 
//define('COOKIEPATH', '/');
//define ('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);


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
	wp_enqueue_style( 'volplus_frontend_css');
	wp_enqueue_style ( 'wp-jquery-ui-dialog');
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog');
	wp_enqueue_script( 'jquery-effects-core');
	wp_enqueue_script( 'jquery-effects-fade' );
	wp_register_script( 'jquery_cookie', VOLPLUS_URL . 'includes/jquery.cookie.min.js');
	wp_enqueue_script('jquery_cookie');
}
add_action( 'admin_init', 'volplus_scripts' );
add_action('wp_enqueue_scripts', 'volplus_scripts');

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';
require_once VOLPLUS_PATH . 'includes/volplus_Search_Widget.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunities_List_Widget.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunities_List_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunities_Returned_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Opportunity_Detail_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Settings.php';
require_once VOLPLUS_PATH . 'includes/volplus_Volunteer_Register_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Manage_Organisation_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Manage_Opportunity_Shortcode.php';
require_once VOLPLUS_PATH . 'includes/volplus_Login_Widget.php';
require_once VOLPLUS_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4p4_Factory::buildUpdateChecker(
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

//get static lists
$volunteer_fields = wp_remote_get(API_URL . 'volunteer_fields', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
$response_code = wp_remote_retrieve_response_code($volunteer_fields);
if($response_code == 200) {
	$GLOBALS['volunteer_fields'] = json_decode($volunteer_fields['body'], true);
	setcookie('volplus_volunteer_fields', json_encode($GLOBALS['volunteer_fields']), 0, COOKIEPATH, COOKIE_DOMAIN );
} else {
	alert_safe ('(Error code '.$response_code.')');
}

$opportunity_fields = wp_remote_get(API_URL . 'opportunity_fields', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
$response_code = wp_remote_retrieve_response_code($opportunity_fields);
if($response_code == 200) {
	$GLOBALS['opportunity_fields'] = json_decode($opportunity_fields['body'], true);
	setcookie('volplus_opportunity_fields', json_encode($GLOBALS['opportunity_fields']), 0, COOKIEPATH, COOKIE_DOMAIN );
} else {
	alert_safe ('(Error code '.$response_code.')');
}

$activities = wp_remote_get(API_URL . 'activities', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
$response_code = wp_remote_retrieve_response_code($activities);
if($response_code == 200) {
	$GLOBALS['volunteer_activities'] = json_decode($activities['body'], true);
} else {
	echo '(Error code '.$response_code.')';
}

$interests = wp_remote_get(API_URL . 'interests', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
$response_code = wp_remote_retrieve_response_code($interests);
if($response_code == 200) {
	$GLOBALS['volunteer_interests'] = json_decode($interests['body'], true);
} else {
	echo '(Error code '.$response_code.')';
}

// function called when plugin activated
register_activation_hook(__FILE__, 'volplus_activate');

function volplus_activate() {
// create necessary pages if they do not exist
	if( get_page_by_path('volunteer-details') === null ) { // page doesn't exist
		wp_insert_post(array(
			'post_type' => 'page',
			'post_title' => 'Volunteer Details',
			'post_content' => '[volplus-volunteer-register wordpress-account=0]',
			'post_name' => 'volunteer-details',
			'post_status' => 'publish',
			'post_author' => 1,
    ));
	}
	if( get_page_by_path('search') === null ) { // page doesn't exist
		wp_insert_post(array(
			'post_type' => 'page',
			'post_title' => 'Opportunity Search',
			'post_content' => '[volplus-list-opportunities]',
			'post_name' => 'search',
			'post_status' => 'publish',
			'post_author' => 1,
    ));
	}
	if( get_page_by_path('opportunities') === null ) { // page doesn't exist
		wp_insert_post(array(
			'post_type' => 'page',
			'post_title' => 'Opportunities',
			'post_content' => '[volplus-opportunity-detail]',
			'post_name' => 'opportunities',
			'post_status' => 'publish',
			'post_author' => 1,
    ));
	}
	if( get_page_by_path('manage-organisation') === null ) { // page doesn't exist
		wp_insert_post(array(
			'post_type' => 'page',
			'post_title' => 'Manage Organisation',
			'post_content' => '[volplus-manage-organisation]',
			'post_name' => 'manage-organisation',
			'post_status' => 'publish',
			'post_author' => 1,
    ));
	}
	if( get_page_by_path('manage-opportunity') === null ) { // page doesn't exist
		wp_insert_post(array(
			'post_type' => 'page',
			'post_title' => 'Manage Opportunity',
			'post_content' => '[volplus-manage-opportunity]',
			'post_name' => 'manage-opportunity',
			'post_status' => 'publish',
			'post_author' => 1,
    ));
	}
// create custom user role
	add_role( 'volunteer', 'Volunteer', array( 'read' => true, 'level_0' => true ) );
}


