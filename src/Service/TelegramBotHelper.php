<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\AgentStat;
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

    private $emojies = [
        'tadaa' => "",
        'redlight' => "\xF0\x9F\x9A\xA8",
        ];

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
        $tada = $this->emojies['tadaa'];

        $firstValue = reset($medalUps);
        $firstMedal = key($medalUps);

        $response = [];

        $response[] = $this->translator->trans('new.medal.header');

        $response[] = '[ ]('.$pageBase.'/build/images/badges/'
            .$this->medalChecker->getBadgePath($firstMedal, $firstValue).')';

        $response[] = $this->translator->trans(
            'new.medal.text.1', [
                'medals' => count($medalUps),
                'agent'  => str_replace(
                    '_', '\\_',
                    $agent->getTelegramName() ?: $agent->getNickname()
                ),
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

    public function sendLevelUpMessage(Agent $agent, int $level, string $groupId)
    {
        $pageBase = $_ENV['PAGE_BASE_URL'];
        $tada = "\xF0\x9F\x8E\x89";

        $response = [];

        $response[] = $this->translator->trans('new.medal.header');

        $response[] = '[ ]('.$pageBase.'/build/images/badges/'
            .$this->medalChecker->getBadgePath('LevelUp_'.$level, 0).')';

        $response[] = $this->translator->trans(
            'new.level.text.1', [
                'agent' => str_replace(
                    '_', '\\_', $agent->getTelegramName()
                    ?: $agent->getNickname()
                ),
            ]
        );

        $response[] = '';

        $response[] = $this->translator->trans(
            'new.level.text.2',
            ['level' => $level]
        );

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

    public function getGroupId(string $name = 'default'): int
    {
        switch ($name) {
            case 'default':
                $id = $_ENV['ANNOUNCE_GROUP_ID_1'];
                break;
            case 'test':
                $id = $_ENV['ANNOUNCE_GROUP_ID_TEST'];
                break;
            case 'admin':
                $id = $_ENV['ANNOUNCE_GROUP_ID_ADMIN'];
                break;
            case 'intro':
                $id = $_ENV['ANNOUNCE_GROUP_ID_INTRO'];
                break;
            default:
                throw new \UnexpectedValueException('Unknown group name');
        }

        if (!$id) {
            throw new \UnexpectedValueException('Required env var has not been set up.');
        }

        return (int)$id;
    }

    public function sendSmurfAlertMessage(User $user, Agent $agent, AgentStat $statEntry)
    {
        $adminCC = $_ENV['ANNOUNCE_ADMIN_CC'];
        $message = [];

        $message[] = str_repeat($this->emojies['redlight'], 3).'** SMURF ALERT !!! **'.str_repeat($this->emojies['redlight'], 3);
        $message[] = '';
        $message[] = 'We have detected an agent with the faction: '
            .$statEntry->getFaction();
        $message[] = '';
        $message[] = 'Agent: '.$agent->getNickname();
        $message[] = 'ID: '.$agent->getId();
        $message[] = '';
        $message[] = 'User: '.$user->getUsername();
        $message[] = 'ID: '.$user->getId();
        $message[] = '';
        $message[] = 'Please verify!';
        $message[] = '';
        $message[] = 'CC: '.$adminCC;

        return $this->api->sendMessage(
            $this->getGroupId('admin'),
            str_replace('_', '\\_', implode("\n", $message)),
            'markdown'
        );
    }

    public function sendNicknameMismatchMessage(User $user, Agent $agent, AgentStat $statEntry)
    {
        $adminCC = $_ENV['ANNOUNCE_ADMIN_CC'];
        $message = [];

        $message[] = str_repeat($this->emojies['redlight'], 2).'** Nickname mismatch **'.str_repeat($this->emojies['redlight'], 2);
        $message[] = '';
        $message[] = 'We have detected a different nickname in uploaded stats!';
        $message[] = '';
        $message[] = 'Nick: '.$statEntry->getNickname();
        $message[] = '';
        $message[] = 'Agent: '.$agent->getNickname();
        $message[] = 'ID: '.$agent->getId();
        $message[] = '';
        $message[] = 'User: '.$user->getUsername();
        $message[] = 'ID: '.$user->getId();
        $message[] = '';
        $message[] = 'Please verify!';
        $message[] = '';
        $message[] = 'CC: '.$adminCC;

        return $this->api->sendMessage(
            $this->getGroupId('admin'),
            str_replace('_', '\\_', implode("\n", $message)),
            'markdown'
        );
    }
}
