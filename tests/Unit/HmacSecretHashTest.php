<?php

namespace Tests\Unit;

use Tests\TestCase;

class HmacSecretHashTest extends TestCase
{
    public function testHmacSecretHash()
    {
        $secret = "It's a Secret to Everybody";
        $payload = "Hello, World!";

        $expected_signature = "757107ea0eb2509fc211221cce984b8a37570b6d7586c22c46f4379c8b043e17";
        $signature = hash_hmac('sha256', $payload, $secret);

        $this->assertEquals($expected_signature, $signature);
    }
}
