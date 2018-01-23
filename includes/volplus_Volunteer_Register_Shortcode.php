<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Volunteer Registration Form
function volplus_volunteer_register_func($atts = [], $content = null, $tag = '') {

// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);

// override default attributes with user attributes
	$volplus_atts = shortcode_atts([
		'wordpress-account' => 1,
//		'roundup' => 1,
	], $atts, $tag);

//	var_dump_safe($GLOBALS['volunteer_activities']);


// remove magic quotes

//	if (get_magic_quotes_gpc()) {
//		echo_safe ("<h1>MAGIC QUOTES ENABLED</H1>");
		$_GET    = remove_magic_quotes($_GET);
		$_POST   = remove_magic_quotes($_POST);
		$_COOKIE = remove_magic_quotes($_COOKIE);
//	}

	
	
	$wpuserid = null;
	$volunteer = new volplus_volunteer();
		
	if (isset($_POST['user_registration'])) {
		$reg_errors = new WP_Error;
		// sanitize user form input


		if(isset($_POST['title']))$volunteer->title = stripslashes(esc_html( $_POST['title']));
		if(isset($_POST['first_name']))$volunteer->first_name = ucfirst(strtolower(sanitize_user( $_POST['first_name'] )));
		if(isset($_POST['last_name']))$volunteer->last_name = ucfirst(strtolower(sanitize_user( $_POST['last_name'] )));
		$volunteer->username = strtolower($volunteer->first_name . '-' . $volunteer->last_name);
		if(isset($_POST['email_address']))$volunteer->email_address = strtolower(sanitize_email( $_POST['email_address'] ));
		if(isset($_POST['password']))$volunteer->password = esc_html( $_POST['password'] );
		if(isset($_POST['password_confirmation']))$volunteer->password_confirmation = esc_html( $_POST['password_confirmation'] );
		if(isset($_POST['address_line_1']))$volunteer->address_line_1 = stripslashes(esc_html( $_POST['address_line_1']));
		if(isset($_POST['address_line_2']))$volunteer->address_line_2 = stripslashes(esc_html( $_POST['address_line_2']));
		if(isset($_POST['address_line_3']))$volunteer->address_line_3 = stripslashes(esc_html( $_POST['address_line_3']));
		if(isset($_POST['town']))$volunteer->town = stripslashes(esc_html( $_POST['town']));
		if(isset($_POST['county']))$volunteer->county = stripslashes(esc_html( $_POST['county']));
		if(isset($_POST['postcode']))$volunteer->postcode = stripslashes(esc_html( $_POST['postcode']));
		if(isset($_POST['telephone']))$volunteer->telephone = stripslashes(esc_html( $_POST['telephone']));
		if(isset($_POST['mobile']))$volunteer->mobile = stripslashes(esc_html( $_POST['mobile']));
		if(isset($_POST['interests'])){
			foreach($_POST['interests'] as $key=>$value){
				$_POST['interests'][$key] = array_map('intval', $value);}
			$volunteer->interests = $_POST['interests'];}
		if(isset($_POST['activities'])){
			foreach($_POST['activities'] as $key=>$value){
				$_POST['activities'][$key] = array_map('intval', $value);}
			$volunteer->activities = $_POST['activities'];}
		if(isset($_POST['availability_details']))$volunteer->availability_details = stripslashes(esc_html( $_POST['availability_details']));
		if(isset($_POST['volunteering_experience']))$volunteer->volunteering_experience = stripslashes(esc_html($_POST['volunteering_experience']));
		if(isset($_POST['reasons'])){
			foreach($_POST['reasons'] as $key=>$value){
				$_POST['reasons'][$key] = array_map('intval', $value);}
			$volunteer->reasons = $_POST['reasons'];}
		if(isset($_POST['volunteering_reason_info']))$volunteer->volunteering_reason_info = stripslashes(esc_html($_POST['volunteering_reason_info']));
		if(isset($_POST['date_birth']))$volunteer->date_birth = esc_html( $_POST['date_birth']);
		if(isset($_POST['gender']))$volunteer->gender = (int) $_POST['gender'];$_POST['gender'] = $volunteer->gender;
		if(isset($_POST['employment']))$volunteer->employment = (int) $_POST['employment'];$_POST['employment'] = $volunteer->employment;
		if(isset($_POST['ethnicity']))$volunteer->ethnicity = (int) $_POST['ethnicity'];$_POST['ethnicity'] = $volunteer->ethnicity;
		if(isset($_POST['disability']))$volunteer->disability = (int) $_POST['disability'];$_POST['disability'] = $volunteer->disability;
		if(isset($_POST['disabilities'])){
			foreach($_POST['disabilities'] as $key=>$value){
				$_POST['disabilities'][$key] = array_map('intval', $value);}
			$volunteer->disabilities = $_POST['disabilities'];}


		$periods = array(
			'mon_mor','mon_aft','mon_eve',
			'tue_mor','tue_aft','tue_eve',
			'wed_mor','wed_aft','wed_eve',
			'thu_mor','thu_aft','thu_eve',
			'fri_mor','fri_aft','fri_eve',
			'sat_mor','sat_aft','sat_eve',
			'sun_mor','sun_aft','sun_eve'
		);
		foreach($periods as $period){
			if(isset($_POST[$period]) && $_POST[$period]=="on"){
				$volunteer->availability[$period]=1;
				$_POST[$period]=1;
			}		
		}
        
		//change username if already exists
		$vol = $volunteer->username;
		if(username_exists($vol)) {
			$x = 1;
			while(username_exists($vol . $x)) {
				$x++;
			}
			$volunteer->username .= $x;
		}
		
		// flag if age not given
		if(isset($_POST['date_birth']) && $_POST['date_birth']!==""){
			$volunteer->date_birth = implode('/', array_reverse(explode('-', $volunteer->date_birth))); // put into UK date order dd-MM-yyyy
 			$volunteer->date_birth_prefer_not_say = NULL;
			$_POST['date_birth'] = $volunteer->date_birth;
			$_POST['date_birth_prefer_not_say'] = NULL;
		} else {
			$volunteer->date_birth_prefer_not_say = 1;
			$_POST['date_birth_prefer_not_say'] = 1;
		}
		
//	var_dump_safe($volunteer);
        
    
    
		if(empty( $volunteer->first_name ) || empty( $volunteer->last_name ) || empty( $volunteer->username ) || empty( $volunteer->email_address ) || empty($volunteer->password)) {
			$reg_errors->add('field', 'Required form field is missing'); }    
		if ( 6 > strlen( $volunteer->username ) ) {
			$reg_errors->add('username_length', 'Your username would be too short. Please make sure your the length of your First name + Last name are at least 5 characters' );}
		if ( !is_email( $volunteer->email_address ) ) {
			$reg_errors->add( 'email_invalid', 'We can\'t make out your email address. Please check it\'s correct' ); }
		if ( $volplus_atts['wordpress-account'] && email_exists( $volunteer->email_address ) ) {
			$reg_errors->add( 'email', 'This email address is already in our system. Have you already registered?' ); }
		if ( 7 > strlen( $volunteer->password ) ) {
			$reg_errors->add( 'password', 'Your password too short. It must be at least 8 characters' ); }
		if ( $volunteer->password !== $volunteer->password_confirmation ) {
			$reg_errors->add( 'password_confirmation', 'Your passwords do not match' ); }    
		if (is_wp_error( $reg_errors )) { 
			foreach ( $reg_errors->get_error_messages() as $error ) {
				$signUpError='<p style="color:#FF0000; text-align:left;"><strong>We have a problem </strong>: '.$error . '<br /></p>';
			} 
		}
    
    
		if ( 1 > count( $reg_errors->get_error_messages() ) ) {
			//add VolPlus account
			print_r_safe($_POST);
			$response = wp_remote_post(API_URL . 'volunteers', array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Authorization' => 'Bearer '.API_KEY),
				'body' => (array) $_POST,
//				'cookies' => array()
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				echo "Something went wrong: $error_message";
			} else {
				print_r_safe( $response );
			}
			
			if ($volplus_atts['wordpress-account']){ // create WP account enabled
				$wpuser = array(
					'user_login'	=>	$volunteer->username,
					'first_name'	=>	$volunteer->first_name,
					'last_name'		=>	$volunteer->last_name,
	         	'user_email'	=>	$volunteer->email_address,
		         'user_pass'		=>	$volunteer->password,
		         'role'			=>	'volunteer',
	   	      );
				$wpuserid = wp_insert_user($wpuser);
//			var_dump_safe($user);

				$wplogin = array(
					'user_login'	=>	$volunteer->username,
					'user_password'=>	$volunteer->password,
					'remember'		=>	true
				);
			
				$user = wp_signon( $wplogin, is_ssl());
				if ( is_wp_error( $user ) ) {
					echo $user->get_error_message();
				}
//			var_dump_safe($user);			
       		echo "<h2>Your account has been created.<br/>Your username is: " . $volunteer->username . ".</h2>";
				echo "<p>You have been logged in with the password you set.</p>";        
       	}
		}
	}

//echo "userid: '" . $userid . "'";	
//	if(is_null($wpuserid)){
		?>
		<div class="volplus-col-12">
			<h2>Create your account</h2>
			<?php if(isset($signUpError)) echo '<div>'.$signUpError.'</div>'?>
			<form action="" method="post" name="user_registration">

				<p><label class="volplus-col-2">Title (optional)  
					<select name="title" class="text">
						<option value="" <?php if($volunteer->title=="") echo 'selected';?> >Select</option>
						<option value="Mr" <?php if($volunteer->title=="Mr") echo 'selected';?> >Mr</option>
						<option value="Master" <?php if($volunteer->title=="Master") echo 'selected';?> >Master</option>
						<option value="Mrs" <?php if($volunteer->title=="Mrs") echo 'selected';?> >Mrs</option>
						<option value="Miss" <?php if($volunteer->title=="Miss") echo 'selected';?> >Miss</option>
						<option value="Ms" <?php if($volunteer->title=="Ms") echo 'selected';?> >Ms</option>
						<option value="Dr" <?php if($volunteer->title=="Dr") echo 'selected';?> >Dr</option>
						<option value="Prof" <?php if($volunteer->title=="Prof") echo 'selected';?> >Prof</option>
					</select>
				</label>

				<label class="volplus-col-5">First name <span class="error">*</span>  
					<input type="text" name="first_name" placeholder="First Name" required autofocus value="<?php echo $volunteer->first_name?>"/></label>
				<label class="volplus-col-5">Last name <span class="error">*</span>  
					<input type="text" name="last_name" placeholder="Last Name" required value="<?php echo $volunteer->last_name?>" /></label></p>
				<label class="volplus-col-8">Email address <span class="error">*</span>
					<input type="email" name="email_address"  placeholder="Email address" required value="<?php echo $volunteer->email_address?>" /></label>
				<p><label class="volplus-col-6">Password (8 characters or more) <span class="error">*</span>
					<input type="password" name="password" placeholder="Password" required value="<?php echo $volunteer->password?>" /></label>
				<label class="volplus-col-6">Password <span class="error">*</span>
					<input type="password" name="password_confirmation" placeholder="Repeat your password" required value="<?php echo $volunteer->password_confirmation?>" /></label></p>

				<h2 class="volplus-col-12"><br/>Address</h2>
				<label class="volplus-col-12">Address 1<span class="error">*</span>  
					<input type="text" name="address_line_1" placeholder="Address 1" required value="<?php echo $volunteer->address_line_1?>"/></label>
				<label class="volplus-col-12">Address 2  
					<input type="text" name="address_line_2" placeholder="Address 2" value="<?php echo $volunteer->address_line_2?>"/></label>
				<label class="volplus-col-12">Address 3  
					<input type="text" name="address_line_3" placeholder="Address 3" value="<?php echo $volunteer->address_line_3?>"/></label>
				<label class="volplus-col-4">Town<span class="error">*</span> 
					<input type="text" name="town" placeholder="Town" required value="<?php echo $volunteer->town?>"/></label>
				<label class="volplus-col-4">County<span class="error">*</span>   
					<input type="text" name="county" placeholder="County" required value="<?php echo $volunteer->county?>"/></label>
				<label class="volplus-col-3">Postcode<span class="error">*</span>   
					<input type="text" name="postcode" placeholder="Postcode" required value="<?php echo $volunteer->postcode?>"/></label>

				<h2 class="volplus-col-12"><br/>Contact Details</h2>
				<label class="volplus-col-6">Telephone<span class="error">*</span>  
					<input type="text" name="telephone" placeholder="Telephone number" required value="<?php echo $volunteer->telephone?>"/></label>
				<label class="volplus-col-6">Mobile  
					<input type="text" name="mobile" placeholder="Mobile number" value="<?php echo $volunteer->mobile?>"/></label>
				<h2 class="volplus-col-12"><br/>Interests & Activities</h2>
				<label class="volplus-col-6">Interests
					<?php display_interests($volunteer->interests);?></label>
				<label class="volplus-col-6">Activities
					<?php display_activities($volunteer->activities);?></label>				
				<h2 class="volplus-col-12"><br/>Availability</h2>
				<label class="volplus-col-4">When are you available?
					<?php display_availability_table($volunteer->availability);?></label>
				<label class="volplus-col-8">Availability Details (specific details regarding your availability e.g. shift worker)  
					<textarea name="availability_details" rows="10"><?php if(isset($volunteer->availability_details)) echo $volunteer->availability_details?></textarea></label>
				<h2 class="volplus-col-12"><br/>Volunteering</h2>
				<label class="volplus-col-12">Volunteering Experience  
					<textarea name="volunteering_experience" rows="10"><?php if(isset($volunteer->volunteering_experience)) echo $volunteer->volunteering_experience?></textarea></label>
				<label class="volplus-col-4">Why volunteer?  
					<?php display_reasons($volunteer->reasons);?></label>				
				<label class="volplus-col-8">Further details on why volunteering  
					<textarea name="volunteering_reason_info" rows="10"><?php if(isset($volunteer->volunteering_reason_info)) echo $volunteer->volunteering_reason_info?></textarea></label>
				<h2 class="volplus-col-12"><br/>About</h2>
				<label class="volplus-col-3">Date of birth (optional) 
					<input type="date" name="date_birth" format= "dd/MM/yyyy" value="<?php echo $volunteer->date_birth;?>"/></label>
				<input type="hidden" name="date_birth_prefer_not_say" value="1"/>
				<label class="volplus-col-2">Gender<span class="error">*</span>   
					<?php gender($volunteer->gender);?></label>				
				<label class="volplus-col-3">Employment Status<span class="error" >*</span>   
					<?php employment_status($volunteer->employment);?></label>				
				<label class="volplus-col-4">Ethnicity 
					<?php ethnicity($volunteer->ethnicity);?></label>				
				<label class="volplus-col-3">Disability 
					<?php disability($volunteer->disability);?></label>				
				<label class="volplus-col-3" id="display-details-label" <?php if($volunteer->disability !== "2") echo " style='display:none;'"?>>Details
					<?php disability_type($volunteer->disabilities);?></label>				


				<div class="volplus-col-12"><input type="submit" name="user_registration" value="SignUp" /></div>

			</form>
		</div>

<?php echo "POST:<br/>" . json_encode($_POST)?><br/>
<?php echo "volunteer:<br/>" . json_encode($volunteer)?><br/>
<div class="volplus-col-6"><?php var_dump_safe($_POST) ?></div>
<div class="volplus-col-6"><?php var_dump_safe($volunteer) ?></div>

	<script type="text/javascript" >
			document.getElementById('disability-type').onchange = function(e) {
				if (document.getElementById("disability-type").value == 2) {
	 				document.getElementById("display-details-label").style.display = "block";
				} else {
					document.getElementById("display-details-label").style.display = "none";
				}
		}
	</script>

<?php
};

// Register the shortcode.

add_shortcode( 'volplus-volunteer-register', 'volplus_volunteer_register_func' );


// Enable shortcodes in widgets
add_filter('widget_text','do_shortcode');