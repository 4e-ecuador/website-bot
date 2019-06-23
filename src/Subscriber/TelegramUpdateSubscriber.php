<?php

namespace App\Subscriber;

use BoShurik\TelegramBotBundle\Event\Telegram\UpdateEvent;
use BoShurik\TelegramBotBundle\Event\TelegramEvents;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InputMessageContent;
use TelegramBot\Api\Types\Inline\QueryResult\AbstractInlineQueryResult;
use TelegramBot\Api\Types\Inline\QueryResult\Contact;

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
        $this->respondWelcome($event)
            ->respondInlineQuery($event);
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

    private function respondWelcome(UpdateEvent $event)
    {
        $message = $event->getUpdate()->getMessage();

        if (!$message) {
            return $this;
        }

        $newChatMember = $message->getNewChatMember();

        if (!$newChatMember) {
            return $this;
        }

        $me = $this->botApi->getMe();

        if ($me->getId() === $newChatMember->getId()) {
            // Bot has been added to chat
            $text = sprintf(
                "Hello my name is %s (@%s) and I am a friendly BOT =;)\n\nPlease /start me now.",
                $me->getFirstName(),
                $me->getUsername()
            );
        } else {
            // New chat member
            $text = sprintf('Hello @%s welcome on board =;)', $newChatMember->getUsername());
        }

        $this->botApi->sendMessage(
            $event->getUpdate()->getMessage()->getChat()->getId(),
            $text
        );

        return $this;
    }

    private function respondInlineQuery(UpdateEvent $event)
    {
        $inlineQuery = $event->getUpdate()->getInlineQuery();

        if (!$inlineQuery) {
            return $this;
        }

        $results = [];

        $results[] = new Contact('1', '123-456', 'helloooo', 'aaacc',
            null, null, null, new InputMessageContent\Text('yay'));
        $results[] = new Contact('2', '123-456',  'helloooo222'.$inlineQuery->getQuery(),);

//        $results[] = new InputMessageContent\Text('yay');

        $this->botApi->answerInlineQuery(
            $inlineQuery->getId(),
            $results

        );

    }
}
