<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
use System\Messenger;
header('Content-type:application/json');

$input = json_decode('{
    "object": "page",
    "entry": [
        {
            "id": "926837650793495",
            "time": 1495875971845,
            "messaging": [
                {
                    "sender": {
                        "id": "926837650793495"
                    },
                    "recipient": {
                        "id": "1255485451166918"
                    },
                    "timestamp": 1495875967761,
                    "message": {
                        "is_echo": true,
                        "app_id": 1061043070663726,
                        "mid": "mid.$cAAMQBwdWfhZiem0jEVcSSfar1_Vj",
                        "seq": 3118316,
                        "text": "Hhhhh"
                    }
                }
            ]
        }
    ]
}',1);
if ($input) {
    foreach ($input['entry'] as $val) {

        if (isset($config[$val['id']])) {
            /**
            *   Init Class
            */
            $bot = new Messenger($config[$val['id']]['validation'], $config[$val['id']]['token']);

            /**
            *   Lha kene kie nek enek method get
            */
            if (isset($val['messaging'])) {
                foreach ($val['messaging'] as $message) {
                    $recipientId = $message['recipient']['id'];
                    if ($message['message']['text']) {
                       print $bot->sendTextMessage($recipientId, $message['message']['text']);
                    } elseif ($message->attachments) {
                        $bot->sendTextMessage($recipientId, "Attachment received");
                    }
                }
            }
        }
    }
    die;
}


http_response_code(404);
print 'a';
die;

/*
if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'set_welcome':
                        $updated = $bot->setWelcomeMessage($config[$val['id']]['page_id'], $config[$val['id']]['welcome_msg']);
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
            } else {*/
