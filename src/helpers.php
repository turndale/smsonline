<?php

use Turndale\SmsOnline\SmsOnline;

if (!function_exists('sms')) {
    /**
     * Create a new SMS instance.
     *
     * @param string|null $sender
     * @return \Turndale\SmsOnline\SmsOnline
     */
    function sms($sender = null)
    {
        // Resolve a fresh instance from the container
        $instance = app('smsonline');
        
        if ($sender) {
            $instance->sender($sender);
        }
        
        return $instance;
    }
}
