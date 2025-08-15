<?php

namespace App\Tests\Service;

use App\Repository\QuizzRepository;
use App\Repository\TagRepository;
use App\Service\QuizSelectionService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QuizSelectionServiceTest extends TestCase
{
    private QuizSelectionService $quizSelectionService;
    private QuizzRepository $quizzRepository;
    private TagRepository $tagRepository;
    private PaginatorInterface $paginator;

    protected function setUp(): void
    {
        $this->quizzRepository = $this->createMock(QuizzRepository::class);
        $this->tagRepository = $this->createMock(TagRepository::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);

        $this->quizSelectionService = new QuizSelectionService(
            $this->quizzRepository,
            $this->tagRepository,
            $this->paginator
        );
    }

    public function testGetQuizSelectionDataWithoutFilters(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('query')->willReturn($this->createMock(\Symfony\Component\HttpFoundation\ParameterBag::class));
        $request->query->method('get')->willReturnMap([
            ['tag', null, null],
            ['search', null, null]
        ]);
        $request->query->method('getInt')->with('page', 1)->willReturn(1);

        $tags = ['tag1', 'tag2'];
        $query = $this->createMock(Query::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->tagRepository->method('findAll')->willReturn($tags);
        $this->quizzRepository->method('createQueryBuilder')->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);
        $this->paginator->method('paginate')->willReturn($pagination);

        // Act
        $result = $this->quizSelectionService->getQuizSelectionData($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('quizzes', $result);
        $this->assertArrayHasKey('tags', $result);
        $this->assertArrayHasKey('selectedTag', $result);
        $this->assertArrayHasKey('searchTerm', $result);
        $this->assertSame($pagination, $result['quizzes']);
        $this->assertSame($tags, $result['tags']);
        $this->assertNull($result['selectedTag']);
        $this->assertNull($result['searchTerm']);
    }

    public function testGetQuizSelectionDataWithTagFilter(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('query')->willReturn($this->createMock(\Symfony\Component\HttpFoundation\ParameterBag::class));
        $request->query->method('get')->willReturnMap([
            ['tag', null, '1'],
            ['search', null, null]
        ]);
        $request->query->method('getInt')->with('page', 1)->willReturn(1);

        $tags = ['tag1', 'tag2'];
        $query = $this->createMock(Query::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->tagRepository->method('findAll')->willReturn($tags);
        $this->quizzRepository->method('createQueryBuilder')->willReturn($queryBuilder);
        $queryBuilder->method('join')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $this->paginator->method('paginate')->willReturn($pagination);

        // Act
        $result = $this->quizSelectionService->getQuizSelectionData($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertSame('1', $result['selectedTag']);
        $this->assertNull($result['searchTerm']);
    }

    public function testGetQuizSelectionDataWithSearchFilter(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('query')->willReturn($this->createMock(\Symfony\Component\HttpFoundation\ParameterBag::class));
        $request->query->method('get')->willReturnMap([
            ['tag', null, null],
            ['search', null, 'test']
        ]);
        $request->query->method('getInt')->with('page', 1)->willReturn(1);

        $tags = ['tag1', 'tag2'];
        $query = $this->createMock(Query::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->tagRepository->method('findAll')->willReturn($tags);
        $this->quizzRepository->method('createQueryBuilder')->willReturn($queryBuilder);
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $this->paginator->method('paginate')->willReturn($pagination);

        // Act
        $result = $this->quizSelectionService->getQuizSelectionData($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertNull($result['selectedTag']);
        $this->assertSame('test', $result['searchTerm']);
    }

    public function testGetQuizSelectionDataWithBothFilters(): void
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $request->method('query')->willReturn($this->createMock(\Symfony\Component\HttpFoundation\ParameterBag::class));
        $request->query->method('get')->willReturnMap([
            ['tag', null, '1'],
            ['search', null, 'test']
        ]);
        $request->query->method('getInt')->with('page', 1)->willReturn(2);

        $tags = ['tag1', 'tag2'];
        $query = $this->createMock(Query::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $pagination = $this->createMock(PaginationInterface::class);

        $this->tagRepository->method('findAll')->willReturn($tags);
        $this->quizzRepository->method('createQueryBuilder')->willReturn($queryBuilder);
        $queryBuilder->method('join')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $this->paginator->method('paginate')->willReturn($pagination);

        // Act
        $result = $this->quizSelectionService->getQuizSelectionData($request);

        // Assert
        $this->assertIsArray($result);
        $this->assertSame('1', $result['selectedTag']);
        $this->assertSame('test', $result['searchTerm']);
    }

    public function testGetTagsForQuizCreation(): void
    {
        // Arrange
        $tags = ['tag1', 'tag2', 'tag3'];
        $this->tagRepository->method('findAllOrderedByName')->willReturn($tags);

        // Act
        $result = $this->quizSelectionService->getTagsForQuizCreation();

        // Assert
        $this->assertSame($tags, $result);
    }
}
