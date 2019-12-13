<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;

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

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(BotApi $api, MedalChecker $medalChecker, TranslatorInterface $translator)
    {
        $this->api = $api;
        $this->medalChecker = $medalChecker;
        $this->translator = $translator;
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

    public function sendNewMedalMessage(Agent $agent, array $medalUps, string $groupId): Message
    {
        $pageBase = $_ENV['PAGE_BASE_URL'];
        $tada = "\xF0\x9F\x8E\x89";

        $firstValue = reset($medalUps);
        $firstMedal = key($medalUps);

        $response = [];

        $response[] = $this->translator->trans('new.medal.header');

        $response[] = '[ ]('.$pageBase.'/build/images/badges/'
            .$this->medalChecker->getBadgePath($firstMedal, $firstValue).')';

        $response[] = $this->translator->trans(
            'new.medal.text.1', [
                'medals' => count($medalUps),
                'agent'  => $agent->getTelegramName() ?: $agent->getNickname(),
            ]
        );

        $response[] = '';

        foreach ($medalUps as $medal => $level) {
            $response[] = $this->translator->trans(
                'new.medal.text.2', [
                    'medal' => $medal,
                    'level' => $this->medalChecker->translateMedalLevel($level),
                ]
            );
        }

        $response[] = '';
        $response[] = $this->translator->trans(
            'new.medal.text.3', [
                'link' => sprintf('%s/stats/agent/%s', $pageBase, $agent->getId()),
            ]
        );
        $response[] = '';
        $response[] = $this->translator->trans(
            'new.medal.text.4', [
                'tadaa' => $tada.$tada.$tada,
            ]
        );

        return $this->api->sendMessage(
            $groupId,
            implode("\n", $response),
            'markdown'
        );
    }

    public function sendMessage($chatId, $text): Message
    {
        return $this->api->sendMessage($chatId, $text, 'markdown');
    }

    public function sendNewUserMessage(int $chatId, User $user): Message
    {
        $message = [];

        $message[] = '** New User **';
        $message[] = '';
        $message[] = 'A new user has just registered: '.$user->getEmail();
        $message[] = '';
        $message[] = 'Please verify!';

        return $this->api->sendMessage($chatId, implode("\n", $message), 'markdown');
    }

    public function sendPhoto($chatId, $photo, $caption): Message
    {
        return $this->api->sendPhoto($chatId, $photo, $caption, null, null, false, 'html');
    }
}
