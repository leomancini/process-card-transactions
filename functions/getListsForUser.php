<?php
    function getListsForUser($user) {
        global $CONFIG;
        global $SECRETS;
        global $PATH;

        if ($CONFIG['DEBUG_MODE']) {
            $url = $PATH.'/../data/sample/149789344-lists.json';
        } else {
            $url = 'https://api.foursquare.com/v2/users/'.$user.'/lists?oauth_token='.$SECRETS['FOURSQUARE']['O_AUTH_TOKEN'].'&group=created&v=20221022&limit=200';
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);

        $json = json_decode($response, true);

        $lists = [];

        foreach ($json['response']['lists']['items'] as $list) {
            array_push($lists, Array(
                'name' => $list['name'],
                'id' => $list['id']
            ));
        }

        return $lists;
    }
?>