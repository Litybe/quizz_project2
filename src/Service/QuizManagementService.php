<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quizz;
use App\Entity\Score;
use App\Entity\UserQuizzStatus;
use App\Repository\QuizzRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuizManagementService
{
    private const ERROR_MESSAGES = [
        'quiz_not_found' => 'Le quiz n\'existe pas',
        'invalid_correct_answers' => 'Le nombre de bonnes réponses ne peut pas dépasser le nombre total de réponses moins 1'
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private QuizzRepository $quizzRepository,
        private TagRepository $tagRepository,
        private LoggerInterface $logger,
        private string $imagesDirectory
    ) {}

    public function createQuizFromRequest(Request $request): Quizz
    {
        $quiz = new Quizz();
        $this->populateQuizFromRequest($quiz, $request);
        $this->processQuestionsFromRequest($quiz, $request);
        
        $this->entityManager->persist($quiz);
        $this->entityManager->flush();

        $this->logger->info('Quiz created successfully', ['quizId' => $quiz->getId()]);
        
        return $quiz;
    }

    public function updateQuizFromRequest(Quizz $quiz, Request $request): void
    {
        // Debug: Log des fichiers reçus
        $files = $request->files->all();
        $this->logger->info('Files received in update request', [
            'quizId' => $quiz->getId(),
            'filesCount' => count($files),
            'fileKeys' => array_keys($files)
        ]);

        $this->populateQuizFromRequest($quiz, $request);
        $this->updateQuestionsFromRequest($quiz, $request);
        
        $this->entityManager->persist($quiz);
        $this->entityManager->flush();

        $this->logger->info('Quiz updated successfully', ['quizId' => $quiz->getId()]);
    }

    public function deleteQuiz(int $quizId): void
    {
        $quiz = $this->findQuizOrThrow($quizId);
        $this->deleteQuizData($quiz);
        
        $this->logger->info('Quiz deleted successfully', ['quizId' => $quizId]);
    }

    public function findQuizOrThrow(int $id): Quizz
    {
        $quiz = $this->quizzRepository->find($id);
        if (!$quiz) {
            throw new NotFoundHttpException(self::ERROR_MESSAGES['quiz_not_found']);
        }
        return $quiz;
    }

    private function populateQuizFromRequest(Quizz $quiz, Request $request): void
    {
        $quiz->setName($request->request->get('quizzName') ?? $request->request->get('title'));
        $quiz->setDescription($request->request->get('quizzDescription'));
        
        // Valeurs par défaut pour les poids si non fournies
        $timeWeight = $request->request->get('timeWeight');
        $correctAnswerWeight = $request->request->get('correctAnswerWeight');
        
        $quiz->setTimeWeight($timeWeight !== null ? (float) $timeWeight : 0.2);
        $quiz->setCorrectAnswerWeight($correctAnswerWeight !== null ? (float) $correctAnswerWeight : 0.8);

        // Gestion du temps de réponse
        $responseTime = $request->request->get('responseTime');
        if ($responseTime !== null) {
            $responseTime = (int) $responseTime;
            if ($responseTime < 0) {
                throw new \InvalidArgumentException('Response time cannot be negative');
            }
            if ($responseTime > 3600) {
                throw new \InvalidArgumentException('Response time cannot exceed 1 hour (3600 seconds)');
            }
            $quiz->setResponseTime($responseTime);
        } else {
            // Valeur par défaut : 14 secondes
            $quiz->setResponseTime(14);
        }

        $this->updateQuizTags($quiz, $request->request->all('tags'));
    }

    private function updateQuizTags(Quizz $quiz, array $tagIds): void
    {
        // Remove all current tags
        foreach ($quiz->getTags() as $tag) {
            $quiz->removeTag($tag);
        }
        
        // Add new tags
        if (is_array($tagIds)) {
            foreach ($tagIds as $tagId) {
                $tag = $this->tagRepository->find($tagId);
                if ($tag) {
                    $quiz->addTag($tag);
                }
            }
        }
    }

    private function processQuestionsFromRequest(Quizz $quiz, Request $request): void
    {
        $questionsData = $request->request->all('questions');
        $files = $request->files->all();

        foreach ($questionsData as $index => $questionData) {
            $question = new Question();
            $this->populateQuestionFromData($question, $questionData, $files, $index);
            $quiz->addQuestion($question);
            $this->entityManager->persist($question);
        }
    }

    private function updateQuestionsFromRequest(Quizz $quiz, Request $request): void
    {
        $questionsData = $request->request->all('questions');
        $files = $request->files->all();
        $existingQuestions = $quiz->getQuestions()->toArray();
        
        foreach ($questionsData as $index => $questionData) {
            $question = $existingQuestions[$index] ?? new Question();
            
            if (isset($existingQuestions[$index])) {
                // Mise à jour d'une question existante
                $this->populateQuestionFromDataForUpdate($question, $questionData, $files, $index);
            } else {
                // Création d'une nouvelle question
                $this->populateQuestionFromData($question, $questionData, $files, $index);
                $quiz->addQuestion($question);
            }
            
            $this->entityManager->persist($question);
        }

        $this->removeUnusedQuestions($quiz, count($questionsData));
    }

    private function populateQuestionFromData(Question $question, array $questionData, array $files, int $index): void
    {
        $question->setQuestionText($questionData['text']);
        $question->setIsTextual($questionData['type'] === 'textual');

        // Gestion de l'image pour tous les types de questions
        if (isset($files['questions'][$index]['image']) && $files['questions'][$index]['image']) {
            $this->handleImageUpload($question, $files['questions'][$index]['image']);
        }

        if ($question->isTextual()) {
            $question->setCorrectTextualAnswer($questionData['correctTextualAnswer']);
        } else {
            $this->processAnswers($question, $questionData);
        }
    }

    private function populateQuestionFromDataForUpdate(Question $question, array $questionData, array $files, int $index): void
    {
        $question->setQuestionText($questionData['text']);
        $question->setIsTextual($questionData['type'] === 'textual');

        // Gestion de l'image pour tous les types de questions
        if (isset($files['questions'][$index]['image']) && $files['questions'][$index]['image']) {
            $this->handleImageUpload($question, $files['questions'][$index]['image']);
        }

        if ($question->isTextual()) {
            $question->setCorrectTextualAnswer($questionData['correctTextualAnswer']);
        } else {
            $this->updateAnswers($question, $questionData);
        }
    }

    private function handleImageQuestion(Question $question, array $questionData, array $files, int $index): void
    {
        // L'image est maintenant gérée dans populateQuestionFromData
        $this->processAnswers($question, $questionData);
    }

    private function handleImageQuestionForUpdate(Question $question, array $questionData, array $files, int $index): void
    {
        // L'image est maintenant gérée dans populateQuestionFromDataForUpdate
        $this->updateAnswers($question, $questionData);
    }

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

    private function updateAnswers(Question $question, array $questionData): void
    {
        $correctAnswers = $questionData['correctAnswers'] ?? [];
        $maxCorrectAnswers = count($questionData['answers']) - 1;

        if (count($correctAnswers) > $maxCorrectAnswers) {
            throw new \InvalidArgumentException(self::ERROR_MESSAGES['invalid_correct_answers'] . " ($maxCorrectAnswers)");
        }

        $existingAnswers = $question->getAnswers()->toArray();
        
        foreach ($questionData['answers'] as $answerIndex => $answerData) {
            // Utiliser la réponse existante si elle existe, sinon en créer une nouvelle
            $answer = $existingAnswers[$answerIndex] ?? new Answer();
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

        // Supprimer les réponses en trop
        $this->removeUnusedAnswers($question, count($questionData['answers']));
    }

    private function removeUnusedAnswers(Question $question, int $expectedAnswerCount): void
    {
        $existingAnswers = $question->getAnswers()->toArray();
        
        for ($i = $expectedAnswerCount; $i < count($existingAnswers); $i++) {
            $answerToRemove = $existingAnswers[$i];
            $question->removeAnswer($answerToRemove);
            $question->removeGoodAnswer($answerToRemove);
            $this->entityManager->remove($answerToRemove);
        }
    }

    private function handleImageUpload(Question $question, UploadedFile $imageFile): void
    {
        $oldImagePath = $question->getImagePath();
        if ($oldImagePath) {
            $oldImageFullPath = rtrim($this->imagesDirectory, '/\\') . '/' . $oldImagePath;
            if (file_exists($oldImageFullPath)) {
                unlink($oldImageFullPath);
            }
        }

        $newFilename = uniqid() . '.' . $imageFile->guessExtension();
        $imageFile->move(
            $this->imagesDirectory,
            $newFilename
        );
        $question->setImagePath($newFilename);
    }

    private function removeUnusedQuestions(Quizz $quiz, int $expectedQuestionCount): void
    {
        $existingQuestions = $quiz->getQuestions()->toArray();
        
        for ($i = $expectedQuestionCount; $i < count($existingQuestions); $i++) {
            $questionToRemove = $existingQuestions[$i];
            
            foreach ($questionToRemove->getAnswers() as $answer) {
                $this->entityManager->remove($answer);
            }
            
            $quiz->removeQuestion($questionToRemove);
            $this->entityManager->remove($questionToRemove);
        }
    }

    private function deleteQuizData(Quizz $quiz): void
    {
        // Delete scores
        $scoreRepository = $this->entityManager->getRepository(Score::class);
        $scores = $scoreRepository->findBy(['IdQuizz' => $quiz]);
        foreach ($scores as $score) {
            $this->entityManager->remove($score);
        }

        // Delete user quiz statuses
        $userQuizzStatusRepository = $this->entityManager->getRepository(UserQuizzStatus::class);
        $userQuizzStatuses = $userQuizzStatusRepository->findBy(['Quizz' => $quiz]);
        foreach ($userQuizzStatuses as $userQuizzStatus) {
            $this->entityManager->remove($userQuizzStatus);
        }

        // Delete questions and answers
        foreach ($quiz->getQuestions() as $question) {
            foreach ($question->getAnswers() as $answer) {
                $this->entityManager->remove($answer);
            }
            $this->entityManager->remove($question);
        }

        $this->entityManager->remove($quiz);
        $this->entityManager->flush();
    }

    // imagesDirectory is injected from container parameters via services.yaml
}
