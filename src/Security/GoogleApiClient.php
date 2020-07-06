<?php

namespace App\Security;

use Google_Client;
use Google_Service_Oauth2;
use Google_Service_Oauth2_Userinfo;
use UnexpectedValueException;

class GoogleApiClient
{
    private Google_Client $apiClient;
    private string $clientId;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->apiClient = new Google_Client(
            [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ]
        );
    }

    public function setRedirectUri(string $redirectUri): self
    {
        $this->apiClient->setRedirectUri($redirectUri);

        return $this;
    }

    public function addScope(string $scope): self
    {
        $this->apiClient->addScope($scope);

        return $this;
    }

    public function getUserInfo(): Google_Service_Oauth2_Userinfo
    {
        $googleOauth = new Google_Service_Oauth2($this->apiClient);

        return $googleOauth->userinfo->get();
    }

    public function fetchEmailWithToken(string $idToken)
    {
        $payload = $this->apiClient->verifyIdToken($idToken);

        if (!$payload) {
            throw new UnexpectedValueException('Can not verify IdToken');
        }

        return $payload['email'] ?? null;

        $x = $payload[''] ?? null;
        $azp = $payload['azp'] ?? null;

        if ($azp !== $this->clientId) {
            throw new UnexpectedValueException('Invalid ClientId');
        }

        return $payload;
    }

    public function setAccessTokenWithAuthCode($code): self
    {
        $tokenArray = $this->apiClient->fetchAccessTokenWithAuthCode($code);

        if (array_key_exists('error', $tokenArray)) {
            $errorText = $tokenArray['error'];
            if (array_key_exists('error_description', $tokenArray)) {
                $errorText .= ' - '.$tokenArray['error_description'];
            }

            throw new UnexpectedValueException($errorText);
        }

        if (!array_key_exists('access_token', $tokenArray)) {
            throw new UnexpectedValueException('Can not get a token =;(');
        }

        $this->apiClient->setAccessToken($tokenArray['access_token']);

        return $this;
    }

    public function createAuthUrl($scope = null): string
    {
        return $this->apiClient->createAuthUrl($scope);
    }
}
