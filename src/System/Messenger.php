<?php
namespace System;

/**
* @author Ammar Faizi <ammarfaizi2@gmail.com>
*/
class Messenger
{
	const BASE_URL = 'https://graph.facebook.com/v2.6/';
	private $input;

	public function __construct()
	{
		$this->input = file_get_contents("php://input");
	}


}