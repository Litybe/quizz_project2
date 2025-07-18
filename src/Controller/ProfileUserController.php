<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileForm;
use App\Repository\ScoreRepository;
use App\Repository\UserRepository;
use App\Utils\StringUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileUserController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function index(
        Security $security,
        ScoreRepository $scoreRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        UserRepository $userRepository
    ): Response {
        // Vérifier si l'utilisateur est connecté
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_test');
        }

        // Récupérer les meilleurs scores de l'utilisateur pour chaque quiz
        $bestScores = $scoreRepository->findBestScoresByUser($user);
        $isFaceit = StringUtils::isStringEmpty($user->getFaceitPseudo()) && StringUtils::isStringEmpty($user->getFaceitPlayerId());

        // Créer le formulaire
        $originalUserData = clone $user; // Sauvegarder l'état original de l'utilisateur
        $form = $this->createForm(UserProfileForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les nouvelles valeurs du formulaire
            $newPseudo = $form->get('pseudo')->getData();
            $newEmail = $form->get('email')->getData();

            // Vérifier si le pseudo ou l'email existe déjà pour un autre utilisateur
            $existingUserWithPseudo = $userRepository->findOneBy(['Pseudo' => $newPseudo]);
            $existingUserWithEmail = $userRepository->findOneBy(['Email' => $newEmail]);
            $isOk = true;
            // Vérifier si le pseudo est déjà utilisé par un autre utilisateur
            if ($existingUserWithPseudo && $existingUserWithPseudo->getId() !== $user->getId()) {
                $this->addFlash('error', 'Ce pseudo est déjà utilisé par un autre utilisateur.');
                // Restaurer les valeurs originales
                $user->setPseudo($originalUserData->getPseudo());
                $user->setEmail($originalUserData->getEmail());
                $isOk = false;
            }
            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            if ($existingUserWithEmail && $existingUserWithEmail->getId() !== $user->getId()) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée par un autre utilisateur.');
                // Restaurer les valeurs originales
                $user->setPseudo($originalUserData->getPseudo());
                $user->setEmail($originalUserData->getEmail());
                $isOk = false;
            }

            if($isOk)
            {
                // Si tout est valide, sauvegarder les modifications
                try {
                    $user->setPseudo($newPseudo);
                    $user->setEmail($newEmail);
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Vos informations ont été mises à jour avec succès!');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de vos informations.');
                    // Restaurer les valeurs originales
                    $user->setPseudo($originalUserData->getPseudo());
                    $user->setEmail($originalUserData->getEmail());
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
