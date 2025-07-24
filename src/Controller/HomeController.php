<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\QuizzRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CourseRepository $courseRepository, QuizzRepository $quizzRepository): Response
    {
        // Récupère le dernier cours mis en ligne
        $lastCourse = $courseRepository->findOneBy([], ['createdAt' => 'DESC']);

        // Récupère le dernier quiz mis en ligne
        $lastQuizz = $quizzRepository->findOneBy([], ['id' => 'DESC']); // Assure-toi d'ajuster selon ta logique métier

        return $this->render('home/index.html.twig', [
            'lastCourse' => $lastCourse,
            'lastQuizz' => $lastQuizz
        ]);
    }
}