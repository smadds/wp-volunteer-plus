<?php

// display disability options
function disability() {
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
	$disabilities = $volunteer_fields['disabilities'];
	echo "<select name='disability'/>";
		echo "<option value='' disabled selected hidden>Select</option>";
		foreach ($disabilities as $disability) {
			echo "<option value=" . $disability['id'] . ">" . $disability['value'] . "</option>";
		}
	echo "</select>";
}

// display ethnicity options
function ethnicity() {
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
	$ethnic_groups = $volunteer_fields['ethnic_groups'];
	echo "<select name='ethnicity'/>";
		echo "<option value='' disabled selected hidden>Select</option>";
		foreach ($ethnic_groups as $ethnic_group) {
			echo "<option value=" . $ethnic_group['id'] . ">" . $ethnic_group['value'] . "</option>";
		}
	echo "</select>";
}

// display employment statuses
function employment_status() {
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
	$employment_statuses = $volunteer_fields['employment_statuses'];
	echo "<select name='gender'/>";
		echo "<option value='' disabled selected hidden>Select</option>";
		foreach ($employment_statuses as $employment_status) {
			echo "<option value=" . $employment_status['id'] . ">" . $employment_status['value'] . "</option>";
		}
	echo "</select>";
}


// display volunteering reasons
function display_reasons() {
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
	$reasons = $volunteer_fields['volunteering_reasons'];
//	print_r_safe($volunteer_fields);
	echo '<div class="colcontainer">';             
		foreach($reasons as $reason) {
			if(isset($_GET['reasons'])) {
				if(in_array($reason['id'], $_GET['reasons'])) {
					echo"<label class='colitem-selected'><input type='checkbox' name='reasons[".$reason['id']."]' value='".$reason['id']."' checked />".$reason['value']."</label><br />";
				} else {
				// not filtered when page returned
					echo"<label class='colitem'><input type='checkbox' name='reasons[".$reason['id']."]' value='".$reason['id']."' />".$reason['value']."</label><br />";
				}
			} else {
				echo"<label class='colitem'><input type='checkbox' name='reasons[".$reason['id']."]' value='".$reason['id']."' />".$reason['value']."</label><br />";
			}
		}
		echo '</div>';
}


