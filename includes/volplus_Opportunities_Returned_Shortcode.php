<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Opportunity List
function volplus_return_opportunities_func($atts = [], $content = null, $tag = '') {

// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);

// override default attributes with user attributes
	$volplus_atts = shortcode_atts([
		'rounddown' => 1,
		'roundup' => 1,
	], $atts, $tag);

$opportunities = wp_remote_get(API_URL . 'opportunities?'.$_SERVER['QUERY_STRING'], array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
$response_code = wp_remote_retrieve_response_code($opportunities);
$opportunities = json_decode($opportunities['body'], true);

// Round up if needed
if($volplus_atts['roundup'] !== 1) $opportunities['rounded'] = ceil($opportunities['total'] / $volplus_atts['roundup']) * $volplus_atts['roundup'];

// Round down if needed
if($volplus_atts['rounddown'] !== 1) $opportunities['rounded'] = floor($opportunities['total'] / $volplus_atts['rounddown']) * $volplus_atts['rounddown'];


if($response_code == 200) {

		if (isset($opportunities['rounded'])) { return $opportunities['rounded']; } else { return $opportunities['total']; }		
	
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
