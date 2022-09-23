<?php

namespace App\Core;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class DataFetcher extends AbstractController
{
    abstract protected function popularityArray(string $word): array;

    abstract protected function requestMaker(string $word, string $popularity = ''): int;

    abstract protected function getScore(int $positive, int $negative): float;
}