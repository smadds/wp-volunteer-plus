<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Volunteer Registration Form
function volplus_volunteer_register_func($atts = [], $content = null, $tag = '') {

// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);

// override default attributes with user attributes
	$volplus_atts = shortcode_atts([
		'wordpress-account' => 0,
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

		unset($_POST['user_registration']);
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
				$_POST['interests'][$key] = intval($value);}
			$volunteer->interests = $_POST['interests'];}
		if(isset($_POST['activities'])){
			foreach($_POST['activities'] as $key=>$value){
				$_POST['activities'][$key] = intval($value);}
			$volunteer->activities = $_POST['activities'];}
		if(isset($_POST['availability_details']))$volunteer->availability_details = stripslashes(esc_html( $_POST['availability_details']));
		if(isset($_POST['volunteering_experience']))$volunteer->volunteering_experience = stripslashes(esc_html($_POST['volunteering_experience']));
		if(isset($_POST['reasons'])){
			foreach($_POST['reasons'] as $key=>$value){
				$_POST['reasons'][$key] = intval($value);}
			$volunteer->reasons = $_POST['reasons'];}
		if(isset($_POST['volunteering_reason_info']))$volunteer->volunteering_reason_info = stripslashes(esc_html($_POST['volunteering_reason_info']));
		if(isset($_POST['date_birth']))$volunteer->date_birth = esc_html( $_POST['date_birth']);
		if(isset($_POST['gender']))$volunteer->gender = (int) $_POST['gender'];$_POST['gender'] = $volunteer->gender;
		if(isset($_POST['employment']))$volunteer->employment = (int) $_POST['employment'];$_POST['employment'] = $volunteer->employment;
		if(isset($_POST['ethnicity']))$volunteer->ethnicity = (int) $_POST['ethnicity'];$_POST['ethnicity'] = $volunteer->ethnicity;
		if(isset($_POST['disability']))$volunteer->disability = (int) $_POST['disability'];$_POST['disability'] = $volunteer->disability;
		if(isset($_POST['disabilities'])){
			foreach($_POST['disabilities'] as $key=>$value){
				$_POST['disabilities'][$key] = intval($value);}
			$volunteer->disabilities = $_POST['disabilities'];}
		if(isset($_POST['how_heard']))$volunteer->how_heard = (int) $_POST['how_heard'];$_POST['how_heard'] = $volunteer->how_heard;


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
				$volunteer->$period=1;
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
			$_POST['date_birth'] = implode('/', array_reverse(explode('-', $_POST['date_birth']))); // put into UK date order dd/MM/yyyy
			$_POST['date_birth_prefer_not_say'] = NULL;
 			$volunteer->date_birth_prefer_not_say = NULL;
			$volunteer->date_birth = $_POST['date_birth'];
		} else {
			$volunteer->date_birth_prefer_not_say = 1;
			$_POST['date_birth_prefer_not_say'] = 1;
		}
		
