<?php

namespace App\Subscriber;

use App\Repository\AgentRepository;
use App\Service\TelegramBotHelper;
use App\Service\Templater;
use BoShurik\TelegramBotBundle\Event\Telegram\UpdateEvent;
use BoShurik\TelegramBotBundle\Event\TelegramEvents;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InputMessageContent;
use TelegramBot\Api\Types\Inline\QueryResult\Contact;

class TelegramUpdateSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    /**
     * @var BotApi
     */
    private $botApi;

    /**
     * @var AgentRepository
     */
    private $agentRepository;

    private $isAllowedChat = false;

    /**
     * @var Templater
     */
    private $templater;
    /**
     * @var TelegramBotHelper
     */
    private $telegramBotHelper;

    public function __construct(
        LoggerInterface $logger,
        BotApi $botApi,
        AgentRepository $agentRepository,
        Templater $templater,
        TelegramBotHelper $telegramBotHelper
    ) {
        $this->setLogger($logger);
        $this->botApi            = $botApi;
        $this->agentRepository   = $agentRepository;
        $this->templater         = $templater;
        $this->telegramBotHelper = $telegramBotHelper;
    }

    public function check(UpdateEvent $event)
    {
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
            TelegramEvents::UPDATE => [
                ['check', 99],
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

        if (!$this->isAllowedChat) {
            $this->botApi->sendMessage(
                $event->getUpdate()->getMessage()->getChat()->getId(),
                'No access for group '.$event->getUpdate()->getMessage()->getChat()->getId()
            );

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

        if (!$this->isAllowedChat) {
            $text = 'You are not allowed to use this bot.';
            $this->botApi->answerInlineQuery(
                $inlineQuery->getId(),
                [new Contact(1, 666, $inlineQuery->getFrom()->getId(), $text)]
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

            $text = $this->templater->replaceAgentTemplate('agent-info.md', $agent);

            $c->setInputMessageContent(new InputMessageContent\Text($text, 'markdown', true));

            $results[] = $c;
        }

        $this->botApi->answerInlineQuery(
            $inlineQuery->getId(),
            $results
        );

        return $this;
    }
}
