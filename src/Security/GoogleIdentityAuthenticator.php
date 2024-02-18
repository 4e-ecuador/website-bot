<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TelegramAdminMessageHelper;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Google\Client;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GoogleIdentityAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly TelegramAdminMessageHelper $telegramAdminMessageHelper,
        #[Autowire('%env(OAUTH_GOOGLE_ID)%')] private readonly string $oauthGoogleId,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/connect/google/verify';
    }

    public function authenticate(Request $request): Passport
    {
        $idToken = (string)$request->request->get('credential');

        if ($idToken === '' || $idToken === '0') {
            throw new AuthenticationException('Missing credentials :(');
        }

        $payload = (new Client(['client_id' => $this->oauthGoogleId]))
            ->verifyIdToken($idToken);

        if (!$payload) {
            throw new AuthenticationException('Invalid ID token :(');
        }

        $user = $this->getUser(new GoogleUser($payload));

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier()),
            [new RememberMeBadge()],
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): RedirectResponse {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $firewallName
        )
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('default'));
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): RedirectResponse {
        $message = strtr(
            $exception->getMessageKey(),
            $exception->getMessageData()
        );

        /**
         * @var Session $session
         */
        $session = $request->getSession();
        $session->getFlashBag()->add('danger', $message);

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
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
            $user = (new User())
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
}
