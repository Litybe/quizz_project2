<?php

namespace App\EventListener;

use App\Service\SessionPersistenceService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionPersistenceListener
{
    private SessionPersistenceService $sessionPersistence;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        SessionPersistenceService $sessionPersistence,
        TokenStorageInterface $tokenStorage
    ) {
        $this->sessionPersistence = $sessionPersistence;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Prolonge automatiquement la session si l'utilisateur est connecté et a le "Remember Me"
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        // Vérifier si c'est une requête principale
        if (!$event->isMainRequest()) {
            return;
        }

        // Vérifier si l'utilisateur est connecté
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() !== 'anon.') {
            // Prolonger la session si le "Remember Me" est activé
            $this->sessionPersistence->extendSessionIfRememberMe();
        }
    }
}
