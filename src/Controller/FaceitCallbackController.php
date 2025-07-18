<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class FaceitCallbackController extends AbstractController
{

    private LoggerInterface $logger;
    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/auth/faceit-callback', name: 'faceit_callback')]
    public function callback(Request $request, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $code = $request->query->get('code');
        $codeVerifier = $session->get('pkce_code_verifier');
        $clientId = $this->getParameter('faceit_client_id');
        $clientSecret = $this->getParameter('faceit_client_secret');
        $redirectUri = "http://localhost:8000/auth/faceit-callback";
        $tokenEndpoint = "https://api.faceit.com/auth/v1/oauth/token";

        $postFields = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'code_verifier' => $codeVerifier,
            'client_id' => $clientId
        ]);

        $authHeader = "Authorization: Basic " . base64_encode("$clientId:$clientSecret");

        $ch = curl_init($tokenEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [$authHeader, 'Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER ,true);
        curl_setopt($ch,CURLOPT_ENCODING , '');
        curl_setopt($ch,CURLOPT_MAXREDIRS , 10);
        curl_setopt($ch,CURLOPT_TIMEOUT , 0);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION , true);
        curl_setopt($ch,CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($ch);
        curl_close($ch);

        $tokenInfo = json_decode($response, true);

        if (isset($tokenInfo['access_token'])) {
            $accessToken = $tokenInfo['access_token'];

            // Utiliser l'access_token pour récupérer les informations de l'utilisateur
            $userInfo = $this->fetchUserInfo($accessToken);
            $this->logger->info("TITIUser info: " . json_encode($userInfo));
            // Créer ou mettre à jour l'utilisateur dans la base de données
            $user = $this->getOrCreateUser($userInfo, $entityManager);

            // Authentifier l'utilisateur dans Symfony
            $token = new UsernamePasswordToken($user,  'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->redirectToRoute('profile');
        } else {
            // Gérer l'erreur
            return $this->redirectToRoute('login');
        }
    }

    private function fetchUserInfo($accessToken)
    {
        $userInfoEndpoint = "https://api.faceit.com/auth/v1/resources/userinfo";
        $ch = curl_init($userInfoEndpoint);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER ,true);
        curl_setopt($ch,CURLOPT_ENCODING , '');
        curl_setopt($ch,CURLOPT_MAXREDIRS , 10);
        curl_setopt($ch,CURLOPT_TIMEOUT , 0);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION , true);
        curl_setopt($ch,CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken"
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function getOrCreateUser(array $userInfo, EntityManagerInterface $entityManager)
    {
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['FaceitPlayerId' => $userInfo['guid']]);
        $this->logger->info("TOTOUser : " . json_encode($user));

        if (!$user) {
            $user = new User();
            $user->setPseudo($userInfo['nickname']);
            $user->setFaceitPseudo($userInfo['nickname']);
            $user->setFaceitPlayerId($userInfo['guid']);
            $user->setEmail($userInfo['email'] ?? 'default@example.com');
            $user->setPassword(bin2hex(random_bytes(16))); // Mot de passe aléatoire, car nous utilisons OAuth
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $user;
    }
}
