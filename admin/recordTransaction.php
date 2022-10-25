<?php
    require(__DIR__.'/../config.php');
    require(__DIR__.'/../secrets.php');

    if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }

    $dataFile = fopen($CONFIG['DATA_FILE'], 'a') or die('Unable to open data file!');
    $input = $_GET['input']."\n";
    fwrite($dataFile, $input);
    fclose($dataFile);
?>