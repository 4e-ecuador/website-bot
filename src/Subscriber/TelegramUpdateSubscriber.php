<?php

namespace App\Subscriber;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use App\Service\Templater;
use BoShurik\TelegramBotBundle\Event\UpdateEvent;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Inline\InputMessageContent;
use TelegramBot\Api\Types\Inline\QueryResult\Contact;

class TelegramUpdateSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    private bool $isAllowedChat = false;

    public function __construct(
        LoggerInterface $logger,
        private readonly BotApi $botApi,
        private readonly AgentRepository $agentRepository,
        private readonly Templater $templater,
        private readonly TelegramBotHelper $telegramBotHelper
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
        $this->respondWelcome($event)->respondInlineQuery();
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
    private function respondWelcome(UpdateEvent $event
    ): TelegramUpdateSubscriber {
        $message = $event->getUpdate()->getMessage();

        if (!$message) {
            return $this;
        }

        $newChatMember = $message->getNewChatMembers();

        if (!$newChatMember) {
            return $this;
        }

        if (!$this->isAllowedChat) {
            $this->botApi->sendMessage(
                $event->getUpdate()->getMessage()->getChat()->getId(),
                'No access for group '.$event->getUpdate()->getMessage()
                    ->getChat()->getId()
            );

            return $this;
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
            return $this;
        }

        $this->botApi->sendMessage(
            $event->getUpdate()->getMessage()->getChat()->getId(),
            $text
        );

        return $this;
    }

    /**
     * @throws Exception
     */
    private function respondInlineQuery(): TelegramUpdateSubscriber
    {
        // Disabled
        return $this;
    }
}
