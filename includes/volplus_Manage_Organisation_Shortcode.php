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
	$organisation = (array) new volplus_new_org();

//var_dump_safe($_POST);
 		
	if (isset($_POST['organisation'])) {
		$reg_errors = new WP_Error;
		// sanitize user form input

		//initial org contact details
		if(isset($_POST['title']))$organisation['title'] = stripslashes($_POST['title']);
		if(isset($_POST['first_name']))$organisation['first_name'] = ucfirst(strtolower(sanitize_user( $_POST['first_name'] )));
		if(isset($_POST['last_name']))$organisation['last_name'] = ucfirst(strtolower(sanitize_user( $_POST['last_name'] )));
		$organisation['username'] = strtolower($organisation['first_name'] . '-' . $organisation['last_name']);
		if(isset($_POST['job_title']))$organisation['job_title'] = stripslashes(esc_html( $_POST['job_title'] ));
		if(isset($_POST['contact_telephone']))$organisation['contact_telephone'] = stripslashes(esc_html( $_POST['contact_telephone']));
		if(isset($_POST['contact_email_address']))$organisation['contact_email_address'] = strtolower(sanitize_email( $_POST['contact_email_address'] ));
		if(isset($_POST['contact_password']))$organisation['contact_password'] = esc_html( $_POST['contact_password'] );
		if(isset($_POST['contact_password_confirmation']))$organisation['contact_password_confirmation'] = esc_html( $_POST['contact_password_confirmation'] );

		//org details
		if(isset($_POST['organisation']))$organisation['organisation'] = stripslashes(esc_html( $_POST['organisation']));
		if(isset($_POST['address_line_1']))$organisation['address_line_1'] = stripslashes(esc_html( $_POST['address_line_1']));
		if(isset($_POST['address_line_2']))$organisation['address_line_2'] = stripslashes(esc_html( $_POST['address_line_2']));
		if(isset($_POST['address_line_3']))$organisation['address_line_3'] = stripslashes(esc_html( $_POST['address_line_3']));
		if(isset($_POST['town']))$organisation['town'] = stripslashes(esc_html( $_POST['town']));
		if(isset($_POST['county']))$organisation['county'] = stripslashes(esc_html( $_POST['county']));
		if(isset($_POST['postcode']))$organisation['postcode'] = stripslashes(esc_html( $_POST['postcode']));
		if(isset($_POST['telephone']))$organisation['telephone'] = stripslashes(esc_html( $_POST['telephone']));
		if(isset($_POST['additional_telephone']))$organisation['additional_telephone'] = stripslashes(esc_html( $_POST['additional_telephone']));
		if(isset($_POST['email_address']))$organisation['email_address'] = strtolower(sanitize_email( $_POST['email_address'] ));
		if(isset($_POST['status']))$organisation['status'] = stripslashes($_POST['status']);
		if(isset($_POST['charity_registration_number']))$organisation['charity_registration_number'] = stripslashes(esc_html( $_POST['charity_registration_number']));
		if(isset($_POST['company_registration_number']))$organisation['company_registration_number'] = stripslashes(esc_html( $_POST['company_registration_number']));
		if(isset($_POST['website']))$organisation['website'] = stripslashes(esc_html( $_POST['website']));
		if(isset($_POST['twitter']))$organisation['twitter'] = stripslashes(esc_html( $_POST['twitter']));
		if(isset($_POST['facebook']))$organisation['facebook'] = stripslashes(esc_html( $_POST['facebook']));
		if(isset($_POST['about']))$organisation['about'] = stripslashes(esc_html( $_POST['about']));
		if(isset($_POST['mission_statement']))$organisation['mission_statement'] = stripslashes(esc_html( $_POST['mission_statement']));
		if(isset($_POST['directions']))$organisation['directions'] = stripslashes(esc_html( $_POST['directions']));
		if(isset($_POST['how_heard']))$organisation['how_heard'] = (int) $_POST['how_heard'];

       
    
    
		if ( !is_email( $organisation['email_address'] ) ) {
			$reg_errors->add( 'email_invalid', 'We can\'t make out your email address. Please check it\'s correct' ); }
		if(!is_volplus_user_logged_in()){
			if ( 7 > strlen( $organisation['contact_password'] ) ) {
				$reg_errors->add( 'contact_password', 'Your password too short. It must be at least 8 characters' ); }
			if ( $organisation['contact_password'] !== $organisation['contact_password_confirmation'] ) {
				$reg_errors->add( 'contact_password_confirmation', 'Your passwords do not match' ); }}    
//print_r_safe($reg_errors);   
    
		if ( 1 > count( $reg_errors->get_error_messages() ) ) {
			//add VolPlus account
//var_dump_safe($organisation);
			$endpoint = 'organisations';
			if(isset($_COOKIE['volplus_org_id'])) $endpoint .= '/' . $_COOKIE['volplus_org_id']; // update if org exists
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
//var_dump_safe( $response );

			if ( !($response['response']['code'] == 201 || $response['response']['code'] ==  200)) {
				$error_message = $response['response']['message'];
				echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
				foreach($responsebody as $key=>$data){
					echo "<br/>".$key.": ";
					foreach($data as $data2){
						echo "<em>".$data2."</em>, ";
					}
				}
				unset($organisation['volplus_id']);
			} else {
				$organisation['volplus_id'] = $responsebody['id'];
//				echo "Your Volunteer Plus ID number is: ".$organisation['volplus_id'];

				if(! is_volplus_user_logged_in()) {
					//VolPlus Org Contact Login
					$volpluslogin = array(
						'email_address' => $organisation['contact_email_address'],
						'password' => $organisation['contact_password'],
						'type' => 2 // 1=volunteer, 2=organisation
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
print_r_safe('Login response body: ');var_dump_safe( $responsebody );
					if ( $response['response']['code'] !== 200) {
						$error_message = $response['response']['message'];
						echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
						foreach($responsebody as $key=>$data){
							echo "<br/>".$key.": ";
							foreach($data as $data2){
								echo "<em>".$data2."</em>, ";
							}
						}
						unset($organisation['volplus_id']);
						echo "<div id='vol_main_heading'><br/><h3>Create an account for your Organisation</h3></div>";
						$buttontext = "Register";
						$organisation = "";

					} else { // Successful org registration
						setcookie('volplus_user_id', $responsebody['id'], time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
						setcookie('volplus_user_first_name', $organisation['first_name'], time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
						setcookie('volplus_user_last_name', $organisation['last_name'], time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );				
						setcookie('volplus_org_id', $organisation['volplus_id'], time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
						setcookie('volplus_org_name', $organisation['organisation'], time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
						wp_redirect("/manage-organisation"); // redraw page
//						echo '<script type="text/javascript">jQuery("#welcomeNewUser").style.display = "inherit";</script>';
//						echo '<script>location.assign("/opportunities/?opp-id=' . $organisation['volplus_id'] . '")</script>';
					
//						var_dump_safe( $responsebody );			
						echo "<div id='vol_main_heading'><br/><h3>Update your Organisation details</h3></div>";
						$buttontext = "Update Details";

					}
				}
			
			
			
				if ($volplus_atts['wordpress-account']){ // create WP account enabled
					$wpuser = array(
						'user_login'	=>	$organisation['username'],
						'first_name'	=>	$organisation['first_name'],
						'last_name'		=>	$organisation['last_name'],
		         	'user_email'	=>	$organisation['email_address'],
			         'user_pass'		=>	$organisation['password'],
			         'role'			=>	'volunteer',
		   	      );
					$wpuserid = wp_insert_user($wpuser);
//			var_dump_safe($user);

					add_user_meta( $wpuserid, '_volplus_id', $organisation['volplus_id']);

					$wplogin = array(
						'user_login'	=>	$organisation-['username'],
						'user_password'=>	$organisation['password'],
						'remember'		=>	true
					);
			
					$user = wp_signon( $wplogin, is_ssl());
					if ( is_wp_error( $user ) ) {
						echo $user->get_error_message();
					}
//			var_dump_safe($user);			
	       		echo "<h2>Your account has been created.<br/>Your username is: " . $organisation['username'] . ".</h2>";
					echo "<p>You have been logged in with the password you set.</p>";        
				}
       	}
		}
	}

//};function nothing() {

	if(!isset($_COOKIE['volplus_org_id'])){
		echo "<div id='vol_main_heading'><br/><h3>Create an account for your Organisation</h3></div>";
		$buttontext = "Register";
//		$organisation = "";
		
	}else {
		echo "<div id='vol_main_heading'><br/><h3>Update your Organisation details</h3></div>";
		$buttontext = "Update Details";
		$organisation = getOrgDetails($_COOKIE['volplus_org_id']);


//		$responsebody = json_decode($response['body']);
//		foreach($responsebody as $key=>$data){
//			$organisation[$key] = $data;
//		}
// var_dump_safe($responsebody);
	}
	
	if(isset($signUpError)) echo '<div>'.$signUpError.'</div>';
//var_dump_safe($organisation);
//	if(!isset($organisation)) $organisation = (array) new volplus_new_org();
	?>

	<form id="form_org_registration" action="" method="post" name="org_registration">

		<?php	if(!isset($_COOKIE['volplus_org_id'])){?>
			<h3 class="volplus-col-12"><br/>Main Contact Details:</h3>
				<label class="volplus-col-2">Title (optional)
					<select id="title" name="title" class="text" autofocus>
						<option value="" <?php if(isset($organisation['title']) && $organisation['title']=="") echo 'selected';?> >Select</option>
						<option value="Mr" <?php if(isset($organisation['title']) && $organisation['title']=="Mr") echo 'selected';?> >Mr</option>
						<option value="Master" <?php if(isset($organisation['title']) && $organisation['title']=="Master") echo 'selected';?> >Master</option>
						<option value="Mrs" <?php if(isset($organisation['title']) && $organisation['title']=="Mrs") echo 'selected';?> >Mrs</option>
						<option value="Miss" <?php if(isset($organisation['title']) && $organisation['title']=="Miss") echo 'selected';?> >Miss</option>
						<option value="Ms" <?php if(isset($organisation['title']) && $organisation['title']=="Ms") echo 'selected';?> >Ms</option>
						<option value="Dr" <?php if(isset($organisation['title']) && $organisation['title']=="Dr") echo 'selected';?> >Dr</option>
						<option value="Prof" <?php if(isset($organisation['title']) && $organisation['title']=="Prof") echo 'selected';?> >Prof</option>
					</select>
				</label>
			<label class="volplus-col-5">First name <span class="error">*</span>
				<input type="text" id="first_name" name="first_name" placeholder="Your First Name" required value="<?php if(isset($organisation['first_name'])) echo $organisation['first_name'] ?>"/></label>
			<label class="volplus-col-5">Last name <span class="error">*</span>  
				<input type="text" id="last_name" name="last_name" placeholder="Your Last Name" required value="<?php if(isset($organisation['last_name'])) echo $organisation['last_name'] ?>" /></label></p>
			<label class="volplus-col-8">Job Title <span class="error">*</span>
				<input type="text" id="job_title" name="job_title"  placeholder="Your Job Title" required value="<?php if(isset($organisation['job_title'])) echo $organisation['job_title']?>" /></label>
			<label class="volplus-col-8">Email address <span class="error">*</span>
				<input type="email" id="contact_email_address" name="contact_email_address"  placeholder="Your Email address" required value="<?php if(isset($organisation['contact_email_address'])) echo $organisation['contact_email_address']?>" /></label>
			<label class="volplus-col-8">Telephone <span class="error">*</span>  
				<input type="tel" id="contact_telephone" name="contact_telephone" placeholder="Your Telephone number" required value="<?php if(isset($organisation['contact_telephone'])) echo $organisation['contact_telephone']?>"/></label>
			<div id="passwords">
				<p><label class="volplus-col-6">Password (8 characters or more) <span class="error">*</span>
					<input type="password" id="contact_password" name="contact_password" placeholder="Password" required value="<?php if(isset($organisation['contact_password'])) echo $organisation['contact_password']?>" /></label>
				<label class="volplus-col-6">Password <span class="error">*</span>
					<input type="password" name="contact_password_confirmation" placeholder="Repeat your password" required value="<?php if(isset($organisation['contact_password_confirmation'])) echo $organisation['contact_password_confirmation']?>" /></label></p>
			</div>
			<?php } ?>
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
		<label class="volplus-col-8">General email address <span class="error">*</span>
			<input type="email" id="email_address" name="email_address"  placeholder="e.g. info@" required value="<?php if(isset($organisation['email_address'])) echo $organisation['email_address']?>" /></label>
		<label class="volplus-col-6">Telephone <span class="error">*</span>  
			<input type="tel" id="telephone" name="telephone" placeholder="Your main office number" value="<?php if(isset($organisation['telephone'])) echo $organisation['telephone']?>"/></label>
		<label class="volplus-col-6">Additional Telephone  
			<input type="tel" id="additional_telephone" name="additional_telephone" placeholder="Alternative office number" value="<?php if(isset($organisation['additional_telephone'])) echo $organisation['additional_telephone']?>"/></label>
		<label class="volplus-col-6">Charity Number
			<input type="text" id="charity_registration_number" name="charity_registration_number" placeholder="If a registered charity" value="<?php if(isset($organisation['charity_registration_number'])) echo $organisation['charity_registration_number']?>"/></label>
		<label class="volplus-col-6">Company Number  
			<input type="text" id="company_registration_number" name="company_registration_number" placeholder="Your company reg number" value="<?php if(isset($organisation['company_registration_number'])) echo $organisation['company_registration_number']?>"/></label>
		<label class="volplus-col-6">Status <span class="error">*</span> 
					<select id="status" name="status" class="text">
						<option value=0 <?php if(isset($organisation['status']) && $organisation['status']==0) echo 'selected';?> >Select your organisation&#39;s status</option>
						<option value=1 <?php if(isset($organisation['status']) && $organisation['status']==1) echo 'selected';?> >Registered Charity</option>
						<option value=2 <?php if(isset($organisation['status']) && $organisation['status']==2) echo 'selected';?> >Charitable Incorporated Organisation (CIO)</option>
						<option value=3 <?php if(isset($organisation['status']) && $organisation['status']==3) echo 'selected';?> >Unregistered Community Group</option>
						<option value=4 <?php if(isset($organisation['status']) && $organisation['status']==4) echo 'selected';?> >Community Interest Company (CIC)</option>
						<option value=5 <?php if(isset($organisation['status']) && $organisation['status']==5) echo 'selected';?> >Company Limited by Guarantee</option>
						<option value=6 <?php if(isset($organisation['status']) && $organisation['status']==6) echo 'selected';?> >Statutory Organisation</option>
						<option value=7 <?php if(isset($organisation['status']) && $organisation['status']==7) echo 'selected';?> >Private Business</option>
						<option value=8 <?php if(isset($organisation['status']) && $organisation['status']==8) echo 'selected';?> >Other</option>
					</select>
			</label>
		<label class="volplus-col-6">Website  
			<input type="url" id="website" name="website" placeholder="Your website URL" value="<?php if(isset($organisation['website'])) echo $organisation['website']?>"/></label>
		<label class="volplus-col-6">Twitter  
			<input type="url" id="twitter" name="twitter" placeholder="Your twitter URL" value="<?php if(isset($organisation['twitter'])) echo $organisation['twitter']?>"/></label>
		<label class="volplus-col-6">Facebook  
			<input type="url" id="facebook" name="facebook" placeholder="Your facebook URL" value="<?php if(isset($organisation['facebook'])) echo $organisation['facebook']?>"/></label>
		<label class="volplus-col-12">About your organisation  
			<textarea id="about" name="about" rows="10"><?php if(isset($organisation['about'])) echo wp_strip_all_tags($organisation['about'])?></textarea></label>
		<label class="volplus-col-12">Mission Statement  
			<textarea id="mission_statement" name="mission_statement" rows="10"><?php if(isset($organisation['mission_statement'])) echo wp_strip_all_tags($organisation['mission_statement'])?></textarea></label>
		<label class="volplus-col-12">Directions to your address  
			<textarea id="directions" name="directions" rows="10"><?php if(isset($organisation['directions'])) echo wp_strip_all_tags($organisation['directions'])?></textarea></label>
		<label class="volplus-col-4">How did you hear of us? <span class="error">*</span>
		
			<?php 
				$temp = (isset($organisation['how_heard'])) ? $organisation['how_heard'] : 0;
				how_heard($temp);
			?></label>


	<div class="volplus-col-12">
			<input id="button_org_registration" class = "button" type="submit" name="org_registration" value="<?php echo $buttontext ?>" style="font-size:1.2em">
		</div>
	</form>
 	<div id="welcomeNewUser" hidden="hidden"><?php
		echo stripslashes(html_entity_decode(get_option('volplus_welcomenewusermsg')))?>
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
		
			
//			$("#org_registration").submit(function(e){
//				$.cookie("volplus_newuser", true,  { path: '/' });
//			});
			
//			$(document).ready(function () {
//				if($.cookie("volplus_newuser")){
//					$.removeCookie("volplus_newuser", {path:'/'});
//					$("#welcomeNewUser").dialog("open");
//				}
//			});

//			$( "#welcomeNewUser" ).dialog({
//				dialogClass: 'wp-dialog',
//				modal: true,
//				autoOpen: false,
//				show: {effect: "fade", duration: 500},
//				closeOnEscape: true,
//				title: "You are registered",
//				width: 400,
//				buttons: [
//		 			{
//						text: 'Take me back',
//						class: 'button',
//						click: function() {
//							$.cookie("volplus_iminterested", true,  { path: '/' });
//							$(this).dialog('close');
//							window.location.assign("opportunities/?opp-id=" + $.cookie('volplus_opp_id'));
//						}
//					},
//		 			{
//						text: 'Close',
//						class: 'button',
//						click: function() {
//							$(this).dialog('close');
//							window.location.reload();
//						}
//					}
//				]
//			});

//		});

	

	</script>

<?php
};



// Register the shortcode.

add_shortcode( 'volplus-manage-organisation', 'volplus_manage_organisation_func' );


// Enable shortcodes in widgets
add_filter('widget_text','do_shortcode');
