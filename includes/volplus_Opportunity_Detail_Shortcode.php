<?php
require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';


// Opportunity Detail
function volplus_opportunity_detail_func($atts) {
	parse_str($_SERVER['QUERY_STRING'],$querystring);
	$opp = $querystring['opp-id'];
	setcookie('volplus_opp_id', $opp, 0, COOKIEPATH, COOKIE_DOMAIN );
//Fetch opportunity details
	$opportunity = wp_remote_get(API_URL . 'opportunities/'.$opp, array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	$response_code = wp_remote_retrieve_response_code($opportunity);

//echo 'id'.$id;
//return;
	
	if($response_code == 200) {
		$opportunity = json_decode($opportunity['body'], true);
		add_query_arg('opp_name',urlencode($opportunity['opportunity'])); // add name to url
		
	} else {
		wp_redirect("/404");
		exit;
	}
	
	$organisation = getOrgDetails($opportunity['organisation']['id']);

?>

<div id='opportunity_detail' class='volplus-col-12'>

	<h1><?php echo remove_brackets($opportunity['opportunity']); ?></h1>
	
<button type="button" id="volplus_respondButton" class="volplus_respondButton button"><i class="fa fa-thumbs-up fa-4x"></i>I'm Interested</button>

<div id="volplus_response_notloggedin"  hidden='hidden'>
	<div id='responseintro'>
		<?php echo stripslashes( html_entity_decode(get_option('volplus_responsenotloggedinintro')));?>
		<?php// the_widget('volplus_Login_Widget');?>
	</div>
	<div id='responseform'>
		<?php echo do_shortcode(get_option('volplus_responseformcontent'));?>
	</div>
</div>	

<div id="volplus_response_loggedin" hidden="hidden">
	<div id='interestedintro'>
		<?php echo stripslashes( html_entity_decode(get_option('volplus_responseloggedinintro')));?>
		<textarea id="interested_notes" rows="5" placeholder="You can add some notes about your interest here..."></textarea>
	</div>
	<p id='volplus_enquiry_status' class="status"></p>
</div>	

<div id="volplus_interest_registered" hidden="hidden">
	<div id='interestedintro'>
		<?php echo stripslashes( html_entity_decode(get_option('volplus_enquirysuccessmsg')));?>
	</div>
	<p id='volplus_enquiry_status' class="status"></p>
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

	<p><?php echo $opportunity['description']; ?></p>

	<div class="volplus-col-12">
		<?php if(null !== $opportunity['skills']){
			echo "<h2>Skills</h2>";
			echo $opportunity['skills'];
		} ?>
	</div>
		
	<div id=orgDetails class="volplus-col-12">
		<h2>Organisation</h2>
		<div class="volplus-col-6">
			<strong><?php echo remove_brackets($organisation['organisation'])?><br/></strong>
			<?php 
			if(get_option('volplus_hideorgdirect', 'on') !== 'on') {
				if(null !== $organisation['address_line_1']) echo stripcslashes(html_entity_decode($organisation['address_line_1'])) . "<br/>"; 
				if(null !== $organisation['address_line_2']) echo stripcslashes(html_entity_decode($organisation['address_line_2'])) . "<br/>"; 
				if(null !== $organisation['address_line_3']) echo stripcslashes(html_entity_decode($organisation['address_line_3'])) . "<br/>"; 
				if(null !== $organisation['town']) echo stripcslashes(html_entity_decode($organisation['town'])); 
				if(null !== $organisation['county']) echo ", " . stripcslashes(html_entity_decode($organisation['county'])) . "<br/>"; 
				if(null !== $organisation['postcode']) echo stripcslashes(html_entity_decode($organisation['postcode'])); 
			}
			?>
		</div>	
		<div class="volplus-col-6">
			<?php 
			$orgStatus = array(
				'',
				'Registered Charity',
				'Charitable Incorporated Organisations (CIO)',
				'Unregistered Community Group',
				'Community Interest Company (CIC)',
				'Company Limited by Guarantee',
				'Statutory Organisation',
				'Private Business',
				'Other'
			);		
			if(null !== $organisation['status']) echo "Org type: <strong>" . $orgStatus[$organisation['status']] . "</strong><br/>"; 
			if(get_option('volplus_hideorgdirect', 'on') !== 'on') {
				if(null !== $organisation['website']) echo "Website: <a target=_blank href='" . stripcslashes(html_entity_decode($organisation['website'])) . "'>". stripcslashes(html_entity_decode($organisation['website'])) . "</a><br/>";
				if(null !== $organisation['telephone_number']) echo "Tel:  <a target=_blank href='tel:" . stripcslashes(html_entity_decode($organisation['telephone_number'])) . "'>"  . stripcslashes(html_entity_decode($organisation['telephone_number'])) . "</a><br/>"; 
				if(null !== $organisation['email_address']) echo "Email: <a target=_blank href='mailto:" . stripcslashes(html_entity_decode($organisation['email_address'])) . "'>" . stripcslashes(html_entity_decode($organisation['email_address'])) . "</a><br/>";
				if(null !== $organisation['additional_email_address']) echo "2nd email: <a target=_blank href='mailto:" . stripcslashes(html_entity_decode($organisation['additional_email_address'])) . "'>" . stripcslashes(html_entity_decode($organisation['additional_email_address'])) . "</a><br/>";
			}
			if(null !== $organisation['charity_registration_number']) echo "Charity number: " . stripcslashes(html_entity_decode($organisation['charity_registration_number'])) . "<br/>";
			if(null !== $organisation['company_registration_number']) echo "Company number: " . stripcslashes(html_entity_decode($organisation['company_registration_number'])) . "<br/>";
			?>
		</div>	
		<?php if(null !== $organisation['about']){
			echo "<div class='volplus-col-12'><strong>Organisation Information</strong><br/>";
			echo stripslashes( html_entity_decode($organisation['about'])) . "</div>";
		} ?>
		<?php if(null !== $organisation['mission_statement']){
			echo "<div class='volplus-col-12'><strong>Mission Statement</strong><br/>";
			echo stripslashes( html_entity_decode($organisation['mission_statement'])) . "</div>";
		} ?>
	</div>

	<?php if(!get_option('volplus_hideoppact', null)){			
		if($opportunity['interests']) { ?>
	
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
		
		<?php }
	} ?>

	<div class="volplus-col-6">
		<h2>Details</h2>
	
		<?php if($opportunity['quality_control']) { ?>
		
			<ul>
				<?php foreach($opportunity['quality_control'] as $quality_control) { ?>
				<li class="status_<?php echo $quality_control['status']; ?>">
					<?php echo $quality_control['title']; ?>
					<?php if($quality_control['notes'] && !get_option('volplus_hideqcdetails')) { ?>
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
						echo "<h2>Opportunity Address</h2>";
					} else {
						echo "<h2>Opportunity Addresses</h2>";
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

	<button type="button" id="volplus_respondButton2" class="volplus_respondButton button"><i class="fa fa-thumbs-up fa-4x"></i>I'm Interested</button>

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
	
	<?php var_dump_safe($organisation);?><!-- -->
	<?php var_dump_safe($opportunity);?><!-- -->
	
	<?php wp_nonce_field( 'ajax-enquiry-nonce', 'security' ); ?>
</div>	

	<script type="text/javascript" >
		jQuery(document).ready(function($) {

			if($.cookie("volplus_iminterested")) {
				$.removeCookie("volplus_iminterested", {path:'/'});
				$("button#volplus_respondButton").click();
			};

		});

	</script>


<?php }?>

<?php
// Register jquery dependency and the shortcode.

//wp_enqueue_script('volplus-opportunity-detail', VOLPLUS_PATH .'/includes/volplus_Opportunity_Detail_Shortcode.php', array('jquery'), null, true);
add_action('wp_enqueue_scripts', 'ajax_enquire_init');
add_action( 'wp_ajax_volplusajaxenquire', 'ajax_enquire' );
add_action( 'wp_ajax_nopriv_volplusajaxenquire', 'ajax_enquire' );

add_shortcode( 'volplus-opportunity-detail', 'volplus_opportunity_detail_func' );
?>
