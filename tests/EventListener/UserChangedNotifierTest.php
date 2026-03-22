<?php

namespace App\Tests\EventListener;

use App\Entity\Agent;
use App\Entity\User;
use App\EventListener\UserChangedNotifier;
use App\Service\TelegramBotHelper;
use Doctrine\Common\EventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use TelegramBot\Api\Types\Message;

class UserChangedNotifierTest extends TestCase
{
    public function testPostUpdateDoesNothingInDevEnv(): void
    {
        $security = $this->createMock(Security::class);
        $security->expects(self::never())->method('getUser');

        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->expects(self::never())->method('sendMessage');

        $notifier = new UserChangedNotifier($security, $telegramBotHelper, 'dev');

        $user = new User();
        $event = $this->createStub(EventArgs::class);

        $notifier->postUpdate($user, $event);
    }

    public function testPostUpdateDoesNothingWhenNoAdminUser(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->expects(self::never())->method('sendMessage');

        $notifier = new UserChangedNotifier($security, $telegramBotHelper, 'prod');

        $user = new User();
        $event = $this->createStub(EventArgs::class);

        $notifier->postUpdate($user, $event);
    }

    public function testPostUpdateDoesNothingWhenUserIsSameAsAdmin(): void
    {
        $adminUser = new User();

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($adminUser);

        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->expects(self::never())->method('sendMessage');

        $notifier = new UserChangedNotifier($security, $telegramBotHelper, 'prod');

        $event = $this->createStub(EventArgs::class);

        // Same user object - should not send message
        $notifier->postUpdate($adminUser, $event);
    }

    public function testPostUpdateSendsMessageWhenAdminChangesOtherUser(): void
    {
        $adminUser = new User();

        $changedUser = new User();
        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $changedUser->setAgent($agent);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($adminUser);

        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-123456789);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendMessage');

        $notifier = new UserChangedNotifier($security, $telegramBotHelper, 'prod');

        $event = $this->createStub(EventArgs::class);

        $notifier->postUpdate($changedUser, $event);
    }

    public function testPostUpdateIncludesAgentNameInMessage(): void
    {
        $adminUser = new User();

        $changedUser = new User();
        $agent = new Agent();
        $agent->setNickname('SpecialAgent');

        $changedUser->setAgent($agent);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($adminUser);

        $sentMessage = '';
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendMessage')
            ->willReturnCallback(function (int $groupId, string $text) use (&$sentMessage): Message {
                $sentMessage = $text;

                return new Message();
            });

        $notifier = new UserChangedNotifier($security, $telegramBotHelper, 'prod');

        $event = $this->createStub(EventArgs::class);
        $notifier->postUpdate($changedUser, $event);

        self::assertStringContainsString('SpecialAgent', $sentMessage);
        self::assertStringContainsString('User account changed', $sentMessage);
    }

    public function testPostUpdateWithNoAgentOnUser(): void
    {
        $adminUser = new User();
        $changedUser = new User();
        // No agent set

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($adminUser);

        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendMessage');

        $notifier = new UserChangedNotifier($security, $telegramBotHelper, 'prod');

        $event = $this->createStub(EventArgs::class);
        $notifier->postUpdate($changedUser, $event);
    }
}
