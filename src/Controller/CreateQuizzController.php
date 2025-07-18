<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quizz;
use App\Repository\QuizzRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private QuizzRepository $_quizzRepository;
    private TagRepository $_tagRepository;

    private EntityManagerInterface $_entityManager;
    private LoggerInterface $_logger;

    function __construct(QuizzRepository $quizzRepository, EntityManagerInterface $entityManager, LoggerInterface $logger, TagRepository $tagRepository)
    {
        $this->_quizzRepository = $quizzRepository;
        $this->_entityManager = $entityManager;
        $this->_logger = $logger;
        $this->_tagRepository = $tagRepository;
    }


    #[Route('/quizz/choose', name: 'quizz_choose')]
    public function chooseQuizz(): Response
    {
        $quizzes = $this->_quizzRepository->findAll();

        return $this->render('create_quizz/choose.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

    #[Route('/quizz/create', name: 'quizz_create')]
    public function createQuizz(): Response
    {
        $tags = $this->_tagRepository->findAllOrderedByName();
        return $this->render('create_quizz/create-quizz.html.twig', [
            'controller_name' => 'CreateQuizzController',
            'tags' => $tags, // Passez les tags à la vue
        ]);
    }

    #[Route('/quizz/save', name: 'quiz_save', methods: ['POST'])]
    public function saveQuizz(Request $request): Response
    {
        $quizz = new Quizz();
        $quizz->setName($request->request->get('quizzName'));
        $quizz->setDescription($request->request->get('quizzDescription'));
        $quizz->setTimeWeight($request->request->get('timeWeight'));
        $quizz->setCorrectAnswerWeight($request->request->get('correctAnswerWeight'));

        $tagIds = $request->request->all('tags');
        if (is_array($tagIds)) {
            foreach ($tagIds as $tagId) {
                $tag = $this->_tagRepository->find($tagId);
                if ($tag) {
                    $quizz->addTag($tag);
                }
            }
        }

        $questionsData = $request->request->all('questions');
        $files = $request->files->all();

        foreach ($questionsData as $index => $questionData) {
            $question = new Question();
            $question->setQuestionText($questionData['text']);
            $question->setIsTextual($questionData['type'] === 'textual');

            if ($question->isTextual()) {
                $question->setCorrectTextualAnswer($questionData['correctTextualAnswer']);
            } else {
                if (isset($files['questions'][$index]['image']) && $files['questions'][$index]['image']) {
                    $this->handleImageUpload2($question, $files['questions'][$index]['image']);
                }

                $correctAnswers = $questionData['correctAnswers'] ?? [];
                $maxCorrectAnswers = count($questionData['answers']) - 1;

                if (count($correctAnswers) > $maxCorrectAnswers) {
                    throw new \InvalidArgumentException("Le nombre de bonnes réponses ne peut pas dépasser " . $maxCorrectAnswers);
                }

                foreach ($questionData['answers'] as $answerIndex => $answerData) {
                    $answer = new Answer();
                    $answer->setTextAnswer($answerData['text']);

                    if (in_array($answerIndex, $correctAnswers)) {
                        $question->addGoodAnswer($answer);
                    }

                    $question->addAnswer($answer);
                    $this->_entityManager->persist($answer);
                }
            }

            $quizz->addQuestion($question);
            $this->_entityManager->persist($question);
        }

        $this->_entityManager->persist($quizz);
        $this->_entityManager->flush();

        return $this->redirectToRoute('quizz_create');
    }

    private function handleImageUpload2(Question $question, UploadedFile $imageFile): void
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

    #[Route('/quizz/edit/{id}', name: 'quizz_edit')]
    public function editQuiz(int $id): Response
    {
        $quiz = $this->_quizzRepository->find($id);
        if (!$quiz) {
            throw $this->createNotFoundException('Le quiz n\'existe pas');
        }
        $tags = $this->_tagRepository->findAllOrderedByName();
        return $this->render('create_quizz/edit.html.twig', [
            'quiz' => $quiz,
            'tags' => $tags,
        ]);
    }

    #[Route('/quizz/update/{id}', name: 'quizz_update', methods: ['POST'])]
    public function updateQuiz(Request $request, int $id): Response
    {
        $quiz = $this->_quizzRepository->find($id);
        if (!$quiz) {
            throw $this->createNotFoundException('Le quiz n\'existe pas');
        }

        $quiz->setName($request->request->get('title'));
        $quiz->setDescription($request->request->get('quizzDescription'));
        $quiz->setTimeWeight($request->request->get('timeWeight'));
        $quiz->setCorrectAnswerWeight($request->request->get('correctAnswerWeight'));

        // Gestion des tags
        $tagIds = $request->request->get('tags');
        // Effacer les tags actuels
        foreach ($quiz->getTags() as $tag) {
            $quiz->removeTag($tag);
        }
        // Ajouter les nouveaux tags
        if (is_array($tagIds)) {
            foreach ($tagIds as $tagId) {
                $tag = $this->_tagRepository->find($tagId);
                if ($tag) {
                    $quiz->addTag($tag);
                }
            }
        }

        $questionsData = $request->request->all('questions');
        $files = $request->files->all();

        $existingQuestionIds = array_map(function($question) {
            return $question->getId();
        }, $quiz->getQuestions()->toArray());

        $submittedQuestionIds = array_keys($questionsData);

        $deletedQuestionIds = array_diff($existingQuestionIds, $submittedQuestionIds);

        foreach ($deletedQuestionIds as $deletedQuestionId) {
            $deletedQuestion = $this->_entityManager->getRepository(Question::class)->find($deletedQuestionId);
            if ($deletedQuestion) {
                foreach ($deletedQuestion->getAnswers() as $answer) {
                    $this->_entityManager->remove($answer);
                }
                $quiz->removeQuestion($deletedQuestion);
                $this->_entityManager->remove($deletedQuestion);
            }
        }

        foreach ($questionsData as $index => $questionData) {
            $question = $quiz->getQuestions()[$index] ?? new Question();
            $question->setQuestionText($questionData['text']);
            $question->setIsTextual($questionData['type'] === 'textual');

            if ($question->isTextual()) {
                $question->setCorrectTextualAnswer($questionData['correctTextualAnswer']);
            } else {
                if (isset($files['questions'][$index]['image']) && $files['questions'][$index]['image'] instanceof UploadedFile) {
                    $this->handleImageUpload($question, $files['questions'][$index]['image']);
                }

                $this->updateAnswers($question, $questionData);
            }

            if (!$quiz->getQuestions()->contains($question)) {
                $quiz->addQuestion($question);
            }

            $this->_entityManager->persist($question);
        }

        $this->_entityManager->persist($quiz);
        $this->_entityManager->flush();

        return $this->redirectToRoute('quizz_choose');
    }

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

    private function updateAnswers(Question $question, array $questionData): void
    {
        $correctAnswers = $questionData['correctAnswers'] ?? [];
        $maxCorrectAnswers = count($questionData['answers']) - 1;

        if (count($correctAnswers) > $maxCorrectAnswers) {
            throw new \InvalidArgumentException("Le nombre de bonnes réponses ne peut pas dépasser " . $maxCorrectAnswers);
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

            $this->_entityManager->persist($answer);
        }
    }
}
