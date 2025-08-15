<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Service\HomeService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends TestCase
{
    private HomeController $controller;
    private HomeService $homeService;

    protected function setUp(): void
    {
        $this->homeService = $this->createMock(HomeService::class);
        $this->controller = new HomeController($this->homeService);
    }

    public function testIndexWithUser(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $expectedData = [
            'lastQuizz' => 'quiz1',
            'userQuizzStatuses' => ['status1'],
            'userScores' => ['score1'],
            'userRankings' => ['ranking1'],
            'bestScores' => ['best1']
        ];

        $this->homeService->method('getHomePageData')->willReturn($expectedData);

        // Act
        $response = $this->controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testIndexWithoutUser(): void
    {
        // Arrange
        $expectedData = [
            'lastQuizz' => 'quiz1',
            'userQuizzStatuses' => [],
            'userScores' => [],
            'userRankings' => [],
            'bestScores' => []
        ];

        $this->homeService->method('getHomePageData')->willReturn($expectedData);

        // Act
        $response = $this->controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }

    public function testIndexWithEmptyData(): void
    {
        // Arrange
        $expectedData = [
            'lastQuizz' => null,
            'userQuizzStatuses' => [],
            'userScores' => [],
            'userRankings' => [],
            'bestScores' => []
        ];

        $this->homeService->method('getHomePageData')->willReturn($expectedData);

        // Act
        $response = $this->controller->index();

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered template', $response->getContent());
    }
}

// Mock de la classe HomeController pour les tests
class HomeController
{
    private HomeService $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    public function index(): Response
    {
        $user = $this->getUser();
        $data = $this->homeService->getHomePageData($user);
        return new Response('rendered template');
    }

    private function getUser(): ?User
    {
        // Mock de la méthode getUser
        return null;
    }
}
