<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Volunteer Registration Form
function volplus_manage_organisation_func($atts = [], $content = null, $tag = '') {

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

//	if(isset($_COOKIE['volplus_org_id'])){ // LOGGED IN
//	var_dump_safe($_COOKIE);
		echo "<div id='test' class='logged_in' style='display:" ;
		if(isset($_COOKIE['volplus_org_id'])){ echo "inherit"; } else { echo "none"; };
		echo "'>";
		include(VOLPLUS_PATH . 'includes/volplus_OrgLoggedInHtml.php');
		echo "</div>";
//	} 

	
	$wpuserid = null;
	$organisation = new volplus_volunteer();
		
	if (isset($_POST['user_registration'])) {
		$reg_errors = new WP_Error;
		// sanitize user form input

		unset($_POST['user_registration']);
		if(isset($_POST['title']))$organisation->title = stripslashes(esc_html( $_POST['title']));
		if(isset($_POST['first_name']))$organisation->first_name = ucfirst(strtolower(sanitize_user( $_POST['first_name'] )));
		if(isset($_POST['last_name']))$organisation->last_name = ucfirst(strtolower(sanitize_user( $_POST['last_name'] )));
		$organisation->username = strtolower($organisation->first_name . '-' . $organisation->last_name);
		if(isset($_POST['email_address']))$organisation->email_address = strtolower(sanitize_email( $_POST['email_address'] ));
		if(isset($_POST['password']))$organisation->password = esc_html( $_POST['password'] );
		if(isset($_POST['password_confirmation']))$organisation->password_confirmation = esc_html( $_POST['password_confirmation'] );
		if(isset($_POST['address_line_1']))$organisation->address_line_1 = stripslashes(esc_html( $_POST['address_line_1']));
		if(isset($_POST['address_line_2']))$organisation->address_line_2 = stripslashes(esc_html( $_POST['address_line_2']));
		if(isset($_POST['address_line_3']))$organisation->address_line_3 = stripslashes(esc_html( $_POST['address_line_3']));
		if(isset($_POST['town']))$organisation->town = stripslashes(esc_html( $_POST['town']));
		if(isset($_POST['county']))$organisation->county = stripslashes(esc_html( $_POST['county']));
		if(isset($_POST['postcode']))$organisation->postcode = stripslashes(esc_html( $_POST['postcode']));
		if(isset($_POST['telephone']))$organisation->telephone = stripslashes(esc_html( $_POST['telephone']));
		if(isset($_POST['mobile']))$organisation->mobile = stripslashes(esc_html( $_POST['mobile']));
		if(isset($_POST['interests'])){
			foreach($_POST['interests'] as $key=>$value){
				$_POST['interests'][$key] = intval($value);}
			$organisation->interests = $_POST['interests'];}
		if(isset($_POST['activities'])){
			foreach($_POST['activities'] as $key=>$value){
				$_POST['activities'][$key] = intval($value);}
			$organisation->activities = $_POST['activities'];}
		if(isset($_POST['availability_details']))$organisation->availability_details = stripslashes(esc_html( $_POST['availability_details']));
		if(isset($_POST['volunteering_experience']))$organisation->volunteering_experience = stripslashes(esc_html($_POST['volunteering_experience']));
		if(isset($_POST['reasons'])){
			foreach($_POST['reasons'] as $key=>$value){
				$_POST['reasons'][$key] = intval($value);}
			$organisation->reasons = $_POST['reasons'];}
		if(isset($_POST['volunteering_reason_info']))$organisation->volunteering_reason_info = stripslashes(esc_html($_POST['volunteering_reason_info']));
		if(isset($_POST['date_birth']))$organisation->date_birth = esc_html( $_POST['date_birth']);
		if(isset($_POST['gender']))$organisation->gender = (int) $_POST['gender'];$_POST['gender'] = $organisation->gender;
		if(isset($_POST['employment']))$organisation->employment = (int) $_POST['employment'];$_POST['employment'] = $organisation->employment;
		if(isset($_POST['ethnicity']))$organisation->ethnicity = (int) $_POST['ethnicity'];$_POST['ethnicity'] = $organisation->ethnicity;
		if(isset($_POST['disability']))$organisation->disability = (int) $_POST['disability'];$_POST['disability'] = $organisation->disability;
		if(isset($_POST['disabilities'])){
			foreach($_POST['disabilities'] as $key=>$value){
				$_POST['interests'][$key] = intval($value);}
			$organisation->disabilities = $_POST['disabilities'];}
		if(isset($_POST['how_heard']))$organisation->how_heard = (int) $_POST['how_heard'];$_POST['how_heard'] = $organisation->ethnicity;


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
				$organisation->$period=1;
				$_POST[$period]=1;
			}		
		}
        
		//change username if already exists
		$vol = $organisation->username;
		if(username_exists($vol)) {
			$x = 1;
			while(username_exists($vol . $x)) {
				$x++;
			}
			$organisation->username .= $x;
		}
		
		// flag if age not given
		if(isset($_POST['date_birth']) && $_POST['date_birth']!==""){
			$_POST['date_birth'] = implode('/', array_reverse(explode('-', $_POST['date_birth']))); // put into UK date order dd/MM/yyyy
			$_POST['date_birth_prefer_not_say'] = NULL;
 			$organisation->date_birth_prefer_not_say = NULL;
			$organisation->date_birth = $_POST['date_birth'];
		} else {
			$organisation->date_birth_prefer_not_say = 1;
			$_POST['date_birth_prefer_not_say'] = 1;
		}
		
