<?php

require_once VOLPLUS_PATH . 'includes/volplus_Functions.php';

// Manage Opportunity Form
function volplus_manage_opportunity_func($atts = [], $content = null, $tag = '') {

// normalize attribute keys, lowercase
	$atts = array_change_key_case((array)$atts, CASE_LOWER);

// override default attributes with user attributes
	$volplus_atts = shortcode_atts([
		'wordpress-account' => 0,
//		'roundup' => 1,
	], $atts, $tag);

//	var_dump_safe($GLOBALS['volunteer_activities']);

	// get url parameters
	
	$opp = null;
	if(isset($_GET['opp-id'])){
		$opp = $_GET['opp-id'];
		setcookie('volplus_opp_id', $opp, 0, COOKIEPATH, COOKIE_DOMAIN );
		$opportunity = get_opportunity_details($opp);

//		var_dump_safe($opportunity['interests']);
		$temparray = [];
		foreach($opportunity['interests'] as $key=>$data) array_push($temparray, $data['id']);
		$opportunity['interests'] = $temparray;
//		var_dump_safe($opportunity['interests']);
	
		$temparray = [];
		foreach($opportunity['activities'] as $key=>$data) array_push($temparray, $data['id']);
		$opportunity['activities'] = $temparray;
	
		$opportunity['start_date'] = $opportunity['availability']['start_date'];
		$opportunity['end_date'] = $opportunity['availability']['end_date'];
		$opportunity['availability_details'] = $opportunity['availability']['information'];

		$temparray = [];
		foreach($opportunity['availability']['availability'] as $key=>$data) if($data) $temparray[$key] = $data;
		$opportunity['availability'] = $temparray;
		
//		var_dump_safe($opportunity['application_process']);
		$temparray = [];
		foreach($opportunity['application_process'] as $key=>$data) array_push($temparray, $data['id']);
		$opportunity['application_process'] = $temparray;
//		var_dump_safe($opportunity['application_process']);
		
		
	} else {
		$opportunity= array()
;	}


// remove magic quotes

//	if (get_magic_quotes_gpc()) {
//		echo_safe ("<h1>MAGIC QUOTES ENABLED</H1>");
		$_GET    = remove_magic_quotes($_GET);
		$_POST   = remove_magic_quotes($_POST);
		$_COOKIE = remove_magic_quotes($_COOKIE);
//	}

	
	
	$wpuserid = null;
		
	if (isset($_POST['update_opportunity'])) {
		$opportunity = new volplus_opportunity();
		$reg_errors = new WP_Error;
		// sanitize user form input

		unset($_POST['update_opportunity']);
		if(isset($_POST['opportunity']))$opportunity->opportunity = stripslashes(esc_html( $_POST['opportunity']));
		if(isset($_POST['organisation']))$opportunity->organisation = stripslashes(esc_html( $_POST['organisation']));
		if(isset($_POST['description']))$opportunity->description = stripslashes(esc_html( $_POST['description']));
		if(isset($_POST['skills']))$opportunity->skills = stripslashes(esc_html(  $_POST['skills']));
		if(isset($_POST['interests'])){
			foreach($_POST['interests'] as $key=>$value){
				$_POST['interests'][$key] = intval($value);}
			$opportunity->interests = $_POST['interests'];}
		if(isset($_POST['activities'])){
			foreach($_POST['activities'] as $key=>$value){
				$_POST['activities'][$key] = intval($value);}
			$opportunity->activities = $_POST['activities'];}
		if(isset($_POST['enquiries']))$opportunity->enquiries = $_POST['enquiries'];
		if(isset($_POST['location']))$opportunity->location = $_POST['location'];
		if(isset($_POST['address_line_1']))$opportunity->address_line_1 = stripslashes(esc_html( $_POST['address_line_1']));
		if(isset($_POST['address_line_2']))$opportunity->address_line_2 = stripslashes(esc_html( $_POST['address_line_2']));
		if(isset($_POST['address_line_3']))$opportunity->address_line_3 = stripslashes(esc_html( $_POST['address_line_3']));
		if(isset($_POST['town']))$opportunity->town = stripslashes(esc_html( $_POST['town']));
		if(isset($_POST['county']))$opportunity->county = stripslashes(esc_html( $_POST['county']));
		if(isset($_POST['postcode']))$opportunity->postcode = stripslashes(esc_html( $_POST['postcode']));

		if(isset($_POST['start_date'])){
//			var_dump_safe($_POST['start_date']);
			$start_date = date('d/m/Y',strtotime($_POST['start_date']));
//			var_dump_safe($start_date);
			$opportunity->start_date = $start_date;
		}
		if(isset($_POST['end_date'])){
			$end_date = date('d/m/Y',strtotime($_POST['end_date']));
//			var_dump_safe($end_date);
			$opportunity->end_date = $end_date;
		}
//		if(isset($_POST['end_date']))$opportunity->end_date = stripslashes(esc_html( $_POST['end_date']));
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
				$opportunity->$period=1;
				$_POST[$period]=1;
			}		
		}
		if(isset($_POST['availability_details']))$opportunity->availability_details = stripslashes(esc_html( $_POST['availability_details']));
		
		

