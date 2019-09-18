<?php

namespace Tests\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SeoAnalyser\Exception\InvalidAuthOptionException;
use SeoAnalyser\Http\Client;

class ClientTest extends TestCase
{
    public function testGetNoAuth()
    {
        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $mockHandler = new MockHandler([]);
        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);
        $httpClient = new GuzzleClient(['handler' => $stack]);
        $client = new Client($httpClient);
        $client->setCliVersion('v1.0');

        $response = new Response;
        $mockHandler->append($response);

        $url = 'http://example.com';
        $this->assertEquals($response, $client->get($url));

        $transaction = array_shift($historyContainer);
        $this->assertEquals($url, (string) $transaction['request']->getUri());

        $headers = $transaction['request']->getHeaders();
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertEquals('SeoAnalyser/v1.0', $headers['User-Agent'][0]);
    }

    public function testGetWithAuth()
    {
        $historyContainer = [];
        $history = Middleware::history($historyContainer);

        $mockHandler = new MockHandler([]);
        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);
        $httpClient = new GuzzleClient(['handler' => $stack]);
        $client = new Client($httpClient);
        $client->configAuth(['user:pwd@example.com']);

        $response = new Response;
        $mockHandler->append($response);

        $url = 'http://example.com';
        $this->assertEquals($response, $client->get($url));

        $transaction = array_shift($historyContainer);
        $this->assertEquals($url, (string) $transaction['request']->getUri());

        $headers = $transaction['request']->getHeaders();
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertEquals('Basic '.base64_encode('user:pwd'), $headers['Authorization'][0]);
    }

    /**
     * @expectedException SeoAnalyser\Exception\InvalidAuthOptionException
     */
    public function testConfigAuthException()
    {
        $client = new Client(new GuzzleClient);
        $client->configAuth(['not.even.close']);
    }
}
