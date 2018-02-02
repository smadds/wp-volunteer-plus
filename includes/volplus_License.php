<?php

const GOOD_RESPONSE = 'HTTP/1.1 200 OK';
const REFDOMAIN = 'https://maddox.co.uk';
const BACKUPDOMAIN = 'https://bcmx.com';
const FOLDER = '3Pub-silly1-0tansy-heady4-Once1-gauss-4bon-chip-Vain-Dub';
const SUFFIX = '.volplus';

function volplus_licensed() {

	$domain = $_SERVER['HTTP_HOST'];
	$key = get_option('volplus_license_code');
	$refkey = '';
	$refkeyurl = REFDOMAIN.'/'.FOLDER.'/'.$domain.SUFFIX;
	$backupkeyurl = BACKUPDOMAIN.'/'.FOLDER.'/'.$domain.SUFFIX;

	$result = 0;
	if(@get_headers($refkeyurl)[0] == GOOD_RESPONSE){
		$refkey = trim(file_get_contents($refkeyurl));
		$result = ($refkey == $key);
	} elseif(@get_headers($backupkeyurl)[0] == GOOD_RESPONSE) {
		$refkey = trim(file_get_contents($backupkeyurl));
		$result = ($refkey == $key);
	}
	return $result;
}
