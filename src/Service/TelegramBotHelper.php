<?php

namespace App\Service;

use App\Entity\Agent;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use UnexpectedValueException;

class TelegramBotHelper
{
    private array $groupIds;

    public function __construct(
        private BotApi $api,

        private string $botName,
        string $groupIdDefault,
        string $groupIdAdmin,
        string $groupIdIntro,
        string $groupIdTest
    ) {
        $this->groupIds = [
            'default' => $groupIdDefault,
            'admin'   => $groupIdAdmin,
            'intro'   => $groupIdIntro,
            'test'    => $groupIdTest,
        ];
    }

    public function getGroupId(string $name = 'default'): int
    {
        if (array_key_exists($name, $this->groupIds)) {
            $id = $this->groupIds[$name];
        } else {
            throw new UnexpectedValueException(
                'Unknown TG bot group name'.$name
            );
        }

        if (!$id) {
            throw new UnexpectedValueException(
                'Required TG bot env var has not been set up: '.$name
            );
        }

        return (int)$id;
    }

    public function checkChatId($chatId): bool
    {
        $allowedIdString = getenv('ALLOWED_TELEGRAM_CHATS');

        if (!$allowedIdString) {
            return false;
        }

        $allowedIds = explode(',', $allowedIdString);

        return in_array($chatId, $allowedIds, false);
    }

    public function checkUserId(int $userId): bool
    {
        $allowedIdString = getenv('ALLOWED_TELEGRAM_USERS');

        if (!$allowedIdString) {
            return false;
        }

        $allowedIds = explode(',', $allowedIdString);

        return in_array($userId, $allowedIds, false);
    }

    public function checkTelegramIP(): bool
    {
        // Set the ranges of valid Telegram IPs.
        // https://core.telegram.org/bots/webhooks#the-short-version
        $telegram_ip_ranges = [
            ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
            // literally 149.154.160.0/20
            ['lower' => '91.108.4.0', 'upper' => '91.108.7.255'],
            // literally 91.108.4.0/22
        ];

        $ip_dec = (float)sprintf('%u', ip2long($_SERVER['REMOTE_ADDR']));
        $ok = false;

        foreach ($telegram_ip_ranges as $telegram_ip_range) {
            if (!$ok) {
                // Make sure the IP is valid.
                $lower_dec = (float)sprintf(
                    '%u',
                    ip2long($telegram_ip_range['lower'])
                );
                $upper_dec = (float)sprintf(
                    '%u',
                    ip2long($telegram_ip_range['upper'])
                );
                if ($ip_dec >= $lower_dec && $ip_dec <= $upper_dec) {
                    $ok = true;
                }
            }
        }

        return $ok;
    }

    public function getConnectLink(Agent $agent): string
    {
        // This seems necessary to lazy load the $agent object (???)
        $agent->getNickname();

        return sprintf(
            'https://t.me/%s?start=%s',
            $this->botName,
            $agent->getTelegramConnectionSecret()
        );
    }

    public function getConnectLink2($agent): string
    {
        return sprintf(
            'http://www.telegram.me/%s?start=%s',
            $this->botName,
            $agent->getTelegramConnectionSecret()
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendMessage(
        int $chatId,
        string $text,
        bool $disablePreview = false
    ): Message {
        return $this->api->sendMessage(
            $chatId,
            $text,
            'markdown',
            $disablePreview
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendPhoto($chatId, $photo, $caption): Message
    {
        return $this->api->sendPhoto(
            $chatId,
            $photo,
            $caption,
            null,
            null,
            false,
            'markdown'
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendButtonMessage(string $groupName): Message
    {
        $prev = 3;
        $text = 'hello test';
        $buttons = [];

        $buttons[] = ['text'          => 'Hello TEST',
                      'callback_data' => '/post_'.$prev,
        ];

        return $this->api->sendMessage(
            $this->getGroupId($groupName),
            $text,
            'markdown',
            false,
            null,
            new InlineKeyboardMarkup([$buttons])
        );
    }
}
