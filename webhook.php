<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
use System\Messsenger;

$bot = new Messsenger(FACEBOOK_VALIDATION_TOKEN, FACEBOOK_PAGE_ACCESS_TOKEN);

$updated = $bot->setWelcomeMessage(FACEBOOK_PAGE_ID, "Greetings! The humans who invented me programmed me to tell you about...");

if($updated){
	echo "Welcome Message updated succesfully!";
} else {
	echo "Error during Welcome Message update";
}