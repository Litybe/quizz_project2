<?php

namespace App\Tests\Service;

use App\Entity\Quizz;
use App\Entity\User;
use App\Repository\QuizzRepository;
use App\Service\HomeService;
use App\Service\QuizDisplayService;
use PHPUnit\Framework\TestCase;

class HomeServiceTest extends TestCase
{
    private HomeService $homeService;
    private QuizzRepository $quizzRepository;
    private QuizDisplayService $quizDisplayService;

    protected function setUp(): void
    {
        $this->quizzRepository = $this->createMock(QuizzRepository::class);
        $this->quizDisplayService = $this->createMock(QuizDisplayService::class);

        $this->homeService = new HomeService(
            $this->quizzRepository,
            $this->quizDisplayService
        );
    }

    public function testGetHomePageDataWithUserAndQuiz(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $quiz = $this->createMock(Quizz::class);
        $quiz->method('getId')->willReturn(1);

        $quizDisplayData = [
            'isDone' => true,
            'userScore' => 85.5,
            'userRanking' => 3,
            'bestScore' => 95.0
        ];

        $this->quizzRepository->method('findOneBy')->willReturn($quiz);
        $this->quizDisplayService->method('getQuizDisplayDataForQuiz')->willReturn($quizDisplayData);

        // Act
        $result = $this->homeService->getHomePageData($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('lastQuizz', $result);
        $this->assertArrayHasKey('userQuizzStatuses', $result);
        $this->assertArrayHasKey('userScores', $result);
        $this->assertArrayHasKey('userRankings', $result);
        $this->assertArrayHasKey('bestScores', $result);

        $this->assertSame($quiz, $result['lastQuizz']);
        $this->assertArrayHasKey(1, $result['userQuizzStatuses']);
        $this->assertArrayHasKey(1, $result['userScores']);
        $this->assertArrayHasKey(1, $result['userRankings']);
        $this->assertArrayHasKey(1, $result['bestScores']);

        $this->assertEquals(85.5, $result['userScores'][1]);
        $this->assertEquals(3, $result['userRankings'][1]);
        $this->assertEquals(95.0, $result['bestScores'][1]);
    }

    public function testGetHomePageDataWithUserButNoQuiz(): void
    {
        // Arrange
        $user = $this->createMock(User::class);

        $this->quizzRepository->method('findOneBy')->willReturn(null);

        // Act
        $result = $this->homeService->getHomePageData($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertNull($result['lastQuizz']);
        $this->assertEmpty($result['userQuizzStatuses']);
        $this->assertEmpty($result['userScores']);
        $this->assertEmpty($result['userRankings']);
        $this->assertEmpty($result['bestScores']);
    }

    public function testGetHomePageDataWithQuizButNoUser(): void
    {
        // Arrange
        $user = null;
        $quiz = $this->createMock(Quizz::class);

        $this->quizzRepository->method('findOneBy')->willReturn($quiz);

        // Act
        $result = $this->homeService->getHomePageData($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertSame($quiz, $result['lastQuizz']);
        $this->assertEmpty($result['userQuizzStatuses']);
        $this->assertEmpty($result['userScores']);
        $this->assertEmpty($result['userRankings']);
        $this->assertEmpty($result['bestScores']);
    }

    public function testGetHomePageDataWithPartialQuizDisplayData(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $quiz = $this->createMock(Quizz::class);
        $quiz->method('getId')->willReturn(1);

        $quizDisplayData = [
            'isDone' => false,
            'userScore' => null,
            'userRanking' => 5,
            'bestScore' => null
        ];

        $this->quizzRepository->method('findOneBy')->willReturn($quiz);
        $this->quizDisplayService->method('getQuizDisplayDataForQuiz')->willReturn($quizDisplayData);

        // Act
        $result = $this->homeService->getHomePageData($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertSame($quiz, $result['lastQuizz']);
        $this->assertEmpty($result['userQuizzStatuses']); // isDone = false
        $this->assertEmpty($result['userScores']); // userScore = null
        $this->assertArrayHasKey(1, $result['userRankings']);
        $this->assertEmpty($result['bestScores']); // bestScore = null

        $this->assertEquals(5, $result['userRankings'][1]);
    }

    public function testGetHomePageDataWithNoQuizDisplayData(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $quiz = $this->createMock(Quizz::class);
        $quiz->method('getId')->willReturn(1);

        $this->quizzRepository->method('findOneBy')->willReturn($quiz);
        $this->quizDisplayService->method('getQuizDisplayDataForQuiz')->willReturn(null);

        // Act
        $result = $this->homeService->getHomePageData($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertSame($quiz, $result['lastQuizz']);
        $this->assertEmpty($result['userQuizzStatuses']);
        $this->assertEmpty($result['userScores']);
        $this->assertEmpty($result['userRankings']);
        $this->assertEmpty($result['bestScores']);
    }

    public function testGetHomePageDataWithEmptyQuizDisplayData(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $quiz = $this->createMock(Quizz::class);
        $quiz->method('getId')->willReturn(1);

        $quizDisplayData = [];

        $this->quizzRepository->method('findOneBy')->willReturn($quiz);
        $this->quizDisplayService->method('getQuizDisplayDataForQuiz')->willReturn($quizDisplayData);

        // Act
        $result = $this->homeService->getHomePageData($user);

        // Assert
        $this->assertIsArray($result);
        $this->assertSame($quiz, $result['lastQuizz']);
        $this->assertEmpty($result['userQuizzStatuses']);
        $this->assertEmpty($result['userScores']);
        $this->assertEmpty($result['userRankings']);
        $this->assertEmpty($result['bestScores']);
    }
}
