<?php

namespace App\Subscriber;

use BoShurik\TelegramBotBundle\Event\Telegram\UpdateEvent;
use BoShurik\TelegramBotBundle\Event\TelegramEvents;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramBot\Api\BotApi;

class TelegramUpdateSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    /**
     * @var BotApi
     */
    private $botApi;

    public function __construct(LoggerInterface $logger, BotApi $botApi)
    {
        $this->setLogger($logger);
        $this->botApi = $botApi;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TelegramEvents::UPDATE => [
                ['processUpdate', 0],
                ['writeLog', -10],
            ],
        ];
    }

    public function processUpdate(UpdateEvent $event): void
    {
        $newMember = $event->getUpdate()->getMessage()->getNewChatMember();

        if ($newMember) {
            $me = $this->botApi->getMe();

            if ($me->getId() === $newMember->getId()) {
                // Bot has been added to chat
                $text = sprintf("Hello my name is %s (@%s) and I am a friendly BOT =;)\n\nPlease /start me now.",$newMember->getFirstName(), $newMember->getUsername());
            } else {
                // New chat member
                $text = sprintf('Hello %s welcome on board `=;)`', $newMember->getUsername());
            }

            $this->botApi->sendMessage(
                $event->getUpdate()->getMessage()->getChat()->getId(),
                $text
            );
        }
    }

    public function writeLog(UpdateEvent $event): void
    {
        $this->logger->info(
            sprintf(
                'Received a new message: %s',
                $event->getUpdate()->getMessage()->getText()
            )
        );
    }
}
