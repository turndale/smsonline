<?php

namespace Turndale\SmsOnline;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Turndale\SmsOnline\SmsResponse;

class SmsOnline
{
    protected string $sender;
    protected string $message;
    protected array $destinations = [];
    protected ?array $schedule = null;
    
    protected string $baseUrl = 'https://api.smsonlinegh.com/v5';

    public function __construct(string $sender = null)
    {
        $this->sender = $sender ?: Config::get('smsonline.default_sender', 'SMSONLINE');
    }

    /**
     * Set the sender ID.
     *
     * @param string $sender
     * @return $this
     */
    public function sender(string $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Set the message content.
     *
     * @param string $message
     * @return $this
     */
    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the destinations.
     *
     * @param array $destinations Array of phone numbers for non-personalised messaging.
     * @return $this
     */
    public function destinations(array $destinations): self
    {
        $this->destinations = $destinations;
        return $this;
    }

    /**
     * Schedule the message.
     *
     * @param string $dateTime Format: YYYY-MM-DD HH:MM
     * @param string|null $offset Timezone offset (e.g., +00:00)
     * @return $this
     */
    public function schedule(string $dateTime, string $offset = null): self
    {
        $this->schedule = [
            'dateTime' => $dateTime,
        ];

        if ($offset) {
            $this->schedule['offset'] = $offset;
        }

        return $this;
    }

    /**
     * Send the SMS.
     *
     * @return \Turndale\SmsOnline\SmsResponse
     * @throws \RuntimeException
     */
    public function send(): SmsResponse
    {
        if (empty($this->destinations)) {
            throw new RuntimeException('No destinations provided for SMS.');
        }

        if (empty($this->message)) {
            throw new RuntimeException('No message content provided for SMS.');
        }

        $apiKey = Config::get('smsonline.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('SMSOnline API Key is not configured.');
        }

        $payload = [
            'text' => $this->message,
            'type' => 0, // GSM default
            'sender' => $this->sender,
            'destinations' => $this->destinations,
        ];

        if ($this->schedule) {
            $payload['schedule'] = $this->schedule;
        }

        // Validate payload for personalised if needed? 
        // For now, assuming standard array of strings for destinations 
        // or whatever valid structure the user passes that json_encode handles.

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'key ' . $apiKey,
        ])->post($this->baseUrl . '/message/sms/send', $payload);

        return new SmsResponse($response);
    }

    /**
     * Get account balance.
     *
     * @return \Turndale\SmsOnline\SmsResponse
     */
    public function balance(): SmsResponse
    {
         $apiKey = Config::get('smsonline.api_key');
         
         if (empty($apiKey)) {
            throw new RuntimeException('SMSOnline API Key is not configured.');
        }

         $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'key ' . $apiKey,
        ])->post($this->baseUrl . '/account/balance');

        return new SmsResponse($response);
    }
    
    /**
     * Cancel a scheduled message batch.
     * 
     * @param string $batchId
     * @return \Turndale\SmsOnline\SmsResponse
     */
    public function cancelScheduled(string $batchId): SmsResponse
    {
         $apiKey = Config::get('smsonline.api_key');

         if (empty($apiKey)) {
            throw new RuntimeException('SMSOnline API Key is not configured.');
        }

         $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'key ' . $apiKey,
        ])->post($this->baseUrl . '/message/scheduled/cancel/' . $batchId);

        return new SmsResponse($response);
    }
}
