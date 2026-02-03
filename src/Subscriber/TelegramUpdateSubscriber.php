<?php

namespace App\Subscriber;

use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class TelegramUpdateSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    private bool $isAllowedChat = false;

    public function __construct(
        LoggerInterface $logger,
        private readonly BotApi $botApi,
    ) {
        $this->setLogger($logger);
    }

    public function check(UpdateEvent $event): void
    {
        $this->isAllowedChat = true;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UpdateEvent::class => [
                ['check', 99],
                ['processUpdate', 0],
                ['writeLog', -10],
            ],
        ];
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function processUpdate(UpdateEvent $event): void
    {
        $this->respondWelcome($event);
        $this->respondInlineQuery();
    }

    public function writeLog(UpdateEvent $event): void
    {
        $message = $event->getUpdate()->getMessage();

        if (!$message) {
            return;
        }

        $this->logger->info(
            sprintf(
                'Received a new message: %s',
                $event->getUpdate()->getMessage()->getText()
            )
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function respondWelcome(UpdateEvent $event): void
    {
        $message = $event->getUpdate()->getMessage();

        if (!$message) {
            return;
        }

        $newChatMember = $message->getNewChatMembers();

        if (!$newChatMember) {
            return;
        }

        if (!$this->isAllowedChat) {
            $this->botApi->sendMessage(
                $event->getUpdate()->getMessage()->getChat()->getId(),
                'No access for group '.$event->getUpdate()->getMessage()
                    ->getChat()->getId()
            );

            return;
        }

        $me = $this->botApi->getMe();

        if ($me->getId() === $newChatMember[0]->getId()) {
            // Bot has been added to chat
            $text = sprintf(
                "Hello my name is %s (@%s) and I am a friendly BOT =;)\n\nDevs: %s",
                $me->getFirstName(),
                $me->getUsername(),
                $event->getUpdate()->getMessage()->getChat()->getId()
            );
        } else {
            // New chat member

            // DISABLED!
            return;
        }

        $this->botApi->sendMessage(
            $event->getUpdate()->getMessage()->getChat()->getId(),
            $text
        );
    }

    private function respondInlineQuery(): void
    {
        // Disabled
    }
}
