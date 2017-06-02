<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
use Messenger\Messenger;

Messenger::setupWebhook(VALIDATION_TOKEN);
$messenger = new Messenger();
$input = json_decode(Messenger::get_input(), 1);

foreach ($config as $key => $value) {
    
}