<?php
namespace System;

/**
* @author Ammar Faizi <ammarfaizi2@gmail.com>
*/

class Messenger
{
    const BASE_URL = 'https://graph.facebook.com/v2.6/';

    private $_validationToken;
    private $_pageAccessToken;
    private $_receivedMessages;

    /**
    * @param	string	$validationToken
    * @param	string	$pageAccessToken
    */
    public function __construct($validationToken, $pageAccessToken)
    {
        $this->_validationToken = $validationToken;
        $this->_pageAccessToken = $pageAccessToken;
        $this->setupWebhook();
    }

    /**
    * @return array
    */
    public function getReceivedMessages()
    {
        return $this->_receivedMessages;
    }

    /**
    * @return array
    */
    public function getPageAccessToken()
    {
        return $this->_pageAccessToken;
    }

    /**
    * @return array
    */
    public function getValidationToken()
    {
        return $this->_validationToken;
    }

    /**
    * @todo Setup Webhook
    */
    private function setupWebhook()
    {
        if (isset($_REQUEST['hub_challenge']) && isset($_REQUEST['hub_verify_token']) && $this->getValidationToken() == $_REQUEST['hub_verify_token']) {
            http_response_code(200);
            echo $_REQUEST['hub_challenge'];
            exit;
        }
    }

    /**
    * @param	string	$recipientId
    * @param	string	$text
    * @return	mixed
    */
    public function sendTextMessage($recipientId, $text)
    {
        $url = self::BASE_URL . "me/messages?access_token=%s";
        $url = sprintf($url, $this->getPageAccessToken());
        $recipient = new \stdClass();
        $recipient->id = $recipientId;
        $message = new \stdClass();
        $message->text = $text;
        $parameters = ['recipient' => $recipient, 'message' => $message];
        $response = self::executePost($url, $parameters, true);
        if ($response) {
            return $response;
            /*$responseObject = json_decode($response);
            return is_object($responseObject) && isset($responseObject->recipient_id) && isset($responseObject->message_id);*/
        }
        return false;
    }

    /**
    * @param	string	$pageId
    * @param	string	$text
    * @return	mixed
    */
    public function setWelcomeMessage($pageId, $text)
    {
        $url = self::BASE_URL . "%s/thread_settings?access_token=%s";
        $url = sprintf($url, $pageId, $this->getPageAccessToken());
        $request = new \stdClass();
        $request->setting_type = "greeting";
        $greeting = new \stdClass();
        $greeting->text = $text;
        $request->greeting = $greeting;
        $response = self::executePost($url, $request, true);
        if ($response) {
            return $response;
            /*$responseObject = json_decode($response);
            return is_object($responseObject) && isset($responseObject->result) && strpos($responseObject->result, 'Success') !== false;*/
        }
        return false;
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
        $response = self::executePost($url, $parameters);
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
    private static function executePost($url, $parameters, $json = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($json) {
            $data = json_encode($parameters);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        } else {
            curl_setopt($ch, CURLOPT_POST, count($parameters));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
