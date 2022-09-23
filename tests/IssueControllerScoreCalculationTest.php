<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Core\GitHubFetcher;
use Symfony\Component\HttpClient\MockHttpClient;


class IssueControllerScoreCalculationTest extends TestCase
{

    public function testCalculationScore(): void
    {
        $class = new \ReflectionClass('App\Core\GitHubFetcher');
        $myProtectedMethod = $class->getMethod('getScore');
        $myInstance = new GitHubFetcher(new MockHttpClient);
        $result = $myProtectedMethod->invokeArgs($myInstance, [80, 20]);
        $this->assertSame($result, 1.25);
    }
}
