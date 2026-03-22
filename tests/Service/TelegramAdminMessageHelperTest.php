<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Service\TelegramAdminMessageHelper;
use App\Service\TelegramBotHelper;
use App\Type\CustomMessage\NewUserMessage;
use App\Type\CustomMessage\NicknameMismatchMessage;
use App\Type\CustomMessage\SmurfAlertMessage;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\Types\Message;

class TelegramAdminMessageHelperTest extends TestCase
{
    private function buildHelper(
        TelegramBotHelper $telegramBotHelper,
        NewUserMessage $newUserMessage,
        NicknameMismatchMessage $nicknameMismatchMessage,
        SmurfAlertMessage $smurfAlertMessage
    ): TelegramAdminMessageHelper {
        return new TelegramAdminMessageHelper(
            $telegramBotHelper,
            'admin@example.com',
            $newUserMessage,
            $nicknameMismatchMessage,
            $smurfAlertMessage
        );
    }

    public function testSendNewUserMessageCallsSendMessage(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendMessage');

        $newUserMessage = $this->createStub(NewUserMessage::class);
        $newUserMessage->method('setUser')->willReturnSelf();
        $newUserMessage->method('getText')->willReturn('New user message');

        $nicknameMismatchMessage = $this->createStub(NicknameMismatchMessage::class);
        $smurfAlertMessage = $this->createStub(SmurfAlertMessage::class);

        $helper = $this->buildHelper(
            $telegramBotHelper,
            $newUserMessage,
            $nicknameMismatchMessage,
            $smurfAlertMessage
        );

        $user = new User();
        $result = $helper->sendNewUserMessage(123, $user);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendSmurfAlertMessageCallsSendMessage(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendMessage');

        $newUserMessage = $this->createStub(NewUserMessage::class);
        $nicknameMismatchMessage = $this->createStub(NicknameMismatchMessage::class);

        $smurfAlertMessage = $this->createStub(SmurfAlertMessage::class);
        $smurfAlertMessage->method('setUser')->willReturnSelf();
        $smurfAlertMessage->method('setAgent')->willReturnSelf();
        $smurfAlertMessage->method('setAnnounceAdminCc')->willReturnSelf();
        $smurfAlertMessage->method('setStatEntry')->willReturnSelf();
        $smurfAlertMessage->method('getText')->willReturn('Smurf alert text');

        $helper = $this->buildHelper(
            $telegramBotHelper,
            $newUserMessage,
            $nicknameMismatchMessage,
            $smurfAlertMessage
        );

        $user = new User();
        $agent = new Agent();
        $statEntry = new AgentStat();

        $result = $helper->sendSmurfAlertMessage('admin', $user, $agent, $statEntry);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendNicknameMismatchMessageCallsSendMessage(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendMessage');

        $newUserMessage = $this->createStub(NewUserMessage::class);

        $nicknameMismatchMessage = $this->createStub(NicknameMismatchMessage::class);
        $nicknameMismatchMessage->method('setUser')->willReturnSelf();
        $nicknameMismatchMessage->method('setAgent')->willReturnSelf();
        $nicknameMismatchMessage->method('setStatEntry')->willReturnSelf();
        $nicknameMismatchMessage->method('getText')->willReturn('Nickname mismatch text');

        $smurfAlertMessage = $this->createStub(SmurfAlertMessage::class);

        $helper = $this->buildHelper(
            $telegramBotHelper,
            $newUserMessage,
            $nicknameMismatchMessage,
            $smurfAlertMessage
        );

        $user = new User();
        $agent = new Agent();
        $statEntry = new AgentStat();

        $result = $helper->sendNicknameMismatchMessage('admin', $user, $agent, $statEntry);

        self::assertInstanceOf(Message::class, $result);
    }
}
