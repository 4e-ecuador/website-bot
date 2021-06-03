<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TelegramAdminMessageHelper;
use App\Service\TelegramBotHelper;
use App\Service\TelegramMessageHelper;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use TelegramBot\Api\Exception;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class GoogleAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private UserRepository $userManager;
    private TelegramMessageHelper $telegramMessageHelper;

    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator,
        private TelegramBotHelper $telegramBotHelper,
        private TelegramAdminMessageHelper $telegramAdminMessageHelper,
    ) {
    }

    public function supports(Request $request): bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        $firewallName
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
    public function authenticate(Request $request): PassportInterface
    {
        $token = $this->getGoogleClient()->getAccessToken();

        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($token);

        $user = $this->getUser($googleUser);

        return new SelfValidatingPassport(
            new UserBadge($user->getEmail()),
        );
    }

    /**
     * @throws Exception
     */
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
            $user = (new User())
                ->setEmail($googleUser->getEmail())
                ->setGoogleId($googleUser->getId());

            try {
                $groupId = $this->telegramBotHelper->getGroupId('admin');
                $this->telegramAdminMessageHelper->sendNewUserMessage(
                    $groupId,
                    $user
                );
            } catch (\Exception) {
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
