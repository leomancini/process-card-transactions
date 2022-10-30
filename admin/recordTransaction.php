<?php
    require(__DIR__.'/../config.php');
    require(__DIR__.'/../secrets.php');

    if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }

    if ($CONFIG['DEBUG_MODE']) {
        $dataFilePath = $CONFIG['DEBUG']['DATA_FILE'];
    } else {
        $dataFilePath = $CONFIG['DATA_FILE'];
    }
    
    $dataFile = fopen($dataFilePath, 'a') or die('Unable to open data file!');
    $input = $_GET['input']."\n";
    fwrite($dataFile, $input);
    fclose($dataFile);
?>