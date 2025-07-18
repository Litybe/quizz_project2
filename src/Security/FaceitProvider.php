<?php

namespace App\Security;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class FaceitProvider extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct([
            'clientId' => $options['clientId'],
            'clientSecret' => $options['clientSecret'],
            'redirectUri' => $options['redirectUri'],
            'urlAuthorize' => 'https://accounts.faceit.com/accounts?redirect_popup=true',
            'urlAccessToken' => 'https://api.faceit.com/auth/v1/oauth/token',
            'urlResourceOwnerDetails' => 'https://api.faceit.com/auth/v1/resources/userinfo',
        ], $collaborators);
    }

    public function getAuthorizationUrl(array $options = [])
    {
        $options += ['pkce' => true];
        return parent::getAuthorizationUrl($options);
    }

    protected function getDefaultHeaders()
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error_description'] ?? $response->getReasonPhrase(),
                $data['error'],
                $data
            );
        }
    }
}
