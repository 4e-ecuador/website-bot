<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use App\Service\TelegramMessageHelper;
use App\Type\CustomMessage\LevelUpMessage;
use App\Type\CustomMessage\MedalDoubleMessage;
use App\Type\CustomMessage\NewMedalMessage;
use App\Type\CustomMessage\NotifyEventsMessage;
use App\Type\CustomMessage\NotifyUploadReminder;
use App\Type\CustomMessage\RecursionMessage;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\Types\Message;
use UnexpectedValueException;

class TelegramMessageHelperTest extends TestCase
{
    private function buildHelper(
        TelegramBotHelper $telegramBotHelper,
        ?MedalChecker $medalChecker = null,
        ?NewMedalMessage $newMedalMessage = null,
        ?MedalDoubleMessage $medalDoubleMessage = null,
        ?LevelUpMessage $levelUpMessage = null,
        ?NotifyEventsMessage $notifyEventsMessage = null,
        ?NotifyUploadReminder $notifyUploadReminder = null,
        ?RecursionMessage $recursionMessage = null
    ): TelegramMessageHelper {
        $medalChecker ??= $this->createStub(MedalChecker::class);
        $newMedalMessage ??= $this->createStub(NewMedalMessage::class);
        $medalDoubleMessage ??= $this->createStub(MedalDoubleMessage::class);
        $levelUpMessage ??= $this->createStub(LevelUpMessage::class);
        $notifyUploadReminder ??= $this->createStub(NotifyUploadReminder::class);
        $notifyEventsMessage ??= $this->createStub(NotifyEventsMessage::class);
        $recursionMessage ??= $this->createStub(RecursionMessage::class);

        return new TelegramMessageHelper(
            $telegramBotHelper,
            $medalChecker,
            '/tmp',
            $newMedalMessage,
            $medalDoubleMessage,
            $levelUpMessage,
            $notifyEventsMessage,
            $notifyUploadReminder,
            $recursionMessage
        );
    }

    public function testSendNotifyUploadReminderMessageCallsSendMessage(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendMessage');

        $notifyUploadReminder = $this->createStub(NotifyUploadReminder::class);
        $notifyUploadReminder->method('getText')->willReturn('Upload reminder text');

        $helper = $this->buildHelper($telegramBotHelper, notifyUploadReminder: $notifyUploadReminder);

        $result = $helper->sendNotifyUploadReminderMessage(123);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendNotifyEventsMessageCallsSendMessage(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendMessage');

        $notifyEventsMessage = $this->createStub(NotifyEventsMessage::class);
        $notifyEventsMessage->method('setFirstAnnounce')->willReturnSelf();
        $notifyEventsMessage->method('getText')->willReturn('Event notification text');

        $helper = $this->buildHelper($telegramBotHelper, notifyEventsMessage: $notifyEventsMessage);

        $result = $helper->sendNotifyEventsMessage(123);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendNotifyEventsMessageThrowsWhenEmptyMessage(): void
    {
        $telegramBotHelper = $this->createStub(TelegramBotHelper::class);

        $notifyEventsMessage = $this->createStub(NotifyEventsMessage::class);
        $notifyEventsMessage->method('setFirstAnnounce')->willReturnSelf();
        $notifyEventsMessage->method('getText')->willReturn('');

        $helper = $this->buildHelper($telegramBotHelper, notifyEventsMessage: $notifyEventsMessage);

        $this->expectException(UnexpectedValueException::class);

        $helper->sendNotifyEventsMessage(123);
    }

    public function testSendNotifyEventsMessageWithFirstAnnounce(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('sendMessage')->willReturn(new Message());

        $notifyEventsMessage = $this->createStub(NotifyEventsMessage::class);
        $notifyEventsMessage->method('setFirstAnnounce')->willReturnSelf();
        $notifyEventsMessage->method('getText')->willReturn('First announce event text');

        $helper = $this->buildHelper($telegramBotHelper, notifyEventsMessage: $notifyEventsMessage);

        $result = $helper->sendNotifyEventsMessage(456, true);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendRecursionMessageCallsSendPhoto(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendPhoto')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendPhoto');

        $recursionMessage = $this->createStub(RecursionMessage::class);
        $recursionMessage->method('setAgent')->willReturnSelf();
        $recursionMessage->method('setRecursions')->willReturnSelf();
        $recursionMessage->method('getText')->willReturn('Recursion message text');

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $helper = $this->buildHelper($telegramBotHelper, recursionMessage: $recursionMessage);

        $result = $helper->sendRecursionMessage('default', $agent, 2);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendNewMedalMessageCallsSendPhoto(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendPhoto')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendPhoto');

        $medalChecker = $this->createStub(MedalChecker::class);
        $medalChecker->method('getBadgePath')->willReturn('badge.png');

        $newMedalMessage = $this->createStub(NewMedalMessage::class);
        $newMedalMessage->method('setAgent')->willReturnSelf();
        $newMedalMessage->method('setMedalUps')->willReturnSelf();
        $newMedalMessage->method('getText')->willReturn('New medal message text');

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $helper = $this->buildHelper(
            $telegramBotHelper,
            medalChecker: $medalChecker,
            newMedalMessage: $newMedalMessage
        );

        $result = $helper->sendNewMedalMessage('default', $agent, ['Guardian' => 3]);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendMedalDoubleMessageCallsSendPhoto(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendPhoto')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendPhoto');

        $medalChecker = $this->createStub(MedalChecker::class);
        $medalChecker->method('getBadgePath')->willReturn('badge.png');

        $medalDoubleMessage = $this->createStub(MedalDoubleMessage::class);
        $medalDoubleMessage->method('setAgent')->willReturnSelf();
        $medalDoubleMessage->method('setMedalDoubles')->willReturnSelf();
        $medalDoubleMessage->method('getText')->willReturn('Medal double message text');

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $helper = $this->buildHelper(
            $telegramBotHelper,
            medalChecker: $medalChecker,
            medalDoubleMessage: $medalDoubleMessage
        );

        $result = $helper->sendMedalDoubleMessage('default', $agent, ['Hacker' => 5]);

        self::assertInstanceOf(Message::class, $result);
    }

    public function testSendLevelUpMessageCallsSendPhoto(): void
    {
        $telegramBotHelper = $this->createMock(TelegramBotHelper::class);
        $telegramBotHelper->method('getGroupId')->willReturn(-100);
        $telegramBotHelper->method('sendPhoto')->willReturn(new Message());
        $telegramBotHelper->expects(self::once())->method('sendPhoto');

        $levelUpMessage = $this->createStub(LevelUpMessage::class);
        $levelUpMessage->method('setAgent')->willReturnSelf();
        $levelUpMessage->method('setLevel')->willReturnSelf();
        $levelUpMessage->method('setRecursions')->willReturnSelf();
        $levelUpMessage->method('getText')->willReturn('Level up message text');

        $agent = new Agent();
        $agent->setNickname('TestAgent');

        $helper = $this->buildHelper($telegramBotHelper, levelUpMessage: $levelUpMessage);

        $result = $helper->sendLevelUpMessage('default', $agent, 8, 0);

        self::assertInstanceOf(Message::class, $result);
    }
}
