<?php

namespace CmsPilot\Client\Tests\Integration;

use CmsPilot\Client\Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function it_will_fail_when_call_validate_without_authentication_headers()
    {
        $response = $this->getJson('cmspilot/validate');

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => "The request did not contain a header named `HTTP_HASH`.",
                'status'  => 400,
                'data'    => [],
            ]);
    }

    /** @test */
    public function it_will_fail_when_no_authentication_headers_are_set()
    {
        $response = $this->getJson('cmspilot/ping', []);

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => "The request did not contain a header named `HTTP_HASH`.",
                'status'  => 400,
                'data'    => [],
            ]);
    }

    /** @test */
    public function it_will_fail_when_using_past_stamp()
    {
        $this->setTimestamp(1500000000);

        $response = $this->getJson('cmspilot/ping', $this->authenticationHeaders());

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => "The timestamp found in the header is invalid",
                'status'  => 400,
                'data'    => [],
            ]);
    }

    /** @test */
    public function it_will_fail_when_using_empty_stamp()
    {
        $this->setTimestamp("");

        $response = $this->getJson('cmspilot/ping', $this->authenticationHeaders());

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => "The timestamp found in the header is invalid",
                'status'  => 400,
                'data'    => [],
            ]);
    }

    /** @test */
    public function it_will_work_when_using_past_stamp_with_skip_time_stamp_validation_enabled()
    {
        $this->setTimestamp(1500000000);

        config(['cmspilot.skip_time_stamp_validation' => true]);

        $response = $this->getJson('cmspilot/ping', $this->authenticationHeaders());

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => "pong",
            ]);
    }

    /** @test */
    public function it_will_work_when_using_valid_stamp_and_hash()
    {
        $response = $this->getJson('cmspilot/ping', $this->authenticationHeaders());

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => "pong",
            ]);
    }

}
