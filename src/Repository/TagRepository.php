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
    
    private const CACHE_KEY_TAGS_ORDERED = 'tags_all_ordered_by_name';

    public function __construct(ManagerRegistry $registry, CacheInterface $cache, LoggerInterface $logger)
    {
        parent::__construct($registry, Tag::class);
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function findAllOrderedByName(): array
    {
        $cacheKey = self::CACHE_KEY_TAGS_ORDERED;

        // Récupérer les données du cache
        $data = $this->cache->get($cacheKey, function(ItemInterface $item) {
            $item->expiresAfter(3600);
            $result = $this->findBy([], ['name' => 'ASC']);

            // Logger le contenu qui vient d'être mis en cache
            $this->logger->info('Cache mis à jour pour la clé: ' . self::CACHE_KEY_TAGS_ORDERED, ['data' => $result]);

            return $result;
        });

        // Logger les données récupérées du cache
        $this->logger->info('Données récupérées du cache pour la clé: ' . $cacheKey, ['data' => $data]);

        return $data;
    }
    
    /**
     * Invalide le cache des tags
     */
    public function invalidateCache(): void
    {
        $this->cache->delete(self::CACHE_KEY_TAGS_ORDERED);
        $this->logger->info('Cache des tags invalidé');
    }
}