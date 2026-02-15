<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('pages/index.html.twig', [
            'title' => 'Юрист Скрипникова Наталья - Профессиональные юридические услуги'
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig', [
            'title' => 'О юристе - Скрипникова Наталья'
        ]);
    }

    #[Route('/services', name: 'services')]
    public function services(): Response
    {
        return $this->render('pages/services.html.twig', [
            'title' => 'Юридические услуги - Скрипникова Наталья'
        ]);
    }

    #[Route('/prices', name: 'prices')]
    public function prices(): Response
    {
        return $this->render('pages/prices.html.twig', [
            'title' => 'Прайс-лист - Скрипникова Наталья'
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig', [
            'title' => 'Контакты - Скрипникова Наталья'
        ]);
    }
}