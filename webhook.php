<?php
require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';

use Messenger\Messenger;

define("data", __DIR__ . '/data');

Messenger::setupWebhook(VALIDATION_TOKEN);
$messenger = new Messenger();
#$input = json_decode(Messenger::get_input(), 1);
/*$input = '{
    "object": "page",
    "entry": [
        {
            "id": "800281320073271",
            "time": 1496390269138,
            "messaging": [
                {
                    "sender": {
                        "id": "1477897602284998"
                    },
                    "recipient": {
                        "id": "800281320073271"
                    },
                    "timestamp": 1496390268994,
                    "message": {
                        "mid": "mid.$cAAKNKTBrcjZimRTAQlcZ89obwsTa",
                        "seq": 3130169,
                        "text": "Aaa"
                    }
                }
            ]
        }
    ]
}';*/
$input = json_decode($input, 1);

if (isset($input['entry'])) {
    foreach ($input['entry'] as $key => $value) {
        if (isset($config[$value['id']]) and isset($value['messaging'])) {
            $messenger->set_token($config[$value['id']]['token']);
            print $messenger->set_welcome_msg($key, $config[$value['id']]['welcome_msg']);
            foreach ($value['messaging'] as $value2) {
                $to         = $value2['sender']['id'];
                $message    = $value2['message']['text'];
                $reply_msg  = $message;
                var_dump($to);
               print $messenger->send_message($to, $reply_msg);
            }
        }
    }
}