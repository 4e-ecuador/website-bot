<?php

namespace App\Service;

use App\Entity\Agent;
use App\Type\CustomMessage\LevelUpMessage;
use App\Type\CustomMessage\MedalDoubleMessage;
use App\Type\CustomMessage\NewMedalMessage;
use App\Type\CustomMessage\NotifyEventsMessage;
use App\Type\CustomMessage\NotifyUploadReminder;
use App\Type\CustomMessage\RecursionMessage;
use CURLFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Message;
use UnexpectedValueException;

class TelegramMessageHelper
{
    public function __construct(
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly MedalChecker $medalChecker,
        #[Autowire('%kernel.project_dir%')] private readonly string $rootDir,
        private readonly NewMedalMessage $newMedalMessage,
        private readonly MedalDoubleMessage $medalDoubleMessage,
        private readonly LevelUpMessage $levelUpMessage,
        private readonly NotifyEventsMessage $notifyEventsMessage,
        private readonly NotifyUploadReminder $notifyUploadReminder,
        private readonly RecursionMessage $recursionMessage
    ) {
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendNotifyUploadReminderMessage(int $chatId): Message
    {
        return $this->telegramBotHelper->sendMessage(
            $chatId,
            $this->notifyUploadReminder->getText()
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendNotifyEventsMessage(
        int $chatId,
        bool $firstAnnounce = false
    ): Message {
        $message = $this->notifyEventsMessage
            ->setFirstAnnounce($firstAnnounce)
            ->getText();

        if ($message === '' || $message === '0') {
            throw new UnexpectedValueException('No events :(');
        }

        return $this->telegramBotHelper->sendMessage($chatId, $message);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendRecursionMessage(
        string $groupName,
        Agent $agent,
        int $recursions
    ): Message {
        $message = $this->recursionMessage
            ->setAgent($agent)
            ->setRecursions($recursions)
            ->getText();

        $photo = new CURLFile(
            $this->rootDir.'/assets/images/badges/UniqueBadge_Simulacrum.png'
        );

        return $this->telegramBotHelper->sendPhoto(
            $this->telegramBotHelper->getGroupId($groupName),
            $photo,
            $message
        );
    }

    /**
     * @param array<string, int> $medalUps
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendNewMedalMessage(
        string $groupName,
        Agent $agent,
        array $medalUps
    ): Message {
        $message = $this->newMedalMessage
            ->setAgent($agent)
            ->setMedalUps($medalUps)
            ->getText();

        $firstValue = (int)reset($medalUps);
        $firstMedal = (string)key($medalUps);

        $photo = new CURLFile(
            $this->rootDir.'/assets/images/badges/'
            .$this->medalChecker->getBadgePath($firstMedal, $firstValue)
        );

        return $this->telegramBotHelper->sendPhoto(
            $this->telegramBotHelper->getGroupId($groupName),
            $photo,
            $message
        );
    }

    /**
     * @param array<string, int> $medalDoubles
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendMedalDoubleMessage(
        string $groupName,
        Agent $agent,
        array $medalDoubles
    ): Message {
        $message = $this->medalDoubleMessage
            ->setAgent($agent)
            ->setMedalDoubles($medalDoubles)
            ->getText();

        $firstMedal = key($medalDoubles);
        $firstValue = 5;

        $photo = new CURLFile(
            $this->rootDir.'/assets/images/badges/'
            .$this->medalChecker->getBadgePath($firstMedal, $firstValue)
        );

        return $this->telegramBotHelper->sendPhoto(
            $this->telegramBotHelper->getGroupId($groupName),
            $photo,
            $message
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sendLevelUpMessage(
        string $groupName,
        Agent $agent,
        int $level,
        int $recursions
    ): Message {
        $message = $this->levelUpMessage
            ->setAgent($agent)
            ->setLevel($level)
            ->setRecursions($recursions)
            ->getText();

        $photo = new CURLFile(
            $this->rootDir.'/assets/images/logos/ingress-enl.jpeg'
        );

        return $this->telegramBotHelper->sendPhoto(
            $this->telegramBotHelper->getGroupId($groupName),
            $photo,
            $message
        );
    }

}
