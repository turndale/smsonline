<?php

namespace Turndale\SmsOnline\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Turndale\SmsOnline\SmsOnline;

use Turndale\SmsOnline\Tests\SmsOnlineTestCase;

class SmsOnlineTest extends SmsOnlineTestCase
{
    public function test_it_can_instantiate_via_helper()
    {
        $sms = sms();
        $this->assertInstanceOf(SmsOnline::class, $sms);
    }

    public function test_it_uses_configured_default_sender()
    {
        // Default is set to 'TEST' in phpunit.xml
        // We can't access protected property directly, but we can verify it in the sent request
        
        Http::fake();

        sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        Http::assertSent(function ($request) {
            return $request['sender'] === 'TEST';
        });
    }

    public function test_it_can_set_custom_sender()
    {
        Http::fake();

        sms('CustomSender')
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        Http::assertSent(function ($request) {
            return $request['sender'] === 'CustomSender';
        });

        // Test via method chaining
        sms()
            ->sender('AnotherSender')
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        Http::assertSent(function ($request) {
            return $request['sender'] === 'AnotherSender';
        });
    }

    public function test_it_sends_sms_params_correctly()
    {
        Http::fake();

        sms()
            ->sender('MyBrand')
            ->message('Hello World')
            ->destinations(['0551234567', '0241234567'])
            ->schedule('2026-01-22 10:00', '+00:00')
            ->send();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.smsonlinegh.com/v5/message/sms/send' &&
                   $request['sender'] === 'MyBrand' &&
                   $request['text'] === 'Hello World' &&
                   $request['destinations'] === ['0551234567', '0241234567'] &&
                   $request['schedule']['dateTime'] === '2026-01-22 10:00' &&
                   $request['schedule']['offset'] === '+00:00';
        });
    }

    public function test_it_throws_exception_if_destinations_missing()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No destinations provided for SMS.');

        sms()->message('Hello')->send();
    }

    public function test_it_throws_exception_if_message_missing()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No message content provided for SMS.');

        sms()->destinations(['123'])->send();
    }
    
    public function test_it_checks_balance()
    {
        Http::fake([
            'api.smsonlinegh.com/v5/account/balance' => Http::response(['data' => ['balance' => 100]], 200),
        ]);

        sms()->balance();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.smsonlinegh.com/v5/account/balance' &&
                   $request->method() === 'POST';
        });
    }

    public function test_it_cancels_scheduled_message()
    {
        Http::fake();
        
        $batchId = 'batch_123';
        sms()->cancelScheduled($batchId);
        
        Http::assertSent(function ($request) use ($batchId) {
            return $request->url() === 'https://api.smsonlinegh.com/v5/message/scheduled/cancel/' . $batchId &&
                   $request->method() === 'POST';
        });
    }
}
