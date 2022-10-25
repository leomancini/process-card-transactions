<?php
	require(__DIR__.'/../secrets.php');

	if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }
	
	function addLocationToList($venue, $list) {
		global $SECRETS;
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://api.foursquare.com/v2/lists/'.$list.'/additem?venueId='.$venue.'&oauth_token='.$SECRETS['FOURSQUARE']['O_AUTH_TOKEN'].'&v=20221022',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST'
		));

		$json = curl_exec($curl);

		curl_close($curl);

		return $json;
	}

	if ($_GET['venue'] && $_GET['list']) {
		if ($_GET['addToSavedPlaces']) {
			$response1 = json_decode(addLocationToList($_GET['venue'], $_GET['list']), true);
			$response2 = json_decode(addLocationToList($_GET['venue'], $SECRETS['FOURSQUARE']['USER_ID'].'/todos'), true);

			if ($response1['meta']['code'] === 200 && $response2['meta']['code'] === 200) {
				$output = ['success' => true ];
			} else {
				$output = ['success' => false, 'errors' => [$response1['meta']['errorDetail'], $response2['meta']['errorDetail']]];
			}

		} else {
			$response = json_decode(addLocationToList($_GET['venue'], $_GET['list']), true);

			if ($response['meta']['code'] === 200) {
				$output = ['success' => true ];
			} else {
				$output = ['success' => false, 'error' => $response['meta']['errorDetail']];
			}
		}
	} else {
		$output = ['success' => false, 'error' => 'No venue or list provided!'];
	}

    if ($_GET['debug']) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($output);
    } else {
    	echo "<script type='text/javascript'>setTimeout(function(){ self.close(); }, 1000);</script>";
    }
?>