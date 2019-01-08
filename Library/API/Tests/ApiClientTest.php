<?php

use Fatchip\Afterbuy\ApiClient;
use PHPUnit\Framework\TestCase;

final class ApiClientTest extends TestCase
{
    public function testCanBeCreated()
    {
        $this->assertInstanceOf(ApiClient::class, new ApiClient([]));
    }
}
