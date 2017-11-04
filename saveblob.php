<?php

  /* LICENSED UNDER GNUPL 3
   * Created by Sem Voigtlander
   * With thanks to Callum Jones and Neal for their TSS firmware API
   */
  
  /* DEFINITION OF THE NEEDED GLOBALS */
	$APIGLOBALS['TSS_API_URL'] = 'https://gs.apple.com/TSS/controller?action=2';
	$APIGLOBALS['TSS_UA_NAME'] = 'MTJailed'; //The User Agent to use for sending requests
	global $APIGLOBALS;


  /* FUNCTION FOR REQUESTING BOARDID FROM DEVICEID */
  // E.g DeviceID = iPhone7,2 BoardID = N61AP
   
	function tss_request_board($deviceid) {
		global $APIGLOBALS;
		$APIGLOBALS['TSS_API_URL'] = 'https://api.ipsw.me/v2.1/firmwares.json/condensed';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $APIGLOBALS['TSS_UA_NAME']);
		curl_setopt($ch, CURLOPT_URL, $APIGLOBALS['TSS_API_URL']);
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		curl_close($ch);
		if(array_key_exists($deviceid, $result['devices'])) {
			return $result['devices'][$deviceid]['BoardConfig'];
		}
	}

/* FUNCTION FOR REQUESTING THE TSS REQUEST PROPERTYLIST */
	function tss_request_manifest($board, $build, $ecid, $cpid = NULL, $bdid = NULL) {
		global $APIGLOBALS;
		$APIGLOBALS['TSS_API_URL'] = 'http://api.ineal.me/tss/manifest/'.$board. '/'.$build;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $APIGLOBALS['TSS_UA_NAME']);
		curl_setopt($ch, CURLOPT_URL, $APIGLOBALS['TSS_API_URL']);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = str_replace('<string>$ECID$</string>', '<integer>'.$ecid.'</integer>', $result);
		return $result;
	}
/* RETRIEVE THE SHSH2 BLOBS FROM APPLE'S TSS SERVER */
// APPLE TSS API RESPONSE CODES:
// 0 SUCCESS
// 94 INELIGIBLE FOR BUILD
// 100 INTERNAL ERROR
// 511 DATA PROVIDED INVALID
// 5000 INVALID OPTION

	function tss_request_apple_blobs($board, $build, $ecid, $cpid=null, $bdid=null) {
		global $APIGLOBALS;
		$manifest = tss_request_manifest($board, $build, $ecid, $cpid, $bdid);
		$APIGLOBALS['TSS_API_URL'] ='http://gs.apple.com/TSS/controller?action=2';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $APIGLOBALS['TSS_UA_NAME']);
		curl_setopt($ch, CURLOPT_URL, $APIGLOBALS['TSS_API_URL']);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $manifest);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/xml','Connection: Keep-Alive'));
		$response = curl_exec($ch);
		curl_close($ch);
		if (preg_match('/STATUS=9/',$response)) {
			return "This device is not eligible for the requested build";
		}
		if (preg_match('/STATUS=100/',$response)) {
			return "The specified ecid might be incorrect.";
		}
		header("Content-Type: application/xml");
				$response=str_replace('= ', '=', $response);
		print_r(parse_tss_response($response));
	}

  //Remove the status from the response
	function parse_tss_response($response) {
		return strstr($response, '<?xml');
	}
  
	
  
	if(isset($_POST['device']) && isset($_POST['build']) && isset($_POST['ecid'])) {
		$board = tss_request_board($_POST['device']);
		tss_request_apple_blobs($board, $_POST['build'], $_POST['ecid']);
 
 // This part is for the demo only
 // It uses an iPhone 5 on 10.3.3 to request SHSH
 // Remove it in your release
 
	} else {
    $board = tss_request_board("iPhone5,2"); //Example (iPhone 5) for when no POST request is made
		tss_request_apple_blobs($board, "14G60", "4234419567950");
	}
?>
