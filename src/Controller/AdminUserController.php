<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminEditUserForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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

    private const ERROR_MESSAGES = [
        'validation_failed' => 'Erreur de validation des données.'
    ];

    private LoggerInterface $_logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Affiche la liste de tous les utilisateurs
     */
    #[Route('/admin/users', name: 'admin_users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Modifie un utilisateur existant
     */
    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit')]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(AdminEditUserForm::class, $user);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            // Sauvegarder les modifications
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', self::SUCCESS_MESSAGES['updated']);
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
