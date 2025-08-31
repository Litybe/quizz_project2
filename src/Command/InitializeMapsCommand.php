<?php

namespace App\Command;

use App\Entity\Map;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:initialize-maps',
    description: 'Initialize default CS:GO maps',
)]
class InitializeMapsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Initializing CS:GO Maps');

        $maps = [
            [
                'name' => 'Dust II',
                'description' => 'The most iconic CS:GO map, featuring a desert theme with A and B bomb sites.',
                'image' => '/assets/images/maps/de_dust2.svg'
            ],
            [
                'name' => 'Mirage',
                'description' => 'A Middle Eastern themed map with complex angles and strategic positions.',
                'image' => '/assets/images/maps/de_mirage.svg'
            ],
            [
                'name' => 'Inferno',
                'description' => 'An Italian village map with tight corridors and close-quarters combat.',
                'image' => '/assets/images/maps/de_inferno.svg'
            ],
            [
                'name' => 'Cache',
                'description' => 'A modern industrial map with clean sightlines and strategic positioning.',
                'image' => '/assets/images/maps/de_cache.svg'
            ],
            [
                'name' => 'Overpass',
                'description' => 'A complex map with multiple levels and unique angles.',
                'image' => '/assets/images/maps/de_overpass.svg'
            ],
            [
                'name' => 'Nuke',
                'description' => 'A nuclear power plant map with vertical gameplay elements.',
                'image' => '/assets/images/maps/de_nuke.svg'
            ],
            [
                'name' => 'Train',
                'description' => 'A train yard map with long sightlines and strategic positioning.',
                'image' => '/assets/images/maps/de_train.svg'
            ],
            [
                'name' => 'Cobblestone',
                'description' => 'A medieval-themed map with castle architecture and strategic positions.',
                'image' => '/assets/images/maps/de_cobblestone.svg'
            ]
        ];

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($maps as $mapData) {
            $slug = strtolower($this->slugger->slug($mapData['name']));
            
            $existingMap = $this->entityManager->getRepository(Map::class)->findOneBy(['slug' => $slug]);
            
            if ($existingMap) {
                $io->text("Map {$mapData['name']} already exists, skipping...");
                $updatedCount++;
                continue;
            }

            $map = new Map();
            $map->setName($mapData['name']);
            $map->setSlug($slug);
            $map->setDescription($mapData['description']);
            $map->setImage($mapData['image']);
            $map->setIsActive(true);

            $this->entityManager->persist($map);
            $createdCount++;
            
            $io->text("Created map: {$mapData['name']}");
        }

        $this->entityManager->flush();

        $io->success([
            "Maps initialization completed!",
            "Created: {$createdCount}",
            "Already existed: {$updatedCount}"
        ]);

        return Command::SUCCESS;
    }
}
