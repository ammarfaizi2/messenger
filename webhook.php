<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
use System\Messsenger;



if (isset($_GET['pg']) && isset($config[$_GET['pg']])) {
	$bot = new Messsenger($config[$_GET['pg']]['validation'], $config[$_GET['token']]);


	switch ($_GET['action']) {
		case 'set_welcome':
			$updated = $bot->setWelcomeMessage(FACEBOOK_PAGE_ID, "Greetings! The humans who invented me programmed me to tell you about...");
			if($updated){
				echo "Welcome Message updated succesfully!";
			} else {
				echo "Error during Welcome Message update";
			}
			break;
		
		default:
			# code...
			break;
	}
}