<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\QuizzService;
use App\Service\ScoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AnswerQuizzController extends AbstractController
{
    private $quizzService;
    private $scoreService;

    public function __construct(QuizzService $quizzService, ScoreService $scoreService)
    {
        $this->quizzService = $quizzService;
        $this->scoreService = $scoreService;
    }

    #[Route('/quizz/select', name: 'selectAll_quizz')]
    public function selectQuiz(Request $request): Response
    {
        $user = $this->getUser();
        $data = $this->quizzService->getPaginatedQuizzes($request, $user);

        return $this->render('answer_quizz/select.html.twig', $data);
    }

    #[Route('/quizz/answer', name: 'answer_quizz')]
    public function displayQuizz(Request $request, SessionInterface $session): Response
    {
        $id = $request->query->get('id');
        $data = $this->quizzService->getQuizzWithQuestions($id, $session);

        return $this->render('answer_quizz/index.html.twig', $data);
    }

    #[Route('/quizz/submit', name: 'submit_quizz', methods: ['POST'])]
    public function submitQuizzAnswer(Request $request, SessionInterface $session): JsonResponse
    {
        try {
            $quizzId = $session->get('quizz');
            $content = json_decode($request->getContent(), true);
            /** @var User $user */
            $user = $this->getUser();

            if (!$quizzId) {
                throw $this->createNotFoundException('Quiz not found in session.');
            }

            $result = $this->scoreService->calculateAndSaveScore($quizzId, $content, $session, $user);

            return new JsonResponse($result);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
