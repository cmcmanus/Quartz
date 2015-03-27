<?php

	include 'activitydefinitions.php';
	
	checkCredentials();
	
	$activity = new ViewAccountActivity();
	
	$activity->run();

?>
