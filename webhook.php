<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
use System\Messenger;

header('Content-type:application/json');
$_GET['pg'] = 'esteh';
$_GET['action'] = 'subscribe';
if (isset($_GET['pg']) && isset($config[$_GET['pg']])) {
    $bot = new Messenger($config[$_GET['pg']]['validation'], $config[$_GET['pg']]['token']);
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'set_welcome':
                $updated = $bot->setWelcomeMessage($config[$_GET['pg']]['page_id'], $config[$_GET['pg']]['welcome_msg']);
                if ($updated) {
                    echo "Welcome Message updated succesfully!";
                } else {
                    echo "Error during Welcome Message update";
                }
                break;
            case 'subscribe':
                $isSubscribed = $bot->subscribeAppToThePage();
                if ($isSubscribed) {
                    echo "App subscribed to the Page succesfully!";
                } else {
                    echo "Error during App subscription to the Page";
                }
                break;
            default:
                
                break;
        }
    } else {
        $bot->run();
        $messages = $bot->getReceivedMessages();
        foreach ($messages as $message) {
            $recipientId = $message->senderId;
            if ($message->text) {
                $bot->sendTextMessage($recipientId, $message->text);
            } elseif ($message->attachments) {
                $bot->sendTextMessage($recipientId, "Attachment received");
            }
        }
    }
    die;
}
http_response_code(404);
print 'a';
die;
