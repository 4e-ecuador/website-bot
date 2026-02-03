<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TelegramAdminMessageHelper;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly TelegramAdminMessageHelper $telegramAdminMessageHelper,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): RedirectResponse {
        $targetPath = $this->getTargetPath(
            $request->getSession(),
            $firewallName
        );

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('default'));
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): Response {
        $message = strtr(
            $exception->getMessageKey(),
            $exception->getMessageData()
        );

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws IdentityProviderException
     */
    public function authenticate(Request $request): Passport
    {
        $token = $this->getGoogleClient()->getAccessToken();

        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($token);

        $user = $this->getUser($googleUser);

        return new SelfValidatingPassport(
            new UserBadge($user->getEmail()), [new RememberMeBadge()]
        );
    }

    private function getUser(GoogleUser $googleUser): User
    {
        // 1) have they logged in with Google before? Easy!
        if ($user = $this->userRepository->findOneBy(
            ['googleId' => $googleUser->getId()]
        )
        ) {
            return $user;
        }

        // @todo remove: Fetch user by email
        if ($user = $this->userRepository->findOneBy(
            ['email' => $googleUser->getEmail()]
        )
        ) {
            // @todo remove: Update existing users google id
            $user->setGoogleId($googleUser->getId());
        } else {
            // Register new user
            $user = new User()
                ->setEmail($googleUser->getEmail())
                ->setGoogleId($googleUser->getId());

            try {
                $groupId = $this->telegramBotHelper->getGroupId('admin');
                $this->telegramAdminMessageHelper->sendNewUserMessage(
                    $groupId,
                    $user
                );
            } catch (Exception) {
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function getGoogleClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('google');
    }
}