//	var_dump_safe($organisation);
        
    
    
		if(empty( $organisation->first_name ) || empty( $organisation->last_name )) {
			$reg_errors->add('field', 'We need your first & last names'); }    
		if(empty( $organisation->email_address )) {
			$reg_errors->add('field', 'We need an email address to create an account'); }    
		if(empty(($organisation->password) || empty($organisation->password_confirmation)) && !is_volplus_user_logged_in) {
			$reg_errors->add('field', 'An email field is missing'); }    
		if ( !is_email( $organisation->email_address ) ) {
			$reg_errors->add( 'email_invalid', 'We can\'t make out your email address. Please check it\'s correct' ); }
		if ( $volplus_atts['wordpress-account'] && email_exists( $organisation->email_address ) ) {
			$reg_errors->add( 'email', 'This email address is already in our system. Please use the Login panel' ); }
		if(!is_volplus_user_logged_in()){
			if ( 7 > strlen( $organisation->password ) ) {
				$reg_errors->add( 'password', 'Your password too short. It must be at least 8 characters' ); }
			if ( $organisation->password !== $organisation->password_confirmation ) {
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
				'body' => (array) $organisation,
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
				unset($organisation->volplus_id);
			} else {
				$organisation->volplus_id = $responsebody['id'];
//				echo "Your Volunteer Plus ID number is: ".$organisation->volplus_id;
				//VolPlus Login
				$volpluslogin = array(
					'email_address' => $organisation->email_address,
					'password' => $organisation->password,
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
					unset($organisation->volplus_id);
				} else { // Successful registration
					setcookie('volplus_user_id', $organisation->volplus_id, time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
					setcookie('volplus_user_first_name', $organisation->first_name, time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
					setcookie('volplus_user_last_name', $organisation->last_name, time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );				
//					echo '<script type="text/javascript">jQuery("#welcomeNewUser").style.display = "inherit";</script>';
//					echo '<script>location.assign("/opportunities/?opp-id=' . $organisation->volplus_id . '")</script>';
					
//				var_dump_safe( $responsebody );			
				}
			
			
			
				if ($volplus_atts['wordpress-account']){ // create WP account enabled
					$wpuser = array(
						'user_login'	=>	$organisation->username,
						'first_name'	=>	$organisation->first_name,
						'last_name'		=>	$organisation->last_name,
		         	'user_email'	=>	$organisation->email_address,
			         'user_pass'		=>	$organisation->password,
			         'role'			=>	'volunteer',
		   	      );
					$wpuserid = wp_insert_user($wpuser);
//			var_dump_safe($user);

					add_user_meta( $wpuserid, '_volplus_id', $organisation->volplus_id);

					$wplogin = array(
						'user_login'	=>	$organisation->username,
						'user_password'=>	$organisation->password,
						'remember'		=>	true
					);
			
					$user = wp_signon( $wplogin, is_ssl());
					if ( is_wp_error( $user ) ) {
						echo $user->get_error_message();
					}
//			var_dump_safe($user);			
	       		echo "<h2>Your account has been created.<br/>Your username is: " . $organisation->username . ".</h2>";
					echo "<p>You have been logged in with the password you set.</p>";        
				}
       	}
		}
	}

	if(!is_volplus_user_logged_in()){
		echo "<div id='vol_main_heading'><h2>Create an account for your Organisation</h2></div>";
		$buttontext = "Register";
	}else {
		echo "<div id='vol_main_heading'><h2>Update your Organisation's details</h2></div>";
		$buttontext = "Update Details";
		$organisation = getOrgDetails($_COOKIE['volplus_user_id']);
//		$responsebody = json_decode($response['body']);
//		foreach($responsebody as $key=>$data){
//			$organisation->$key = $data;
//		}
// var_dump_safe($responsebody);
	}
	
	if(isset($signUpError)) echo '<div>'.$signUpError.'</div>'?>

	<form id="user_registration" action="" method="post" name="user_registration">

<!--		<p><label class="volplus-col-2">Title (optional)  
			<select id="title" name="title" class="text">
				<option value="" <?php if($organisation->title=="") echo 'selected';?> >Select</option>
				<option value="Mr" <?php if($organisation->title=="Mr") echo 'selected';?> >Mr</option>
				<option value="Master" <?php if($organisation->title=="Master") echo 'selected';?> >Master</option>
				<option value="Mrs" <?php if($organisation->title=="Mrs") echo 'selected';?> >Mrs</option>
				<option value="Miss" <?php if($organisation->title=="Miss") echo 'selected';?> >Miss</option>
				<option value="Ms" <?php if($organisation->title=="Ms") echo 'selected';?> >Ms</option>
				<option value="Dr" <?php if($organisation->title=="Dr") echo 'selected';?> >Dr</option>
				<option value="Prof" <?php if($organisation->title=="Prof") echo 'selected';?> >Prof</option>
			</select>
		</label>

		<h2 class="volplus-col-12"><br/>Main Contact Details</h2>
		<label class="volplus-col-5">First name <span class="error">*</span>  
			<input type="text" id="first_name" name="first_name" placeholder="Your First Name" required autofocus value="<?php echo $organisation->first_name?>"/></label>
		<label class="volplus-col-5">Last name <span class="error">*</span>  
			<input type="text" id="last_name" name="last_name" placeholder="Your Last Name" required value="<?php echo $organisation->last_name?>" /></label></p>
		<label class="volplus-col-8">Job Title <span class="error">*</span>
			<input type="text" id="job_title" name="job_title"  placeholder="Your Job Title" required value="<?php echo $organisation->job_title?>" /></label>
		<label class="volplus-col-8">Email address <span class="error">*</span>
			<input type="email" id="contact_email_address" name="contact_email_address"  placeholder="Your Email address" required value="<?php echo $organisation->contact_email_address?>" /></label>
		<label class="volplus-col-6">Telephone <span class="error">*</span>  
			<input type="text" id="contact_telephone" name="contact_telephone" placeholder="Your Main Telephone number" required value="<?php echo $organisation->contact_telephone?>"/></label>
		<div id="passwords">
			<p><label class="volplus-col-6">Password (8 characters or more) <span class="error">*</span>
				<input type="password" id="contact_password" name="contact_password" placeholder="Password" required value="<?php echo $organisation->contact_password?>" /></label>
			<label class="volplus-col-6">Password <span class="error">*</span>
				<input type="password" name="contact_password_confirmation" placeholder="Repeat your password" required value="<?php echo $organisation->contact_password_confirmation?>" /></label></p>
		</div>-->
<!--		<h2 class="volplus-col-12"><br/>Organisation's Details</h2>-->
		<label class="volplus-col-12">Organisation Name <span class="error">*</span>  
			<input type="text" id="organisation" name="organisation" placeholder="Your organisation's name" required value="<?php if(isset($organisation['organisation'])) echo $organisation['organisation']?>"/></label>
		<label class="volplus-col-12">Address 1 <span class="error">*</span>  
			<input type="text" id="address_line_1" name="address_line_1" placeholder="Address 1" required value="<?php if(isset($organisation['address_line_1'])) echo $organisation['address_line_1']?>"/></label>
		<label class="volplus-col-12">Address 2  
			<input type="text" id="address_line_2" name="address_line_2" placeholder="Address 2" value="<?php if(isset($organisation['address_line_2'])) echo $organisation['address_line_2']?>"/></label>
		<label class="volplus-col-12">Address 3  
			<input type="text" id="address_line_3" name="address_line_3" placeholder="Address 3" value="<?php if(isset($organisation['address_line_3'])) echo $organisation['address_line_3']?>"/></label>
		<label class="volplus-col-4">Town <span class="error">*</span> 
			<input type="text" id="town" name="town" placeholder="Town" required value="<?php if(isset($organisation['town'])) echo $organisation['town']?>"/></label>
		<label class="volplus-col-4">County  
			<input type="text" id="county" name="county" placeholder="County" value="<?php if(isset($organisation['county'])) echo $organisation['county']?>"/></label>
		<label class="volplus-col-3">Postcode <span class="error">*</span>   
			<input type="text" id="postcode" name="postcode" placeholder="Postcode" required value="<?php if(isset($organisation['postcode'])) echo $organisation['postcode']?>"/></label>


	<div class="volplus-col-12">
			<input id="user_registration" class = "button" type="submit" name="user_registration" value="<?php echo $buttontext ?>" style="font-size:1.2em">
		</div>
	</form>
 	<div id="welcomeNewUser" hidden="hidden"><?php
		echo stripslashes(html_entity_decode(get_option('volplus_welcomenewusermsg')))?>
	</div>

	<div id="calcagerange" hidden><?php
		echo stripslashes(html_entity_decode(get_option('volplus_agebandmsg')))?>
		<input type="date" id="popup_date_birth" name="popup_date_birth" format= "dd/MM/yyyy" style="width: 200px" value="<?php echo implode('-', array_reverse(explode('/', $organisation->date_birth)));?>"/>
	</div>


<!--		</div>-->
	<?php// }; ?>
<div class="volplus-col-12">

<!-- <?php echo "POST:<br/>" . json_encode($_POST, JSON_UNESCAPED_SLASHES)?><br/>
<?php echo "volunteer:<br/>" . json_encode($organisation, JSON_UNESCAPED_SLASHES)?><br/>
<?php// var_dump_safe($GLOBALS['volunteer_fields']);?>-->
<div class="volplus-col-5"><?php var_dump_safe($_POST) ?></div>
<div class="volplus-col-5"><?php var_dump_safe($organisation) ?></div>
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
			
			$('#calcagerange').live('keyup', function(e){
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

add_shortcode( 'volplus-manage-organisation', 'volplus_manage_organisation_func' );


// Enable shortcodes in widgets
add_filter('widget_text','do_shortcode');
