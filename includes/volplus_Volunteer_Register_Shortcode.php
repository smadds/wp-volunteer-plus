<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Volunteer Registration Form
function volplus_volunteer_register_func($atts = [], $content = null, $tag = '') {

// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);

// override default attributes with user attributes
	$volplus_atts = shortcode_atts([
		'rounddown' => 1,
		'roundup' => 1,
	], $atts, $tag);

//$opportunities = wp_remote_get(API_URL . 'opportunities?'.$_SERVER['QUERY_STRING'], array('headers' => array('Authorization' => 'Bearer '.API_KEY)));
//$response_code = wp_remote_retrieve_response_code($opportunities);
//$opportunities = json_decode($opportunities['body'], true);



//if($response_code == 200) {

//		if (isset($opportunities['rounded'])) { return $opportunities['rounded']; } else { return $opportunities['total']; }		
	
//  } elseif($response_code == 204) {
		
//		return '';
		
//	} else {
		
//		return 'Error';
		
//  }

	$wpuserid = null;
	global $reg_errors, $firstname, $lastname, $username, $useremail, $password, $repeatpassword;
	global $address1, $address2, $address3, $town, $county, $postcode, $telephone, $mobile, $availability_details;
	global $volunteering_experience, $volunteering_reason_info;

	if (isset($_POST['user_registration'])) {
		$reg_errors = new WP_Error;
		// sanitize user form input
		$firstname   =   ucfirst(strtolower(sanitize_user( $_POST['firstname'] )));
		$lastname   =   ucfirst(strtolower(sanitize_user( $_POST['lastname'] )));
		$username = strtolower($firstname . '-' . $lastname);
		$useremail  =   strtolower(sanitize_email( $_POST['useremail'] ));
		$password   =   esc_attr( $_POST['password'] );
		$repeatpassword   =   esc_attr( $_POST['repeatpassword'] );
        
		//change username if already exists
		if(username_exists($username)) {
			$x = 1;
			while(username_exists($username . $x)) {
				$x++;
			}
			$username .= $x;
		}
        
    
    
		if(empty( $firstname ) || empty( $lastname ) || empty( $username ) || empty( $useremail ) || empty($password)) {
			$reg_errors->add('field', 'Required form field is missing'); }    
		if ( 6 > strlen( $username ) ) {
			$reg_errors->add('username_length', 'Your username would be too short. Please make sure your the length of your First name + Last name are at least 5 characters' );}
		if ( !is_email( $useremail ) ) {
			$reg_errors->add( 'email_invalid', 'We can\'t make out your email address. Please check it\'s correct' ); }
		if ( email_exists( $useremail ) ) {
			$reg_errors->add( 'email', 'This email address is already in our system. Have you already registered?' ); }
		if ( 7 > strlen( $password ) ) {
			$reg_errors->add( 'password', 'Your password too short. It must be at least 8 characters' ); }
		if ( $password !== $repeatpassword ) {
			$reg_errors->add( 'repeatpassword', 'Your passwords do not match' ); }    
		if (is_wp_error( $reg_errors )) { 
			foreach ( $reg_errors->get_error_messages() as $error ) {
				$signUpError='<p style="color:#FF0000; text-aling:left;"><strong>We have a problem </strong>: '.$error . '<br /></p>';
			} 
		}
    
    
		if ( 1 > count( $reg_errors->get_error_messages() ) ) {
			$wpuser = array(
				'user_login'	=>	$username,
				'first_name'	=>	$firstname,
				'last_name'		=>	$lastname,
	         'user_email'	=>	$useremail,
	         'user_pass'		=>	$password,
	         'role'			=>	'volunteer'
	         );
			$wpuserid = wp_insert_user($wpuser);
//			var_dump_safe($user);
       
			echo "<h2>Your account has been created.</h2><p>You can log in with your username (" . $username . ") or email (" . $useremail . ")</p>";        

		}
	}

//echo "userid: '" . $userid . "'";	
	if(is_null($wpuserid)){?>
		<div class="volplus-col-12">
			<h2>Create your account</h2>
			<form action="" method="post" name="user_registration">

				<p><label class="volplus-col-2">Title (optional)  
					<select name="title" class="text"/>
						<option value="" disabled selected hidden>Select</option>
						<option value="Mr">Mr</option>
						<option value="Master">Master</option>
						<option value="Mrs">Mrs</option>
						<option value="Miss">Miss</option>
						<option value="Ms">Ms</option>
						<option value="Dr">Dr</option>
						<option value="Prof">Prof</option>
					</select>
				</label>

				<label class="volplus-col-5">First name <span class="error">*</span>  
					<input type="text" name="firstname" placeholder="First Name" required autofocus value="<?php echo $firstname;?>"/></label>
				<label class="volplus-col-5">Last name <span class="error">*</span>  
					<input type="text" name="lastname" placeholder="Last Name" required value="<?php echo $lastname;?>" /></label></p>
				<label class="volplus-col-8">Email address <span class="error">*</span>
					<input type="email" name="useremail"  placeholder="Email address" required value="<?php echo $useremail;?>" /></label>
				<p><label class="volplus-col-6">Password (8 characters or more) <span class="error">*</span>
					<input type="password" name="password" placeholder="Password" required value="<?php echo $password;?>" /></label>
				<label class="volplus-col-6">Password <span class="error">*</span>
					<input type="password" name="repeatpassword" placeholder="Repeat your password" required value="<?php echo $repeatpassword;?>" /></label></p>

				<h2 class="volplus-col-12"><br/>Address</h2>
				<label class="volplus-col-12">Address 1<span class="error">*</span>  
					<input type="text" name="address1" placeholder="Address 1" required value="<?php echo $address1;?>"/></label>
				<label class="volplus-col-12">Address 2  
					<input type="text" name="address2" placeholder="Address 2" value="<?php echo $address2;?>"/></label>
				<label class="volplus-col-12">Address 3  
					<input type="text" name="address3" placeholder="Address 3" value="<?php echo $address3;?>"/></label>
				<label class="volplus-col-4">Town<span class="error">*</span> 
					<input type="text" name="town" placeholder="Town" required value="<?php echo $town;?>"/></label>
				<label class="volplus-col-4">County<span class="error">*</span>   
					<input type="text" name="county" placeholder="County" required value="<?php echo $county;?>"/></label>
				<label class="volplus-col-3">Postcode<span class="error">*</span>   
					<input type="text" name="postcode" placeholder="Postcode" required value="<?php echo $postcode;?>"/></label>

				<h2 class="volplus-col-12"><br/>Contact Details</h2>
				<label class="volplus-col-6">Telephone<span class="error">*</span>  
					<input type="text" name="telephone" placeholder="Telephone number" required value="<?php echo $telephone;?>"/></label>
				<label class="volplus-col-6">Mobile  
					<input type="text" name="mobile" placeholder="Mobile number" value="<?php echo $mobile;?>"/></label>
				<h2 class="volplus-col-12"><br/>Interests & Activities</h2>
				<div class="volplus-col-6"><?php display_interests();?></div>
				<div class="volplus-col-6"><?php display_activities();?></div>				
				<div class="volplus-col-4"><?php display_availability_table();?></div>

				<h2 class="volplus-col-12"><br/>Availability</h2>
				<label class="volplus-col-8">Availability Details (specific details regarding your availability e.g. shift worker)  
					<textarea name="availability_details" rows="10">
					<?php echo $availability_details;?></textarea></label>

				<h2 class="volplus-col-12"><br/>Volunteering</h2>
				<label class="volplus-col-12">Volunteering Experience  
					<textarea name="volunteering_experience" rows="10">
					<?php echo $volunteering_experience;?></textarea></label>
				<label class="volplus-col-4">Why volunteer?  
					<?php display_reasons();?></label>				
				<label class="volplus-col-8">Further details on why volunteering  
					<textarea name="volunteering_reason_info" rows="10">
					<?php echo $volunteering_reason_info;?></textarea></label>

				<h2 class="volplus-col-12"><br/>About</h2>
				<label class="volplus-col-3">Date of birth
					<input type="date" name="date_birth"/></wbr></label>
				<label class="volplus-col-2">Gender  
					<select name="gender"/>
						<option value="" disabled selected hidden>Select</option>
						<option value=1>Male</option>
						<option value=2>Female</option>
						<option value=3>Prefer not to say</option>
						<option value=4>Not Known</option>
					</select>
				</label>
				<label class="volplus-col-3">Employment Status  
					<?php employment_status();?></label>				
				<label class="volplus-col-2">Ethnicity 
					<?php ethnicity();?></label>				
				<label class="volplus-col-2">Disability 
					<?php disability();?></label>				


				<div class="volplus-col-12"><input type="submit" name="user_registration" value="SignUp" /></div>

<?php $a=$GLOBALS['volunteer_fields'];$a=json_decode($a['body'], true);print_r_safe($a);?>				

			</form>
		</div>
		<?php if(isset($signUpError)){echo '<div>'.$signUpError.'</div>';}
	}?>


<?php
};

// Register the shortcode.

add_shortcode( 'volplus-volunteer-register', 'volplus_volunteer_register_func' );


// Enable shortcodes in widgets
add_filter('widget_text','do_shortcode');
