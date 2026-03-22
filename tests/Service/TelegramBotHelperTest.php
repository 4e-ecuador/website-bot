<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Service\TelegramBotHelper;
use PHPUnit\Framework\TestCase;
use TelegramBot\Api\BotApi;
use UnexpectedValueException;

class TelegramBotHelperTest extends TestCase
{
    private TelegramBotHelper $helper;

    protected function setUp(): void
    {
        $api = new class extends BotApi {
            public function __construct()
            {
            }

            public function __destruct() {}

            public function sendMessage(
                $chatId,
                $text,
                $parseMode = null,
                $disablePreview = false,
                $replyToMessageId = null,
                $replyMarkup = null,
                $disableNotification = false,
                $messageThreadId = null,
                $protectContent = null,
                $allowSendingWithoutReply = null
            ): \TelegramBot\Api\Types\Message {
                return new \TelegramBot\Api\Types\Message();
            }

            public function sendPhoto(
                $chatId,
                $photo,
                $caption = null,
                $replyToMessageId = null,
                $replyMarkup = null,
                $disableNotification = false,
                $parseMode = null,
                $messageThreadId = null,
                $protectContent = null,
                $allowSendingWithoutReply = null
            ): \TelegramBot\Api\Types\Message {
                return new \TelegramBot\Api\Types\Message();
            }
        };

        $this->helper = new TelegramBotHelper(
            $api,
            'test_bot',
            '123',
            '456',
            '789',
            '101',
        );
    }

    public function testGetGroupIdDefault(): void
    {
        self::assertSame(123, $this->helper->getGroupId());
    }

    public function testGetGroupIdAdmin(): void
    {
        self::assertSame(456, $this->helper->getGroupId('admin'));
    }

    public function testGetGroupIdIntro(): void
    {
        self::assertSame(789, $this->helper->getGroupId('intro'));
    }

    public function testGetGroupIdTest(): void
    {
        self::assertSame(101, $this->helper->getGroupId('test'));
    }

    public function testGetGroupIdUnknownThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unknown TG bot group name');

        $this->helper->getGroupId('nonexistent');
    }

    public function testGetGroupIdEmptyValueThrows(): void
    {
        $api = new class extends BotApi {
            public function __construct()
            {
            }

            public function __destruct() {}
        };

        $helper = new TelegramBotHelper(
            $api,
            'test_bot',
            '',
            '456',
            '789',
            '101',
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Required TG bot env var has not been set up');

        $helper->getGroupId('default');
    }

    public function testCheckChatIdReturnsFalseWhenEnvNotSet(): void
    {
        $original = getenv('ALLOWED_TELEGRAM_CHATS');
        putenv('ALLOWED_TELEGRAM_CHATS');

        try {
            self::assertFalse($this->helper->checkChatId('123'));
        } finally {
            if ($original !== false) {
                putenv('ALLOWED_TELEGRAM_CHATS='.$original);
            }
        }
    }

    public function testCheckChatIdReturnsTrueForAllowed(): void
    {
        $original = getenv('ALLOWED_TELEGRAM_CHATS');
        putenv('ALLOWED_TELEGRAM_CHATS=100,200,300');

        try {
            self::assertTrue($this->helper->checkChatId('200'));
        } finally {
            if ($original !== false) {
                putenv('ALLOWED_TELEGRAM_CHATS='.$original);
            } else {
                putenv('ALLOWED_TELEGRAM_CHATS');
            }
        }
    }

    public function testCheckChatIdReturnsFalseForNotAllowed(): void
    {
        $original = getenv('ALLOWED_TELEGRAM_CHATS');
        putenv('ALLOWED_TELEGRAM_CHATS=100,200,300');

        try {
            self::assertFalse($this->helper->checkChatId('999'));
        } finally {
            if ($original !== false) {
                putenv('ALLOWED_TELEGRAM_CHATS='.$original);
            } else {
                putenv('ALLOWED_TELEGRAM_CHATS');
            }
        }
    }

    public function testCheckUserIdReturnsFalseWhenEnvNotSet(): void
    {
        $original = getenv('ALLOWED_TELEGRAM_USERS');
        putenv('ALLOWED_TELEGRAM_USERS');

        try {
            self::assertFalse($this->helper->checkUserId(123));
        } finally {
            if ($original !== false) {
                putenv('ALLOWED_TELEGRAM_USERS='.$original);
            }
        }
    }

    public function testCheckUserIdReturnsTrueForAllowed(): void
    {
        $original = getenv('ALLOWED_TELEGRAM_USERS');
        putenv('ALLOWED_TELEGRAM_USERS=100,200,300');

        try {
            self::assertTrue($this->helper->checkUserId(200));
        } finally {
            if ($original !== false) {
                putenv('ALLOWED_TELEGRAM_USERS='.$original);
            } else {
                putenv('ALLOWED_TELEGRAM_USERS');
            }
        }
    }

    public function testCheckUserIdReturnsFalseForNotAllowed(): void
    {
        $original = getenv('ALLOWED_TELEGRAM_USERS');
        putenv('ALLOWED_TELEGRAM_USERS=100,200,300');

        try {
            self::assertFalse($this->helper->checkUserId(999));
        } finally {
            if ($original !== false) {
                putenv('ALLOWED_TELEGRAM_USERS='.$original);
            } else {
                putenv('ALLOWED_TELEGRAM_USERS');
            }
        }
    }

    public function testCheckTelegramIPReturnsFalseForLocalhost(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        self::assertFalse($this->helper->checkTelegramIP());
    }

    public function testCheckTelegramIPReturnsTrueForValidTelegramIP(): void
    {
        $_SERVER['REMOTE_ADDR'] = '149.154.160.1';

        self::assertTrue($this->helper->checkTelegramIP());
    }

    public function testCheckTelegramIPReturnsTrueForSecondRange(): void
    {
        $_SERVER['REMOTE_ADDR'] = '91.108.4.1';

        self::assertTrue($this->helper->checkTelegramIP());
    }

    public function testGetConnectLink(): void
    {
        $agent = new Agent();
        $agent->setTelegramConnectionSecret('secret123');

        $link = $this->helper->getConnectLink($agent);

        self::assertSame('https://t.me/test_bot?start=secret123', $link);
    }

    public function testGetConnectLink2(): void
    {
        $agent = new Agent();
        $agent->setTelegramConnectionSecret('secret456');

        $link = $this->helper->getConnectLink2($agent);

        self::assertSame('http://www.telegram.me/test_bot?start=secret456', $link);
    }

    public function testSendMessageReturnsMessage(): void
    {
        $result = $this->helper->sendMessage(123, 'Hello World');

        self::assertInstanceOf(\TelegramBot\Api\Types\Message::class, $result);
    }

    public function testSendMessageWithDisablePreview(): void
    {
        $result = $this->helper->sendMessage(123, 'Hello World', true);

        self::assertInstanceOf(\TelegramBot\Api\Types\Message::class, $result);
    }

    public function testSendPhotoReturnsMessage(): void
    {
        $result = $this->helper->sendPhoto(123, 'https://example.com/photo.jpg', 'Caption');

        self::assertInstanceOf(\TelegramBot\Api\Types\Message::class, $result);
    }

    public function testSendButtonMessageReturnsMessage(): void
    {
        $result = $this->helper->sendButtonMessage('default');

        self::assertInstanceOf(\TelegramBot\Api\Types\Message::class, $result);
    }
}
