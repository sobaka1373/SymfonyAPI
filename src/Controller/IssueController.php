<?php

namespace App\Controller;

use App\Entity\Issue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IssueController extends AbstractController
{
    private HttpClientInterface $client;
    private const GIT_HUB_HEADERS = ['headers' => [ 'Accept' => 'application/vnd.github+json' ]];
    public static string $apiUrl = 'https://api.github.com/search/issues?q=';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    #[Route('/issue/{word}', name: 'app_issue')]
    public function index(string $word, EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(Issue::class);
        $issue = $repository->findOneBy(['word' => $word]);

        if (!isset($issue)) {
            $totalArray = $this->popularityArray($word);
            $score = self::getScore($totalArray['rocks'], $totalArray['sucks']);
            $issue = new Issue();
            $issue->setWord($word);
            $issue->setRating($score);
            $em->persist($issue);
            $em->flush();
            return new JsonResponse([
                'term' => $word,
                'score' => $score
            ]);
        }
        return new JsonResponse([
            'term' => $issue->getWord(),
            'score' => $issue->getRating()
        ]);

    }

    public static function getScore(int $rock, int $sucks): float
    {
        return round((($rock + $sucks) / $rock ), 2);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function popularityArray(string $word): array
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
    private function requestMaker(string $word, string $popularity = ''): int
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
