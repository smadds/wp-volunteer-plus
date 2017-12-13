<?php

const GOOD_RESPONSE = 'HTTP/1.1 200 OK';
const REFDOMAIN = 'https://maddox.co.uk/volplus/';
const REFSUFFIX = '.volplus';

function volplus_licensed() {

	$domain = $_SERVER['HTTP_HOST'];
	$key = get_option('volplus_license_code');
	$refkey = '';
	$refkeyurl = REFDOMAIN.$domain.REFSUFFIX;

	if(@get_headers($refkeyurl)[0] == GOOD_RESPONSE){
		$refkey = trim(file_get_contents($refkeyurl));
		$result = ($refkey == $key);
	} else {
		$result = 0;
	}
	return $result;
}
