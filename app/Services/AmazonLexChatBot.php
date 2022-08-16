<?php

namespace App\Services;

use Aws\Credentials\Credentials;
use Aws\LexRuntimeService\LexRuntimeServiceClient;

class AmazonLexChatBot
{
    public LexRuntimeServiceClient $client;

    public function __construct()
    {
        $this->client = new LexRuntimeServiceClient([
            'version'     => '2016-11-28',
            'credentials' => $this->getCredentials(),
            'region'      => config('app.aws.region'),
        ]);
    }

    public function sendMessage(): object
    {
        $result = $this->client->postText([
            'botAlias' => 'testchatbot',
            'botName' => 'TestChatbot',
            'inputText' => request()->message,
            'userId' => 'TestUser',
        ]);

        return (object) $result->toArray();
    }

    private function getCredentials(): Credentials
    {
        return new Credentials(
            config('app.aws.access'),
            config('app.aws.secret'),
        );
    }
}