//		var_dump_safe($_POST['application_process']);

		if(isset($_POST['application_process'])){
			foreach($_POST['application_process'] as $key=>$value){
				$_POST['application_process'][$key] = intval($value);}
			$opportunity->application_process = $_POST['application_process'];}

//		var_dump_safe($_POST['quality_control']);
		if(isset($_POST['quality_control'])){
			foreach($_POST['quality_control'] as $key=>$value){
				if($value!==""){
					$opportunity->quality_control[$key] = intval($value);
					$opportunity->quality_control_id[$key] = $key;
					$opportunity->quality_control_notes[$key] = $_POST['quality_control_notes'][$key];
				}
			}
		}

		if(empty( $opportunity->interests )) {
			$reg_errors->add('field', 'You need to select between 1 and 3 Interests'); }    
		if(empty( $opportunity->activities )) {
			$reg_errors->add('field', 'You need to select between 1 and 3 Interests'); }    

		if (is_wp_error( $reg_errors )) { 
			foreach ( $reg_errors->get_error_messages() as $key=>$data ) {
				$signUpError='<p style="color:#FF0000; text-align:left;"><strong>We have a problem </strong>: '.$data . '<br /></p>';
			} 
		}



//	var_dump_safe($opportunity);

		if ( 1 > count( $reg_errors->get_error_messages() ) ) {
			//add VolPlus account
			print_r_safe($opportunity);
			$endpoint = 'opportunities';
			if(isset($opp)) $endpoint .= '/' . $opp;
			$response = wp_remote_post(API_URL . $endpoint, array(
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Authorization' => 'Bearer '.API_KEY),
//				'body' => (array) $_POST,
				'body' => (array) $opportunity,
				'cookies' => array()
				)
			);

					setcookie('volplus_debug_body', json_encode($opportunity), time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );
					setcookie('volplus_debug_endpoint', $endpoint, time()+(3600* get_option('volplus_voltimeout',1)), COOKIEPATH, COOKIE_DOMAIN );


			$responsebody = (array) json_decode($response['body']);
//			var_dump_safe( $response );

			if ( !($response['response']['code'] == 201 || $response['response']['code'] ==  200)) {
				$error_message = $response['response']['message'];
				echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
				foreach($responsebody as $key=>$data){
					echo "<br/>".$key.": ";
					foreach($data as $data2){
						echo "<em>".$data2."</em>, ";
					}
				}
				unset($opportunity->volplus_id);
			} else {
				$responsebody = (array) json_decode($response['body']);
				if ( $response['response']['code'] !== 201 && $response['response']['code'] !== 200) {
					$error_message = $response['response']['message'];
					echo "Something went wrong: <em>".$response['response']['message']." (Code ".$response['response']['code'].")</em>";
					foreach($responsebody as $key=>$data){
						echo "<br/>".$key.": ";
						foreach($data as $data2){
							echo "<em>".$data2."</em>, ";
						}
					}
					unset($opportunity->volplus_id);
				} else { // Successful registration
//					var_dump_safe( $responsebody );			
					wp_redirect('/manage-organisation');
					exit;
				}
       	}
		}
	}

