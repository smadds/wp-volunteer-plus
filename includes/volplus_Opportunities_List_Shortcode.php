<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';


// Opportunity List
function volplus_list_opportunities_func($atts = [], $content = null, $tag = '') {

// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);

// override default attributes with user attributes
	$volplus_atts = shortcode_atts([
		'org' => 1,
		'location' => 1,
		'button' => 1,
	], $atts, $tag);

	$opportunities = wp_remote_get(API_URL . 'opportunities?'.$_SERVER['QUERY_STRING'], array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	$response_code = wp_remote_retrieve_response_code($opportunities);
	$opportunities = json_decode($opportunities['body'], true);
	$location = ["","No Location","Working from home","Specific Address","Specific Address","Multiple Specific Addresses","County-wide","Regional"];

// split query string to array
	parse_str($_SERVER['QUERY_STRING'],$querystring);

	if($response_code == 200) {
	
		foreach($opportunities['data'] as $opportunity) { ?>
			<?php $querystring['opp-id'] = $opportunity['id'];$returnstring = http_build_query($querystring,'', '&');?>			
			
			<div class="volplus-list">
								
				<h2><a href="/opportunities/?<?php echo $returnstring; ?>"><?php echo remove_brackets($opportunity['opportunity']); ?></a></h2>
				
				<?php if($volplus_atts['org']){?>
					<p class="organisation"><?php echo remove_brackets($opportunity['organisation']); ?></p>
				<?php }
				
				if($volplus_atts['location']){?>
					<?php if(array_key_exists('regions', array_filter($opportunity))) {
						 echo '<p>District';
						 if (strchr($opportunity['regions'],',')) echo 's';
						 echo ' : ' . $opportunity['regions'] . "&nbsp;&nbsp;</p>";
					 }
					 if(array_key_exists('distance', array_filter($opportunity))) {
						 echo '<p>Distance ~ '.round($opportunity['distance'],1).' miles</p>';
					 }else{ ?>
					<p class="location"><?php echo $location[$opportunity['location']]; ?> 
					<?php }?>
					</p>
				<?php }
						
				if($volplus_atts['button']){?>
					</p><a class="button" href="/opportunities/?<?php echo $returnstring; ?>">View Opportunity</a></p>
				<?php }?>				
				<!-- --><?php var_dump_safe($opportunity);?>	
			</div>
			
		<?php 
	  }?>
	  
	  		<ul class="volplus-pagination">
			<?php
				if($opportunities['last_page']!== 1) { //don't bother if just 1 page
				
					if($opportunities['current_page'] > 1){ //not on 1st page
						$querystring['Page'] = $opportunities['current_page']-1; // use capitalised Page to avoid WP search
						echo "<li><a href='/search?".http_build_query($querystring,'', '&')."'>Previous</a></li>";
					}
					
					foreach (range(max(1,$opportunities['current_page']-5), min($opportunities['current_page']+5,$opportunities['last_page'])) as $number) {
						$querystring['Page'] = $number;
						if($opportunities['current_page'] == $number) { // current page
							echo "<li><a href='/search?".http_build_query($querystring,'', '&')."' class='current'>".$number."</a></li>";
						} else { //not current page(s))
							echo "<li><a href='/search?".http_build_query($querystring,'', '&')."'>".$number."</a></li>";
						}
					}
					
					if($opportunities['current_page'] < $opportunities['last_page']){
						$querystring['Page'] = $opportunities['current_page']+1;
						echo "<li><a href='/search?".http_build_query($querystring,'', '&')."'>Next</a></li>";
					}
					
				}
				
			?>
		</ul>
		
	
  <?php } elseif($response_code == 204) { ?>
		
		<h2>No opportunities found</h2>
		
		<p>Sorry, no opportunities could be found for your search criteria. Please amend your search and try again.</p>
		
	<?php 
	} elseif($response_code == 422) { ?>
		
		<ul class="volunteer-plus-error">
			<li>Sorry, an error occurred. Please try again later.</li>
		</ul>
		
  <?php
  }

}

// Register the shortcode.

add_shortcode( 'volplus-list-opportunities', 'volplus_list_opportunities_func' );

?>