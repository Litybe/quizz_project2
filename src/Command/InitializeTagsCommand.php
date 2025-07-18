<?php
namespace App\Command;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:initialize-tags')]
class InitializeTagsCommand extends Command
{
    // Nom valide pour la commande
    protected static string $defaultName = 'app:initialize-tags';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Initializes the default tags for courses')
            ->setHelp('Cette commande initialise les tags par défaut pour les cours');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tagNames = [
            'Dust_2', 'Mirage', 'Overpass', 'Ancient',
            'Anubis', 'Inferno', 'Nuke', 'Train', 'Cache', 'CT', 'T'
        ];

        $existingTags = $this->em->getRepository(Tag::class)->findAll();
        $existingTagNames = array_map(fn(Tag $tag) => $tag->getName(), $existingTags);

        $createdCount = 0;
        $existingCount = 0;

        foreach ($tagNames as $name) {
            if (!in_array($name, $existingTagNames)) {
                $tag = new Tag();
                $tag->setName($name);
                $this->em->persist($tag);
                $createdCount++;
                $io->writeln("Création du tag : <info>$name</info>");
            } else {
                $existingCount++;
                $io->writeln("Le tag existe déjà : <comment>$name</comment>");
            }
        }

        $this->em->flush();

        $io->success(sprintf(
            'Initialisation des tags terminée avec succès ! %d tags créés, %d tags existants.',
            $createdCount,
            $existingCount
        ));

        return Command::SUCCESS;
    }
}
