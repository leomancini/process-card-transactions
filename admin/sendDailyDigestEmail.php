<?php
    require(__DIR__.'/../config.php');
    require(__DIR__.'/../secrets.php');
    require(__DIR__.'/../functions/base.php');
    require(__DIR__.'/../functions/getLocationsForMerchant.php');
    require(__DIR__.'/../functions/getListsForUser.php');
    require(__DIR__.'/../functions/getTransactions.php');

    if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }

    $userLists = getListsForUser($SECRETS['FOURSQUARE']['USER_ID']);

    date_default_timezone_set('America/New_York');

    $datetime = new DateTime();
    if ($_GET['date']) {
        $datetime->setTimestamp(strtotime($_GET['date']));
        $transactions = getTransactions($datetime->format('Y-m-d'));
    } else {
        $transactions = getTransactions(null);
    }

    if ($transactions && count($transactions) > 0) {
        $message = '';

        foreach ($transactions as $transaction) {
            $message .= '<b>'.$transaction['merchant'].'</b>';
            $message .= ' $'.number_format($transaction['amount'], 2, '.', '');;
            $message .= "<br>";
            if ($transaction['bestMatchLocation'] && count($transaction['bestMatchLocation']) > 0) {
                $message .= '<a href="'.$transaction['bestMatchLocation']['url'].'">';
                $message .= $transaction['bestMatchLocation']['name'];
                $message .= '</a>';
                $message .= ' ('.$transaction['bestMatchLocation']['category'].')';
                $message .= '<br>';
                $message .= $transaction['bestMatchLocation']['address'];
                $message .= '<br>';

                if ($transaction['bestMatchLocation']['lists'] && count($transaction['bestMatchLocation']['lists']) > 0) {
                    $lists = $transaction['bestMatchLocation']['lists'];

                    foreach ($lists as $list) {
                        if ($list['name'] !== 'My Saved Places') {
                            $message .= '&#9989; On '.$list['name'].' list';
                        }
                    }
                }

                if ($transaction['bestMatchLocation']['suggestedLists'] && count($transaction['bestMatchLocation']['suggestedLists']) > 0) {
                    $suggestedList = $transaction['bestMatchLocation']['suggestedLists'][0];
                    $message .= '&#128205; <a href="'.$suggestedList['addToFoursquareList'].'">Add to '.$suggestedList['name'].' list</a>';
                }
            }

            $message .= '&nbsp;&nbsp;&nbsp;';
            $message .= '&#128184; <a href="'.$transaction['addToSplitwise'].'">Add to Splitwise</a>';
            $message .= '<br><br>';
        }

        $to = $CONFIG['EMAIL_RECIPIENT'];

        $subject = 'Transactions for '.$datetime->format('F j, Y');

        $headers = "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: ".$CONFIG['EMAIL_SENDER']."\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";

        echo '<pre>';
        echo 'To: '.$to;
        echo '<br>';
        echo 'Subject: '.$subject;
        echo '<br>';
        echo 'Message:';
        echo '<br>';
        echo '<br>';
        echo $message;
        echo '</pre>';

        $message = chunk_split(base64_encode($message));
        
        mail($to, $subject, $message, $headers);

        file_put_contents($CONFIG['DATA_FILE'], '');
    }
?>