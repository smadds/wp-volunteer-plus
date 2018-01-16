<?php

// get post variable if it exists
function get($key,  $default_value = '')
{
    return isset($_GET[$key]) ? esc_attr($_GET[$key]) : $default_value;
}

class volplus_volunteer {
			var $title;
			var $first_name;		// required
			var $last_name;		// required
			var $address_line_1;	// required
			var $address_line_2;
			var $address_line_3;
			var $town;				// required
			var $county;			// required
			var $postcode;			// required
			var $telephone;		// required
			var $mobile;
			var $email_address;	// required
			var $interests;		// required
			var $activities;	// required
			var $mon_mor, $mon_aft, $mon_eve;
			var $tue_mor, $tue_aft, $tue_eve;
			var $wed_mor, $wed_aft, $wed_eve;
			var $thu_mor, $thu_aft, $thu_eve;
			var $fri_mor, $fri_aft, $fri_eve;
			var $sat_mor, $sat_aft, $sat_eve;
			var $sun_mor, $sun_aft, $sun_eve;
			var $availability_details;
			var $volunteering_experience;
			var $reasons;
			var $volunteering_reason_info;
			var $date_birth;		// required unless prefer not say
			var $date_birth_prefer_not_say;
			var $gender;			// required 1:male, 2:female, 3:prefer not to say, 4:not known
			var $employment;		//required specific integer from Volunteer fields
			var $ethnicity;		//required specific integer from Volunteer fields
			var $disability;		// required 1:yes, 2:no, 3:prefer not to say
			var $disabilities;	// required if disability=1. list of objects that each represent a single disability. see volunteer fields
			var $password;			// required
			var $password_confirmation; // required
}


// display disability types
function disability_type($vol){
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
	$disabilities = $volunteer_fields['disabilities'];
	echo '<div class="colcontainer"  id="disability-type">';             
	foreach($disabilities as $disability) {
		if(isset($_GET['disabilities'])) {
			if(in_array($disability['id'], $vol->disabilities)) {
				echo"<label class='colitem-selected'><input type='checkbox' name='disabilities[".$disability['id']."]' value=1 checked />".$disability['value']."</label><br />";
			} else {
				echo"<label class='colitem'><input type='checkbox' name='disabilities[".$disability['id']."]' value=0 />".$disability['value']."</label><br />";
			}
		} else {
			echo"<label class='colitem'><input type='checkbox' name='disabilities[".$disability['id']."]' value=0 />".$disability['value']."</label><br />";
		}
	}
	echo '</div>';
	return $vol;
}


// display disability options
function disability($vol) {
	$thedisabilities = array( 
		['id'=>1, 'value'=>'Prefer not to say'], 
		['id'=>2, 'value'=> 'Yes'],
		['id'=>3, 'value'=> 'No'],
		['id'=>4, 'value'=> 'Not known']
	);
//	var_dump_safe($disabilities);
	$selected = $vol->disability;
	echo "<select name='disability' id='disability-type'>";
		echo "<option value='' ";
		if($selected=="") echo 'selected';
		echo ">Select</option>";
		foreach ($thedisabilities as $thedisability) {
			echo "<option value=" . $thedisability['id'];
			if($thedisability['id'] == $selected) echo " selected";
			echo ">" . $thedisability['value'] . "</option>";
		}
	echo "</select>";
	return $vol;
}

// display ethnicity options
function ethnicity() {
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
	$ethnic_groups = $volunteer_fields['ethnic_groups'];
	$selected = get('ethnicity');
	echo "<select name='ethnicity'>";
		echo "<option value='' ";
		if($selected=="") echo 'selected';
		echo ">Select</option>";
		foreach ($ethnic_groups as $ethnic_group) {
			echo "<option value=" . $ethnic_group['id'];
			if($ethnic_group['id'] == $selected) echo " selected";
			echo ">" . $ethnic_group['value'] . "</option>";
		}
	echo "</select>";
}

// display employment statuses
function employment_status() {
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
	$employment_statuses = $volunteer_fields['employment_statuses'];
	$selected = get('employment');
	echo "<select name='employment'>";
		echo "<option value='' ";
		if($selected=="") echo 'selected';
		echo ">Select</option>";
		foreach ($employment_statuses as $employment_status) {
			echo "<option value=" . $employment_status['id'];
			if($employment_status['id'] == $selected) echo " selected";
			echo ">" . $employment_status['value'] . "</option>";
		}
	echo "</select>";
	return $employment_status;
}


// display volunteering reasons
function display_reasons() {
	$volunteer_fields = $GLOBALS['volunteer_fields'];
	$volunteer_fields = json_decode($volunteer_fields['body'], true);
//	print_r_safe($volunteer_fields);
	$reasons = $volunteer_fields['volunteering_reasons'];
//	print_r_safe($reasons);
	echo '<div class="colcontainer">';             
		foreach($reasons as $reason) {
			if(isset($_GET['reasons'])) {
				if(in_array($reason['id'], $_GET['reasons'])) {
					echo"<label class='colitem-selected'><input type='checkbox' name='why_volunteer[".$reason['id']."]' value='".$reason['id']."' checked />".$reason['value']."</label><br />";
				} else {
				// not filtered when page returned
					echo"<label class='colitem'><input type='checkbox' name='why_volunteer[".$reason['id']."]' value='".$reason['id']."' />".$reason['value']."</label><br />";
				}
			} else {
				echo"<label class='colitem'><input type='checkbox' name='why_volunteer[".$reason['id']."]' value='".$reason['id']."' />".$reason['value']."</label><br />";
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