<?php

namespace App\Tests\Service;

use App\Entity\Quizz;
use App\Repository\QuizzRepository;
use App\Repository\TagRepository;
use App\Service\QuizManagementService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class QuizManagementServiceTest extends TestCase
{
    private QuizManagementService $quizManagementService;
    private EntityManagerInterface $entityManager;
    private QuizzRepository $quizzRepository;
    private TagRepository $tagRepository;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->quizzRepository = $this->createMock(QuizzRepository::class);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->quizManagementService = new QuizManagementService(
            $this->entityManager,
            $this->quizzRepository,
            $this->tagRepository,
            $this->logger,
            'public/uploads/images'
        );
    }

    public function testCreateQuizWithResponseTime(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('request')->willReturn($this->createMock(\Symfony\Component\HttpFoundation\ParameterBag::class));
        $request->request->method('get')->willReturnMap([
            ['title', null, 'Test Quiz'],
            ['quizzDescription', null, 'Test Description'],
            ['timeWeight', null, '0.3'],
            ['correctAnswerWeight', null, '0.7'],
            ['responseTime', null, '120'], // 2 minutes en secondes
        ]);
        $request->request->method('all')->willReturn(['tags' => ['1', '2'], 'questions' => []]);

        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        // Act
        $result = $this->quizManagementService->createQuizFromRequest($request);

        // Assert
        $this->assertInstanceOf(Quizz::class, $result);
        $this->assertEquals(120, $result->getResponseTime());
    }

    public function testCreateQuizWithDefaultResponseTime(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('request')->willReturn($this->createMock(\Symfony\Component\HttpFoundation\ParameterBag::class));
        $request->request->method('get')->willReturnMap([
            ['title', null, 'Test Quiz'],
            ['quizzDescription', null, 'Test Description'],
            ['timeWeight', null, '0.3'],
            ['correctAnswerWeight', null, '0.7'],
            ['responseTime', null, null], // Pas de responseTime fourni
        ]);
        $request->request->method('all')->willReturn(['tags' => ['1'], 'questions' => []]);

        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        // Act
        $result = $this->quizManagementService->createQuizFromRequest($request);

        // Assert
        $this->assertInstanceOf(Quizz::class, $result);
        $this->assertEquals(14, $result->getResponseTime()); // 14 secondes par défaut
    }

    public function testValidateResponseTimeWithInvalidValue(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('request')->willReturn($this->createMock(\Symfony\Component\HttpFoundation\ParameterBag::class));
        $request->request->method('get')->willReturnMap([
            ['title', null, 'Test Quiz'],
            ['quizzDescription', null, 'Test Description'],
            ['timeWeight', null, '0.3'],
            ['correctAnswerWeight', null, '0.7'],
            ['responseTime', null, '-10'], // Valeur négative invalide
        ]);
        $request->request->method('all')->willReturn(['tags' => ['1'], 'questions' => []]);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Response time must be greater than 0');
        
        $this->quizManagementService->createQuizFromRequest($request);
    }
}
