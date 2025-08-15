<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileForm;
use App\Repository\ScoreRepository;
use App\Service\UserManagementService;
use App\Utils\StringUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileUserController extends AbstractController
{
    public function __construct(
        private UserManagementService $userManagementService
    ) {}

    #[Route('/profile', name: 'profile')]
    public function index(
        Security $security,
        ScoreRepository $scoreRepository,
        Request $request
    ): Response {
        // Vérifier si l'utilisateur est connecté
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        // Récupérer les meilleurs scores de l'utilisateur pour chaque quiz
        $bestScores = $scoreRepository->findBestScoresByUser($user);
        $isFaceit = StringUtils::isStringEmpty($user->getFaceitPseudo()) && StringUtils::isStringEmpty($user->getFaceitPlayerId());

        // Créer le formulaire
        $form = $this->createForm(UserProfileForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPseudo = $form->get('pseudo')->getData();
            $newEmail = $form->get('email')->getData();

            $result = $this->userManagementService->updateUserProfile($user, $newPseudo, $newEmail);
            
            if ($result['success']) {
                $this->addFlash('success', 'Vos informations ont été mises à jour avec succès!');
            } else {
                foreach ($result['errors'] as $error) {
                    $this->addFlash('error', $error);
                }
            }
            
            return $this->redirectToRoute('profile');
        }

        return $this->render('profile_user/index.html.twig', [
            'user' => $user,
            'isFaceit' => $isFaceit,
            'bestScores' => $bestScores,
            'form' => $form->createView(),
        ]);
    }
}
