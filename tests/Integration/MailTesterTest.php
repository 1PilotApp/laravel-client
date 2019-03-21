<?php

namespace OnePilot\Client\Tests\Integration;

use Illuminate\Support\Facades\Mail;
use OnePilot\Client\Tests\TestCase;

class MailTesterTest extends TestCase
{
    /** @var \Illuminate\Foundation\Testing\TestResponse */
    private static $response;

    public function setUp(): void
    {
        parent::setUp();

        if (empty(self::$response)) {
            Mail::fake();

            self::$response = $this->postJson('onepilot/mail-tester', [
                'email' => 'test-mail@example.com',
            ], $this->authenticationHeaders());
        }
    }

    /** @test */
    public function require_authentication_headers()
    {
        $response = $this->postJson('onepilot/mail-tester');

        $response->assertStatus(400);
    }

    /** @test */
    public function response_is_success()
    {
        self::$response->assertStatus(200);
    }
}
