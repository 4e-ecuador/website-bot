<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Entity\User;
use App\Exception\TelegramBotMissingChatIdException;
use App\Type\CustomMessage\LevelUpMessage;
use App\Type\CustomMessage\NewMedalMessage;
use App\Type\CustomMessage\NewUserMessage;
use App\Type\CustomMessage\NicknameMismatchMessage;
use App\Type\CustomMessage\RecursionMessage;
use App\Type\CustomMessage\SmurfAlertMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use UnexpectedValueException;

class TelegramBotHelper
{
    private BotApi $api;
    private MedalChecker $medalChecker;
    private TranslatorInterface $translator;
    private array $emojies
        = [
            'tadaa'      => "\xF0\x9F\x8E\x89",
            'redlight'   => "\xF0\x9F\x9A\xA8",
            'cross-mark' => "\xE2\x9D\x8C",
            'check-mark' => "\xE2\x9C\x85",
        ];
    private string $botName;
    private string $pageBaseUrl;
    private string $announceAdminCc;
    private array $groupIds = [];

    public function __construct(
        BotApi $api, MedalChecker $medalChecker, TranslatorInterface $translator,
        string $botName, string $pageBaseUrl, string $announceAdminCc,
        string $groupIdDefault, string $groupIdAdmin, string $groupIdIntro, string $groupIdTest
    ) {
        $this->api = $api;
        $this->medalChecker = $medalChecker;
        $this->translator = $translator;
        $this->botName = $botName;
        $this->pageBaseUrl = $pageBaseUrl;
        $this->announceAdminCc = $announceAdminCc;
        $this->groupIds = [
            'default' => $groupIdDefault,
            'admin' => $groupIdAdmin,
            'intro' => $groupIdIntro,
            'test' => $groupIdTest,
            ];
    }

    public function getGroupId(string $name = 'default'): int
    {
        if (array_key_exists($name, $this->groupIds)) {
            $id = $this->groupIds[$name];
        } else {
            throw new UnexpectedValueException('Unknown TG bot group name'.$name);
        }

        if (!$id) {
            throw new UnexpectedValueException(
                'Required TG bot env var has not been set up: '.$name
            );
        }

        return (int)$id;
    }

    public function getEmoji(string $name): string
    {
        return array_key_exists($name, $this->emojies)
            ? $this->emojies[$name]
            : '?';
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

        return sprintf('https://t.me/%s?start=%s', $this->botName, $agent->getTelegramConnectionSecret());
    }

    public function getConnectLink2($agent): string
    {
        return sprintf('http://www.telegram.me/%s?start=%s', $this->botName, $agent->getTelegramConnectionSecret());
    }

    public function sendMessage(int $chatId, string $text, bool $disablePreview = false): Message
    {
        if (0 === $chatId) {
            return new Message();
        }

        return $this->api->sendMessage($chatId, $text, 'markdown', $disablePreview);
    }

    public function sendPhoto($chatId, $photo, $caption): Message
    {
        return $this->api->sendPhoto($chatId, $photo, $caption, null, null, false, 'html');
    }

    public function sendNewMedalMessage(string $groupName, Agent $agent, array $medalUps): Message
    {
        $message = (new NewMedalMessage($this, $this->translator, $agent, $this->medalChecker, $medalUps, $this->pageBaseUrl))
            ->getMessage();

        return $this->sendMessage($this->getGroupId($groupName), implode("\n", $message));
    }

    public function sendLevelUpMessage(string $groupName, Agent $agent, int $level, int $recursions): Message
    {
        $message = (new LevelUpMessage($this, $this->translator, $agent, $this->medalChecker, $level, $recursions, $this->pageBaseUrl))
            ->getMessage();

        return $this->sendMessage($this->getGroupId($groupName), implode("\n", $message));
    }

    public function sendButtonMessage(string $groupName): Message
    {
        $prev = 3;
        $text = 'hello test';
        $buttons = [];

        $buttons[] = ['text' => 'Prev', 'callback_data' => '/post_'.$prev];

        return $this->api->sendMessage(
            $this->getGroupId($groupName),
            $text,
            'markdown',
            false,
            null,
            new InlineKeyboardMarkup([$buttons])
        );
    }

    public function sendNewUserMessage(int $chatId, User $user): Message
    {
        $message = (new NewUserMessage($this, $user))
            ->getMessage();

        return $this->sendMessage($chatId, implode("\n", $message));
    }

    public function sendSmurfAlertMessage(string $groupName, User $user, Agent $agent, AgentStat $statEntry): Message
    {
        $message = (new SmurfAlertMessage($this, $user, $agent, $statEntry, $this->announceAdminCc))
            ->getMessage();

        return $this->sendMessage(
            $this->getGroupId($groupName),
            str_replace('_', '\\_', implode("\n", $message))
        );
    }

    public function sendNicknameMismatchMessage(string $groupName, User $user, Agent $agent, AgentStat $statEntry): Message
    {
        $message = (new NicknameMismatchMessage($this, $user, $agent, $statEntry, $this->announceAdminCc))
            ->getMessage();

        return $this->sendMessage(
            $this->getGroupId($groupName),
            str_replace('_', '\\_', implode("\n", $message))
        );
    }

    public function sendRecursionMessage(string $groupName, Agent $agent, int $recursions): Message
    {
        $message = (new RecursionMessage($this, $this->translator, $agent, $recursions, $this->pageBaseUrl))
            ->getText();

        return $this->api->sendMessage($this->getGroupId($groupName), $message,'markdown');
    }
}
