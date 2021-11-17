<?php

declare(strict_types=1);

namespace App\Tests\Mocks;

use Laminas\Feed\Reader\Http\ResponseInterface;

class MockResponse implements ResponseInterface
{
    public function __construct(protected int $statusCode, protected string $body = '')
    {
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

}