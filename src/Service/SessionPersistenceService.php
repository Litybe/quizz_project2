<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Entity\User;

class SessionPersistenceService
{
    private TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    /**
     * Active le "Remember Me" pour un utilisateur
     */
    public function enableRememberMe(User $user): void
    {
        // Créer un token avec le "Remember Me" activé
        $token = new UsernamePasswordToken(
            $user,
            'main',
            $user->getRoles()
        );

        // Stocker le token
        $this->tokenStorage->setToken($token);
        
        // Marquer la session comme "Remember Me"
        $session = $this->requestStack->getSession();
        $session->set('_remember_me', true);
        $session->set('_security_main', serialize($token));
        
        // Définir la durée de vie de la session
        $session->migrate(true, 2592000); // 30 jours
    }

    /**
     * Vérifie si l'utilisateur a le "Remember Me" activé
     */
    public function isRememberMeEnabled(): bool
    {
        $session = $this->requestStack->getSession();
        return $session->get('_remember_me', false);
    }

    /**
     * Prolonge la session si le "Remember Me" est activé
     */
    public function extendSessionIfRememberMe(): void
    {
        if ($this->isRememberMeEnabled()) {
            // Prolonger la session de 30 jours
            $session = $this->requestStack->getSession();
            $session->migrate(true, 2592000);
        }
    }

    /**
     * Nettoie les données de session lors de la déconnexion
     */
    public function clearRememberMe(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('_remember_me');
        $session->remove('_security_main');
    }
}
