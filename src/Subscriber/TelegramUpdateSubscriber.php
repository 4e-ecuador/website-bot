<?php

namespace App\Subscriber;

use App\Repository\AgentRepository;
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

    public function __construct(LoggerInterface $logger, BotApi $botApi, AgentRepository $agentRepository)
    {
        $this->setLogger($logger);
        $this->botApi = $botApi;
        $this->agentRepository = $agentRepository;
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
            $c = new Contact($agent->getId(), 0,$agent->getNickname());

            $info = [];

            $info[] = sprintf('Intel del agente: `%s`', $agent->getNickname());
            $info[] = '';
            $info[] = 'Nombre real: '.($agent->getRealName()?:'Desconocido');

            $c->setInputMessageContent(new InputMessageContent\Text(implode("\n", $info), 'markdown'));

            $results[] = $c;
        }

        $this->botApi->answerInlineQuery(
            $inlineQuery->getId(),
            $results
        );
    }
}
