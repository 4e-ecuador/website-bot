<?php

namespace App\Service;

use App\Entity\Agent;
use TelegramBot\Api\BotApi;

class TelegramBotHelper
{
    /**
     * @var BotApi
     */
    private $api;

    /**
     * @var MedalChecker
     */
    private $medalChecker;

    public function __construct(BotApi $api, MedalChecker $medalChecker)
    {
        $this->api = $api;
        $this->medalChecker = $medalChecker;
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
                if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) {
                    $ok = true;
                }
            }
        }

        return $ok;
    }

    public function sendNewMedalMessage(Agent $agent, array $medalUps, string $groupId)
    {
        $pageBase = $_ENV['PAGE_BASE_URL'];
        $tada = "\xF0\x9F\x8E\x89";

        $response = [];

        $response[] = '--- *A N U N C I O* ---';
        $response[] = '[ ]('.$pageBase
            .'/build/images/medals/pioneer-1.png)';

        if (count($medalUps) > 1) {
            $response[] = sprintf('El agente @%s se ha ganado %d nuevas medallas!', $agent->getNickname(), count($medalUps));
        } else {
            $response[] = sprintf('El agente @%s se ha ganado una nueva medalla!', $agent->getNickname());
        }

        $response[] = '';

        foreach ($medalUps as $medal => $level) {
            $response[] = sprintf('** %s de %s', $medal, $this->medalChecker->getLevelName($level));
        }

        $response[] = '';

        $response[] = sprintf(
            '[Admiren este medallero](%s/stats/agent/%s)',
            $pageBase,
            $agent->getId()
        );

        $response[] = '';

        $response[] = sprintf('Felicitaciones %s ', $tada.$tada.$tada);

        return $this->api->sendMessage(
            $groupId,
            implode("\n", $response),
            'markdown'
        );
    }
}