// display interests
function display_interests() {
	$interests = wp_remote_get(API_URL . 'interests', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	echo "<label for='interests'>Interests</label>";
	$response_code = wp_remote_retrieve_response_code($interests);
	if($response_code == 200) {
		$interests = json_decode($interests['body'], true);
		echo '<div class="colcontainer">';             
		foreach($interests as $interest) {
			if(isset($_GET['interests'])) {
				if(in_array($interest['id'], $_GET['interests'])) {
					echo"<label class='colitem-selected'><input type='checkbox' name='interests[".$interest['id']."]' value='".$interest['id']."' checked />".$interest['interest']."</label><br />";
				} else {
					// not filtered when page returned
					echo"<label class='colitem'><input type='checkbox' name='interests[".$interest['id']."]' value='".$interest['id']."' />".$interest['interest']."</label><br />";
				}
			} else {
				echo"<label class='colitem'><input type='checkbox' name='interests[".$interest['id']."]' value='".$interest['id']."' />".$interest['interest']."</label><br />";
			}
		}
		echo '</div>';
	}
}

//display activities
function display_activities(){
	$activities = wp_remote_get(API_URL . 'activities', array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	echo "<label for=activities>Activities</label>";
	$response_code = wp_remote_retrieve_response_code($activities);
	if($response_code == 200) {
		$activities = json_decode($activities['body'], true);
		echo '<div class="colcontainer">';             
		foreach($activities as $activity) {
			if(isset($_GET['activities'])) {
				if(in_array($activity['id'], $_GET['activities'])) {
					echo"<label class='colitem-selected'><input type='checkbox' name='activities[".$activity['id']."]' value='".$activity['id']."' checked />".$activity['activity']."</label><br />";
				} else {
					echo"<label class='colitem'><input type='checkbox' name='activities[".$activity['id']."]' value='".$activity['id']."' />".$activity['activity']."</label><br />";
				}
			} else {
				echo"<label class='colitem'><input type='checkbox' name='activities[".$activity['id']."]' value='".$activity['id']."' />".$activity['activity']."</label><br />";
			}
		}
		echo '</div>';
	} else {
		echo '(Error code '.$response_code.')';
	}
}

// display availability table
function display_availability_table(){
	echo "<label for='availability'>When are you available?</label>";
	
	$periods = array(
		'mon_mor','mon_aft','mon_eve',
		'tue_mor','tue_aft','tue_eve',
		'wed_mor','wed_aft','wed_eve',
		'thu_mor','thu_aft','thu_eve',
		'fri_mor','fri_aft','fri_eve',
		'sat_mor','sat_aft','sat_eve',
		'sun_mor','sun_aft','sun_eve'
	);

	$days = array('mon','tue','wed','thu','fri','sat','sun');
	$dayperiods = array('AM','PM','Eve');

	echo '<table><tr><th>&nbsp;</th><th>AM</th><th>PM</th><th>Eve</th></tr>';
	
	$index = 0;
	foreach($days as $day) {
		echo '<tr>';
		echo '<th>'.ucfirst($day).'</th>';
		foreach($dayperiods as $dayperiod) {
			echo '<td><input class="volplus-checkbox" type="checkbox" name="'.$periods[$index].'" value="1"';
			if(isset($_GET[$periods[$index]])){
				if($_GET[$periods[$index]]) {
					echo ' checked';
				} 
			}
			echo '/></td>';
			$index++;
		}
		echo '</tr>';
	}
	echo '</table>';
	
}



// safe printing of array
// needs to be in debug mode (WP_DEBUG = true in wp-config.php) AND logged in as an admin
function print_r_safe ($a){
	if(defined('WP_DEBUG') && WP_DEBUG === true && current_user_can('administrator')) {
		echo "<pre style='white-space:pre-wrap;'>";
		print_r($a);
		echo "</pre>";
	}
}
function var_dump_safe ($a){
	if(defined('WP_DEBUG') && WP_DEBUG === true && current_user_can('administrator')) {
		echo "<pre style='white-space:pre-wrap;'>";
		var_dump($a);
		echo "</pre>";
	}
}


// Remove ALL bracketed text from string if setting configured
function remove_brackets($fstring) {
	if(get_option('volplus_hidebrackets') == 'on') {
		while(strpos($fstring, '(')) {
			$fstring = preg_replace("/\([^)]+\)/", '', $fstring);
		}
	}
	return $fstring;
}

//format postcode
function postcodeFormat($postcode)
{
    //trim and remove spaces
    $cleanPostcode = preg_replace("/[^A-Za-z0-9]/", '', $postcode);
 
    //make uppercase
    $cleanPostcode = strtoupper($cleanPostcode);
 
    //if 5 charcters, insert space after the 2nd character
    if(strlen($cleanPostcode) == 5)
    {
        $postcode = substr($cleanPostcode,0,2) . " " . substr($cleanPostcode,2,3);
    }
 
    //if 6 characters, insert space after the 3rd character
    elseif(strlen($cleanPostcode) == 6)
    {
        $postcode = substr($cleanPostcode,0,3) . " " . substr($cleanPostcode,3,3);
    }
 
 
    //if 7 charcters, insert space after the 4th character
    elseif(strlen($cleanPostcode) == 7)
    {
        $postcode = substr($cleanPostcode,0,4) . " " . substr($cleanPostcode,4,3);
    }
 
    return $postcode;
}


// function to geocode address, it will return false if unable to geocode address
function geocode($address){
 
    // url encode the address
    $address = urlencode($address);
     
    // google map geocode api url
    $url = "http://maps.google.com/maps/api/geocode/json?address={$address}";
    $url .= "?key=" + get_option('volplus_googlemapkey');
 
    // get the json response
    $resp_json = file_get_contents($url);
     
    // decode the json
    $resp = json_decode($resp_json, true);
 
    // response status will be 'OK', if able to geocode given address 
    if($resp['status']=='OK'){
 
        // get the important data
        $lati = $resp['results'][0]['geometry']['location']['lat'];
        $longi = $resp['results'][0]['geometry']['location']['lng'];
        $formatted_address = $resp['results'][0]['formatted_address'];
         
        // verify if data is complete
        if($lati && $longi && $formatted_address){
         
            // put the data in the array
            $data_arr = array();            
             
            array_push(
                $data_arr, 
                    $lati, 
                    $longi, 
                    $formatted_address
                );
             
            return $data_arr;
             
        }else{
            return false;
        }
         
    }else{
        return false;
    }
}