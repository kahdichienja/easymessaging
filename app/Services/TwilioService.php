<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;

    public function __construct()
    {
        $accountSid = config('services.twilio.sid');
        $authToken = config('services.twilio.auth_token');
        $this->client = new Client($accountSid, $authToken);
    }

    public function sendSms($to, $message)
    {
        $from = config('services.twilio.phone_number');

        $this->client->messages->create($to, [
            'from' => $from,
            'body' => $message,
        ]);
    }
}
