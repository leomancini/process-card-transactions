<?php
    require(__DIR__.'/../secrets.php');
    require(__DIR__.'/../config.php');

    if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }

    function addToIgnoreList($merchant) {
        global $CONFIG;
        
        $ignoreFileRead = file_get_contents($CONFIG['IGNORE_FILE'], true);
        $ignore = explode("\n", $ignoreFileRead);
        $ignoreMerchants = [];

        foreach ($ignore as $line) {
            if ($line !== '') {
                array_push($ignoreMerchants, strtoupper($line));
            }
        }

        if (!in_array(strtoupper($merchant), $ignoreMerchants)) {
            $ignoreFileWrite = fopen($CONFIG['IGNORE_FILE'], 'a') or die('Unable to open data file!');
            $input = strtoupper($merchant)."\n";
            fwrite($ignoreFileWrite, $input);
            fclose($ignoreFileWrite);

            return json_encode([ 'success' => true ]);
        } else {
            return json_encode([ 'success' => false, 'error' => 'Merchant already on ignore list' ]);
        }
    }

    $output = addToIgnoreList(urldecode($_GET['merchant']));

    if ($_GET['debug']) {
        header('Content-Type: application/json; charset=utf-8');
        echo $output;
    } else {
        echo "<script type='text/javascript'>setTimeout(function(){ self.close(); }, 1000);</script>";
    }
?>