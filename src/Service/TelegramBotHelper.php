<?php


namespace App\Service;


class TelegramBotHelper
{
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
            ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], // literally 149.154.160.0/20
            ['lower' => '91.108.4.0', 'upper' => '91.108.7.255'],    // literally 91.108.4.0/22
        ];

        $ip_dec = (float)sprintf('%u', ip2long($_SERVER['REMOTE_ADDR']));
        $ok     = false;

        foreach ($telegram_ip_ranges as $telegram_ip_range) {
            if (!$ok) {
                // Make sure the IP is valid.
                $lower_dec = (float)sprintf('%u', ip2long($telegram_ip_range['lower']));
                $upper_dec = (float)sprintf('%u', ip2long($telegram_ip_range['upper']));
                if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) {
                    $ok = true;
                }
            }
        }

        return $ok;
    }
}