//	check if url is for existing opportunity
	if(is_null($opp)) {
		echo "<div id='vol_main_heading'><h2>Create your Opportunity</h2></div>";
		$buttontext = "Create Opportunity";
	}else {
		echo "<div id='vol_main_heading'><h2>Update your Opportunity</h2></div>";
		$buttontext = "Update Opportunity";
//		$response = get_opportunity_details($opp);
//		var_dump_safe($response);
//		$responsebody = json_decode($response['body']);
//		foreach($responsebody as $key=>$data){
//			$opportunity->$key = $data;
//		}
//		foreach($opportunity->availability as $key=>$data){
//			$opportunity->$key = $data;
//		}
//		unset($opportunity->availability);
// var_dump_safe($responsebody);
	}
	
	if(isset($signUpError)) echo '<div>'.$signUpError.'</div>'?>

	<form id="manage_opportunity" action="" method="post" name="manage_opportunity">
		<h2 class="volplus-col-12"><br/>Main Opportunity Details</h2>
		<label class="volplus-col-12">Opportunity Title <span class="error">*</span>  
			<input type="text" id="opportunity" name="opportunity" placeholder="Title of your opportunity" required autofocus value="<?php if(isset($opportunity['opportunity'])) echo $opportunity['opportunity']?>"/></label>
		<input type="hidden" id="organisation" name="organisation" value='<?php echo $_COOKIE['volplus_org_id']?>'/>
		<label class="volplus-col-12">Description <span class="error">*</span>
			<textarea id="description" name="description" rows="5" required><?php if(isset($opportunity['description'])) echo sanitize_textarea_field($opportunity['description'])?></textarea></label>
		<label class="volplus-col-12">Skills Required <span class="error">*</span>
			<textarea id="skills" name="skills" rows="5" required><?php if(isset($opportunity['skills'])) echo sanitize_textarea_field($opportunity['skills'])?></textarea></label>
		<h2 class="volplus-col-12"><br/>Location</h2>
		<label class="volplus-col-4">Select Location Type <span class="error">*</span>
			<?php $loc="";if(isset($opportunity['location'])) $loc=$opportunity['location']['location']; location($loc);?></label>
		<label class="volplus-col-12">Address 1 <span class="error">*</span>  
			<input type="text" id="address_line_1" name="address_line_1" placeholder="Address 1" required value="<?php if(isset($opportunity['location']['address']['0']['address_line_1'])) echo $opportunity['location']['address']['0']['address_line_1']?>"/></label>
		<label class="volplus-col-12">Address 2  
			<input type="text" id="address_line_2" name="address_line_2" placeholder="Address 2" value="<?php if(isset($opportunity['location']['address']['0']['address_line_2'])) echo $opportunity['location']['address']['0']['address_line_2']?>"/></label>
		<label class="volplus-col-12">Address 3  
			<input type="text" id="address_line_3" name="address_line_3" placeholder="Address 3" value="<?php if(isset($opportunity['location']['address']['0']['address_line_3'])) echo $opportunity['location']['address']['0']['address_line_3']?>"/></label>
		<label class="volplus-col-4">Town <span class="error">*</span> 
			<input type="text" id="town" name="town" placeholder="Town" required value="<?php if(isset($opportunity['location']['address']['0']['town'])) echo $opportunity['location']['address']['0']['town']?>"/></label>
		<label class="volplus-col-4">County  
			<input type="text" id="county" name="county" placeholder="County" value="<?php if(isset($opportunity['location']['address']['0']['county'])) echo $opportunity['location']['address']['0']['county']?>"/></label>
		<label class="volplus-col-3">Postcode <span class="error">*</span>   
			<input type="text" id="postcode" name="postcode" placeholder="Postcode" required value="<?php if(isset($opportunity['location']['address']['0']['postcode'])) echo $opportunity['location']['address']['0']['postcode']?>"/></label>

		<h2 class="volplus-col-12"><br/>Dates</h2>
		<label class="volplus-col-6">Start Date   
			<input type="date" id="start_date" name="start_date" placeholder="Earliest date volunteers could start" value="<?php if(isset($opportunity['start_date'])) echo $opportunity['start_date']?>"/></label>
		<label class="volplus-col-6">End Date   
			<input type="date" id="end_date" name="end_date" placeholder="Last date volunteers are needed" value="<?php if(isset($opportunity['end_date'])) echo $opportunity['end_date']?>"/></label>
		<h2 class="volplus-col-12"><br/>Interests & Activities</h2>
		<label class="volplus-col-6">Interests
			<?php $int=array();if(isset($opportunity['interests']))$int=$opportunity['interests'];display_interests($int,'opp-');?></label>
		<label class="volplus-col-6">Activities
			<?php $act=array();if(isset($opportunity['activities']))$act=$opportunity['activities'];display_activities($act,'opp-');?></label>
		<h2 class="volplus-col-12"><br/>Enquiry Handling</h2>
		<p class="volplus-col-12">Where should enquiries be sent to?
		<strong><input type="radio" name="enquiries" value=1 <?php if(isset($opportunity['enquiries']))if($opportunity['enquiries']==1) echo 'CHECKED'?>/>The Volunteer Centre
		<input type="radio" name="enquiries" value=2 <?php if(isset($opportunity['enquiries']))if($opportunity['enquiries']==2 ) echo 'CHECKED'?>/><?php echo $_COOKIE['volplus_org_name'] ?></strong>
		</p>
		<h2 class="volplus-col-12"><br/>Availability</h2>
		<label class="volplus-col-4">When is the opportunity available?
			<?php $avail=array();if(isset($opportunity['availability']))$avail=$opportunity['availability'];display_availability_table($avail);?></label>
		<label class="volplus-col-8">Availability Details (Further information regarding the availability of this opportunity)  
			<textarea id="availability_details" name="availability_details" rows="10"><?php if(isset($opportunity['availability_details'])) echo wp_strip_all_tags($opportunity['availability_details'])?></textarea></label>
		<h2 class="volplus-col-12"><br/>Application Process</h2>
		<label class="volplus-col-6">Application Stages
			<?php $proc=array();if(isset($opportunity['application_process'])) $proc=$opportunity['application_process']; applicationProcess($proc);?></label>
		<label class="volplus-col-12"><br/>Quality Control Requirements
			<?php $qc=array();if(isset($opportunity['quality_control'])) $qc=$opportunity['quality_control']; qualityControl($qc);?></label>

		<div class="volplus-col-12">
			<input id="update_opportunity" class = "button" type="submit" name="update_opportunity" value="<?php echo $buttontext ?>" style="font-size:1.2em">
		</div>
	</form>
 	<div id="welcomeNewUser" hidden="hidden"><?php
		echo stripslashes(html_entity_decode(get_option('volplus_welcomenewusermsg')))?>
	</div>



<!--		</div>-->
	<?php// }; ?>
<div class="volplus-col-12">

<!-- <?php echo "POST:<br/>" . json_encode($_POST, JSON_UNESCAPED_SLASHES)?><br/>
<?php echo "volunteer:<br/>" . json_encode($opportunity, JSON_UNESCAPED_SLASHES)?><br/>
<?php// var_dump_safe($GLOBALS['volunteer_fields']);?>-->
<div class="volplus-col-5"><?php var_dump_safe($_POST) ?></div//<div class="volplus-col-5"><?php var_dump_safe($opportunity) ?></div>
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

add_shortcode( 'volplus-manage-opportunity', 'volplus_manage_opportunity_func' );


// Enable shortcodes in widgets
add_filter('widget_text','do_shortcode');
