<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminEditUserForm;
use App\Service\UserManagementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    private const SUCCESS_MESSAGES = [
        'updated' => 'Les informations de l\'utilisateur ont été mises à jour avec succès !'
    ];

    public function __construct(
        private UserManagementService $userManagementService
    ) {}

    /**
     * Affiche la liste de tous les utilisateurs
     */
    #[Route('/admin/users', name: 'admin_users')]
    public function index(): Response
    {
        $users = $this->userManagementService->getAllUsers();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Modifie un utilisateur existant
     */
    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request): Response
    {
        $form = $this->createForm(AdminEditUserForm::class, $user);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            try {
                $this->userManagementService->updateUser($user);
                $this->addFlash('success', self::SUCCESS_MESSAGES['updated']);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour.');
            }
            
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
