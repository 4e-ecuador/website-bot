<?php

namespace App\Tests\Security;

use App\Repository\UserRepository;
use App\Security\GoogleIdentityAuthenticator;
use App\Service\TelegramAdminMessageHelper;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GoogleIdentityAuthenticatorTest extends TestCase
{
    private GoogleIdentityAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->authenticator = new GoogleIdentityAuthenticator(
            $this->createStub(UserRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(TelegramBotHelper::class),
            $this->createStub(TelegramAdminMessageHelper::class),
            'test-google-client-id',
        );
    }

    public function testSupportsCorrectPath(): void
    {
        $request = Request::create('/connect/google/verify', \Symfony\Component\HttpFoundation\Request::METHOD_POST);

        self::assertTrue($this->authenticator->supports($request));
    }

    public function testDoesNotSupportOtherPaths(): void
    {
        $request = Request::create('/login', \Symfony\Component\HttpFoundation\Request::METHOD_POST);

        self::assertFalse($this->authenticator->supports($request));
    }

    public function testAuthenticateThrowsOnEmptyCredential(): void
    {
        $request = Request::create('/connect/google/verify', \Symfony\Component\HttpFoundation\Request::METHOD_POST);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Missing credentials');

        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateThrowsOnInvalidToken(): void
    {
        $request = Request::create('/connect/google/verify', \Symfony\Component\HttpFoundation\Request::METHOD_POST, [
            'credential' => 'invalid-token',
        ]);

        $this->expectException(AuthenticationException::class);

        $this->authenticator->authenticate($request);
    }

    public function testOnAuthenticationFailureRedirectsToLogin(): void
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->willReturn('/login');

        $authenticator = new GoogleIdentityAuthenticator(
            $this->createStub(UserRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $urlGenerator,
            $this->createStub(TelegramBotHelper::class),
            $this->createStub(TelegramAdminMessageHelper::class),
            'test-google-client-id',
        );

        $request = Request::create('/connect/google/verify', \Symfony\Component\HttpFoundation\Request::METHOD_POST);
        $request->setSession(new \Symfony\Component\HttpFoundation\Session\Session(
            new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()
        ));

        $response = $authenticator->onAuthenticationFailure(
            $request,
            new AuthenticationException('Test error')
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getTargetUrl());
    }
}
