<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        $user = $this->getUser($request);

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier()),
        );
    }

    private function getToken(Request $request): string
    {
        $token = $request->headers->get('X-AUTH-TOKEN');

        $scheme = $request->getScheme();

        if ('https' !== $scheme) {
            throw new CustomUserMessageAuthenticationException(
                'Please use SSL and not: '.$scheme
            );
        }

        if ($token === 'ILuvAPIs') {
            throw new CustomUserMessageAuthenticationException(
                'ILuvAPIs is not a real API key: it\'s just a silly phrase'
            );
        }

        return $token;
    }

    private function getUser(Request $request): User
    {
        $token = $this->getToken($request);

        return $this->em->getRepository(User::class)
            ->findOneBy(['apiToken' => $token]);
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $firewallName
    ): ?Response {
        return null;
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?Response {
        $data = [
            'message' => strtr(
                $exception->getMessageKey(),
                $exception->getMessageData()
            ),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function start(): JsonResponse
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
