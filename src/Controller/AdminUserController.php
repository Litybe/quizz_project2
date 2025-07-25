<?php

// src/Controller/AdminUserController.php
namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserCourseStatus;
use App\Form\AdminEditUserForm;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')] // Seuls les administrateurs peuvent accéder à cette page
class AdminUserController extends AbstractController
{
    private LoggerInterface $_logger;
    function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }
    #[Route('/admin/users', name: 'admin_users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        // Créer le formulaire pour modifier l'utilisateur
        $form = $this->createForm(AdminEditUserForm::class, $user);
        $form->handleRequest($request);

        if ($request->isMethod('POST')){
            // Sauvegarder les modifications
            $entityManager->persist($user);
            $entityManager->flush();

            //$this->addFlash('success', 'Les informations de l\'utilisateur ont été mises à jour avec succès!');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /*#[Route('/admin/users/{id}/edit', name: 'admin_user_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $pseudo = $request->request->get('pseudo');
            $email = $request->request->get('email');
            $roles = $request->request->all('roles');

            $this->_logger->error("TOTO: " . json_encode($roles));

            // Valider les données
            $errors = [];
            if (empty($pseudo)) {
                $errors['pseudo'] = 'Le pseudo ne peut pas être vide';
            }
            if (empty($email)) {
                $errors['email'] = 'L\'email ne peut pas être vide';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas une adresse email valide';
            }

            if (empty($errors)) {
                // Mettre à jour l'utilisateur
                $user->setPseudo($pseudo);
                $user->setEmail($email);
                $user->setRoles($roles);

                // Sauvegarder les modifications
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Les informations de l\'utilisateur ont été mises à jour avec succès!');
                return $this->redirectToRoute('admin_users');
            } else {
                // Afficher les erreurs
                foreach ($errors as $field => $message) {
                    $this->addFlash('error', $message);
                }
            }
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }*/
}
