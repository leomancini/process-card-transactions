<?php
    function getSuggestedLists($location) {
        global $PATH;
        global $userLists;
        
        $suggestedLists = [];

        // These are lists that are full, so hide them from the suggestions
        // https://github.com/leomancini/place-lists/blob/master/resources/helpers/base.php#L242

        $hiddenLists = [
            '567d7b1d38fa9c91825e5c7a', // San Francisco
            '59e5a3ba8a6f1741c057072f', // San Francisco 2
            '567e16a238fa9c9182a0b903' // New York
        ];

        $nameConversion = [
            'San Francisco 2' => 'San Francisco',
            'San Francisco 3' => 'San Francisco',
            'New York 2' => 'New York'
        ];

        foreach ($userLists as $userList) {
            if (!in_array($userList['id'], $hiddenLists)) {
                if (in_array($userList['name'], array_keys($nameConversion))) {
                    $userListDisplayName = $nameConversion[$userList['name']];
                } else {
                    $userListDisplayName = $userList['name'];
                }

                array_push($suggestedLists, Array(
                    'name' => $userListDisplayName,
                    'id' => $userList['id'],
                    'similarity' => levenshtein($userList['name'], $location['market']),
                    'addToFoursquareList' => $PATH.'/../actions/addLocationToFoursquareList?venue='.$location['id'].'&list='.$userList['id'].'&addToSavedPlaces=true&password='.$_GET['password']
                ));
            }
        }

        usort($suggestedLists, 'sortBySimilarity');

        $suggestedLists = array_filter($suggestedLists, 'filterOutLowSimilarity');

        $suggestedListsLimited = array_slice($suggestedLists, 0, 3);

        return $suggestedListsLimited;
    }

    function getTransactions($dateFilter) {
        global $CONFIG;
        global $PATH;

        $dataFile = file_get_contents($CONFIG['DATA_FILE'], true);
        $data = explode("\n", $dataFile);
        $transactions = [];

        $ignoreFile = file_get_contents($CONFIG['IGNORE_FILE'], true);
        $ignore = explode("\n", $ignoreFile);
        $ignoreMerchants = [];

        foreach ($ignore as $line) {
            if ($line !== '') {
                array_push($ignoreMerchants, strtoupper($line));
            }
        }

        foreach ($data as $line) {
            if ($line !== '') {
                $elements = explode(';', $line);

                $transaction = [];
                $transaction['merchant'] = $elements[0];
                $transaction['amount'] = floatval($elements[1]);
                $date = $elements[2];
                $time = $elements[3];
                $transaction['card'] = $elements[4];

                $transaction['datetime'] = new DateTime();

                if (strpos($time, ' ET') !== false) {
                    $time = str_replace(' ET', '', $time);
                } else {
                    $transaction['datetime']->setTimezone(new DateTimeZone('America/New_York'));
                }

                $transaction['datetime']->setTimestamp(strtotime($date.' '.$time));

                if (!in_array(strtoupper($transaction['merchant']), $ignoreMerchants)) {
                    if ($dateFilter) {
                        if ($dateFilter === $datetime->format('Y-m-d')) {
                            array_push($transactions, formatTransaction($transaction));
                        }
                    } else {
                        array_push($transactions, formatTransaction($transaction));
                    }
                }
            }
        }
        
        return $transactions;
    }

    function formatTransaction($transaction) {
        global $PATH;

        $bestMatchLocation = getLocationsForMerchant($transaction['merchant'])[0];
        if (is_null($bestMatchLocation['lists'])) {
            $bestMatchLocation['suggestedLists'] = getSuggestedLists($bestMatchLocation);
        }

        return Array(
            'merchant' => $transaction['merchant'],
            'amount' => $transaction['amount'],
            'date' => $transaction['datetime']->format('Y-m-d'),
            'time' => $transaction['datetime']->format('G:i'),
            'card' => $transaction['card'],
            'bestMatchLocation' => $bestMatchLocation,
            'addToSplitwise' => $PATH.'/../actions/addAmountToSplitwise?amount='.$transaction['amount'].'&description='.urlencode($transaction['merchant']).'&datetimeFormatted='.urlencode($transaction['datetime']->format('F j, Y \a\t g:i A')).'&password='.$_GET['password']
        );
    }
?>