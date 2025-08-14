<?php

$redirectreports = filter_input(INPUT_GET, 'redirectreports');
    if ($redirectreports == "intOn" ){  

    	$json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
		$json = json_decode($json, true);
		$json['RedirectReports']['int'] = 'On';
		$newJsonString = json_encode($json);
		file_put_contents('/usr/local/bin/bottelegram/settings.json', $newJsonString);
		//echo 'intOn';
		


        }
    if ($redirectreports == "intOff" ) {        
 
    	$json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
		$json = json_decode($json, true);
		$json['RedirectReports']['int'] = 'Off';
		$newJsonString = json_encode($json);
		file_put_contents('/usr/local/bin/bottelegram/settings.json', $newJsonString);
		//echo 'intOff';

        exit();
        }

    if ($redirectreports == "sdOn" ){  

    	$json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
		$json = json_decode($json, true);
		$json['RedirectReports']['sd'] = 'On';
		$newJsonString = json_encode($json);
		file_put_contents('/usr/local/bin/bottelegram/settings.json', $newJsonString);
		//echo 'sdOn';


        }
    if ($redirectreports == "sdOff" ) {        
 
    	$json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
		$json = json_decode($json, true);
		$json['RedirectReports']['sd'] = 'Off';
		$newJsonString = json_encode($json);
		file_put_contents('/usr/local/bin/bottelegram/settings.json', $newJsonString);
		//echo 'sdOff';

        exit();
        }


    if ($redirectreports == "usbOn" ){  

    	$json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
		$json = json_decode($json, true);
		$json['RedirectReports']['usb'] = 'On';
		$newJsonString = json_encode($json);
		file_put_contents('/usr/local/bin/bottelegram/settings.json', $newJsonString);
		//echo 'usbOn';


        }
    if ($redirectreports == "usbOff" ) {        
 
    	$json = file_get_contents('/usr/local/bin/bottelegram/settings.json');
		$json = json_decode($json, true);
		$json['RedirectReports']['usb'] = 'Off';
		$newJsonString = json_encode($json);
		file_put_contents('/usr/local/bin/bottelegram/settings.json', $newJsonString);
		//echo 'usbOff';

        exit();
        }