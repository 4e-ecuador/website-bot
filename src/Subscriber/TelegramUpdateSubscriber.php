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

        return;
        $message = $event->getUpdate()->getMessage();

        if ($message) {
            $this->isAllowedChat = $this->telegramBotHelper->checkChatId(
                $message->getChat()->getId()
            );
        } else {
            $inlineQuery = $event->getUpdate()->getInlineQuery();

            if ($inlineQuery) {
                $this->isAllowedChat = $this->telegramBotHelper->checkUserId(
                    $inlineQuery->getFrom()->getId()
                );
            }
        }
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

            $text = sprintf(
                'Hello @%s welcome on board =;)',
                $newChatMember[0]->getUsername()
            );
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
    private function respondInlineQuery(UpdateEvent $event
    ): TelegramUpdateSubscriber {
        // Disabled
        return $this;

        $inlineQuery = $event->getUpdate()->getInlineQuery();

        if (!$inlineQuery) {
            return $this;
        }

        if (!$this->isAllowedChat) {
            $text = "You are not allowed to use this bot.\n\nYou may ask an admin to add your ID: "
                .$inlineQuery->getFrom()->getId();
            $contact = new Contact(
                1,
                666,
                'You are not allowed to use this bot.'
            );
            $contact->setInputMessageContent(
                new InputMessageContent\Text($text, 'markdown', true)
            );

            $this->botApi->answerInlineQuery(
                $inlineQuery->getId(),
                [$contact]

            );

            return $this;
        }

        $search = $inlineQuery->getQuery();

        if (!$search) {
            // Empty query
        }

        // Keep only the first 8 chars
        $search = substr($search, 0, 8);

        // @todo sanitize more?

        $agents = $this->agentRepository->searchByAgentName($search);

        $results = [];

        foreach ($agents as $agent) {
            $c = new Contact($agent->getId(), 0, $agent->getNickname());

            $text = $this->templater->replaceAgentTemplate(
                'agent-info.md',
                $agent
            );

            $c->setInputMessageContent(
                new InputMessageContent\Text($text, 'markdown', true)
            );

            $results[] = $c;
        }

        $this->botApi->answerInlineQuery(
            $inlineQuery->getId(),
            $results
        );

        return $this;
    }
}
