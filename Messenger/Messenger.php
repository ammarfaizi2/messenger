<?php
namespace Messenger;

defined('data') or die('Data not defined !');

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 */

class Messenger
{
    const BASE_URL = 'https://graph.facebook.com/v2.6/';

    private $_pgtoken;
    private $_validationToken;

    public function __construct()
    {
        is_dir(data) or mkdir(data);
        is_dir(data.'/.tmp') or mkdir(data.'/.tmp');
        is_dir(data.'/.msg_cache') or mkdir(data.'/.msg_cache');
        is_dir(data.'/msg_data') or mkdir(data.'/msg_data');
    }

    /**
    * @param	string	$validationToken
    * @param	string	$pageAccessToken
    */
    public function set_token($pageAccessToken)
    {
        $this->_pgtoken = "?access_token=".$pageAccessToken;
    }

    public static function get_input()
    {
        return file_get_contents("php://input");
    }

    /**
    * @todo Setup Webhook
    * @param    string  $validationToken
    */
    public static function setupWebhook($validationToken)
    {
        header("Content-Type: application/json");
        if (isset($_REQUEST['hub_challenge']) && isset($_REQUEST['hub_verify_token']) && $validationToken == $_REQUEST['hub_verify_token']) {
            http_response_code(200);
            echo $_REQUEST['hub_challenge'];
            exit;
        }
    }

    /**
    * @param	string	$recipientId
    * @param	string	$text
    * @return   mixed
    */
    public function send_message($recipientId, $text)
    {   
        $long_text = strlen($text);
        if ($long_text>640) {
            $tmp_file = data.'/.tmp/msg_'.sha1($text).'.tmp';
            file_exists($tmp_file) or file_put_contents($tmp_file, $text);
            $act = array();
            $handle = fopen($tmp_file, 'r');
            while ($r = fread($handle, 640)) {
                $act[] = $this->send_message($recipientId, $r);
            }
            return $act;
        }
        $param = '{"recipient": {"id": "'.$recipientId.'"},"message": {"text": '.json_encode($text).'}}';
        $response = self::execute(self::BASE_URL."me/messages".$this->_pgtoken, $param, true);
        return $response ? $response : false;
    }

    /**
    * @param    string  $recipientId
    * @param    string  $imageurl
    * @return   mixed
    */
    public function send_image($recipientId, $imageurl)
    {
        $hash = sha1($imageurl);
        if ($cid = $this->check_cache(data.'/.msg_cache/img_cache.txt', $hash)) {
            $param = '{"recipient": {"id": "'.$recipientId.'"},"message": {"attachment": {"type": "image","payload": {"attachment_id": "'.$cid.'"}}}}';
            $response = self::execute(self::BASE_URL."me/messages".$this->_pgtoken, $param, true);
        } else {
            $param = '{"recipient": {"id": "'.$recipientId.'"},"message": {"attachment": {"type": "image","payload": {"url": '.json_encode($imageurl).',"is_reusable": true}}}}';
            $response = self::execute(self::BASE_URL."me/messages".$this->_pgtoken, $param, true);
            $data = json_decode($response, 1);
            $this->create_cache(data.'/.msg_cache/img_cache.txt', $hash, $data['attachment_id']);
        }
        return $response ? $response : false;
    }

    public function get_sender_name($id)
    {
        $a = json_decode(self::execute('https://graph.facebook.com/'.$id.$this->_pgtoken.'&fields=first_name,last_name'), 1);
        return $a['first_name'].(isset($a['last_name']) ? ' '.$a['last_name'] : '');
    }

    /**
    * @param    string  $file
    * @param    string  $hash
    * @return   mixed
    */
    private function check_cache($file, $hash)
    {
        if (file_exists($file) ) {
            return false;
        }
        $src = json_decode(file_get_contents($file), 1);
        $src = is_array($src) ? $src : array();
        return isset($src[$hash]) ? $src[$hash] : false;
    }

    /**
    * @param    string  $file
    * @param    string  $hash
    * @param    string  $id
    * @return   mixed
    */
    private function create_cache($file, $hash, $id)
    {
        $src = file_exists($file) ? json_decode(file_get_contents($file), 1) : array();
        $src[$hash] = $id;
        return file_put_contents($file, json_encode($src, 128));
    }


    /**
    * @param	string	$pageId
    * @param	string	$text
    * @return	mixed
    */
    public function set_welcome_msg($pageId, $text)
    {
        $hash = md5($text);
        if (!file_exists(data.'/msg_data/welcome_msg_'.$pageId."_".$hash.".txt")) {
            $url = self::BASE_URL . "%s/thread_settings".$this->_pgtoken;
            $response = self::execute($url, '{"setting_type":"greeting","greeting":{"text":'.json_encode($text).'}}', true);
            if ($response) {
                file_put_contents(data.'/msg_data/welcome_msg_'.$pageId."_".$hash.".txt", $text);
                return $response;
                /*$responseObject = json_decode($response);
                return is_object($responseObject) && isset($responseObject->result) && strpos($responseObject->result, 'Success') !== false;*/
            }
            return false;
        } else {
            return false;
        }
    }

    /**
    * @todo App run
    */
    public function run()
    {
        $request = self::getJsonRequest();
        if (!$request) {
            return;
        }
        $entries = isset($request->entry) ? $request->entry : null;
        if (!$entries) {
            return;
        }
        $messages = [];
        foreach ($entries as $entry) {
            $messagingList = isset($entry->messaging) ? $entry->messaging : null;
            if (!$messagingList) {
                continue;
            }
            foreach ($messagingList as $messaging) {
                $message = new \stdClass();
                $message->entryId = isset($entry->id) ? $entry->id : null;
                $message->senderId = isset($messaging->sender->id) ? $messaging->sender->id : null;
                $message->recipientId = isset($messaging->recipient->id) ? $messaging->recipient->id : null;
                $message->timestamp = isset($messaging->timestamp) ? $messaging->timestamp : null;
                $message->messageId = isset($messaging->message->mid) ? $messaging->message->mid : null;
                $message->sequenceNumber = isset($messaging->message->seq) ? $messaging->message->seq : null;
                $message->text = isset($messaging->message->text) ? $messaging->message->text : null;
                $message->attachments = isset($messaging->message->attachments) ? $messaging->message->attachments : null;
                $messages[] = $message;
            }
        }
        $this->_receivedMessages = $messages;
    }

    /**
    * @return mixed
    */
    public function subscribeAppToThePage()
    {
        $url = self::BASE_URL . "me/subscribed_apps";
        $parameters = ['access_token' => $this->getPageAccessToken()];
        $response = self::execute($url, $parameters);
        if ($response) {
            $responseObject = json_decode($response);
            return is_object($responseObject) && isset($responseObject->success) && $responseObject->success == "true";
        }
        return false;
    }

    /**
    * @return object
    */
    private static function getJsonRequest()
    {
        $content = file_get_contents("php://input");
        return json_decode($content, false, 512, JSON_BIGINT_AS_STRING);
    }

    /**
    * @param	string	$url
    * @param	string	$parameters
    * @param	bool	$json
    * @return	string
    */
    private static function execute($url, $parameters = null, $json = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($parameters) {
            if ($json) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($parameters)));
            } else {
                curl_setopt($ch, CURLOPT_POST, count($parameters));
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
