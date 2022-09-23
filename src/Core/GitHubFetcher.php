<?php

namespace App\Core;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubFetcher extends DataFetcher
{
    private HttpClientInterface $client;
    private const GIT_HUB_HEADERS = ['headers' => [ 'Accept' => 'application/vnd.github+json' ]];
    public static string $apiUrl = 'https://api.github.com/search/issues?q=';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    protected function getScore(int $positive, int $negative): float
    {
        return round((($positive + $negative) / $positive ), 2);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function popularityArray(string $word): array
    {
        $response_array['rocks'] = $this->requestMaker($word, 'rocks');
        $response_array['sucks'] = $this->requestMaker($word, 'sucks');
        return $response_array;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    protected function requestMaker(string $word, string $popularity = ''): int
    {
        $response = $this->client->request(
            'GET',
            self::$apiUrl . $word . ' ' . $popularity,
            self::GIT_HUB_HEADERS
        );
        $response = $response->getContent();
        $response = json_decode($response, false, 512, JSON_THROW_ON_ERROR);
        return $response->total_count;
    }
}