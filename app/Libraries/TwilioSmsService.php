<?php

//created_by: Ash

namespace App\Libraries;

use Twilio\Rest\Client;

class TwilioSmsService
{
	protected $client = null;
	protected $from   = null;

	public function __construct($twilio_config)
	{
		$this->client = new Client($twilio_config['app_id'], $twilio_config['token']);
		$this->from   = $twilio_config['from'];
	}

	public function getClient()
	{
		return $this->client;
	}

	public function send($to, $body)
	{
		$result['status'] = 0;
		$result['msg']    = '';

		try
		{
			$this->client->messages->create(
				$to,
				[
					'from' => $this->from,
					'body' => $body 
				]
			);

			$result['status'] = 1;
			$result['msg']    = trans('messages.sms');
		}
		catch(\Exception $e)
		{
			$result['msg'] = $e->getMessage();
		}

		return $result;
	}
}