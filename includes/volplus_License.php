<?php

function volplus_licensed() {

	$domain = $_SERVER['HTTP_HOST'];
	$key = get_option('volplus_license_code');
	$refkey = '';
	$refkeyurl = 'https://maddox.co.uk/volplus/'.$domain.'.volplus';

	if(@get_headers($refkeyurl)[0] == GOOD_RESPONSE){
		$refkey = trim(file_get_contents($refkeyurl));
		$result = ($refkey == $key) ? 1 : 0;
	} else {
		$result = 0;
	}
	return $result;
}
