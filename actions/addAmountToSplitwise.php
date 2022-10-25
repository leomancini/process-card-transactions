<?php
	require(__DIR__.'/../secrets.php');

	if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }
	
	function addAmountToSplitwise($expense) {
		global $SECRETS;
		
	    $curl = curl_init();

	    curl_setopt_array($curl, array(
	      CURLOPT_URL => "https://secure.splitwise.com/api/v3.0/create_expense?cost=".$expense['cost']."&description=".urlencode($expense['description'])."&payment=false&group_id=".$expense['group_id']."&details=".urlencode($expense['details'])."&split_equally=true",
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => "",
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 0,
	      CURLOPT_FOLLOWLOCATION => true,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_CUSTOMREQUEST => "POST",
	      CURLOPT_HTTPHEADER => array(
	        "Content-Type: application/json",
	        "Authorization: Bearer ".$SECRETS['SPLITWISE']['AUTH_HEADER']
	      ),
	    ));
	    
	    $response = curl_exec($curl);
	    
	    curl_close($curl);

	    $json = json_decode($response, true);

	    if (count($json['errors']) === 0) {
	    	return json_encode([ 'success' => true ]);
	    } else {
	    	return json_encode([ 'success' => false, 'error' => $json['errors'] ]);
	    }
	}

	date_default_timezone_set('America/New_York');

    $output = addAmountToSplitwise([
        'cost' => $_GET['amount'],
        'description' => urldecode($_GET['description']),
        'group_id' => $SECRETS['SPLITWISE']['GROUP_ID'],
        'details' => "Transaction occurred: ".urldecode($_GET['datetimeFormatted'])."\n\nAdded by automation: ".date('F j, Y \a\t g:i A')
    ]);

    if ($_GET['debug']) {
		header('Content-Type: application/json; charset=utf-8');
		echo $output;
    } else {
    	echo "<script type='text/javascript'>setTimeout(function(){ self.close(); }, 1000);</script>";
    }
?>