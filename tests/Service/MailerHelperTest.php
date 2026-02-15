<?php

namespace App\Tests\Service;

use App\Entity\Comment;
use App\Entity\User;
use App\Service\MailerHelper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

class MailerHelperTest extends TestCase
{
    public function testSendConfirmationMailSuccess(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $helper = new MailerHelper($mailer, 'admin@test.com', 'Admin', $this->createStub(LoggerInterface::class));

        $user = new User();
        $user->setEmail('user@test.com');

        $result = $helper->sendConfirmationMail($user, 'Welcome');

        self::assertSame('Confirmation mail has been sent to user@test.com', $result);
    }

    public function testSendConfirmationMailFailure(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('SMTP error'));

        $helper = new MailerHelper($mailer, 'admin@test.com', 'Admin', $this->createStub(LoggerInterface::class));

        $user = new User();
        $user->setEmail('user@test.com');

        $result = $helper->sendConfirmationMail($user, 'Welcome');

        self::assertSame('SMTP error', $result);
    }

    public function testSendNewCommentMailSuccess(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $helper = new MailerHelper($mailer, 'admin@test.com', 'Admin', $this->createStub(LoggerInterface::class));

        $result = $helper->sendNewCommentMail(new Comment());

        self::assertSame('Confirmation mail has been sent to admin@test.com', $result);
    }

    public function testSendNewUserMailSuccess(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $helper = new MailerHelper($mailer, 'admin@test.com', 'Admin', $this->createStub(LoggerInterface::class));

        $result = $helper->sendNewUserMail(new User());

        self::assertSame('Mail has been sent to admin@test.com', $result);
    }

    public function testSendNewUserMailFailure(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('Connection refused'));

        $helper = new MailerHelper($mailer, 'admin@test.com', 'Admin', $this->createStub(LoggerInterface::class));

        $result = $helper->sendNewUserMail(new User());

        self::assertSame('Connection refused', $result);
    }

    public function testFailureLogsError(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('SMTP error'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $helper = new MailerHelper($mailer, 'admin@test.com', 'Admin', $logger);

        $user = new User();
        $user->setEmail('user@test.com');

        $helper->sendConfirmationMail($user, 'Welcome');
    }

    public function testSendTestMail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $helper = new MailerHelper($mailer, 'admin@test.com', 'Admin', $this->createStub(LoggerInterface::class));

        $helper->sendTestMail('recipient@test.com');
    }
}
