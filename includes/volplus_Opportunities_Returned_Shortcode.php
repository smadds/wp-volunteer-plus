<?php

require_once MY_PLUGIN_PATH . 'includes/volplus_Functions.php';

// Opportunity List
function volplus_return_opportunities_func($atts) {


$opportunities = wp_remote_get(API_URL . 'opportunities?'.$_SERVER['QUERY_STRING'], array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
$response_code = wp_remote_retrieve_response_code($opportunities);
$opportunities = json_decode($opportunities['body'], true);

if($response_code == 200) {
	
		return $opportunities['total'];			
	
  } elseif($response_code == 204) {
		
		return '';
		
	} else {
		
		return 'Error';
		
  }

}

// Register the shortcode.

add_shortcode( 'volplus-opportunities-returned', 'volplus_return_opportunities_func' );


// Enable shortcodes in widgets
add_filter('widget_text','do_shortcode');
?>