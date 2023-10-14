<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;

    public function __construct()
    {

        
        // env('TWILIO_AUTH_TOKEN', '8371e18d3f1c9eee23ed1d8c9a1500a1');
        // env('TWILIO_PHONE_NUMBER', '+12543205891');
        
        $accountSid = env('TWILIO_SID', 'AC81702a0bd87f8c5192713f9dda783b1a');
        $authToken =  env('TWILIO_AUTH_TOKEN', '8371e18d3f1c9eee23ed1d8c9a1500a1');
        $this->client = new Client($accountSid, $authToken);
    }

    public function sendSms($to, $message)
    {
        $from = env('TWILIO_PHONE_NUMBER', '+12543205891');

        $this->client->messages->create($to, [
            'from' => $from,
            'body' => $message,
        ]);
    }
}
