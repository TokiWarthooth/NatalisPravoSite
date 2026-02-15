<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/status', name: 'status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        return $this->json([
            'status' => 'ok',
            'timestamp' => time(),
            'version' => '1.0.0'
        ]);
    }

    #[Route('/pages', name: 'pages', methods: ['GET'])]
    public function getPages(): JsonResponse
    {
        $pages = [
            ['id' => 1, 'title' => 'Главная', 'url' => '/', 'description' => 'Главная страница сайта'],
            ['id' => 2, 'title' => 'О нас', 'url' => '/about', 'description' => 'Информация о компании'],
            ['id' => 3, 'title' => 'Услуги', 'url' => '/services', 'description' => 'Наши услуги'],
            ['id' => 4, 'title' => 'Контакты', 'url' => '/contact', 'description' => 'Контактная информация'],
            ['id' => 5, 'title' => 'Портфолио', 'url' => '/portfolio', 'description' => 'Наши работы']
        ];

        return $this->json($pages);
    }

    #[Route('/contact', name: 'contact_form', methods: ['POST'])]
    public function contactForm(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Здесь можно добавить валидацию и сохранение в БД
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $message = $data['message'] ?? '';

        if (empty($name) || empty($email) || empty($message)) {
            return $this->json(['error' => 'Все поля обязательны'], 400);
        }

        // Имитация сохранения
        return $this->json([
            'success' => true,
            'message' => 'Сообщение отправлено успешно',
            'data' => [
                'name' => $name,
                'email' => $email,
                'received_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}