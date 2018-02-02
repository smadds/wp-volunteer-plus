<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';
require_once VOLPLUS_PATH . 'includes/volplus_License.php';

add_action('admin_menu', 'volplus_plugin_menu');

function volplus_plugin_menu() {
	add_options_page('Volunteer Plus', 'Volunteer Plus', 'administrator', 'volunteer-plus', 'volplus_plugin_settings_page', 'dashicons-admin-generic');
}

function volplus_plugin_settings_page() {
	$args = array(
		'textarea_rows' => 15,
		'teeny' => true,
		'quicktags' => true,
		'media_buttons' => true,
		'wpautop' => false
	);

$intro_not_logged_in = "<p><strong>You can show your interested in volunteering for this position by registering as a volunteer with us. ";
$intro_not_logged_in .= "It's not difficult - we just need some of your details so that we can introduce you to the organisation.</strong></p>";
$intro_not_logged_in .= "<p>If you have not already registered, click the 'Register' button below to sign up. ";
$intro_not_logged_in .= "If you have already registered, clicking 'Cancel' will close this popup and you can log in by entering your email ";
$intro_not_logged_in .= "&amp; password on this page.</p>";
$intro_not_logged_in .= "<p>Finally, if you just want to discuss this, use the 'Contact Us' button and let us know how we can contact you.</p>";
$intro_logged_in = "<p><strong>You are already logged in, so go ahead and register your interest.</strong></p>";
$intro_logged_in .= "<p>Just so we're clear - by clicking the Register My Interest button, you're agreeing that we can pass your details onto the organisation offering this opportunity.</p>";
$enquiry_success_msg = "<h3>Congratulations!</h3><p>Your interest in this opportunity has been registered.</p>";
$enquiry_success_msg .= "<p>We will now pass your details on to the organisation offering the opportunity."; 
$enquiry_compliance_msg = "This information will be held on a computer which complies with the Data Protection Act 1984.";
$enquiry_compliance_msg .= " We use a volunteer database which is accessible to other volunteer centres in the county.";
$enquiry_compliance_msg .= " Your information will be seen by these centres unless you request otherwise.</p>"; 
$enquiry_compliance_msg .= "<p>Any information marked above as confidential will not be released to any person without your prior agreement.</p>";
$welcome_new_user_msg = "<h2>Congratulations!</h2><p>You have successfully registered as a volunteer.<p>";
$welcome_new_user_msg .= "<p>Please make a note of your email and password details, as you will need these if you want to show your interest in opportunities in the future.</p>";
$welcome_new_user_msg .= "<p>Do you want to register your interest in this volunteering opportunity now?</p>";
$age_band_message = "<p>To help us match you to opportunities, and for safeguarding reasons, we need to calculate your age band.</p>";
$age_band_message .= "<p>Please enter your date of birth and we will calculate the band.</p>";


?>  
<div class="wrap">
<h2>Volunteer Plus Settings</h2>

<form method="post" action="options.php">
	<?php settings_fields( 'volplus-plugin-settings-group' ); ?>
	<?php do_settings_sections( 'volplus-plugin-settings-group' ); ?>
	<p>This plugin is for the use of existing customers of Volunteer Plus software by Pipe Media.</p>
	<p>For more information visit the <a href="https://volunteerplus.org.uk" target="_blank">Volunteer Plus</a> website.</p>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><h3>Volunteer Plus API Credentials</h3></th>
		</tr>
		<tr valign="top">
			<th scope="row">API Endpoint URL</th>
			<td><input type="text" name="volplus_endpoint" size="40" value="<?php echo esc_attr( get_option('volplus_endpoint') ); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">API Key</th>
			<td><input type="password" name="volplus_api_key" size="40" value="<?php echo esc_attr( get_option('volplus_api_key') ); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><h3>Plugin Licensing</h3></th>
		</tr>
		<tr valign="top">
			<th scope="row">Domain</th>
			<td><?php echo $_SERVER['HTTP_HOST'] ?></td>
		</tr>
		<th scope="row">Plugin License Code</th>
		<td><input type="password" name="volplus_license_code" size="40" value="<?php echo esc_attr( get_option('volplus_license_code') ); ?>" />
			<span class="description">If you have not received your code contact <a href:"mailto:info@maddox.co.uk">info@maddox.co.uk</a></span></td>
		<tr valign="top">
			<th scope="row"><h3>Google Maps</h3></th>
		</tr>
		<tr valign="top">
			<th scope="row">Show Google Map on Opportunity Detail page</th>
			<td><input type="checkbox" name="volplus_showmap" <?php if(get_option('volplus_showmap') == 'on') echo 'checked'; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Google Maps API key</th>
			<td><input type="text" name="volplus_googlemapkey" size="40" value="<?php echo esc_attr( get_option('volplus_googlemapkey') ); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Map default centre</th>
			<td><input type="text" name="volplus_googlemapcentre" size="40" value="<?php echo esc_attr( get_option('volplus_googlemapcentre') ); ?>" />
			<span class="description">Enter a postcode, place or district name</span></td>
		</tr>
		<tr valign="top">
			<th scope="row">Google Maps Zoom</th>
			<td><input type="number" name="volplus_googlemapzoom" min="0" max="20" value="<?php echo esc_attr( get_option('volplus_googlemapzoom', 10) ); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><h3>Other Settings</h3></th>
		</tr>
		<tr valign="top">
			<th scope="row">Contact Us Response Form</th>
			<td><input type="text" name="volplus_responseformcontent" size="40" value="<?php echo esc_attr(get_option('volplus_responseformcontent')); ?>" />
			<span class="description">Use a shortcode to a contact form, or leave blank to hide this option</span></td>
		</tr>
		<tr valign="top">
			<th scope="row">Response Intro - not logged in</th>
			<td><?php wp_editor(get_option('volplus_responsenotloggedinintro', $intro_not_logged_in), 'volplus_responsenotloggedinintro', $args)?></td>
		</tr>
		<tr valign="top">
			<th scope="row">Response Intro - logged in</th>
			<td><?php wp_editor(get_option('volplus_responseloggedinintro', $intro_logged_in), 'volplus_responseloggedinintro', $args)?></td>
		</tr>
		<tr valign="top">
			<th scope="row">Enquiry success message</th>
			<td><?php wp_editor(get_option('volplus_enquirysuccessmsg', $enquiry_success_msg), 'volplus_enquirysuccessmsg', $args)?></td>
		</tr>
		<tr valign="top">
			<th scope="row">Registration compliance message (bottom of registration form)</th>
			<td><?php wp_editor(get_option('volplus_enquirycompliancemsg', $enquiry_compliance_msg), 'volplus_enquirycompliancemsg', $args)?></td>
		</tr>
		<tr valign="top">
			<th scope="row">New user welcome message</th>
			<td><?php wp_editor(get_option('volplus_welcomenewusermsg', $welcome_new_user_msg), 'volplus_welcomenewusermsg', $args)?></td>
		</tr>
		<tr valign="top">
			<th scope="row">Date of birth popup message</th>
			<td><?php wp_editor(get_option('volplus_agebandmsg', $age_band_message), 'volplus_agebandmsg', $args)?></td>
		</tr>
		<tr valign="top">
			<th scope="row">Hide bracketed text</th>
			<td><input type="checkbox" name="volplus_hidebrackets" <?php if(get_option('volplus_hidebrackets') == 'on') echo 'checked'; ?> />
			<span class="description">Strips out text within brackets in Opportunity titles & Organisation names</span></td>
		</tr>
		<tr valign="top">
			<th scope="row">Hide Quality Control item details </th>
			<td><input type="checkbox" name="volplus_hideqcdetails" <?php if(get_option('volplus_hideqcdetails', null) == 'on') echo 'checked'; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Volunteer login timeout (hours)</th>
			<td><input type="number" name="volplus_voltimeout" min="1" max="72" value="<?php echo esc_attr(get_option('volplus_voltimeout', 1)); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">T&C page (checkbox on registration if selected)</th>
			<td><?php
				$args= array(
					'show_option_none'=>'(Not Used)',
					'selected'=> get_option('volplus_compliancepage', null),
					'name'=> 'volplus_compliancepage'
  				);
				wp_dropdown_pages($args);?>
			</td>
		</tr>
	</table>
	<?php submit_button();?> 
</form>

<?php
	$authresponse = volplus_checkauth();
//	print_r($authresponse);
	if($authresponse == 200) { 
		echo '<div class="updated notice"><p>The Volunteer Plus API credentials are working</p></div>';
	} else {
		echo '<div class="error notice"><p><strong>The Volunteer Plus API credentials failure. Error code'.$authresponse.'</strong></p></div>';
	}
	
	if(volplus_licensed()) {
		echo '<div class="updated notice"><p>The WP Volunteer Plus plugin is licensed</p></div>';
	}else {
		echo '<div class="error notice"><p><strong><strong>Unlicensed WP Volunteer Plus plugin. Please contact <a href:"mail:info@maddox.co.uk">info@maddox.co.uk</a> quoting your organisation name and your Domain as shown above.</strong></p></div>';
	}

?>
</div>
<?php
}  

add_action( 'admin_init', 'volplus_plugin_settings' );

function volplus_plugin_settings() {
	register_setting( 'volplus-plugin-settings-group', 'volplus_endpoint' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_api_key' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_showmap' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_googlemapkey' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_googlemapcentre' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_googlemapzoom' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_license_code' );
	register_setting( 'volplus-plugin-settings-group', 'volplus_responseformcontent' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_responsenotloggedinintro' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_responseloggedinintro' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_enquirysuccessmsg' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_enquirycompliancemsg' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_welcomenewusermsg' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_agebandmsg' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_compliancepage' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_hidebrackets' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_hideqcdetails' );	
	register_setting( 'volplus-plugin-settings-group', 'volplus_voltimeout' );	
}

function volplus_checkauth(){
  $authcheck = wp_remote_get(get_option('volplus_endpoint') . 'interests', array('headers' => array('Authorization' => 'Bearer ' . get_option('volplus_api_key'))));
  $response_code = wp_remote_retrieve_response_code($authcheck);
  return $response_code;
//  return $authcheck;
}