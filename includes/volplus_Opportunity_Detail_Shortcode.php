<?php
require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Opportunity Detail
function volplus_opportunity_detail_func($atts) {
	parse_str($_SERVER['QUERY_STRING'],$querystring);
	$opp = $querystring['opp-id'];
	$opportunity = wp_remote_get(API_URL . 'opportunities/'.$opp, array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	$response_code = wp_remote_retrieve_response_code($opportunity);

//echo 'id'.$id;
//return;
	
	if($response_code == 200) {
		$opportunity = json_decode($opportunity['body'], true);
	} else {
		wp_redirect("/404");
		exit;
	}

?>

	<h1><?php echo remove_brackets($opportunity['opportunity']); ?></h1>
	
<button type="button" class="volplus_respondButton button"><i class="fa fa-thumbs-up fa-4x"></i>I'm Interested</button>

<div id="volplus_response_notloggedin"  hidden='hidden'>
	<div id='responseintro'>
		<?php echo stripslashes( html_entity_decode(get_option('volplus_responsenotloggedinintro')));?>
	</div>
	<div id='responseform'>
		<?php echo do_shortcode(get_option('volplus_responseformcontent'));?>
	</div>
</div>	

<div id="volplus_response_loggedin" hidden="hidden">
	<div id='interestedintro'>
		<?php echo stripslashes( html_entity_decode(get_option('volplus_responseloggedinintro')));?>
	</div>
</div>	

<style type="text/css">
	.ui-dialog-titlebar-close:before {
		
	}	
	.ui-dialog {
		z-index: 9999 !important;
	}
	.ui-widget-overlay {
		position: fixed !important;
	}
	.volplus_respondButton {
		cursor: pointer;
		float:right;
		display: inline;
		padding: 1.5rem 1rem;
		margin: 0 0 1rem 1rem;
	}
</style>
<script>
	<?php
	wp_enqueue_style ("wp-jquery-ui-dialog"); 
	wp_enqueue_script("jquery-ui-dialog");
	wp_enqueue_script("jquery-effects-core");
	wp_enqueue_style("volplus_frontend_css");
	?>
	jQuery(document).ready(function($) {
		$( "#volplus_response_notloggedin" ).dialog({
			dialogClass: 'wp-dialog',
			modal: true,
			autoOpen: false,
			show: {effect: "fade", duration: 500},
			closeOnEscape: true,
			title: "I'm Interested...",
			width: 400,
			buttons: [
				{
					text: 'Register',
					class: 'button',
					click: function() {
						window.location.assign("volunteer-registration/?opp-id=" + <?php echo $querystring['opp-id'];?>);
					}
				},
 				{
					text: 'Contact us',
					class: 'button',
					click: function() {
						document.getElementById("responseintro").style.display = "none";
						document.getElementById("responseform").style.display = "inherit";
					}
				},
 				{
					text: 'Cancel',
					class: 'button',
					click: function() {
						$(this).dialog('close');
					}
				}
			]
		});

		$( "#volplus_response_loggedin" ).dialog({
			dialogClass: 'wp-dialog',
			modal: true,
			autoOpen: false,
			show: {effect: "fade", duration: 500},
			closeOnEscape: true,
			title: "I'm Interested...",
			width: 400,
			buttons: [
				{
					text: 'Register my interest',
					class: 'button',
					click: function() {
						alert('Register interest of volunteer');
					}
				},
 				{
					text: 'Cancel',
					class: 'button',
					click: function() {
						$(this).dialog('close');
					}
				}
			]
		});

		$( ".volplus_respondButton" ).click(function() {
			var loggedin = (document.cookie.indexOf("volplus_user_id") >= 0);
//console.log('loggedin:', loggedin);
			if (loggedin) {
				$( "#volplus_response_loggedin" ).dialog( "open" );				
			}else {
				document.getElementById("logout").click();
				document.getElementById("responseform").style.display = "none";
				document.getElementById("responseintro").style.display = "inherit";
				$( "#volplus_response_notloggedin" ).dialog( "open" );
			}
		});

	});
</script>

	<p><?php echo $opportunity['description']; ?></p>

	<h2>Skills</h2>

	<?php echo $opportunity['skills']; ?>
	
	<p>Posted by <?php echo $opportunity['organisation']['organisation']; ?></p>
				
	<?php if($opportunity['interests']) { ?>
	
		<div class="volplus-col-6">
			<h2>Interests</h2>
	
			<ul>
				<?php foreach($opportunity['interests'] as $interests) { ?>
				<li><?php echo $interests['interest']; ?></li>
				<?php } ?>
			</ul>
	</div>
	
	<?php } ?>

	<?php if($opportunity['activities']) { ?>
	
		<div class="volplus-col-6">
			<h2>Activities</h2>
	
			<ul>
				<?php foreach($opportunity['activities'] as $activities) { ?>
				<li><?php echo $activities['activity']; ?></li>
				<?php } ?>
			</ul>
		</div>
	
	<?php } ?>

	<div class="volplus-col-6">
		<h2>Details</h2>
	
		<?php if($opportunity['quality_control']) { ?>
		
			<ul>
				<?php foreach($opportunity['quality_control'] as $quality_control) { ?>
				<li class="status_<?php echo $quality_control['status']; ?>">
					<?php echo $quality_control['title']; ?>
					<?php if($quality_control['notes']) { ?>
						<br /><small><?php echo $quality_control['notes']; ?></small>
					<?php } ?>
				</li>
				<?php } ?>
			</ul>
			
		<?php } else { ?>
		
			<p>No additional details are recorded for this opportunity.</p>
		
		<?php } ?>
	</div>

	<?php if(isset($opportunity['location']['location'])){
		echo "<div class='volplus-col-6'>";
		switch($opportunity['location']['location']) {
			case 1:
				echo "<h2>Location</h2>";
				echo "<p>This opportunity has <strong>no specific location</strong>.</p>";
				break;
			case 2:
				echo "<h2>Location</h2>";
				echo "<p>This opportunity can be carried out whilst <strong>working from home</strong>.</p>";
				break;
			case 6:
				echo "<h2>Location</h2>";
				echo "<p>This opportunity is available <strong>Countywide</strong>.</p>";
				break;
			case 3: case 4: case 5:
				if($opportunity['location']['address']) {
					if(count($opportunity['location']['address']) == 1) {
						echo "<h2>Address</h2>";
					} else {
						echo "<h2>Addresses</h2>";
					}
				}
				foreach($opportunity['location']['address'] as $address) {
					echo "<p>";
					if(!empty($address['address_line_1'])) { echo $address['address_line_1']."<br />"; }
					if(!empty($address['address_line_2'])) { echo $address['address_line_2']."<br />"; }
					if(!empty($address['address_line_3'])) { echo $address['address_line_3']."<br />"; }
					if(!empty($address['town'])) { echo $address['town']."<br />"; }
					if(!empty($address['county'])) { echo $address['county']."<br />"; }
					if(!empty($address['postcode'])) { echo $address['postcode']; }
					echo "</p>";
				}
				break;
			case 7:
				if(count($opportunity['location']['regions']) == 1) {
					echo "<h2>District</h2>";
				} else {
					echo "<h2>Districts</h2>";
				}
				echo "<ul>";
				foreach($opportunity['location']['regions'] as $region) {
					echo "<li>".$region['region']."</li>";
				}
				echo "</ul>";
				break;
		}
	echo "</div>";
	}
?>

					
<div class="volplus-col-6">
	<h2>Availability</h2>
	
	<?php if(!empty($opportunity['availability']['start_date'])) { ?>
	
		<strong>Start Date:</strong> <?php echo date("d/m/Y", strtotime($opportunity['availability']['start_date'])); ?>
	
	<?php }?>
	
	<?php if(!empty($opportunity['availability']['end_date'])) { ?>
	
		<strong>&nbsp;&nbsp;&nbsp; End Date:</strong> <?php echo date("d/m/Y", strtotime($opportunity['availability']['end_date'])); ?>
	
	<?php }?>
	
	 <?php
	    $availability = $opportunity['availability']['availability'];
		$available = '<span class="available fa fa-check fa-lg"></span>';
		$not_available = '<span class="not_available fa fa-times" style="opacity:0.2"></span>';
	 ?>

		<table>
		<thead>
			<tr>
				<th></th>
				<th>AM</th>
				<th>PM</th>
				<th>Eve</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Monday</td>
				<td><?php if($availability['mon_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['mon_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['mon_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr><tr>
				<td>Tuesday</td>
				<td><?php if($availability['tue_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['tue_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['tue_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr><tr>
				<td>Wednesday</td>
				<td><?php if($availability['wed_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['wed_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['wed_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr><tr>
				<td>Thursday</td>
				<td><?php if($availability['thu_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['thu_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['thu_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr><tr>
				<td>Friday</td>
				<td><?php if($availability['fri_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['fri_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['fri_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr><tr>
				<td>Saturday</td>
				<td><?php if($availability['sat_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sat_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sat_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr><tr>
				<td>Sunday</td>
				<td><?php if($availability['sun_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sun_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sun_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr>
		</tbody>
	</table>
	
	<?php if(!empty($opportunity['availability']['information'])) { ?>
	
		<h4>Availability Details</h4>
	
		<p><?php echo $opportunity['availability']['information']; ?></p>
	
	<?php } ?>
	</div>

	<div class="volplus-col-12"><hr></div>

<!-- google map -->
	<?php if(get_option('volplus_showmap')) {?>
		<div class="volplus-col-12"><hr></div>
		<div id="map" class="volplus-col-12" style="height: 300px;"></div>
		<div class="volplus-col-12"><hr></div>

		<script type="text/javascript">
			function initMap() {
			  	var map = new google.maps.Map(document.getElementById('map'), {zoom: 8});
			  	var geocoder = new google.maps.Geocoder;
			  	geocoder.geocode({'address': <?php echo get_option('volplus_googlemapcentre')?>}, function(results, status) {
			   	if (status === 'OK') {
			      	map.setCenter(results[0].geometry.location);
						new google.maps.Marker({
							map: map,
							position: results[0].geometry.location
						});
					} else {
						window.alert('Geocode was not successful for the following reason: ' + status);
					}
				});  
			}
		</script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo get_option('volplus_googlemapkey');?>&callback=initMap" async defer>
	</script>
	<?php } ?>

			
	<div class="volplus-col-6">
		<?php if($opportunity['organisation_opportunities']) { ?>
			<h2>More Opportunities from <?php echo remove_brackets($opportunity['organisation']['organisation']); ?></h2>
			<?php foreach($opportunity['organisation_opportunities'] as $opp) {
				$querystring['opp-id'] = $opp['id'];
				$returnstring = http_build_query($querystring,'', '&');?>
				<div class="volunteer-plus-opportunity-list">				
					<h2><a href="/opportunities/?<?php echo $returnstring; ?>"><?php echo remove_brackets($opp['opportunity']); ?></a></h2>
					<p><a class="button" href="/opportunities/?<?php echo $returnstring; ?>">View Opportunity</a></p>
				</div>
			<?php } ?>
	<?php } ?>
	</div>
					
	<div class="volplus-col-6">
	<?php if($opportunity['similar_opportunities']) { ?>	
		<h2>Similar Opportunities</h2>
		<?php foreach($opportunity['similar_opportunities'] as $opp) {
				$querystring['opp-id'] = $opp['id'];
				$returnstring = http_build_query($querystring,'', '&');?>
				<div class="volunteer-plus-opportunity-list">
					<h2><a href="/opportunities/?<?php echo $returnstring; ?>"><?php echo remove_brackets($opp['opportunity']); ?></a></h2>
					<p class="organisation"><?php echo remove_brackets($opp['organisation']); ?></p>
					<p><a class="button" href="/opportunities/?<?php echo $returnstring; ?>">View Opportunity</a></p>
				</div>			
			<?php } ?>
		<?php } ?>
	</div>
	<!-- <?php var_dump_safe($opportunity);?>-->

<?php }


// Register jquery dependency and the shortcode.

//wp_enqueue_script('volplus-opportunity-detail', VOLPLUS_PATH .'/includes/volplus_Opportunity_Detail_Shortcode.php', array('jquery'), null, true);
add_shortcode( 'volplus-opportunity-detail', 'volplus_opportunity_detail_func' );

?>
