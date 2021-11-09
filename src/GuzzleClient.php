<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Laminas\Feed\Reader\Http\ClientInterface as FeedReaderHttpClientInterface;
use Laminas\Feed\Reader\Http\Psr7ResponseDecorator;

class GuzzleClient implements FeedReaderHttpClientInterface
{
    private GuzzleClientInterface $client;

    public function __construct(?GuzzleClientInterface $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri): Psr7ResponseDecorator
    {
        return new Psr7ResponseDecorator(
            $this->client->request('GET', $uri)
        );
    }
}