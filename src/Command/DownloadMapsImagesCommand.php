<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:download-maps-images',
    description: 'Create CS:GO maps placeholder images',
)]
class DownloadMapsImagesCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('This command creates placeholder images for CS:GO maps.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating CS:GO Maps Placeholder Images');

        $maps = [
            'de_dust2' => 'Dust II',
            'de_mirage' => 'Mirage',
            'de_inferno' => 'Inferno',
            'de_nuke' => 'Nuke',
            'de_overpass' => 'Overpass',
            'de_train' => 'Train',
            'de_cache' => 'Cache',
            'de_cobblestone' => 'Cobblestone'
        ];

        $mapsDir = 'public/assets/images/maps';
        
        if (!is_dir($mapsDir)) {
            mkdir($mapsDir, 0755, true);
        }

        $io->progressStart(count($maps));
        $successCount = 0;

        foreach ($maps as $slug => $name) {
            try {
                $filename = $mapsDir . '/' . $slug . '.svg';
                $svgContent = $this->createMapPlaceholder($name, $slug);
                file_put_contents($filename, $svgContent);
                $successCount++;
                $io->text("✓ Created {$name} ({$slug})");
            } catch (\Exception $e) {
                $io->text("✗ Error creating {$name}: " . $e->getMessage());
            }
            
            $io->progressAdvance();
        }

        $io->progressFinish();

        if ($successCount > 0) {
            $io->success("Successfully created {$successCount} map placeholder images in {$mapsDir}/");
        } else {
            $io->error('No maps were created successfully.');
        }

        return Command::SUCCESS;
    }

    private function createMapPlaceholder(string $name, string $slug): string
    {
        $colors = [
            '#dc3545', '#007bff', '#28a745', '#ffc107', 
            '#6f42c1', '#fd7e14', '#20c997', '#e83e8c'
        ];
        
        $color = $colors[array_rand($colors)];
        
        return <<<SVG
<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$color};stop-opacity:0.8" />
            <stop offset="100%" style="stop-color:{$color};stop-opacity:0.6" />
        </linearGradient>
    </defs>
    
    <rect width="400" height="300" fill="url(#bg)" rx="10"/>
    
    <rect x="50" y="50" width="100" height="60" fill="rgba(255,255,255,0.2)" rx="5"/>
    <rect x="250" y="50" width="100" height="60" fill="rgba(255,255,255,0.2)" rx="5"/>
    
    <rect x="50" y="190" width="100" height="60" fill="rgba(255,255,255,0.2)" rx="5"/>
    <rect x="250" y="190" width="100" height="60" fill="rgba(255,255,255,0.2)" rx="5"/>
    
    <circle cx="200" cy="150" r="30" fill="rgba(255,255,255,0.3)"/>
    
    <text x="200" y="280" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="18" font-weight="bold">{$name}</text>
    <text x="200" y="295" text-anchor="middle" fill="rgba(255,255,255,0.7)" font-family="Arial, sans-serif" font-size="12">{$slug}</text>
</svg>
SVG;
    }
}
