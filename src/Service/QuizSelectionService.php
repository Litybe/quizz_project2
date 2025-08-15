<?php

namespace App\Service;

use App\Repository\QuizzRepository;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class QuizSelectionService
{
    private const QUIZZES_PER_PAGE = 10;

    public function __construct(
        private QuizzRepository $quizzRepository,
        private TagRepository $tagRepository,
        private PaginatorInterface $paginator
    ) {}

    public function getQuizSelectionData(Request $request): array
    {
        $selectedTagId = $request->query->get('tag');
        $searchTerm = $request->query->get('search');
        $page = $request->query->getInt('page', 1);

        $tags = $this->tagRepository->findAll();
        $quizzesQuery = $this->buildFilteredQuery($selectedTagId, $searchTerm);
        $quizzes = $this->paginator->paginate($quizzesQuery, $page, self::QUIZZES_PER_PAGE);

        return [
            'quizzes' => $quizzes,
            'tags' => $tags,
            'selectedTag' => $selectedTagId,
            'searchTerm' => $searchTerm
        ];
    }

    public function getTagsForQuizCreation(): array
    {
        return $this->tagRepository->findAllOrderedByName();
    }

    private function buildFilteredQuery(?string $selectedTagId, ?string $searchTerm)
    {
        $queryBuilder = $this->quizzRepository->createQueryBuilder('q');

        if ($selectedTagId) {
            $queryBuilder->join('q.tags', 't')
                ->andWhere('t.id = :tagId')
                ->setParameter('tagId', $selectedTagId);
        }

        if ($searchTerm) {
            $queryBuilder->andWhere('q.name LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        return $queryBuilder->getQuery();
    }
}
