<?php

namespace App\Tests\EventSubscriber;

use App\Entity\LoginAttempt;
use App\EventSubscriber\LoginAttemptSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginAttemptSubscriberTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    private LoginAttemptSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->subscriber = new LoginAttemptSubscriber($this->entityManager);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetSubscribedEvents(): void
    {
        $events = LoginAttemptSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(LoginSuccessEvent::class, $events);
        self::assertArrayHasKey(LoginFailureEvent::class, $events);
        self::assertSame('onLoginSuccess', $events[LoginSuccessEvent::class]);
        self::assertSame('onLoginFailure', $events[LoginFailureEvent::class]);
    }

    public function testOnLoginSuccess(): void
    {
        $user = $this->createStub(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user@example.com');

        $request = new Request(server: ['REMOTE_ADDR' => '127.0.0.1']);

        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $event = $this->createStub(LoginSuccessEvent::class);
        $event->method('getUser')->willReturn($user);
        $event->method('getRequest')->willReturn($request);
        $event->method('getAuthenticator')->willReturn($authenticator);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(fn(LoginAttempt $attempt): bool => $attempt->isSuccess()
                && $attempt->getEmail() === 'user@example.com'
                && $attempt->getIpAddress() === '127.0.0.1'));
        $this->entityManager->expects(self::once())->method('flush');

        $this->subscriber->onLoginSuccess($event);
    }

    public function testOnLoginFailureWithPassportUser(): void
    {
        $user = $this->createStub(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('failed@example.com');

        $passport = $this->createStub(Passport::class);
        $passport->method('getUser')->willReturn($user);

        $request = new Request(server: ['REMOTE_ADDR' => '10.0.0.1']);

        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $event = $this->createStub(LoginFailureEvent::class);
        $event->method('getPassport')->willReturn($passport);
        $event->method('getRequest')->willReturn($request);
        $event->method('getAuthenticator')->willReturn($authenticator);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(fn(LoginAttempt $attempt): bool => !$attempt->isSuccess()
                && $attempt->getEmail() === 'failed@example.com'
                && $attempt->getIpAddress() === '10.0.0.1'));
        $this->entityManager->expects(self::once())->method('flush');

        $this->subscriber->onLoginFailure($event);
    }

    public function testOnLoginFailureWithUserBadgeFallback(): void
    {
        $userBadge = new UserBadge('badge@example.com', fn () => $this->createStub(UserInterface::class));

        $passport = $this->createMock(Passport::class);
        $passport->method('getUser')->willThrowException(new \RuntimeException('No user'));
        $passport->expects(self::once())
            ->method('getBadge')
            ->with(UserBadge::class)
            ->willReturn($userBadge);

        $request = new Request(server: ['REMOTE_ADDR' => '192.168.1.1']);

        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $event = $this->createStub(LoginFailureEvent::class);
        $event->method('getPassport')->willReturn($passport);
        $event->method('getRequest')->willReturn($request);
        $event->method('getAuthenticator')->willReturn($authenticator);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(fn(LoginAttempt $attempt): bool => $attempt->getEmail() === 'badge@example.com'));

        $this->subscriber->onLoginFailure($event);
    }

    public function testOnLoginFailureWithRequestEmailFallback(): void
    {
        $passport = $this->createStub(Passport::class);
        $passport->method('getUser')->willThrowException(new \RuntimeException('No user'));
        $passport->method('getBadge')->willReturn(null);

        $request = new Request(
            request: ['email' => 'request@example.com'],
            server: ['REMOTE_ADDR' => '10.10.10.10'],
        );

        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $event = $this->createStub(LoginFailureEvent::class);
        $event->method('getPassport')->willReturn($passport);
        $event->method('getRequest')->willReturn($request);
        $event->method('getAuthenticator')->willReturn($authenticator);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(fn(LoginAttempt $attempt): bool => $attempt->getEmail() === 'request@example.com'
                && $attempt->getAuthMethod() === 'unknown'));

        $this->subscriber->onLoginFailure($event);
    }

    public function testOnLoginFailureWithUsernameFallback(): void
    {
        $passport = $this->createStub(Passport::class);
        $passport->method('getUser')->willThrowException(new \RuntimeException('No user'));
        $passport->method('getBadge')->willReturn(null);

        $request = new Request(
            request: ['_username' => 'username@example.com'],
            server: ['REMOTE_ADDR' => '10.10.10.10'],
        );

        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $event = $this->createStub(LoginFailureEvent::class);
        $event->method('getPassport')->willReturn($passport);
        $event->method('getRequest')->willReturn($request);
        $event->method('getAuthenticator')->willReturn($authenticator);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(fn(LoginAttempt $attempt): bool => $attempt->getEmail() === 'username@example.com'));

        $this->subscriber->onLoginFailure($event);
    }

    public function testOnLoginFailureWithNoEmailResolvable(): void
    {
        $passport = $this->createStub(Passport::class);
        $passport->method('getUser')->willThrowException(new \RuntimeException('No user'));
        $passport->method('getBadge')->willReturn(null);

        $request = new Request(server: ['REMOTE_ADDR' => '10.10.10.10']);

        $authenticator = $this->createStub(AuthenticatorInterface::class);

        $event = $this->createStub(LoginFailureEvent::class);
        $event->method('getPassport')->willReturn($passport);
        $event->method('getRequest')->willReturn($request);
        $event->method('getAuthenticator')->willReturn($authenticator);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(fn(LoginAttempt $attempt): bool => $attempt->getEmail() === null));

        $this->subscriber->onLoginFailure($event);
    }
}
