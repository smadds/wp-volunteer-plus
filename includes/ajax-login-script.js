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

	$('div#logout.button').click(function(){
		document.cookie = 'volplus_user_id=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain='+location.host;
		document.cookie = 'volplus_user_first_name=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain='+location.host;
		document.cookie = 'volplus_user_last_name=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain='+location.host;
		document.getElementById("not_logged_in").style.display = "inherit";
		document.getElementById("login").style.display = "inherit";
		document.getElementById("logged_in").style.display = "none";
		document.getElementById("welcome").style.display = "none";
		$('form#login p.status').text('');
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
// console.log("success data:", data);
				$('form#login p.status').text(data.message);
				if (data.loggedin == true){
//					document.location.href = ajax_login_object.redirecturl;
					document.getElementById("not_logged_in").style.display = "none";
					document.getElementById("login").style.display = "none";
					if (!data.first_name) data.first_name = 'Private';
					if (!data.last_name) data.last_name = 'Volunteer';
					$('div#welcome_name').text(data.first_name + " " + data.last_name);
					document.getElementById("welcome").style.display = "inherit";
					
				}
			}
		});
		e.preventDefault();
	});
});
