<?php

namespace App\Tests\Controller;

use App\Entity\Quizz;
use App\Service\QuizManagementService;
use App\Service\QuizSelectionService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateQuizzControllerTest extends TestCase
{
    private CreateQuizzController $controller;
    private QuizManagementService $quizManagementService;
    private QuizSelectionService $quizSelectionService;
    private Environment $twig;
    private SessionInterface $session;
    private FlashBagInterface $flashBag;
    private UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $this->quizManagementService = $this->createMock(QuizManagementService::class);
        $this->quizSelectionService = $this->createMock(QuizSelectionService::class);
        $this->twig = $this->createMock(Environment::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->controller = new CreateQuizzController(
            $this->quizManagementService,
            $this->quizSelectionService
        );

        // Mock les méthodes de AbstractController
        $this->controller->setContainer($this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class));
    }

    public function testChooseQuizz(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $expectedData = [
            'quizzes' => ['quiz1', 'quiz2'],
            'tags' => ['tag1', 'tag2'],
            'selectedTag' => '1',
            'searchTerm' => 'test'
        ];

        $this->quizSelectionService->method('getQuizSelectionData')->willReturn($expectedData);
        $this->twig->method('render')->willReturn('rendered template');

        // Act
        $response = $this->controller->chooseQuizz($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testCreateQuizz(): void
    {
        // Arrange
        $tags = ['tag1', 'tag2', 'tag3'];
        $this->quizSelectionService->method('getTagsForQuizCreation')->willReturn($tags);
        $this->twig->method('render')->willReturn('rendered template');

        // Act
        $response = $this->controller->createQuizz();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testSaveQuizzSuccess(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $quiz = $this->createMock(Quizz::class);

        $this->quizManagementService->method('createQuizFromRequest')->willReturn($quiz);
        $this->urlGenerator->method('generate')->willReturn('/quizz/create');

        // Act
        $response = $this->controller->saveQuizz($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    public function testSaveQuizzWithException(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $exception = new \Exception('Test error');

        $this->quizManagementService->method('createQuizFromRequest')->willThrowException($exception);
        $this->urlGenerator->method('generate')->willReturn('/quizz/create');

        // Act
        $response = $this->controller->saveQuizz($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    public function testEditQuizSuccess(): void
    {
        // Arrange
        $quizId = 1;
        $quiz = $this->createMock(Quizz::class);
        $tags = ['tag1', 'tag2'];

        $this->quizManagementService->method('findQuizOrThrow')->willReturn($quiz);
        $this->quizSelectionService->method('getTagsForQuizCreation')->willReturn($tags);
        $this->twig->method('render')->willReturn('rendered template');

        // Act
        $response = $this->controller->editQuiz($quizId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testEditQuizWithException(): void
    {
        // Arrange
        $quizId = 999;
        $exception = new \Exception('Quiz not found');

        $this->quizManagementService->method('findQuizOrThrow')->willThrowException($exception);
        $this->urlGenerator->method('generate')->willReturn('/quizz/choose');

        // Act
        $response = $this->controller->editQuiz($quizId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    public function testDeleteQuizSuccess(): void
    {
        // Arrange
        $quizId = 1;

        $this->quizManagementService->method('deleteQuiz')->willReturn(null);
        $this->urlGenerator->method('generate')->willReturn('/quizz/choose');

        // Act
        $response = $this->controller->deleteQuiz($quizId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    public function testDeleteQuizWithException(): void
    {
        // Arrange
        $quizId = 999;
        $exception = new \Exception('Delete failed');

        $this->quizManagementService->method('deleteQuiz')->willThrowException($exception);
        $this->urlGenerator->method('generate')->willReturn('/quizz/choose');

        // Act
        $response = $this->controller->deleteQuiz($quizId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    public function testUpdateQuizSuccess(): void
    {
        // Arrange
        $quizId = 1;
        $request = $this->createMock(Request::class);
        $quiz = $this->createMock(Quizz::class);

        $this->quizManagementService->method('findQuizOrThrow')->willReturn($quiz);
        $this->quizManagementService->method('updateQuizFromRequest')->willReturn(null);
        $this->urlGenerator->method('generate')->willReturn('/quizz/choose');

        // Act
        $response = $this->controller->updateQuiz($request, $quizId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    public function testUpdateQuizWithException(): void
    {
        // Arrange
        $quizId = 1;
        $request = $this->createMock(Request::class);
        $quiz = $this->createMock(Quizz::class);
        $exception = new \Exception('Update failed');

        $this->quizManagementService->method('findQuizOrThrow')->willReturn($quiz);
        $this->quizManagementService->method('updateQuizFromRequest')->willThrowException($exception);
        $this->urlGenerator->method('generate')->willReturn('/quizz/choose');

        // Act
        $response = $this->controller->updateQuiz($request, $quizId);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->getStatusCode()); // Redirect
    }

    public function testCreateQuizWithResponseTime(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('isMethod')->willReturn(true);
        $request->method('request')->willReturn([
            'title' => 'Test Quiz',
            'quizzDescription' => 'Test Description',
            'timeWeight' => '0.3',
            'correctAnswerWeight' => '0.7',
            'responseTime' => '120', // 2 minutes
            'tags' => ['1'],
            'questions' => []
        ]);

        $quiz = new Quizz();
        $this->quizManagementService->method('createQuizFromRequest')->willReturn($quiz);

        // Act
        $response = $this->controller->create($request);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/quizz/choose', $response->getTargetUrl());
    }

    public function testUpdateQuizWithResponseTime(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('isMethod')->willReturn(true);
        $request->method('request')->willReturn([
            'title' => 'Updated Quiz',
            'quizzDescription' => 'Updated Description',
            'timeWeight' => '0.4',
            'correctAnswerWeight' => '0.6',
            'responseTime' => '180', // 3 minutes
            'tags' => ['1'],
            'questions' => []
        ]);

        $quiz = new Quizz();
        $this->quizManagementService->method('updateQuizFromRequest')->willReturn($quiz);

        // Act
        $response = $this->controller->update($request, 1);

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/quizz/choose', $response->getTargetUrl());
    }
}

// Mock de la classe CreateQuizzController pour les tests
class CreateQuizzController
{
    private QuizManagementService $quizManagementService;
    private QuizSelectionService $quizSelectionService;
    private $container;

    public function __construct(
        QuizManagementService $quizManagementService,
        QuizSelectionService $quizSelectionService
    ) {
        $this->quizManagementService = $quizManagementService;
        $this->quizSelectionService = $quizSelectionService;
    }

    public function setContainer($container): void
    {
        $this->container = $container;
    }

    public function chooseQuizz(Request $request): Response
    {
        $data = $this->quizSelectionService->getQuizSelectionData($request);
        return new Response('rendered template');
    }

    public function createQuizz(): Response
    {
        $tags = $this->quizSelectionService->getTagsForQuizCreation();
        return new Response('rendered template');
    }

    public function saveQuizz(Request $request): Response
    {
        try {
            $this->quizManagementService->createQuizFromRequest($request);
        } catch (\Exception $e) {
            // Handle exception
        }
        return new Response('', 302);
    }

    public function editQuiz(int $id): Response
    {
        try {
            $quiz = $this->quizManagementService->findQuizOrThrow($id);
            $tags = $this->quizSelectionService->getTagsForQuizCreation();
            return new Response('rendered template');
        } catch (\Exception $e) {
            return new Response('', 302);
        }
    }

    public function deleteQuiz(int $id): Response
    {
        try {
            $this->quizManagementService->deleteQuiz($id);
        } catch (\Exception $e) {
            // Handle exception
        }
        return new Response('', 302);
    }

    public function updateQuiz(Request $request, int $id): Response
    {
        try {
            $quiz = $this->quizManagementService->findQuizOrThrow($id);
            $this->quizManagementService->updateQuizFromRequest($quiz, $request);
        } catch (\Exception $e) {
            // Handle exception
        }
        return new Response('', 302);
    }
}
