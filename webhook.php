<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
use System\Messsenger;

header('Content-type:application/json');

if (isset($_GET['pg']) && isset($config[$_GET['pg']])) {
	$bot = new Messsenger($config[$_GET['pg']]['validation'], $config[$_GET['pg']]['token']);
	switch ($_GET['action']) {
		case 'set_welcome':
			$updated = $bot->setWelcomeMessage($config[$_GET['pg']]['page_id'], $config[$_GET['pg']]['welcome_msg']);
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
	die;
}
http_response_code(404);
die;