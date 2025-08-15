<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserManagementServiceTest extends TestCase
{
    private UserManagementService $userManagementService;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->userManagementService = new UserManagementService(
            $this->userRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testUpdateUserProfileSuccess(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getPseudo')->willReturn('oldPseudo');
        $user->method('getEmail')->willReturn('old@email.com');

        $newPseudo = 'newPseudo';
        $newEmail = 'new@email.com';

        $this->userRepository->method('findOneBy')->willReturnMap([
            [['Pseudo' => $newPseudo], null],
            [['Email' => $newEmail], null]
        ]);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');
        $this->logger->expects($this->once())->method('info');

        $user->expects($this->once())->method('setPseudo')->with($newPseudo);
        $user->expects($this->once())->method('setEmail')->with($newEmail);

        // Act
        $result = $this->userManagementService->updateUserProfile($user, $newPseudo, $newEmail);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['errors']);
    }

    public function testUpdateUserProfileWithPseudoAlreadyExists(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $existingUser = $this->createMock(User::class);
        $existingUser->method('getId')->willReturn(2);

        $newPseudo = 'existingPseudo';
        $newEmail = 'new@email.com';

        $this->userRepository->method('findOneBy')->willReturnMap([
            [['Pseudo' => $newPseudo], $existingUser],
            [['Email' => $newEmail], null]
        ]);

        // Act
        $result = $this->userManagementService->updateUserProfile($user, $newPseudo, $newEmail);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertContains('Ce pseudo est déjà utilisé par un autre utilisateur.', $result['errors']);
    }

    public function testUpdateUserProfileWithEmailAlreadyExists(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $existingUser = $this->createMock(User::class);
        $existingUser->method('getId')->willReturn(2);

        $newPseudo = 'newPseudo';
        $newEmail = 'existing@email.com';

        $this->userRepository->method('findOneBy')->willReturnMap([
            [['Pseudo' => $newPseudo], null],
            [['Email' => $newEmail], $existingUser]
        ]);

        // Act
        $result = $this->userManagementService->updateUserProfile($user, $newPseudo, $newEmail);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertContains('Cette adresse email est déjà utilisée par un autre utilisateur.', $result['errors']);
    }

    public function testUpdateUserProfileWithDatabaseError(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getPseudo')->willReturn('oldPseudo');
        $user->method('getEmail')->willReturn('old@email.com');

        $newPseudo = 'newPseudo';
        $newEmail = 'new@email.com';

        $this->userRepository->method('findOneBy')->willReturnMap([
            [['Pseudo' => $newPseudo], null],
            [['Email' => $newEmail], null]
        ]);

        $this->entityManager->method('persist')->willThrowException(new \Exception('Database error'));
        $this->logger->expects($this->once())->method('error');

        $user->expects($this->once())->method('setPseudo')->with($newPseudo);
        $user->expects($this->once())->method('setEmail')->with($newEmail);

        // Act
        $result = $this->userManagementService->updateUserProfile($user, $newPseudo, $newEmail);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertContains('Une erreur est survenue lors de la mise à jour de vos informations.', $result['errors']);
    }

    public function testUpdateUserProfileWithSameUser(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $newPseudo = 'newPseudo';
        $newEmail = 'new@email.com';

        // Même utilisateur trouvé pour le pseudo et l'email
        $this->userRepository->method('findOneBy')->willReturnMap([
            [['Pseudo' => $newPseudo], $user],
            [['Email' => $newEmail], $user]
        ]);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');
        $this->logger->expects($this->once())->method('info');

        $user->expects($this->once())->method('setPseudo')->with($newPseudo);
        $user->expects($this->once())->method('setEmail')->with($newEmail);

        // Act
        $result = $this->userManagementService->updateUserProfile($user, $newPseudo, $newEmail);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEmpty($result['errors']);
    }

    public function testGetAllUsers(): void
    {
        // Arrange
        $users = [
            $this->createMock(User::class),
            $this->createMock(User::class),
            $this->createMock(User::class)
        ];

        $this->userRepository->method('findAll')->willReturn($users);

        // Act
        $result = $this->userManagementService->getAllUsers();

        // Assert
        $this->assertSame($users, $result);
    }

    public function testUpdateUser(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');
        $this->logger->expects($this->once())->method('info');

        // Act
        $this->userManagementService->updateUser($user);

        // Assert
        $this->assertTrue(true); // Si on arrive ici, pas d'exception
    }
}
