<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserManagementService
{
    private const ERROR_MESSAGES = [
        'pseudo_already_exists' => 'Ce pseudo est déjà utilisé par un autre utilisateur.',
        'email_already_exists' => 'Cette adresse email est déjà utilisée par un autre utilisateur.',
        'update_failed' => 'Une erreur est survenue lors de la mise à jour de vos informations.'
    ];

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    public function updateUserProfile(User $user, string $newPseudo, string $newEmail): array
    {
        $originalUserData = clone $user;
        $errors = [];

        // Check if pseudo is already used by another user
        $existingUserWithPseudo = $this->userRepository->findOneBy(['Pseudo' => $newPseudo]);
        if ($existingUserWithPseudo && $existingUserWithPseudo->getId() !== $user->getId()) {
            $errors[] = self::ERROR_MESSAGES['pseudo_already_exists'];
        }

        // Check if email is already used by another user
        $existingUserWithEmail = $this->userRepository->findOneBy(['Email' => $newEmail]);
        if ($existingUserWithEmail && $existingUserWithEmail->getId() !== $user->getId()) {
            $errors[] = self::ERROR_MESSAGES['email_already_exists'];
        }

        if (empty($errors)) {
            try {
                $user->setPseudo($newPseudo);
                $user->setEmail($newEmail);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->logger->info('User profile updated successfully', ['userId' => $user->getId()]);
                
                return ['success' => true, 'errors' => []];
            } catch (\Exception $e) {
                $this->logger->error('Failed to update user profile', [
                    'userId' => $user->getId(),
                    'error' => $e->getMessage()
                ]);
                
                // Restore original values
                $user->setPseudo($originalUserData->getPseudo());
                $user->setEmail($originalUserData->getEmail());
                
                return ['success' => false, 'errors' => [self::ERROR_MESSAGES['update_failed']]];
            }
        }

        return ['success' => false, 'errors' => $errors];
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function updateUser(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $this->logger->info('User updated by admin', ['userId' => $user->getId()]);
    }
}
