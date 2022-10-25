<?php
	function findLocationByMerchant($merchantName) {
		global $CONFIG;
		global $SECRETS;
		global $PATH;

		$searchCenterPoint = '40.730610,-73.935242';
		if ($CONFIG['DEBUG_MODE']) {
			$url = $PATH.'/../data/sample/KOREA-GINSENG-MANHAT.json';
		} else {
			$url = 'https://api.foursquare.com/v3/places/search?query='.urlencode($merchantName).'&ll='.urlencode($searchCenterPoint).'&radius=10000&limit=3';
		}

		$curl = curl_init();

		$header = array();
		$header[] = 'Content-length: 0';
		$header[] = 'Content-type: application/json';
		$header[] = 'Authorization: '.$SECRETS['FOURSQUARE']['AUTH_HEADER'];

		curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_URL, $url);
		
		$response = curl_exec($curl);

		$json = json_decode($response, true);
		 
		return $json['results'];
	}

	function checkLocationLists($id) {
		global $CONFIG;
		global $SECRETS;
		global $PATH;

		if ($CONFIG['DEBUG_MODE']) {
			$url = $PATH.'/../data/sample/59074b83f870fd51dcd5711d-lists.json';
		} else {
			$url = 'https://api.foursquare.com/v2/venues/'.$id.'/listed?oauth_token='.$SECRETS['FOURSQUARE']['O_AUTH_TOKEN'].'&group=created&v=20221022';
		}

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_URL, $url);
		 
		$response = curl_exec($curl);

		$json = json_decode($response, true);
		 
		if ($json['response'] && $json['response']['lists'] && $json['response']['lists']['items']) {
			$lists = [];

			foreach ($json['response']['lists']['items'] as $list) {
				array_push($lists, Array(
					'name' => $list['name'],
					'id' => $list['id']
				));
			}

			return $lists;
		} else {
			return null;
		}
	}
	
	function getLocationsForMerchant($merchantName) {
		$results = findLocationByMerchant($merchantName);

		$names = [];

		foreach ($results as $result) {
			array_push($names, Array(
				'name' => $result['name'],
				'id' => $result['fsq_id'],
				'lists' => checkLocationLists($result['fsq_id']),
				'category' => $result['categories'][0]['name'],
				'address' => $result['location']['formatted_address'],
				'market' => $result['location']['dma'],
				'url' => 'https://foursquare.com/v/'.$result['fsq_id'],
				'similarity' => levenshtein(ucwords(strtolower($merchantName)), $result['name'])
			));
		}

		usort($names, 'sortBySimilarity');

		return $names;
	}
?>