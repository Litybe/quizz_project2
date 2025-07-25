<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    private $cache;
    private $logger;

    public function __construct(ManagerRegistry $registry, CacheInterface $cache, LoggerInterface $logger)
    {
        parent::__construct($registry, Tag::class);
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function findAllOrderedByName(): array
    {
        $cacheKey = 'all_objects_ordered_by_name';

        // Récupérer les données du cache
        $data = $this->cache->get($cacheKey, function(ItemInterface $item) {
            $cacheKey = 'all_objects_ordered_by_name';

            $item->expiresAfter(3600);
            $result = $this->findBy([], ['name' => 'ASC']);

            // Logger le contenu qui vient d'être mis en cache
            $this->logger->info('Cache mis à jour pour la clé: ' . $cacheKey, ['data' => $result]);

            return $result;
        });

        // Logger les données récupérées du cache
        $this->logger->info('Données récupérées du cache pour la clé: ' . $cacheKey, ['data' => $data]);

        return $data;
    }
}