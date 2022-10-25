<?php
	require(__DIR__.'/../config.php');
	require(__DIR__.'/../secrets.php');
	require(__DIR__.'/../functions/base.php');
	require(__DIR__.'/../functions/getLocationsForMerchant.php');
	require(__DIR__.'/../functions/getListsForUser.php');
	require(__DIR__.'/../functions/getTransactions.php');
	
	if ($_GET['password'] !== $SECRETS['PASSWORD']) { die(); }

	header('Content-Type: application/json; charset=utf-8');

	$userLists = getListsForUser($SECRETS['FOURSQUARE']['USER_ID']);

	echo json_encode(getTransactions($_GET['date']));
?>