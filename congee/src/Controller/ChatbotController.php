<?php

// src/Controller/ChatbotController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    private $client;
    private $openaiApiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->openaiApiKey = 'sk-0uU-szt_zkNHIlY7TbQzvOL_YA2SzQT_2YrTzHaCGAT3BlbkFJlDZMZ_HrgoZYPTa7JjJ_5VLiPNHAa3ZKatJK2YysIA'; // replace this
    }

    #[Route('/chat', name: 'chatbot_index')]
    public function index()
    {
        return $this->render('chatbot/index.html.twig');
    }

    #[Route('/chat/send', name: 'chatbot_send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';

        try {
            $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $userMessage]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 150
                ],
            ]);

            $content = $response->toArray();
            $botMessage = $content['choices'][0]['message']['content'];

            return new JsonResponse(['response' => $botMessage]);

        } catch (\Exception $e) {
            return new JsonResponse(['response' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    
}
