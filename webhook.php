<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
use System\Messenger;
use System\AI;
define('data', __DIR__ . '/data');
is_dir(data) or mkdir(data);
header('Content-type:application/json');

$input = json_decode('{
    "object": "page",
    "entry": [
        {
            "id": "926837650793495",
            "time": 1495876680358,
            "messaging": [
                {
                    "sender": {
                        "id": "1255485451166918"
                    },
                    "recipient": {
                        "id": "926837650793495"
                    },
                    "timestamp": 1495876680244,
                    "message": {
                        "mid": "mid.$cAAMQBwdWfhZiengCNFcSTK1P8txA",
                        "seq": 3118457,
                        "text": "i_anime 31765"
                    }
                }
            ]
        }
    ]
}',1);
#file_put_contents('test.txt', json_encode(json_decode(file_get_contents('php://input')),128));
#$input = json_decode(file_get_contents("php://input"),1);
if ($input) {
    $ai = new AI();
    foreach ($input['entry'] as $val) {
        if (isset($config[$val['id']])) {
            /**
            *   Init Class
            */
            $bot = new Messenger($config[$val['id']]['validation'], $config[$val['id']]['token']);

            /**
            *   Lha kene kie foreach message e
            */
            if (isset($val['messaging'])) {
                foreach ($val['messaging'] as $message) {
                    $recipientId = isset($message['sender']['id']) ? $message['sender']['id'] : false;
                    if ($message['message']['text']) {
                        $st = $ai->prepare($message['message']['text'], $recipientId);
                        if($st->execute()){
                            $r = $st->fetch_reply();
                            $r = is_array($r) ? json_encode($r) : $r;
                            var_dump($bot->sendTextMessage($recipientId, $r));
                        }
                    } elseif ($message) {
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
