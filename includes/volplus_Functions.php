<?php

// Display Organisation details

function getOrgDetails($id) {
	$organisation = wp_remote_get(API_URL . 'organisations/'.$id, array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
	$response_code = wp_remote_retrieve_response_code($organisation);
	if($response_code == 200) {
		$organisation = json_decode($organisation['body'], true);
		return $organisation;
	} else {
		wp_redirect("/404");
		exit;
	}
}


// get volunteer details given ID - WITH CORRECTIONS
function get_volunteer_details($id) {
	$endpoint = 'volunteers/' . $id;
	$response = wp_remote_get(API_URL . $endpoint, array(
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array('Authorization' => 'Bearer '.API_KEY),
		'body' => null,
		'cookies' => array()
		)
	);
// ******************** FIX /volunteers GET to match PUSH ************************

	$bodyobj = json_decode($response['body']);
//var_dump_safe($bodyobj);		

	$bodyobj->telephone = $bodyobj->telephone_number;unset($bodyobj->telephone_number);	//different name - API documentation error
	$bodyobj->mobile = isset($bodyobj->mobile_number) ? $bodyobj->mobile_number : null;unset($bodyobj->mobile_number);	//different name - API documentation error

	$temparray = [];
	foreach($bodyobj->interests as $key=>$data) array_push($temparray, $data->id);
	$bodyobj->interests = $temparray;

	$temparray = [];
	foreach($bodyobj->activities as $key=>$data) array_push($temparray, $data->id);
	$bodyobj->activities = $temparray;

	if(isset($bodyobj->why_volunteer)){
		$temparray = [];
		foreach($bodyobj->why_volunteer as $key=>$data) array_push($temparray, $data->id);
		$bodyobj->reasons = $temparray; unset($bodyobj->why_volunteer);	//different name, but as documented
	}

	if(isset($bodyobj->disability_type)){
		$temparray = [];
		foreach($bodyobj->disability_type as $key=>$data) array_push($temparray, $data->id);
		$bodyobj->disabilities = $temparray; unset($bodyobj->disability_type); //different name, but as documented
	}

	$temparray = [];
	foreach($bodyobj->availability as $key=>$data) if($data) $temparray[$key] = $data;
	$bodyobj->availability = $temparray;


	$bodyobj->gender = isset($bodyobj->gender) ? $bodyobj->gender->id : null;
	$bodyobj->ethnicity = isset($bodyobj->ethnicity) ? $bodyobj->ethnicity->id : null;
	$bodyobj->how_heard = isset($bodyobj->how_heard) ? $bodyobj->how_heard->id : null;
	$bodyobj->disability = isset($bodyobj->disability) ? $bodyobj->disability->id : null;
	$bodyobj->employment = isset($bodyobj->employment) ? $bodyobj->employment->id : null;
	$bodyobj->volunteering_reason_info = isset($bodyobj->why_volunteer_info) ? $bodyobj->why_volunteer_info : null;
	$bodyobj->volunteering_experience = isset($bodyobj->experience) ? $bodyobj->experience : null;	

$response['body'] = json_encode($bodyobj);
	
// *************************************************************************

//var_dump_safe($response);		

	return $response;
}



// check logged in cookie & reset the countdown if already set
function is_volplus_user_logged_in(){
	if(isset($_COOKIE['volplus_user_first_name'])) setcookie('volplus_user_first_name', $_COOKIE['volplus_user_first_name'], time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
	if(isset($_COOKIE['volplus_user_last_name'])) setcookie('volplus_user_last_name', $_COOKIE['volplus_user_last_name'], time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
//	if(isset($_COOKIE['volplus_user'])) setcookie('volplus_user', $_COOKIE['volplus_user'], time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
	if(isset($_COOKIE['volplus_user_id'])){
		setcookie('volplus_user_id', $_COOKIE['volplus_user_id'], time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
		return true;
	} else {
		return false;
	}
}


// remove magic quotes
function remove_magic_quotes($array) {
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = remove_magic_quotes($v);
        } else {
            $array[$k] = stripslashes($v);
        }
    }
    return $array;
}

//search in sub arrays for item
function array_multi_search($needle,$haystack){
//	echo_safe($needle);
//	var_dump_safe($haystack);
	foreach($haystack as $key=>$data){
		if(in_array($needle,$data)) return $needle;
	}
return false;
}


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
			var $interests = array();		// required
			var $activities = array();	// required
//			var $mon_mor, $mon_aft, $mon_eve;
//			var $tue_mor, $tue_aft, $tue_eve;
//			var $wed_mor, $wed_aft, $wed_eve;
//			var $thu_mor, $thu_aft, $thu_eve;
//			var $fri_mor, $fri_aft, $fri_eve;
//			var $sat_mor, $sat_aft, $sat_eve;
//			var $sun_mor, $sun_aft, $sun_eve;
			var $availability = array();
			var $availability_details;
			var $volunteering_experience;
			var $reasons = array();
			var $volunteering_reason_info;
			var $date_birth;		// required unless prefer not say
			var $date_birth_prefer_not_say=0;
			var $gender=0;			// required 1:male, 2:female, 3:prefer not to say, 4:not known
			var $employment=0;		//required specific integer from Volunteer fields
			var $ethnicity=0;		//required specific integer from Volunteer fields
			var $disability=0;		// required 1:yes, 2:no, 3:prefer not to say
			var $disabilities = array();	// required if disability=1. list of objects that each represent a single disability. see volunteer fields
			var $password;			// required
			var $password_confirmation; // required
			var $how_heard=0;		//required specific integer from Volunteer fields
}


// display disability types (multi-select)
function disability_type($selected){
	echo '<div class="colcontainer"  id="disability-type">';
	if(in_array('disabilities',$GLOBALS['volunteer_fields'])){   //disability types may not be defined          
		foreach($GLOBALS['volunteer_fields']['disabilities'] as $disability) {
			if(in_array($disability['id'], $selected)) echo"<label class='colitem-selected'><input type='checkbox' name='disabilities[]' value='".$disability['id']."' checked";
			else echo"<label class='colitem'><input type='checkbox' id='disabilities-".$disability['id']."' name='disabilities[]' value='".$disability['id']."'";
			echo ">".$disability['value']."</label><br />";
		}
	}
	echo '</div>';
}

// display gender options
function gender($selected) {
	$genders = array( 
		['id'=>1, 'value'=>'Male'], 
		['id'=>2, 'value'=> 'Female'],
		['id'=>3, 'value'=> 'Prefer not to say'],
//		['id'=>4, 'value'=> 'Not known'] // option not shown for web form
	);
	echo "<select id='gender' name='gender' id='gender' required>";
		echo "<option value='' ";
		if($selected=="") echo 'selected'; // nothing selected
		echo ">Select</option>";
		foreach ($genders as $gender) {
			echo "<option value=" . $gender['id'];
			if($gender['id'] == $selected) echo " selected";
			echo ">" . $gender['value'] . "</option>";
		}
	echo "</select>";
}

// display disability options
function disability($selected) {
	$thedisabilities = array( 
		['id'=>1, 'value'=>'Yes'], 
		['id'=>2, 'value'=> 'No'],
		['id'=>3, 'value'=> 'Prefer not to say'],
//		['id'=>4, 'value'=> 'Not known'] // option not shown for web form
	);
	echo "<select id='disability' name='disability' required>";
		echo "<option value='' ";
		if($selected=="") echo 'selected'; // nothing selected
		echo ">Select</option>";
		foreach ($thedisabilities as $thedisability) {
			echo "<option value=" . $thedisability['id'];
			if($thedisability['id'] == $selected) echo " selected";
			echo ">" . $thedisability['value'] . "</option>";
		}
	echo "</select>";
}

// display how_heard options
function how_heard($selected) {
	echo "<select id='how_heard' name='how_heard' required>";
		echo "<option value='' ";
		if($selected=="") echo 'selected';
		echo ">Select</option>";
		foreach ($GLOBALS['volunteer_fields']['how_heard'] as $how_heard) {
			echo "<option value=" . $how_heard['id'];
			if($how_heard['id'] == $selected) echo " selected";
			echo ">" . $how_heard['value'] . "</option>";
		}
	echo "</select>";
}

// display ethnicity options
function ethnicity($selected) {
	echo "<select id='ethnicity' name='ethnicity' required>";
		echo "<option value='' ";
		if($selected=="") echo 'selected';
		echo ">Select</option>";
		foreach ($GLOBALS['volunteer_fields']['ethnic_groups'] as $ethnic_group) {
			echo "<option value=" . $ethnic_group['id'];
			if($ethnic_group['id'] == $selected) echo " selected";
			echo ">" . $ethnic_group['value'] . "</option>";
		}
	echo "</select>";
}

// display employment statuses
function employment($selected) {
	echo "<select id='employment' name='employment' required>";
		echo "<option value='' ";
		if($selected=="") echo 'selected';
		echo ">Select</option>";
		foreach ($GLOBALS['volunteer_fields']['employment_statuses'] as $employment_status) {
			echo "<option value=" . $employment_status['id'];
			if($employment_status['id'] == $selected) echo " selected";
			echo ">" . $employment_status['value'] . "</option>";
		}
	echo "</select>";
}


// display volunteering reasons (multi-select)
function display_reasons($selected) {
	echo '<div class="colcontainer">';             
	foreach($GLOBALS['volunteer_fields']['volunteering_reasons'] as $reason) {
		if(in_array($reason['id'], $selected)) echo"<label class='colitem-selected'><input type='checkbox' name='reasons[]' value='".$reason['id']."' checked";
		else echo"<label class='colitem'><input type='checkbox' id='reason-".$reason['id']."' name='reasons[]' value='".$reason['id']."'";
		echo ">".$reason['value']."</label><br />";
	}
	echo '</div>';
}


// display interests (multi-select)
function display_interests($selected) {
	echo '<div class="colcontainer">';             
	foreach($GLOBALS['volunteer_interests'] as $interest) {
		if(in_array($interest['id'], $selected)) echo"<label class='colitem-selected'><input type='checkbox' name='interests[]' value='".$interest['id']."' checked";
		else echo"<label class='colitem'><input type='checkbox' id='interest-".$interest['id']."' name='interests[]' value='".$interest['id']."'";
		echo " onclick='return LimitInterests()'>".$interest['interest']."</label><br />";
	}
	echo '</div>';
}

//display activities (multi-select)
function display_activities($selected){
	echo '<div class="colcontainer">';             
	foreach($GLOBALS['volunteer_activities'] as $activity) {
		if(in_array($activity['id'], $selected)) echo"<label class='colitem-selected'><input type='checkbox' name='activities[]' value='".$activity['id']."' checked";
		else echo"<label class='colitem'><input type='checkbox' id='activity-".$activity['id']."' name='activities[]' value='".$activity['id']."'";
		echo " onclick='return LimitActivities()'>".$activity['activity']."</label><br />";
	}
	echo '</div>';
}

// display availability options (multi-select)
function display_availability_simple($selected) {
	$availabilities = array( 
		['id'=>1, 'value'=> 'Weekdays (During the day)'], 
		['id'=>2, 'value'=> 'Weekdays (Evenings)'],
		['id'=>3, 'value'=> 'Weekends (During the day)'],
		['id'=>4, 'value'=> 'Weekends (Evenings)']
	);
	echo '<div class="colcontainer">';             
	foreach($availabilities as $availability) {
		if(in_array($availability['id'], $selected)) echo"<label class='colitem-selected'><input type='checkbox' name='availability[]' value='".$availability['id']."' checked";
		else echo"<label class='colitem'><input type='checkbox' name='availability[]' value='".$availability['id']."'";
		echo ">".$availability['value']."</label><br />";
	}
	echo '</div>';
}


// display availability table
function display_availability_table($selected){
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
			$period = $periods[$index];
// var_dump_safe($period);
// var_dump_safe($selected);
			echo '<td><input type="checkbox" id="availability-'.$index.'" class="volplus-checkbox" name="'.$period.'"';
			if(array_key_exists($period, $selected)) echo ' checked';
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
		echo "<pre style='white-space:pre-wrap'>";
		print_r($a);
		echo "</pre>";
	}
}
function var_dump_safe ($a){
	if(defined('WP_DEBUG') && WP_DEBUG === true && current_user_can('administrator')) {
		echo "<pre style='white-space:pre-wrap'>";
		var_dump($a);
		echo "</pre>";
	}
}

