<?php

namespace App\Controller;

use App\Service\QuizManagementService;
use App\Service\QuizSelectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODO")'))]
final class CreateQuizzController extends AbstractController
{
    private const SUCCESS_MESSAGES = [
        'created' => 'Le quiz a été créé avec succès !',
        'updated' => 'Le quiz a été mis à jour avec succès !',
        'deleted' => 'Le quiz a été supprimé avec succès !'
    ];

    public function __construct(
        private QuizManagementService $quizManagementService,
        private QuizSelectionService $quizSelectionService
    ) {}

    /**
     * Affiche la page de sélection de quiz avec filtres et pagination
     */
    #[Route('/quizz/choose', name: 'quizz_choose')]
    public function chooseQuizz(Request $request): Response
    {
        $data = $this->quizSelectionService->getQuizSelectionData($request);
        
        return $this->render('create_quizz/choose.html.twig', $data);
    }

    /**
     * Affiche le formulaire de création de quiz
     */
    #[Route('/quizz/create', name: 'quizz_create')]
    public function createQuizz(): Response
    {
        $tags = $this->quizSelectionService->getTagsForQuizCreation();
        
        return $this->render('create_quizz/create-quizz.html.twig', [
            'tags' => $tags,
        ]);
    }

    /**
     * Sauvegarde un nouveau quiz
     */
    #[Route('/quizz/save', name: 'quiz_save', methods: ['POST'])]
    public function saveQuizz(Request $request): Response
    {
        try {
            $this->quizManagementService->createQuizFromRequest($request);
            $this->addFlash('success', self::SUCCESS_MESSAGES['created']);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        
        return $this->redirectToRoute('quizz_create');
    }

    /**
     * Affiche le formulaire d'édition d'un quiz
     */
    #[Route('/quizz/edit/{id}', name: 'quizz_edit')]
    public function editQuiz(int $id): Response
    {
        try {
            $quiz = $this->quizManagementService->findQuizOrThrow($id);
            $tags = $this->quizSelectionService->getTagsForQuizCreation();
            
            return $this->render('create_quizz/edit.html.twig', [
                'quiz' => $quiz,
                'tags' => $tags,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('quizz_choose');
        }
    }

    /**
     * Supprime un quiz et toutes ses données associées
     */
    #[Route('/quizz/delete/{id}', name: 'quizz_delete', methods: ['POST'])]
    public function deleteQuiz(int $id): Response
    {
        try {
            $this->quizManagementService->deleteQuiz($id);
            $this->addFlash('success', self::SUCCESS_MESSAGES['deleted']);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        
        return $this->redirectToRoute('quizz_choose');
    }

    /**
     * Met à jour un quiz existant
     */
    #[Route('/quizz/update/{id}', name: 'quizz_update', methods: ['POST'])]
    public function updateQuiz(Request $request, int $id): Response
    {
        try {
            $quiz = $this->quizManagementService->findQuizOrThrow($id);
            $this->quizManagementService->updateQuizFromRequest($quiz, $request);
            $this->addFlash('success', self::SUCCESS_MESSAGES['updated']);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        
        return $this->redirectToRoute('quizz_choose');
    }

}
