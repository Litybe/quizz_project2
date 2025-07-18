<?php

namespace App\Controller;

use App\Repository\Http\FaceitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;


final class FaceitController extends AbstractController
{
    #[Route('/login/faceit', name: 'faceit_login_page')]
    public function faceitLoginPage(): Response
    {
        return $this->render('security/faceit_login.html.twig');
    }

    #[Route('/faceit-login', name: 'faceit_login2', methods: ['POST'])]
    public function faceitLogin(Request $request, FaceitRepository $faceitRepository, EntityManagerInterface $entityManager,Security $security,): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        $data = json_decode($request->getContent(), true);
        $faceitPseudo = $data['pseudoFaceit'] ?? null;
        $faceitDetail = json_decode($faceitRepository->GetFaceitPlayerDetailByPseudo($faceitPseudo), true);

        $user->setFaceitPseudo($faceitPseudo);
        $user->setFaceitPlayerId($faceitDetail['player_id']);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse($faceitDetail);
    }

    #[Route('/auth/faceit-login', name: 'faceit_login')]
    public function login(SessionInterface $session)
    {
        $clientId = $this->getParameter('faceit_client_id');
        $codeVerifier = $this->generatePkceCodeVerifier();
        $session->set('pkce_code_verifier', $codeVerifier);
        $codeChallenge = $this->generatePkceCodeChallenge($codeVerifier);

        $authUrl = "https://accounts.faceit.com/accounts?redirect_popup=true"
            . "&response_type=code"
            . "&client_id=" . urlencode($clientId)
            . "&code_challenge=" . urlencode($codeChallenge)
            . "&code_challenge_method=S256"
            . "&redirect_uri=" . urlencode("http://localhost:8000/auth/faceit-callback");

        return $this->redirect($authUrl);
    }

    private function generatePkceCodeVerifier()
    {
        return bin2hex(random_bytes(32));
    }

    private function generatePkceCodeChallenge($verifier)
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}
