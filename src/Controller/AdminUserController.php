<?php

// src/Controller/AdminUserController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\AdminEditUserForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')] // Seuls les administrateurs peuvent accéder à cette page
class AdminUserController extends AbstractController
{
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
        EntityManagerInterface $entityManager
    ): Response {
        // Créer le formulaire pour modifier l'utilisateur
        $form = $this->createForm(AdminEditUserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
}
