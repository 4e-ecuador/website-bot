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
}
