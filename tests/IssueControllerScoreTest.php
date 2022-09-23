<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class IssueControllerScoreTest extends TestCase
{

    public function testScoreIndex(): void
    {
        $ch = curl_init('http://127.0.0.1:8000/issue/php');
        $headers = array(
            'AUTH-TOKEN: 1233',
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        $output =  json_decode($output, false, 512, JSON_THROW_ON_ERROR);


        $this->assertSame($output->score, 2.93);
    }
}
