<?php

namespace App\Controller;

use App\Entity\Issue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Core\GitHubFetcher;

class IssueController extends GitHubFetcher
{

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
            $score = $this->getScore($totalArray['rocks'], $totalArray['sucks']);
            $issue = new Issue();
            $issue->setWord($word);
            $issue->setRating($score);
            $repository->add($issue, true);

            return new JsonResponse([
                'term' => $word,
                'score' => $score
            ],
                200,
                [
                    'Content-Type: application/vnd.api+json'
                ],false
            );
        }
        return new JsonResponse([
            'term' => $issue->getWord(),
            'score' => $issue->getRating()
        ],
            200,
            [
                'Content-Type: application/vnd.api+json'
            ],
            false
        );
    }

}
