<?php

namespace Turndale\SmsOnline\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Turndale\SmsOnline\SmsOnline;
use Turndale\SmsOnline\SmsResponse;

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

    public function test_send_returns_sms_response_instance()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'data' => ['batch_id' => 'abc123']], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $this->assertInstanceOf(SmsResponse::class, $response);
    }

    public function test_response_to_array_method()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'data' => ['batch_id' => 'abc123', 'credits' => 1]], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $array = $response->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('success', $array['status']);
        $this->assertEquals('abc123', $array['data']['batch_id']);
    }

    public function test_response_json_method()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'data' => ['batch_id' => 'abc123']], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        // Test full json
        $this->assertEquals(['status' => 'success', 'data' => ['batch_id' => 'abc123']], $response->json());

        // Test with key
        $this->assertEquals('success', $response->json('status'));
        $this->assertEquals('abc123', $response->json('data.batch_id'));

        // Test with default
        $this->assertEquals('default', $response->json('nonexistent', 'default'));
    }

    public function test_response_get_data_method()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'data' => ['batch_id' => 'abc123', 'credits' => 5]], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $this->assertEquals(['batch_id' => 'abc123', 'credits' => 5], $response->getData());
        $this->assertEquals('abc123', $response->getData('batch_id'));
        $this->assertEquals(5, $response->getData('credits'));
    }

    public function test_response_get_batch_id_method()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'data' => ['batch_id' => 'xyz789']], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $this->assertEquals('xyz789', $response->getBatchId());
    }

    public function test_response_status_helpers()
    {
        // Test successful response
        Http::fake([
            '*' => Http::response(['status' => 'success'], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $this->assertTrue($response->successful());
        $this->assertTrue($response->ok());
        $this->assertFalse($response->failed());
        $this->assertEquals(200, $response->status());
    }

    public function test_response_failed_status()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Invalid API key'], 401),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $this->assertTrue($response->failed());
        $this->assertTrue($response->clientError());
        $this->assertFalse($response->successful());
        $this->assertEquals(401, $response->status());
    }

    public function test_response_get_error_method()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Something went wrong'], 400),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $this->assertEquals('Something went wrong', $response->getError());
    }

    public function test_response_array_access()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'data' => ['batch_id' => 'test123']], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $this->assertEquals('success', $response['status']);
        $this->assertEquals(['batch_id' => 'test123'], $response['data']);
        $this->assertTrue(isset($response['status']));
        $this->assertFalse(isset($response['nonexistent']));
    }

    public function test_response_json_serializable()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'data' => ['batch_id' => 'test123']], 200),
        ]);

        $response = sms()
            ->message('Hello')
            ->destinations(['1234567890'])
            ->send();

        $json = json_encode($response);
        $decoded = json_decode($json, true);

        $this->assertEquals('success', $decoded['status']);
        $this->assertEquals('test123', $decoded['data']['batch_id']);
    }

    public function test_balance_returns_sms_response()
    {
        Http::fake([
            '*' => Http::response(['data' => ['balance' => 100.50]], 200),
        ]);

        $response = sms()->balance();

        $this->assertInstanceOf(SmsResponse::class, $response);
        $this->assertEquals(100.50, $response->getData('balance'));
    }

    public function test_cancel_scheduled_returns_sms_response()
    {
        Http::fake([
            '*' => Http::response(['status' => 'cancelled'], 200),
        ]);

        $response = sms()->cancelScheduled('batch_123');

        $this->assertInstanceOf(SmsResponse::class, $response);
        $this->assertEquals('cancelled', $response->json('status'));
    }
}
