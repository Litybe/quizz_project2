<?php

namespace App\Service;

use App\Entity\Quizz;
use App\Entity\Tag;
use App\Repository\QuizzRepository;
use App\Repository\ScoreRepository;
use App\Repository\UserQuizzStatusRepository;
use App\Service\QuizDisplayService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class QuizzService
{
    private $entityManager;
    private $quizzRepository;
    private $userQuizzStatusRepository;
    private $scoreRepository;
    private $paginator;
    private $quizDisplayService;

    public function __construct(
        EntityManagerInterface $entityManager,
        QuizzRepository $quizzRepository,
        UserQuizzStatusRepository $userQuizzStatusRepository,
        ScoreRepository $scoreRepository,
        PaginatorInterface $paginator,
        QuizDisplayService $quizDisplayService
    ) {
        $this->entityManager = $entityManager;
        $this->quizzRepository = $quizzRepository;
        $this->userQuizzStatusRepository = $userQuizzStatusRepository;
        $this->scoreRepository = $scoreRepository;
        $this->paginator = $paginator;
        $this->quizDisplayService = $quizDisplayService;
    }

    public function getPaginatedQuizzes(Request $request, $user)
    {
        // Utiliser QueryBuilder pour construire la requête
        $queryBuilder = $this->entityManager->getRepository(Quizz::class)->createQueryBuilder('q');

        // Filtrer par tag si un tag est sélectionné
        $selectedTagId = $request->query->get('tag');
        if ($selectedTagId) {
            $queryBuilder->join('q.tags', 't')
                ->andWhere('t.id = :tagId')
                ->setParameter('tagId', $selectedTagId);
        }

        $query = $queryBuilder->getQuery();

        $page = $request->query->getInt('page', 1);
        $quizzes = $this->paginator->paginate($query, $page, 10);

        // Récupérer les données d'affichage des quiz via le service partagé
        $quizDisplayData = $this->quizDisplayService->getQuizDisplayData($user);

        // Récupérer tous les tags pour le filtre
        $tagRepository = $this->entityManager->getRepository(Tag::class);
        $tags = $tagRepository->findAllOrderedByName();

        $totalPages = ceil($quizzes->getTotalItemCount() / 10);

        return [
            'quizzes' => $quizzes,
            'page' => $page,
            'totalPages' => $totalPages,
            'userQuizzStatuses' => $quizDisplayData['userQuizzStatuses'],
            'userScores' => $quizDisplayData['userScores'],
            'userRankings' => $quizDisplayData['userRankings'],
            'bestScores' => $quizDisplayData['bestScores'],
            'tags' => $tags, // Ajoutez les tags au tableau retourné
            'selectedTag' => $selectedTagId // Ajoutez l'ID du tag sélectionné au tableau retourné
        ];
    }


    public function getQuizzWithQuestions($id, SessionInterface $session)
    {
        $quizz = $this->quizzRepository->find($id);
        $session->set('quizz', $id);
        return [
            'questions' => $quizz->getQuestions(),
            'quizz' => $quizz,
        ];
    }
}