//	var_dump_safe($volunteer);
        
    
    
		if(empty( $volunteer->first_name ) || empty( $volunteer->last_name )) {
			$reg_errors->add('field', 'We need your first & last names'); }    
		if(empty( $volunteer->email_address )) {
			$reg_errors->add('field', 'We need an email address to create an account'); }    
		if(empty(($volunteer->password) || empty($volunteer->password_confirmation)) && !is_volplus_user_logged_in) {
			$reg_errors->add('field', 'An email field is missing'); }    
		if ( !is_email( $volunteer->email_address ) ) {
			$reg_errors->add( 'email_invalid', 'We can\'t make out your email address. Please check it\'s correct' ); }
		if ( $volplus_atts['wordpress-account'] && email_exists( $volunteer->email_address ) ) {
			$reg_errors->add( 'email', 'This email address is already in our system. Please use the Login panel' ); }
		if(!is_volplus_user_logged_in()){
			if ( 7 > strlen( $volunteer->password ) ) {
				$reg_errors->add( 'password', 'Your password too short. It must be at least 8 characters' ); }
			if ( $volunteer->password !== $volunteer->password_confirmation ) {
				$reg_errors->add( 'password_confirmation', 'Your passwords do not match' ); }}    
		if (is_wp_error( $reg_errors )) { 
			foreach ( $reg_errors->get_error_messages() as $key=>$data ) {
				$signUpError='<p style="color:#FF0000; text-align:left;"><strong>We have a problem </strong>: '.$data . '<br /></p>';
			} 
		}
    
    
		if ( 1 > count( $reg_errors->get_error_messages() ) ) {
			//add VolPlus account
//			print_r_safe($_POST);
			$endpoint = 'volunteers';
			if(isset($_COOKIE['volplus_user_id'])) $endpoint .= '/' . $_COOKIE['volplus_user_id'];
			$response = wp_remote_post(API_URL . $endpoint, array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Authorization' => 'Bearer '.API_KEY),
//				'body' => (array) $_POST,
				'body' => (array) $volunteer,
				'cookies' => array()
				)
			);

			$responsebody = (array) json_decode($response['body']);
