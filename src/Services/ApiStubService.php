<?php

namespace App\Services;

use App\Entity\Comment;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class ApiStubService
{
    private $client;
    private $endpoint;
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->endpoint = 'https://localhost:8000/apistub';
    }

    public function sendComment(Comment $comment): bool
    {
        $response = $this->client->request('GET', $this->endpoint, [
            'query' =>  [
                'id' => $comment->getId(),
            ],
        ]);

        return $response->getStatusCode() === 200;
    }
}