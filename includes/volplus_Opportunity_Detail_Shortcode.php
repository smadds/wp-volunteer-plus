<?php
require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Opportunity Detail
function volplus_opportunity_detail_func($atts) {

	$uriarray = (explode("/",$_SERVER["REQUEST_URI"]));
	$id = $uriarray[2];
	$opportunity = wp_remote_get(API_URL . 'opportunities/'.$id, array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	$response_code = wp_remote_retrieve_response_code($opportunity);
	
	if($response_code == 200) {
		$opportunity = json_decode($opportunity['body'], true);
	} else {
		wp_redirect("/404");
		exit;
	}

?>

	<h1><?php echo remove_brackets($opportunity['opportunity']); ?></h1>
	
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
		$available = '<span class="available">Yes</span>';
		$not_available = '<span class="not_available">No</span>';
	 ?>

	<p><table>
		<thead>
			<tr>
				<th></th>
				<th>Mon</th>
				<th>Tue</th>
				<th>Wed</th>
				<th>Thu</th>
				<th>Fri</th>
				<th>Sat</th>
				<th>Sun</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Morning</td>
				<td><?php if($availability['mon_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['tue_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['wed_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['thu_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['fri_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sat_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sun_mor'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr>
			<tr>
				<td>Afternoon</td>
				<td><?php if($availability['mon_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['tue_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['wed_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['thu_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['fri_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sat_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sun_aft'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr>
			<tr>
				<td>Evening</td>
				<td><?php if($availability['mon_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['tue_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['wed_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['thu_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['fri_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sat_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
				<td><?php if($availability['sun_eve'] == "1") { echo $available; } else { echo $not_available; }; ?></td>
			</tr>
		</tbody>
	</table></p>
	
	<?php if(!empty($opportunity['availability']['information'])) { ?>
	
		<h4>Availability Details</h4>
	
		<p><?php echo $opportunity['availability']['information']; ?></p>
	
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
					echo "<h2>Location</h2>";
				} else {
					echo "<h2>Locations</h2>";
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

//google map
?>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 300px;
      }
    </style>

<?php	$mapcentre = geocode(get_option('volplus_googlemapcentre'));?>
<!--<pre><?php var_dump($mapcentre);?></pre>-->
	
    <div id="map" class="volplus-col-6"></div>
    <script>
      var map;
      function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: <?php echo $mapcentre[0];?>, lng: <?php echo $mapcentre[1];?>},
          zoom: <?php echo get_option('volplus_googlemapzoom');?>
        });
      }
    </script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo get_option('volplus_googlemapkey');?>&callback=initMap" async defer>
</script>

	
<!-- <pre><?php print_r($opportunity);?></pre>-->
			
	<?php if($opportunity['organisation_opportunities']) { ?>
	
		<h2>More Opportunities from <?php echo remove_brackets($opportunity['organisation']['organisation']); ?></h2>
		
		<?php foreach($opportunity['organisation_opportunities'] as $opp) { ?>
			
			<div class="volunteer-plus-opportunity-list">
				
				<h2><a href="/opportunities/<?php echo $opp['id'].'/?'.$_SERVER['QUERY_STRING']; ?>"><?php echo remove_brackets($opp['opportunity']); ?></a></h2>
				
				<p><a class="button" href="/opportunities/<?php echo $opp['id'].'/?'.$_SERVER['QUERY_STRING']; ?>">View Opportunity</a></p>
				
			</div>
			
		<?php } ?>
		
	<?php } ?>
					
	<?php if($opportunity['similar_opportunities']) { ?>
	
		<h2>Similar Opportunities</h2>
		
		<?php foreach($opportunity['similar_opportunities'] as $opp) { ?>
			
			<div class="volunteer-plus-opportunity-list">
				
				<h2><a href="/opportunities/<?php echo $opp['id'].'/?'.$_SERVER['QUERY_STRING']; ?>"><?php echo remove_brackets($opp['opportunity']); ?></a></h2>
				
				<p class="organisation"><?php echo remove_brackets($opp['organisation']); ?></p>
				
				<p><a class="button" href="/opportunities/<?php echo $opp['id'].'/?'.$_SERVER['QUERY_STRING']; ?>">View Opportunity</a></p>
				
			</div>
			
		<?php }
		
	}

}

// Register the shortcode.

add_shortcode( 'volplus-opportunity-detail', 'volplus_opportunity_detail_func' );
?>
