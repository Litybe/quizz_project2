<?php

namespace App\Controller;

use App\Entity\Map;
use App\Entity\Strategy;
use App\Service\StrategyService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/strategies')]
class StrategyController extends AbstractController
{
    public function __construct(
        private StrategyService $strategyService,
        private ManagerRegistry $doctrine
    ) {}

    #[Route('/', name: 'app_strategy_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $mapId = $request->query->get('map');
        $side = $request->query->get('side');
        $difficulty = $request->query->get('difficulty');

        $map = null;
        if ($mapId) {
            $map = $this->doctrine->getRepository(Map::class)->find($mapId);
        }

        $strategies = $this->strategyService->searchStrategies($search, $map, $side, $difficulty);
        $maps = $this->strategyService->getActiveMaps();
        $recentStrategies = $this->strategyService->getRecentStrategies(5);
        $statistics = $this->strategyService->getStrategyStatistics();

        return $this->render('strategy/index.html.twig', [
            'strategies' => $strategies,
            'maps' => $maps,
            'recentStrategies' => $recentStrategies,
            'statistics' => $statistics,
            'search' => $search,
            'selectedMap' => $map,
            'selectedSide' => $side,
            'selectedDifficulty' => $difficulty,
        ]);
    }

    #[Route('/map/{slug}', name: 'app_strategy_by_map', methods: ['GET'])]
    public function byMap(string $slug): Response
    {
        $map = $this->doctrine->getRepository(Map::class)->findOneBy(['slug' => $slug]);
        
        if (!$map) {
            throw $this->createNotFoundException('Map not found');
        }

        $strategies = $this->strategyService->getStrategiesByMap($map);
        $maps = $this->strategyService->getActiveMaps();

        return $this->render('strategy/by_map.html.twig', [
            'map' => $map,
            'strategies' => $strategies,
            'maps' => $maps,
        ]);
    }

    #[Route('/side/{side}', name: 'app_strategy_by_side', methods: ['GET'])]
    public function bySide(string $side): Response
    {
        if (!in_array($side, ['T', 'CT'])) {
            throw $this->createNotFoundException('Invalid side');
        }

        $strategies = $this->strategyService->getStrategiesBySide($side);
        $maps = $this->strategyService->getActiveMaps();

        return $this->render('strategy/by_side.html.twig', [
            'side' => $side,
            'strategies' => $strategies,
            'maps' => $maps,
        ]);
    }

    #[Route('/difficulty/{difficulty}', name: 'app_strategy_by_difficulty', methods: ['GET'])]
    public function byDifficulty(string $difficulty): Response
    {
        if (!in_array($difficulty, ['Easy', 'Medium', 'Hard'])) {
            throw $this->createNotFoundException('Invalid difficulty');
        }

        $strategies = $this->strategyService->getStrategiesByDifficulty($difficulty);
        $maps = $this->strategyService->getActiveMaps();

        return $this->render('strategy/by_difficulty.html.twig', [
            'difficulty' => $difficulty,
            'strategies' => $strategies,
            'maps' => $maps,
        ]);
    }

    #[Route('/new', name: 'app_strategy_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = [
                'title' => $request->request->get('title'),
                'description' => $request->request->get('description'),
                'side' => $request->request->get('side'),
                'difficulty' => $request->request->get('difficulty'),
                'execution' => $request->request->get('execution'),
                'counters' => $request->request->get('counters'),
                'mapId' => $request->request->get('map'),
                'isPublic' => $request->request->get('isPublic', true),
            ];

            $strategy = $this->strategyService->createStrategy($data, $this->getUser());

            // Handle positions if provided
            $positions = $request->request->all('positions');
            if (!empty($positions)) {
                $this->strategyService->addPositionsToStrategy($strategy, $positions);
            }

            $this->addFlash('success', 'Strategy created successfully!');
            return $this->redirectToRoute('app_strategy_show', ['id' => $strategy->getId()]);
        }

        $maps = $this->strategyService->getActiveMaps();

        return $this->render('strategy/new.html.twig', [
            'maps' => $maps,
        ]);
    }

    #[Route('/{id}', name: 'app_strategy_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $strategy = $this->doctrine->getRepository(Strategy::class)->find($id);
        
        if (!$strategy) {
            throw $this->createNotFoundException('Strategy not found');
        }
        
        if (!$strategy->isPublic() && $strategy->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('This strategy is private');
        }

        $positions = $this->strategyService->getPositionsForStrategy($strategy);
        $relatedStrategies = $this->strategyService->getStrategiesByMap($strategy->getMap());

        return $this->render('strategy/show.html.twig', [
            'strategy' => $strategy,
            'positions' => $positions,
            'relatedStrategies' => $relatedStrategies,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_strategy_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, int $id): Response
    {
        $strategy = $this->doctrine->getRepository(Strategy::class)->find($id);
        
        if (!$strategy) {
            throw $this->createNotFoundException('Strategy not found');
        }
        
        if ($strategy->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own strategies');
        }

        if ($request->isMethod('POST')) {
            $data = [
                'title' => $request->request->get('title'),
                'description' => $request->request->get('description'),
                'side' => $request->request->get('side'),
                'difficulty' => $request->request->get('difficulty'),
                'execution' => $request->request->get('execution'),
                'counters' => $request->request->get('counters'),
                'mapId' => $request->request->get('map'),
                'isPublic' => $request->request->get('isPublic', true),
            ];

            $this->strategyService->updateStrategy($strategy, $data);

            $this->addFlash('success', 'Strategy updated successfully!');
            return $this->redirectToRoute('app_strategy_show', ['id' => $strategy->getId()]);
        }

        $maps = $this->strategyService->getActiveMaps();
        $positions = $this->strategyService->getPositionsForStrategy($strategy);

        return $this->render('strategy/edit.html.twig', [
            'strategy' => $strategy,
            'maps' => $maps,
            'positions' => $positions,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_strategy_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, int $id): Response
    {
        $strategy = $this->doctrine->getRepository(Strategy::class)->find($id);
        
        if (!$strategy) {
            throw $this->createNotFoundException('Strategy not found');
        }
        
        if ($strategy->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own strategies');
        }

        if ($this->isCsrfTokenValid('delete' . $strategy->getId(), $request->request->get('_token'))) {
            $this->strategyService->deleteStrategy($strategy);
            $this->addFlash('success', 'Strategy deleted successfully!');
        }

        return $this->redirectToRoute('app_strategy_index');
    }

    #[Route('/maps', name: 'app_strategy_maps', methods: ['GET'])]
    public function maps(): Response
    {
        $maps = $this->strategyService->getMapsWithStrategyCount();

        return $this->render('strategy/maps.html.twig', [
            'maps' => $maps,
        ]);
    }
}