//			var_dump_safe( $responsebody );

			if ( !($response['response']['code'] == 201 || $response['response']['code'] ==  200)) {
				$error_message = $response['response']['message'];
				echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
				foreach($responsebody as $key=>$data){
					echo "<br/>".$key.": ";
					foreach($data as $data2){
						echo "<em>".$data2."</em>, ";
					}
				}
				unset($volunteer->volplus_id);
			} else {
				$volunteer->volplus_id = $responsebody['id'];
//				echo "Your Volunteer Plus ID number is: ".$volunteer->volplus_id;
				//VolPlus Login
				$volpluslogin = array(
					'email_address' => $volunteer->email_address,
					'password' => $volunteer->password,
//					'password' => '123456',
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
//				var_dump_safe( $response );
			
				$responsebody = (array) json_decode($response['body']);
				if ( $response['response']['code'] !== 200) {
					$error_message = $response['response']['message'];
					echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
					foreach($responsebody as $key=>$data){
						echo "<br/>".$key.": ";
						foreach($data as $data2){
							echo "<em>".$data2."</em>, ";
						}
					}
					unset($volunteer->volplus_id);
				} else { // Successful registration
					setcookie('volplus_user_id', $volunteer->volplus_id, time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
					setcookie('volplus_user_first_name', $volunteer->first_name, time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
					setcookie('volplus_user_last_name', $volunteer->last_name, time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );				
//					echo '<script type="text/javascript">jQuery("#welcomeNewUser").style.display = "inherit";</script>';
//					echo '<script>location.assign("/opportunities/?opp-id=' . $volunteer->volplus_id . '")</script>';
					
//				var_dump_safe( $responsebody );			
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

					add_user_meta( $wpuserid, '_volplus_id', $volunteer->volplus_id);

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
	}

	if(!is_volplus_user_logged_in()){
		echo "<div id='vol_main_heading'><h2>Create your account</h2></div>";
		$buttontext = "Register";
	}else {
		echo "<div id='vol_main_heading'><h2>Update your details</h2></div>";
		$buttontext = "Update Details";
		$response = get_volunteer_details($_COOKIE['volplus_user_id']);
		$responsebody = json_decode($response['body']);
		foreach($responsebody as $key=>$data){
			$volunteer->$key = $data;
		}
		foreach($volunteer->availability as $key=>$data){
			$volunteer->$key = $data;
		}
		unset($volunteer->availability);
// var_dump_safe($responsebody);
	}
	
	if(isset($signUpError)) echo '<div>'.$signUpError.'</div>'?>

	<form id="user_registration" action="" method="post" name="user_registration">

		<p><label class="volplus-col-2">Title (optional)  
			<select id="title" name="title" class="text">
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
			<input type="text" id="first_name" name="first_name" placeholder="First Name" required autofocus value="<?php echo $volunteer->first_name?>"/></label>
		<label class="volplus-col-5">Last name <span class="error">*</span>  
			<input type="text" id="last_name" name="last_name" placeholder="Last Name" required value="<?php echo $volunteer->last_name?>" /></label></p>
		<label class="volplus-col-8">Email address <span class="error">*</span>
			<input type="email" id="email_address" name="email_address"  placeholder="Email address" required value="<?php echo $volunteer->email_address?>" /></label>
		<div id="passwords">
			<p><label class="volplus-col-6">Password (8 characters or more) <span class="error">*</span>
				<input type="password" id="password" name="password" placeholder="Password" required value="<?php echo $volunteer->password?>" /></label>
			<label class="volplus-col-6">Password <span class="error">*</span>
				<input type="password" name="password_confirmation" placeholder="Repeat your password" required value="<?php echo $volunteer->password_confirmation?>" /></label></p>
		</div>
		<h2 class="volplus-col-12"><br/>Address</h2>
		<label class="volplus-col-12">Address 1 <span class="error">*</span>  
			<input type="text" id="address_line_1" name="address_line_1" placeholder="Address 1" required value="<?php echo $volunteer->address_line_1?>"/></label>
		<label class="volplus-col-12">Address 2  
			<input type="text" id="address_line_2" name="address_line_2" placeholder="Address 2" value="<?php echo $volunteer->address_line_2?>"/></label>
		<label class="volplus-col-12">Address 3  
			<input type="text" id="address_line_3" name="address_line_3" placeholder="Address 3" value="<?php echo $volunteer->address_line_3?>"/></label>
		<label class="volplus-col-4">Town <span class="error">*</span> 
			<input type="text" id="town" name="town" placeholder="Town" required value="<?php echo $volunteer->town?>"/></label>
		<label class="volplus-col-4">County <span class="error">*</span>   
			<input type="text" id="county" name="county" placeholder="County" required value="<?php echo $volunteer->county?>"/></label>
		<label class="volplus-col-3">Postcode <span class="error">*</span>   
			<input type="text" id="postcode" name="postcode" placeholder="Postcode" required value="<?php echo $volunteer->postcode?>"/></label>

		<h2 class="volplus-col-12"><br/>Contact Details</h2>
		<label class="volplus-col-6">Telephone <span class="error">*</span>  
			<input type="text" id="telephone" name="telephone" placeholder="Telephone number" required value="<?php echo $volunteer->telephone?>"/></label>
		<label class="volplus-col-6">Mobile  
			<input type="text" id="mobile" name="mobile" placeholder="Mobile number" value="<?php echo $volunteer->mobile?>"/></label>
		<h2 class="volplus-col-12"><br/>Interests & Activities</h2>
		<label class="volplus-col-6">Interests
			<?php display_interests($volunteer->interests,'volreg-');?></label>
		<label class="volplus-col-6">Activities
			<?php display_activities($volunteer->activities,'volreg-');?></label>
		<h2 class="volplus-col-12"><br/>Availability</h2>
		<label class="volplus-col-4">When are you available?
			<?php display_availability_table($volunteer);?></label>
		<label class="volplus-col-8">Availability Details (specific details regarding your availability e.g. shift worker)  
			<textarea id="availability_details" name="availability_details" rows="10"><?php if(isset($volunteer->availability_details)) echo $volunteer->availability_details?></textarea></label>
		<h2 class="volplus-col-12"><br/>Volunteering</h2>
		<label class="volplus-col-12">Volunteering & Work Experience
			<textarea id="volunteering_experience" name="volunteering_experience" rows="10"><?php if(isset($volunteer->volunteering_experience)) echo $volunteer->volunteering_experience?></textarea></label>
		<label class="volplus-col-4">Why volunteer?
			<?php display_reasons($volunteer->reasons);?></label>
		<label class="volplus-col-8">Any additional information
			<textarea id="volunteering_reason_info" name="volunteering_reason_info" rows="10"><?php if(isset($volunteer->volunteering_reason_info)) echo $volunteer->volunteering_reason_info?></textarea></label>
		<h2 class="volplus-col-12"><br/>About</h2>
		<label class="volplus-col-3">Age band
<?php
?>
			<div id='agerangediv'><input type="text" id='agerange' style='height:2.6em;' readonly value='<?php if(isset($volunteer->date_birth)){echo dobToAgeBand($volunteer->date_birth);}else{ echo "Set age band";}?>'/></div></label> 
<!--			<input type="date" id="date_birth" name="date_birth" format= "dd/MM/yyyy" style="height: 2.3rem" value="<?php echo implode('-', array_reverse(explode('/', $volunteer->date_birth)));?>"/></label>-->
		<input type="hidden" id="date_birth_prefer_not_say" name="date_birth_prefer_not_say" value="1"/>
		<input type="hidden" id="date_birth" name="date_birth"/>
		<label class="volplus-col-2">Gender <span class="error">*</span>   
			<?php gender($volunteer->gender);?></label>
		<label class="volplus-col-3">Employment Status <span class="error" >*</span>   
			<?php employment($volunteer->employment);?></label>
		<label class="volplus-col-4">Ethnicity <span class="error">*</span>
			<?php ethnicity($volunteer->ethnicity);?></label>
		<label class="volplus-col-4">How did you hear of us? <span class="error">*</span>
			<?php how_heard($volunteer->how_heard);?></label>
		<label class="volplus-col-3">Disability <span class="error">*</span>
			<?php disability($volunteer->disability);?></label>
			<?php if(in_array('disabilities',$GLOBALS['volunteer_fields'])){?>
			<label class="volplus-col-5" id="display-details-label" <?php if($volunteer->disability !== 1) echo " style='display:none;'"?>>Details
				<?php disability_type($volunteer->disabilities);?></label>
			<?php }?>
		<div class="volplus-col-12">
			<?php if(get_option('volplus_compliancepage')) {?>
				<div id="legal" hidden><?php echo get_post_field('post_content', get_option('volplus_compliancepage'));?></div>
				<label class="volplus-col-8">I accept the <a id="legalPopup" href="#">Terms & Conditions</a>
<!--				<label class="volplus-col-8">I accept the <a href="/<?php echo get_post_field( 'post_name', get_option('volplus_compliancepage'))?>" target=_blank>Terms & Conditions</a>-->
					<input type="checkbox" name="accept_terms" required></label>
			<?php }?>
			<input id="button_user_registration" class = "button" type="submit" name="user_registration" value="<?php echo $buttontext ?>" style="font-size:1.2em">
			<p id='compliancemsg'><?php echo stripslashes(get_option('volplus_enquirycompliancemsg'));?></p>
		</div>
	</form>
 	<div id="welcomeNewUser" hidden="hidden"><?php
		echo stripslashes(html_entity_decode(get_option('volplus_welcomenewusermsg')))?>
	</div>

	<div id="calcagerange" hidden><?php
		echo stripslashes(html_entity_decode(get_option('volplus_agebandmsg')))?>
		<input type="date" id="popup_date_birth" name="popup_date_birth" format= "dd/MM/yyyy" style="width: 200px" value="<?php echo implode('-', array_reverse(explode('/', $volunteer->date_birth)));?>"/>
	</div>


<!--		</div>-->
	<?php// }; ?>
<div class="volplus-col-12">

<!-- <?php echo "POST:<br/>" . json_encode($_POST, JSON_UNESCAPED_SLASHES)?><br/>
<?php echo "volunteer:<br/>" . json_encode($volunteer, JSON_UNESCAPED_SLASHES)?><br/>
<?php// var_dump_safe($GLOBALS['volunteer_fields']);?>-->
<div class="volplus-col-5"><?php var_dump_safe($_POST) ?></div>
<div class="volplus-col-5"><?php var_dump_safe($volunteer) ?></div>
</div>

	<script type="text/javascript" >
		document.getElementById('disability').onchange = function() {
			if (document.getElementById("disability").value == 1 && document.getElementById("disability-type").length > 1) {
	 			document.getElementById("display-details-label").style.display = "block";
			} else {
				document.getElementById("display-details-label").style.display = "none";
			}
		}
		



		jQuery(document).ready(function($) {

			function getAge(dateString) {
				var today = new Date();
				var birthDate = new Date(dateString);
				var age = today.getFullYear() - birthDate.getFullYear();
				var m = today.getMonth() - birthDate.getMonth();
				if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
					age--;
				}
				return age;
			}
			
			$("#legalPopup").click(function () {
				$("#legal").dialog("open");
			})
			
			$("#legal").dialog({
				dialogClass: 'wp-dialog',
				modal: true,
				autoOpen: false,
				show: {effect: "fade", duration: 500},
				closeOnEscape: true,
				title: "Terms & Conditions",
				width: ($(window).width()*0.8),
				buttons: [
		 			{
						text: 'Close',
						class: 'button',
						click: function() {
							$(this).dialog('close');
						}
					}
				]
				
			})
			
			$("#agerangediv").click(function () {
				$("#calcagerange").dialog("open");
			})
			
			$("#calcagerange").dialog({
				dialogClass: 'wp-dialog',
				modal: true,
				autoOpen: false,
				show: {effect: "fade", duration: 500},
				closeOnEscape: true,
				title: "Calculate your age band",
				width: 500,
				buttons: [
		 			{
						text: 'I\'d rather not say',
						class: 'button',
						click: function() {
							$("#date_birth_prefer_not_say").val("yes");
								$("#date_birth").val(null);
								$("#popup_date_birth").val(null);
								$("#agerange").val("Not recorded");
							$(this).dialog('close');
						}
					},
		 			{
						text: 'Cancel',
						class: 'button',
						click: function() {
							$(this).dialog('close');
						}
					},
		 			{
						text: 'Calculate Band',
						class: 'button',
						click: function() {
							var dob = $("#popup_date_birth").val();
							if(dob){
								$("#date_birth").val(dob);
								$("#date_birth_prefer_not_say").val("no");
								var age = getAge(dob);
								if(age < 16){ $("#agerange").val("Under 15");
								} else if(age <19){ $("#agerange").val("15-18");
								} else if(age <26){ $("#agerange").val("19-25");
								} else if(age <45){ $("#agerange").val("26-44");
								} else if(age <65){ $("#agerange").val("45-64");
								} else {$("#agerange").val("Over 65");
								}								
								$(this).dialog('close');
							}
						}
					}
				]
				
			})
			
			$('#calcagerange').on('keyup', function(e){
				if (e.keyCode == 13) {
					$(':button:contains("Calculate Band")').click();
				}
			});
			
			$("#user_registration").submit(function(e){
				$.cookie("volplus_newuser", true,  { path: '/' });
			});
			
			$(document).ready(function () {
				if($.cookie("volplus_newuser")){
					$.removeCookie("volplus_newuser", {path:'/'});
					$("#welcomeNewUser").dialog("open");
				}
			});

			$( "#welcomeNewUser" ).dialog({
				dialogClass: 'wp-dialog',
				modal: true,
				autoOpen: false,
				show: {effect: "fade", duration: 500},
				closeOnEscape: true,
				title: "You are registered",
				width: 400,
				buttons: [
		 			{
						text: 'Take me back',
						class: 'button',
						click: function() {
							$.cookie("volplus_iminterested", true,  { path: '/' });
							$(this).dialog('close');
							window.location.assign("opportunities/?opp-id=" + $.cookie('volplus_opp_id'));
						}
					},
		 			{
						text: 'Close',
						class: 'button',
						click: function() {
							$(this).dialog('close');
							window.location.reload();
						}
					}
				]
			});

		});

	

	</script>

<?php
};

// Register the shortcode.

add_shortcode( 'volplus-volunteer-register', 'volplus_volunteer_register_func' );


// Enable shortcodes in widgets
add_filter('widget_text','do_shortcode');