function echo_safe ($a){
	if(defined('WP_DEBUG') && WP_DEBUG === true && current_user_can('administrator')) {
		echo $a;
	}
}

function alert_safe ($a){
	if(defined('WP_DEBUG') && WP_DEBUG === true && current_user_can('administrator')) {
		echo '<script type="text/javascript">window.alert("'.$a.'")</script>';
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

// AJAX FUNCTIONS***********************************************************

// AJAX LOGIN ********************************
function ajax_login_init(){
    wp_register_script('volplus_scripts', VOLPLUS_URL . 'includes/volplus_scripts.js', array('jquery') ); 
    wp_enqueue_script('volplus_scripts');
    wp_localize_script( 'volplus_scripts', 'ajax_login_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Checking your details, please wait...')
    ));
    // Enable the user with no privileges to run ajax_login() in AJAX
}

function ajax_login(){
	// First check the nonce, if it fails the function will break
	check_ajax_referer( 'ajax-login-nonce', 'security' );
	// Nonce is checked, get the POST data and sign user on
	$volpluslogin = array(
		'email_address' => $_POST['email_address'],
		'password' => $_POST['password'],
		'type' => 1 // 1=volunteer, 2=organisation
	);
	$response = wp_remote_post(API_URL . 'login', array(
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array('Authorization' => 'Bearer '.API_KEY),
		'body' => (array) $volpluslogin,
		'cookies' => array()
		)
	);
			
	$responsebody = (array) json_decode($response['body']);
	if ( $response['response']['code'] !== 200) {
		$error_message = $response['response']['message'];
//		echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
		$body = "";
		foreach($responsebody as $key=>$data){
//			echo "<br/>".$key.": ";
			foreach($data as $data2){
				if($data2=="The password field is incorrect." || $data2=="The email address does not exist.")$data2="Login error. Please check your login details";
				$body .= $data2;
			}
		}
		echo json_encode(array('loggedin'=>false, 'message'=>__($body)));
		setcookie('volplus_user_id',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
		setcookie('volplus_user_first_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
		setcookie('volplus_user_last_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
		setcookie('volplus_user',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
	} else {
// get user details
//		echo json_encode(array('loggedin'=>true, 'message'=>$body, 'volplus_id'=>$responsebody['id']));die();
//		setcookie('volplus_user_id',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
//		$response2 = get_volunteer_details($responsebody['id']);
		$response2 = get_volunteer_details($responsebody['id']);
		$volunteer = json_decode($response2['body']);
		if ( $response2['response']['code'] !== 200) {
			$error_message = $response2->response->message;
//			echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
			$body = "";
			foreach($volunteer as $key=>$data){
//				echo "<br/>".$key.": ";
				foreach($data as $data2){
					$body .= $data2;
				}
			}
			echo json_encode(array('loggedin'=>false, 'message'=>__($body)));
			setcookie('volplus_user_id',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_first_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_last_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
//			setcookie('volplus_user',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
		}else{
	 		setcookie('volplus_user_id', $responsebody['id'], time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_first_name', $volunteer->first_name, time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_last_name', $volunteer->last_name, time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
//			setcookie('volplus_user', json_encode($volunteer), time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );

			echo json_encode(array(
				'loggedin'=>true,
				'message'=>__('Login successful'),
				'response'=> $response2,
				'first_name'=> $volunteer->first_name,
				'last_name'=> $volunteer->last_name,
				'volplus_id'=> $responsebody['id']
			));
		}
	}
	die();
}


// AJAX ENQUIRE ********************************
function ajax_enquire_init(){
    wp_register_script('volplus_scripts', VOLPLUS_URL . 'includes/volplus_scripts.js', array('jquery') ); 
    wp_enqueue_script('volplus_scripts');
    wp_localize_script( 'volplus_scripts', 'ajax_enquire_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Registering your interest, please wait...')
    ));
    // Enable the user with no privileges to run ajax_login() in AJAX
}

function ajax_enquire(){
	// First check the nonce, if it fails the function will break
//	check_ajax_referer( 'ajax-enquire-nonce', 'security' );
	// Nonce is checked, get the POST data and sign user on
	$volplusenquiry = array(
		'volunteer' => $_COOKIE['volplus_user_id'],
		'opportunity' => $_COOKIE['volplus_opp_id'],
		'notes'=> $_POST['interested_notes']
	);

	$response = wp_remote_post(API_URL . 'enquiries', array(
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array('Authorization' => 'Bearer '.API_KEY),
		'body' => (array) $volplusenquiry,
		'cookies' => array()
		)
	);
			
	$responsebody = (array) json_decode($response['body']);
	if ( $response['response']['code'] !== 201) {
		$error_message = $response['response']['message'];
		$body = "";
		foreach($responsebody as $key=>$data){
			foreach($data as $data2){
				$body .= $data2;
			}
		}
		echo json_encode(array(
			'message'=>__($body),
			'enquiry_id'=> null,
//			'debug'=>$volplusenquiry
		));
	} else {
		echo json_encode(array(
			'message'=>__('Your interest has been registered'),
			'enquiry_id'=> $responsebody['id'],
//			'debug'=>$response
		));
	}
	die();
}

// AJAX REGISTER USER **************************
function ajax_userreg_init(){
    wp_register_script('volplus_scripts', VOLPLUS_URL . 'includes/volplus_scripts.js', array('jquery') ); 
    wp_enqueue_script('volplus_scripts');
    wp_localize_script( 'volplus_scripts', 'ajax_userrreg_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Submitting data, please wait...')
    ));
}

function ajax_addvolunteer(){
	// First check the nonce, if it fails the function will break
	check_ajax_referer( 'ajax-addvolunteer-nonce', 'security' );
	// Nonce is checked, get the POST data and sign user on
	$volplusaddvolunteer = array(
		'email_address' => $_POST['email_address'],
		'password' => $_POST['password'],
		'type' => 1 // 1=volunteer, 2=organisation
	);
	$response = wp_remote_post(API_URL . 'volunteers', array(
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array('Authorization' => 'Bearer '.API_KEY),
		'body' => (array) $volpluslogin,
		'cookies' => array()
		)
	);
			
	$responsebody = (array) json_decode($response['body']);
	if ( $response['response']['code'] !== 200) {
		$error_message = $response['response']['message'];
//		echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
		$body = "";
		foreach($responsebody as $key=>$data){
//			echo "<br/>".$key.": ";
			foreach($data as $data2){
				if($data2=="The password field is incorrect." || $data2=="The email address does not exist.")$data2="Login error. Please check your login details";
				$body .= $data2;
			}
		}
		echo json_encode(array('loggedin'=>false, 'message'=>__($body)));
		setcookie('volplus_user_id',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
		setcookie('volplus_user_first_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
		setcookie('volplus_user_last_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
//		setcookie('volplus_user',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
	} else {
// get user details
//		echo json_encode(array('loggedin'=>true, 'message'=>$body, 'volplus_id'=>$responsebody['id']));die();
//		setcookie('volplus_user_id',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
//		$response2 = get_volunteer_details($responsebody['id']);
		$response2 = get_volunteer_details($responsebody['id']);
		$volunteer = json_decode($response2['body']);
		if ( $response2['response']['code'] !== 200) {
			$error_message = $response2->response->message;
//			echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
			$body = "";
			foreach($volunteer as $key=>$data){
//				echo "<br/>".$key.": ";
				foreach($data as $data2){
					$body .= $data2;
				}
			}
			echo json_encode(array('loggedin'=>false, 'message'=>__($body)));
			setcookie('volplus_user_id',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_first_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_last_name',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
//			setcookie('volplus_user',0 , time()-60, COOKIEPATH, COOKIE_DOMAIN );
		}else{
	 		setcookie('volplus_user_id', $responsebody['id'], time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_first_name', $volunteer->first_name, time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
			setcookie('volplus_user_last_name', $volunteer->last_name, time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
//			setcookie('volplus_user', json_encode($volunteer), time()+(3600 * get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );

			echo json_encode(array(
				'loggedin'=>true,
				'message'=>__('Login successful'),
				'response'=> $response2,
				'first_name'=> $volunteer->first_name,
				'last_name'=> $volunteer->last_name,
				'volplus_id'=> $responsebody['id']
			));
		}
	}
	die();
}

//AJAX GET PAGE
add_action('wp_ajax_nopriv_volplus_get_legal', 'volplus_get_legal');
add_action('wp_ajax_volplus_get_legal', 'volplus_get_legal');
function volplus_get_page(){
	if(null !==(get_option('volplus_compliancepage'))) {
		$output = apply_filters('the_content', get_post_field('post_content', get_option('volplus_compliancepage')));
		echo json_encode($output);
		die;
	}
}


//convert dob to age band
function dobToAgeBand($age){
	$yourage = (int) DateTime::createFromFormat('Y-m-d', $age)
     ->diff(new DateTime('now'))
     ->y;
	if($yourage < 16) return("Under 15");
	elseif($yourage <19) return("15-18");
	elseif($yourage <26) return("19-25");
	elseif($yourage <45) return("26-44");
	elseif($yourage <65) return("45-64");
	elseif($yourage >64) return("Over 65");
	else return("Error");
}


