<?php

namespace App\Service;

use App\Entity\Strategy;
use App\Entity\Map;
use App\Entity\User;
use App\Entity\StrategyPosition;
use App\Repository\StrategyRepository;
use App\Repository\MapRepository;
use App\Repository\StrategyPositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class StrategyService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StrategyRepository $strategyRepository,
        private MapRepository $mapRepository,
        private StrategyPositionRepository $positionRepository,
        private SluggerInterface $slugger
    ) {}

    /**
     * Get all active maps
     */
    public function getActiveMaps(): array
    {
        return $this->mapRepository->findActiveMaps();
    }

    /**
     * Get maps with strategy count
     */
    public function getMapsWithStrategyCount(): array
    {
        return $this->mapRepository->findMapsWithStrategyCount();
    }

    /**
     * Get strategies for a specific map
     */
    public function getStrategiesByMap(Map $map): array
    {
        return $this->strategyRepository->findByMap($map);
    }

    /**
     * Get strategies by side
     */
    public function getStrategiesBySide(string $side): array
    {
        return $this->strategyRepository->findBySide($side);
    }

    /**
     * Get strategies by difficulty
     */
    public function getStrategiesByDifficulty(string $difficulty): array
    {
        return $this->strategyRepository->findByDifficulty($difficulty);
    }

    /**
     * Search strategies
     */
    public function searchStrategies(string $search, ?Map $map = null, ?string $side = null, ?string $difficulty = null): array
    {
        return $this->strategyRepository->searchStrategies($search, $map, $side, $difficulty);
    }

    /**
     * Get recent strategies
     */
    public function getRecentStrategies(int $limit = 10): array
    {
        return $this->strategyRepository->findRecentStrategies($limit);
    }

    /**
     * Create a new strategy
     */
    public function createStrategy(array $data, User $author): Strategy
    {
        $strategy = new Strategy();
        $strategy->setTitle($data['title']);
        $strategy->setDescription($data['description']);
        $strategy->setSide($data['side']);
        $strategy->setDifficulty($data['difficulty']);
        $strategy->setExecution($data['execution'] ?? null);
        $strategy->setCounters($data['counters'] ?? null);
        $strategy->setIsPublic($data['isPublic'] ?? true);
        $strategy->setAuthor($author);

        // Set map
        if (isset($data['mapId'])) {
            $map = $this->mapRepository->find($data['mapId']);
            if ($map) {
                $strategy->setMap($map);
            }
        }

        // Add tags if provided
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagId) {
                $tag = $this->entityManager->getRepository(\App\Entity\Tag::class)->find($tagId);
                if ($tag) {
                    $strategy->addTag($tag);
                }
            }
        }

        $this->entityManager->persist($strategy);
        $this->entityManager->flush();

        return $strategy;
    }

    /**
     * Update a strategy
     */
    public function updateStrategy(Strategy $strategy, array $data): Strategy
    {
        if (isset($data['title'])) {
            $strategy->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $strategy->setDescription($data['description']);
        }
        if (isset($data['side'])) {
            $strategy->setSide($data['side']);
        }
        if (isset($data['difficulty'])) {
            $strategy->setDifficulty($data['difficulty']);
        }
        if (isset($data['execution'])) {
            $strategy->setExecution($data['execution']);
        }
        if (isset($data['counters'])) {
            $strategy->setCounters($data['counters']);
        }
        if (isset($data['isPublic'])) {
            $strategy->setIsPublic($data['isPublic']);
        }

        // Update map if provided
        if (isset($data['mapId'])) {
            $map = $this->mapRepository->find($data['mapId']);
            if ($map) {
                $strategy->setMap($map);
            }
        }

        $this->entityManager->flush();

        return $strategy;
    }

    /**
     * Add positions to a strategy
     */
    public function addPositionsToStrategy(Strategy $strategy, array $positions): void
    {
        foreach ($positions as $positionData) {
            $position = new StrategyPosition();
            $position->setPlayerNumber($positionData['playerNumber']);
            $position->setPositionName($positionData['positionName']);
            $position->setDescription($positionData['description'] ?? null);
            $position->setX($positionData['x']);
            $position->setY($positionData['y']);
            $position->setRole($positionData['role'] ?? null);
            $position->setInstructions($positionData['instructions'] ?? null);
            $position->setStrategy($strategy);

            $this->entityManager->persist($position);
        }

        $this->entityManager->flush();
    }

    /**
     * Get positions for a strategy
     */
    public function getPositionsForStrategy(Strategy $strategy): array
    {
        return $this->positionRepository->findByStrategy($strategy);
    }

    /**
     * Delete a strategy
     */
    public function deleteStrategy(Strategy $strategy): void
    {
        $this->entityManager->remove($strategy);
        $this->entityManager->flush();
    }

    /**
     * Get strategy statistics
     */
    public function getStrategyStatistics(): array
    {
        $totalStrategies = $this->strategyRepository->count(['isPublic' => true]);
        $totalMaps = $this->mapRepository->count(['isActive' => true]);
        
        $sideStats = [
            'T' => $this->strategyRepository->count(['side' => 'T', 'isPublic' => true]),
            'CT' => $this->strategyRepository->count(['side' => 'CT', 'isPublic' => true])
        ];

        $difficultyStats = [
            'Easy' => $this->strategyRepository->count(['difficulty' => 'Easy', 'isPublic' => true]),
            'Medium' => $this->strategyRepository->count(['difficulty' => 'Medium', 'isPublic' => true]),
            'Hard' => $this->strategyRepository->count(['difficulty' => 'Hard', 'isPublic' => true])
        ];

        return [
            'totalStrategies' => $totalStrategies,
            'totalMaps' => $totalMaps,
            'sideStats' => $sideStats,
            'difficultyStats' => $difficultyStats
        ];
    }
}
