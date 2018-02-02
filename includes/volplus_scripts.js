// run php in javascript
function fromPHP(phpString) {
	var category = phpString;
	alert(category);
}

function LimitInterests()  { 
	var elements = document.getElementsByName("interests[]");
	var total = 0;
	for(var i in elements) {
		var element = elements[i];
		if(element.checked) total++;
		if(total>3) {
			alert("No more than 3 Interests can be selected. Please uncheck another Interest if you want to select this one.");
			element.checked = false;
			return false;    
		}
	}
} 

function LimitActivities()  { 
	var elements = document.getElementsByName("activities[]");
	var total = 0;
	for(var i in elements) {
		var element = elements[i];
		if(element.checked) total++;
		if(total>3) {
			alert("No more than 3 Activities can be selected. Please uncheck another Activity if you want to select this one.");
			element.checked = false;
			return false;    
		}
	}
} 


jQuery(document).ready(function($) {

//		Log out by clearing cookies & changing displayed divs
	$('div#logout.button').click(function(){
		document.cookie = 'volplus_user_id=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
		document.cookie = 'volplus_user_first_name=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
		document.cookie = 'volplus_user_last_name=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
		document.cookie = 'volplus_user=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
		document.location.href = '/';
//		document.getElementById("not_logged_in").style.display = "inherit";
//		document.getElementById("login").style.display = "inherit";
//		document.getElementById("logged_in").style.display = "none";
//		document.getElementById("welcome").style.display = "none";
//		$('form#login p.status').text('');
//		document.getElementById("vol_main_heading").innerHTML = "<h2>Create your account</h2>";
	})

//		Perform AJAX login on form submit
	$('form#login').on('submit', function(e){
		$('form#login p.status').show().text(ajax_login_object.loadingmessage);
//console.log("V+ Ajax URL: ", ajax_login_object.ajaxurl);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_login_object.ajaxurl,
			data: { 
				'action': 'volplusajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
				'email_address': $('form#login #email_address').val(), 
				'password': $('form#login #password').val(), 
				'security': $('form#login #security').val() },
			success: function(data){
				$('form#login p.status').text(data.message);
				if (data.loggedin == true){
					var voldetails = JSON.parse(data.response.body);
//console.log("success data:", voldetails);
					document.getElementById("not_logged_in").style.display = "none";
					document.getElementById("login").style.display = "none";
					if (!data.first_name) data.first_name = 'Private';
					if (!data.last_name) data.last_name = 'Volunteer';
					$('div#welcome_name').text(data.first_name + " " + data.last_name);
					document.getElementById("welcome").style.display = "inherit";
					if (document.getElementById("user_registration")) document.getElementById("user_registration").value = "Update";
					if(location.pathname == '/volunteer-details/'){
						document.getElementById("vol_main_heading").innerHTML = "<h2>Update your details</h2>";
						document.getElementById("title").value = voldetails.title;
						document.getElementById("first_name").value = decodeHtml(voldetails.first_name);
						document.getElementById("last_name").value = decodeHtml(voldetails.last_name);
						document.getElementById("email_address").value = voldetails.email_address;
						document.getElementById("address_line_1").value = decodeHtml(voldetails.address_line_1);
						document.getElementById("address_line_2").value = decodeHtml(voldetails.address_line_2);
						document.getElementById("address_line_3").value = decodeHtml(voldetails.address_line_3);
						document.getElementById("town").value = decodeHtml(voldetails.town);
						document.getElementById("county").value = decodeHtml(voldetails.county);
						document.getElementById("postcode").value = voldetails.postcode;
						document.getElementById("telephone").value = voldetails.telephone;
						document.getElementById("mobile").value = voldetails.mobile;
						document.getElementById("availability_details").value = decodeHtml(voldetails.availability_details);
						document.getElementById("volunteering_experience").value = decodeHtml(voldetails.volunteering_experience);
						document.getElementById("volunteering_reason_info").value = decodeHtml(voldetails.volunteering_reason_info);
						document.getElementById("date_birth").value = voldetails.date_birth;
						document.getElementById("gender").value = voldetails.gender;
						document.getElementById("employment").value = voldetails.employment;
						document.getElementById("ethnicity").value = voldetails.ethnicity;
						document.getElementById("how_heard").value = voldetails.how_heard;
						document.getElementById("date_birth_prefer_not_say").value = voldetails.date_birth_prefer_not_say;
						document.getElementById("disability").value = voldetails.disability;
						if (voldetails.disability == 1) {
			 				document.getElementById("display-details-label").style.display = "block";}
			 			else document.getElementById("display-details-label").style.display = "none";
	
						
						for(var i=0, len=voldetails.activities.length; i<len; i++){
							document.getElementById("activity-"+(voldetails.activities[i])).checked = true;}
						for(var i=0, len=voldetails.interests.length; i<len; i++){
							document.getElementById("interest-"+(voldetails.interests[i])).checked = true;}
						for(var i=0, len=voldetails.disabilities.length; i<len; i++){
							document.getElementById("disabilities-"+(voldetails.disabilities[i])).checked = true;}
						for(var i=0, len=voldetails.reasons.length; i<len; i++){
							document.getElementById("reason-"+(voldetails.reasons[i])).checked = true;}
						
						var periods = [
							'mon_mor','mon_aft','mon_eve',
							'tue_mor','tue_aft','tue_eve',
							'wed_mor','wed_aft','wed_eve',
							'thu_mor','thu_aft','thu_eve',
							'fri_mor','fri_aft','fri_eve',
							'sat_mor','sat_aft','sat_eve',
							'sun_mor','sun_aft','sun_eve'
						];
						for(var i=0, len=periods.length; i<len; i++){
							document.getElementById("availability-"+ i).checked = (voldetails.availability.hasOwnProperty(periods[i]))}
					}
				}
			}
		});
		e.preventDefault();
	});

	$( "#volplus_response_notloggedin" ).dialog({
		dialogClass: 'wp-dialog',
		modal: true,
		autoOpen: false,
		show: {effect: "fade", duration: 500},
		closeOnEscape: true,
		title: "I'm Interested...",
		width: 400,
		buttons: [
			{
				text: 'Register',
				class: 'button',
				click: function() {
					window.location.assign("volunteer-details/?opp-id=" + $.cookie('volplus_opp_id'));
				}
			},
 				{
				text: 'Contact us',
				class: 'button',
				click: function() {
					document.getElementById("responseintro").style.display = "none";
					document.getElementById("responseform").style.display = "inherit";
				}
			},
 				{
				text: 'Cancel',
				class: 'button',
				click: function() {
					$(this).dialog('close');
				}
			}
		]
	});

	$( "#volplus_response_loggedin" ).dialog({
		dialogClass: 'wp-dialog',
		modal: true,
		autoOpen: false,
		show: {effect: "fade", duration: 500},
		closeOnEscape: true,
		title: "I'm Interested...",
		width: 400,
		buttons: [
			{
				text: 'Register my interest',
				class: 'button',
				click: function() {
					$('div#volplus_response_loggedin p.status').show().text(ajax_login_object.loadingmessage);
					$.ajax({
						type: 'POST',
						dataType: 'json',
						url: ajax_login_object.ajaxurl,
						data: { 
							'action': 'volplusajaxenquire', //calls wp_ajax_nopriv_ajaxlogin
							'security': $('div#opportunity_detail #security').val(),
							'interested_notes': $('textarea#interested_notes').val()
						}, 
						success: function(result){
							$("div#volplus_response_loggedin p.status").text(result.message);
							$("#volplus_response_loggedin").dialog('close');
							$('#volplus_interest_registered').dialog('open');
						}
					});
				}
			},
 				{
				text: 'Cancel',
				class: 'button',
				click: function() {
					$(this).dialog('close');
				}
			}
		]
	});

	$( "#volplus_interest_registered" ).dialog({
		dialogClass: 'wp-dialog',
		modal: true,
		autoOpen: false,
		show: {effect: "fade", duration: 500},
		closeOnEscape: true,
		title: "Interest Registered",
		width: 400,
		buttons: [
 				{
				text: 'Close',
				class: 'button',
				click: function() {
					$(this).dialog('close');
				}
			}
		]
	});

	$( ".volplus_respondButton" ).click(function() {
		var loggedin = (document.cookie.indexOf("volplus_user_id") >= 0);
		if(loggedin) {
			document.getElementById("not_logged_in").style.display = "none";
			document.getElementById("login").style.display = "none";
			document.getElementById("logged_in").style.display = "inherit";
			document.getElementById("welcome").style.display = "inherit";
			$( "#volplus_response_loggedin" ).dialog( "open" );				
		}else {
			document.getElementById("responseform").style.display = "none";
			document.getElementById("responseintro").style.display = "inherit";
			document.getElementById("not_logged_in").style.display = "inherit";
			document.getElementById("login").style.display = "inherit";
			document.getElementById("logged_in").style.display = "none";
			document.getElementById("welcome").style.display = "none";
			$( "#volplus_response_notloggedin" ).dialog( "open" );
		}
	});


});

function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}




// enquiry
