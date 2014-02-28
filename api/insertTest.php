<?php
	error_reporting(E_ALL);

	$url = 'http://pointsofinterest.info/api/points/';
	//$url = "localhost/api/points";
	// $url = "google.com";
	$fields = array(
		'name' => urlencode("test point star"),
		'longitude' => urlencode("43.111"),
		'latitude' => urlencode("2.444"),
		'message' => urlencode("starbucks test point")
	);

	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');

	// $fields = json_encode($fields);
	var_dump($fields);
	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);   
	// curl_setopt($ch,CURLOPT_POST, count($fields));
	// curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                                                                                                                
	 
	$content = curl_exec($ch);
	if(curl_errno($ch)){
    echo '<br>Curl error: ' . curl_error($ch)."<br>";
	}
	curl_close($ch);

	// $ch = curl_init($url);
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	// $content = curl_exec($ch);
	

	echo "result: ".$content;

?>