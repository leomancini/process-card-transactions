<?php
	require(__DIR__.'/../secrets.php');
	require(__DIR__.'/../functions/getLocationsForMerchant.php');
	
	if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }

	header('Content-Type: application/json; charset=utf-8');

	echo json_encode(getLocationsForMerchant($_GET['merchant']));
?>