<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Volunteer Registration Form
function volplus_volunteer_register_func($atts = [], $content = null, $tag = '') {

// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);

// override default attributes with user attributes
	$volplus_atts = shortcode_atts([
//		'rounddown' => 1,
//		'roundup' => 1,
	], $atts, $tag);



	
	
	
	$wpuserid = null;
	$volunteer = new volplus_volunteer();
		
	if (isset($_POST['user_registration'])) {
		$reg_errors = new WP_Error;
		// sanitize user form input


		$volunteer->first_name = ucfirst(strtolower(sanitize_user( $_POST['first_name'] )));
		$volunteer->last_name = ucfirst(strtolower(sanitize_user( $_POST['last_name'] )));
		$volunteer->username = strtolower($volunteer->first_name . '-' . $volunteer->last_name);
		$volunteer->email_address = strtolower(sanitize_email( $_POST['email_address'] ));
		$volunteer->password = esc_attr( $_POST['password'] );
		$volunteer->password_confirmation = esc_attr( $_POST['password_confirmation'] );
		$volunteer->address_line_1 = esc_attr( $_POST['address_line_1']);
		$volunteer->address_line_2 = esc_attr( $_POST['address_line_2']);
		$volunteer->address_line_3 = esc_attr( $_POST['address_line_3']);
		$volunteer->town = esc_attr( $_POST['town']);
		$volunteer->county = esc_attr( $_POST['county']);
		$volunteer->postcode = esc_attr( $_POST['postcode']);
		$volunteer->telephone = esc_attr( $_POST['telephone']);
		$volunteer->mobile = esc_attr( $_POST['mobile']);
		$volunteer->date_birth = esc_attr( $_POST['date_birth']);
		$volunteer->gender = esc_attr( $_POST['gender']);
		$volunteer->ethnicity = esc_attr( $_POST['ethnicity']);
		$volunteer->disability = esc_attr( $_POST['disability']);
		$volunteer->employment = esc_attr( $_POST['employment']);

        
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
		$volunteer->date_birth_prefer_not_say = ($volunteer->date_of_birth = null);
		
//	var_dump_safe($volunteer);
        
    
    
		if(empty( $volunteer->first_name ) || empty( $volunteer->last_name ) || empty( $volunteer->username ) || empty( $volunteer->email_address ) || empty($volunteer->password)) {
			$reg_errors->add('field', 'Required form field is missing'); }    
		if ( 6 > strlen( $volunteer->username ) ) {
			$reg_errors->add('username_length', 'Your username would be too short. Please make sure your the length of your First name + Last name are at least 5 characters' );}
		if ( !is_email( $volunteer->email_address ) ) {
			$reg_errors->add( 'email_invalid', 'We can\'t make out your email address. Please check it\'s correct' ); }
		if ( email_exists( $volunteer->email_address ) ) {
			$reg_errors->add( 'email', 'This email address is already in our system. Have you already registered?' ); }
		if ( 7 > strlen( $volunteer->password ) ) {
			$reg_errors->add( 'password', 'Your password too short. It must be at least 8 characters' ); }
		if ( $volunteer->password !== $volunteer->password_confirmation ) {
			$reg_errors->add( 'password_confirmation', 'Your passwords do not match' ); }    
		if (is_wp_error( $reg_errors )) { 
			foreach ( $reg_errors->get_error_messages() as $error ) {
				$signUpError='<p style="color:#FF0000; text-aling:left;"><strong>We have a problem </strong>: '.$error . '<br /></p>';
			} 
		}
    
    
		if ( 1 > count( $reg_errors->get_error_messages() ) ) {
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
       
			echo "<h2>Your account has been created with your username:" . $username . ".</h2>";
			echo "<p>You have been logged in with the password you set.</p>";        

		}
	}

//echo "userid: '" . $userid . "'";	
//	if(is_null($wpuserid)){
		?>
		<div class="volplus-col-12">
			<h2>Create your account</h2>
			<form action="" method="post" name="user_registration">

				<p><label class="volplus-col-2">Title (optional)  
					<?php $selected = get('title')?>
					<select name="title" class="text">
						<option value="" <?php if($selected=="") echo 'selected';?> >Select</option>
						<option value="Mr" <?php if($selected=="Mr") echo 'selected';?> >Mr</option>
						<option value="Master" <?php if($selected=="Master") echo 'selected';?> >Master</option>
						<option value="Mrs" <?php if($selected=="Mrs") echo 'selected';?> >Mrs</option>
						<option value="Miss" <?php if($selected=="Miss") echo 'selected';?> >Miss</option>
						<option value="Ms" <?php if($selected=="Ms") echo 'selected';?> >Ms</option>
						<option value="Dr" <?php if($selected=="Dr") echo 'selected';?> >Dr</option>
						<option value="Prof" <?php if($selected=="Prof") echo 'selected';?> >Prof</option>
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
				<div class="volplus-col-6"><?php display_interests();?></div>
				<div class="volplus-col-6"><?php display_activities();?></div>				
				<div class="volplus-col-4"><?php display_availability_table();?></div>

				<h2 class="volplus-col-12"><br/>Availability</h2>
				<label class="volplus-col-8">Availability Details (specific details regarding your availability e.g. shift worker)  
					<textarea name="availability_details" rows="10">
					<?php echo get('availability_details')?></textarea></label>

				<h2 class="volplus-col-12"><br/>Volunteering</h2>
				<label class="volplus-col-12">Volunteering Experience  
					<textarea name="volunteering_experience" rows="10">
					<?php echo get('volunteering_experience')?></textarea></label>
				<label class="volplus-col-4">Why volunteer?  
					<?php display_reasons();?></label>				
				<label class="volplus-col-8">Further details on why volunteering  
					<textarea name="volunteering_reason_info" rows="10">
					<?php echo get('volunteering_reason_info')?></textarea></label>

				<h2 class="volplus-col-12"><br/>About</h2>
				<label class="volplus-col-3">Date of birth 
					<input type="date" name="date_birth" value="<?php echo $volunteer->date_birth;?>"/></label>
				<input type="hidden" name="date_birth_prefer_not_say" value="1"/>
				<label class="volplus-col-2">Gender<span class="error">*</span>   
					<?php $selected = $volunteer->gender?>
					<select name="gender">
						<option value="" <?php if($selected=="") echo 'selected';?> >Select</option>
						<option value=1 <?php if($selected==1) echo 'selected';?> >Male</option>
						<option value=2 <?php if($selected==2) echo 'selected';?> >Female</option>
						<option value=3 <?php if($selected==3) echo 'selected';?> >Prefer not to say</option>
						<option value=4 <?php if($selected==4) echo 'selected';?> >Not Known</option>
					</select>
				</label>
				<label class="volplus-col-3">Employment Status<span class="error" >*</span>   
					<?php employment_status();?></label>				
				<label class="volplus-col-4">Ethnicity 
					<?php ethnicity();?></label>				
				<label class="volplus-col-3">Disability 
					<?php disability($volunteer);?></label>				
				<label class="volplus-col-3" id="display-details-label" <?php if($volunteer->disability !== 2){echo " style='display:none;'";} ?> >Details
					<?php $volunteer = disability_type($volunteer);?></label>				


				<div class="volplus-col-12"><input type="submit" name="user_registration" value="SignUp" /></div>

<!--<?php $a=$GLOBALS['volunteer_fields'];$a=json_decode($a['body'], true);print_r_safe($a);?>-->
			</form>
		</div>
		<?php if(isset($signUpError)){echo '<div>'.$signUpError.'</div>';}
//	}

	?>
<div class="volplus-col-6"><?php var_dump_safe($_POST) ?></div>;
<div class="volplus-col-6"><?php var_dump_safe($volunteer) ?></div>;

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
