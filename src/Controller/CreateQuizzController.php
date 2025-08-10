<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quizz;
use App\Entity\Score;
use App\Entity\UserQuizzStatus;
use App\Repository\QuizzRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_MODO")'))]
final class CreateQuizzController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private QuizzRepository $quizzRepository;
    private TagRepository $tagRepository;
    
    private const QUIZZES_PER_PAGE = 10;
    private const SUCCESS_MESSAGES = [
        'created' => 'Le quiz a été créé avec succès !',
        'updated' => 'Le quiz a été mis à jour avec succès !',
        'deleted' => 'Le quiz a été supprimé avec succès !'
    ];

    private const ERROR_MESSAGES = [
        'quiz_not_found' => 'Le quiz n\'existe pas',
        'invalid_correct_answers' => 'Le nombre de bonnes réponses ne peut pas dépasser le nombre total de réponses moins 1'
    ];

    function __construct(QuizzRepository $quizzRepository, EntityManagerInterface $entityManager, LoggerInterface $logger, TagRepository $tagRepository)
    {
        $this->quizzRepository = $quizzRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Affiche la page de sélection de quiz avec filtres et pagination
     */
    #[Route('/quizz/choose', name: 'quizz_choose')]
    public function chooseQuizz(Request $request, PaginatorInterface $paginator): Response
    {
        $selectedTagId = $request->query->get('tag');
        $searchTerm = $request->query->get('search');
        $page = $request->query->getInt('page', 1);

        $tags = $this->tagRepository->findAll();
        $quizzesQuery = $this->quizzRepository->createQueryBuilder('q');

        // Filtrage par tag
        if ($selectedTagId) {
            $quizzesQuery->join('q.tags', 't')
                ->andWhere('t.id = :tagId')
                ->setParameter('tagId', $selectedTagId);
        }

        // Filtrage par recherche
        if ($searchTerm) {
            $quizzesQuery->andWhere('q.name LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        $quizzes = $paginator->paginate(
            $quizzesQuery->getQuery(),
            $page,
            self::QUIZZES_PER_PAGE
        );

        return $this->render('create_quizz/choose.html.twig', [
            'quizzes' => $quizzes,
            'tags' => $tags,
            'selectedTag' => $selectedTagId,
            'searchTerm' => $searchTerm
        ]);
    }

    /**
     * Affiche le formulaire de création de quiz
     */
    #[Route('/quizz/create', name: 'quizz_create')]
    public function createQuizz(): Response
    {
        $tags = $this->tagRepository->findAllOrderedByName();
        
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
        $quizz = $this->createQuizzFromRequest($request);
        $this->processQuestionsFromRequest($quizz, $request);
        
        $this->entityManager->persist($quizz);
        $this->entityManager->flush();

        $this->addFlash('success', self::SUCCESS_MESSAGES['created']);
        return $this->redirectToRoute('quizz_create');
    }

    /**
     * Affiche le formulaire d'édition d'un quiz
     */
    #[Route('/quizz/edit/{id}', name: 'quizz_edit')]
    public function editQuiz(int $id): Response
    {
        $quiz = $this->findQuizOrThrow($id);
        $tags = $this->tagRepository->findAllOrderedByName();
        
        return $this->render('create_quizz/edit.html.twig', [
            'quiz' => $quiz,
            'tags' => $tags,
        ]);
    }

    /**
     * Supprime un quiz et toutes ses données associées
     */
    #[Route('/quizz/delete/{id}', name: 'quizz_delete', methods: ['POST'])]
    public function deleteQuiz(int $id): Response
    {
        $quiz = $this->findQuizOrThrow($id);
        $this->deleteQuizData($quiz);

        $this->addFlash('success', self::SUCCESS_MESSAGES['deleted']);
        return $this->redirectToRoute('quizz_choose');
    }

    /**
     * Met à jour un quiz existant
     */
    #[Route('/quizz/update/{id}', name: 'quizz_update', methods: ['POST'])]
    public function updateQuiz(Request $request, int $id): Response
    {
        $quiz = $this->findQuizOrThrow($id);
        $this->updateQuizzFromRequest($quiz, $request);
        
        $this->entityManager->persist($quiz);
        $this->entityManager->flush();

        $this->addFlash('success', self::SUCCESS_MESSAGES['updated']);
        return $this->redirectToRoute('quizz_choose');
    }

    /**
     * Crée un objet Quiz à partir des données de la requête
     */
    private function createQuizzFromRequest(Request $request): Quizz
    {
        $quizz = new Quizz();
        $quizz->setName($request->request->get('quizzName'));
        $quizz->setDescription($request->request->get('quizzDescription'));
        $quizz->setTimeWeight($request->request->get('timeWeight'));
        $quizz->setCorrectAnswerWeight($request->request->get('correctAnswerWeight'));

        $this->addTagsToQuizz($quizz, $request->request->all('tags'));
        
        return $quizz;
    }

    /**
     * Met à jour un quiz existant à partir des données de la requête
     */
    private function updateQuizzFromRequest(Quizz $quiz, Request $request): void
    {
        $quiz->setName($request->request->get('title'));
        $quiz->setDescription($request->request->get('quizzDescription'));
        $quiz->setTimeWeight($request->request->get('timeWeight'));
        $quiz->setCorrectAnswerWeight($request->request->get('correctAnswerWeight'));

        // Mise à jour des tags
        $this->updateQuizzTags($quiz, $request->request->all('tags'));
        
        // Mettre à jour les questions existantes au lieu d'en créer de nouvelles
        $this->updateQuestionsFromRequest($quiz, $request);
    }

    /**
     * Ajoute des tags à un quiz
     */
    private function addTagsToQuizz(Quizz $quizz, array $tagIds): void
    {
        if (!is_array($tagIds)) {
            return;
        }

        foreach ($tagIds as $tagId) {
            $tag = $this->tagRepository->find($tagId);
            if ($tag) {
                $quizz->addTag($tag);
            }
        }
    }

    /**
     * Met à jour les tags d'un quiz
     */
    private function updateQuizzTags(Quizz $quizz, array $tagIds): void
    {
        // Supprimer tous les tags actuels
        foreach ($quizz->getTags() as $tag) {
            $quizz->removeTag($tag);
        }
        
        // Ajouter les nouveaux tags
        $this->addTagsToQuizz($quizz, $tagIds);
    }

    /**
     * Traite les questions à partir des données de la requête
     */
    private function processQuestionsFromRequest(Quizz $quizz, Request $request): void
    {
        $questionsData = $request->request->all('questions');
        $files = $request->files->all();

        foreach ($questionsData as $index => $questionData) {
            $question = new Question();
            $question->setQuestionText($questionData['text']);
            $question->setIsTextual($questionData['type'] === 'textual');

            if ($question->isTextual()) {
                $question->setCorrectTextualAnswer($questionData['correctTextualAnswer']);
            } else {
                $this->processImageQuestion($question, $questionData, $files, $index);
            }

            $quizz->addQuestion($question);
            $this->entityManager->persist($question);
        }
    }

    /**
     * Traite une question avec image
     */
    private function processImageQuestion(Question $question, array $questionData, array $files, int $index): void
    {
        // Gestion de l'image
        if (isset($files['questions'][$index]['image']) && $files['questions'][$index]['image']) {
            $this->handleImageUpload($question, $files['questions'][$index]['image']);
        }

        // Gestion des réponses
        $this->processAnswers($question, $questionData);
    }

    /**
     * Traite les réponses d'une question
     */
    private function processAnswers(Question $question, array $questionData): void
    {
        $correctAnswers = $questionData['correctAnswers'] ?? [];
        $maxCorrectAnswers = count($questionData['answers']) - 1;

        if (count($correctAnswers) > $maxCorrectAnswers) {
            throw new \InvalidArgumentException(self::ERROR_MESSAGES['invalid_correct_answers'] . " ($maxCorrectAnswers)");
        }

        foreach ($questionData['answers'] as $answerIndex => $answerData) {
            $answer = new Answer();
            $answer->setTextAnswer($answerData['text']);

            if (in_array($answerIndex, $correctAnswers)) {
                $question->addGoodAnswer($answer);
            }

            $question->addAnswer($answer);
            $this->entityManager->persist($answer);
        }
    }

    /**
     * Met à jour les réponses d'une question existante
     */
    private function updateAnswers(Question $question, array $questionData): void
    {
        $correctAnswers = $questionData['correctAnswers'] ?? [];
        $maxCorrectAnswers = count($questionData['answers']) - 1;

        if (count($correctAnswers) > $maxCorrectAnswers) {
            throw new \InvalidArgumentException(self::ERROR_MESSAGES['invalid_correct_answers'] . " ($maxCorrectAnswers)");
        }

        foreach ($questionData['answers'] as $answerIndex => $answerData) {
            $answer = $question->getAnswers()[$answerIndex] ?? new Answer();
            $answer->setTextAnswer($answerData['text']);

            if (in_array($answerIndex, $correctAnswers)) {
                if (!$question->getGoodAnswers()->contains($answer)) {
                    $question->addGoodAnswer($answer);
                }
            } else {
                $question->removeGoodAnswer($answer);
            }

            if (!$question->getAnswers()->contains($answer)) {
                $question->addAnswer($answer);
            }

            $this->entityManager->persist($answer);
        }
    }

    /**
     * Gère l'upload d'une image pour une question
     */
    private function handleImageUpload(Question $question, UploadedFile $imageFile): void
    {
        $oldImagePath = $question->getImagePath();
        if ($oldImagePath) {
            $oldImageFullPath = $this->getParameter('images_directory') . '/' . $oldImagePath;
            if (file_exists($oldImageFullPath)) {
                unlink($oldImageFullPath);
            }
        }

        $newFilename = uniqid() . '.' . $imageFile->guessExtension();
        $imageFile->move(
            $this->getParameter('images_directory'),
            $newFilename
        );
        $question->setImagePath($newFilename);
    }

    /**
     * Supprime toutes les données associées à un quiz
     */
    private function deleteQuizData(Quizz $quiz): void
    {
        // Supprimer les scores
        $scoreRepository = $this->entityManager->getRepository(Score::class);
        $scores = $scoreRepository->findBy(['IdQuizz' => $quiz]);
        foreach ($scores as $score) {
            $this->entityManager->remove($score);
        }

        // Supprimer les statuts utilisateur
        $userQuizzStatusRepository = $this->entityManager->getRepository(UserQuizzStatus::class);
        $userQuizzStatuses = $userQuizzStatusRepository->findBy(['Quizz' => $quiz]);
        foreach ($userQuizzStatuses as $userQuizzStatus) {
            $this->entityManager->remove($userQuizzStatus);
        }

        // Supprimer les questions et réponses
        foreach ($quiz->getQuestions() as $question) {
            foreach ($question->getAnswers() as $answer) {
                $this->entityManager->remove($answer);
            }
            $this->entityManager->remove($question);
        }

        // Supprimer le quiz
        $this->entityManager->remove($quiz);
    }

    /**
     * Trouve un quiz par son ID ou lance une exception
     */
    private function findQuizOrThrow(int $id): Quizz
    {
        $quiz = $this->quizzRepository->find($id);
        if (!$quiz) {
            throw $this->createNotFoundException(self::ERROR_MESSAGES['quiz_not_found']);
        }
        return $quiz;
    }

    /**
     * Met à jour les questions existantes d'un quiz à partir des données de la requête
     */
    private function updateQuestionsFromRequest(Quizz $quiz, Request $request): void
    {
        $questionsData = $request->request->all('questions');
        $files = $request->files->all();

        // Récupérer les questions existantes
        $existingQuestions = $quiz->getQuestions()->toArray();
        
        foreach ($questionsData as $index => $questionData) {
            // Utiliser la question existante si elle existe, sinon en créer une nouvelle
            if (isset($existingQuestions[$index])) {
                $question = $existingQuestions[$index];
            } else {
                $question = new Question();
                $quiz->addQuestion($question);
            }

            $question->setQuestionText($questionData['text']);
            $question->setIsTextual($questionData['type'] === 'textual');

            if ($question->isTextual()) {
                $question->setCorrectTextualAnswer($questionData['correctTextualAnswer']);
            } else {
                $this->updateImageQuestion($question, $questionData, $files, $index);
            }

            $this->entityManager->persist($question);
        }

        // Supprimer les questions qui ne sont plus dans les données
        $this->removeUnusedQuestions($quiz, count($questionsData));
    }

    /**
     * Met à jour une question avec image existante
     */
    private function updateImageQuestion(Question $question, array $questionData, array $files, int $index): void
    {
        // Gestion de l'image
        if (isset($files['questions'][$index]['image']) && $files['questions'][$index]['image']) {
            $this->handleImageUpload($question, $files['questions'][$index]['image']);
        }

        // Mettre à jour les réponses existantes
        $this->updateAnswers($question, $questionData);
    }

    /**
     * Supprime les questions qui ne sont plus utilisées
     */
    private function removeUnusedQuestions(Quizz $quiz, int $expectedQuestionCount): void
    {
        $existingQuestions = $quiz->getQuestions()->toArray();
        
        // Supprimer les questions en trop
        for ($i = $expectedQuestionCount; $i < count($existingQuestions); $i++) {
            $questionToRemove = $existingQuestions[$i];
            
            // Supprimer d'abord toutes les réponses
            foreach ($questionToRemove->getAnswers() as $answer) {
                $this->entityManager->remove($answer);
            }
            
            // Supprimer la question
            $quiz->removeQuestion($questionToRemove);
            $this->entityManager->remove($questionToRemove);
        }
    }
}
