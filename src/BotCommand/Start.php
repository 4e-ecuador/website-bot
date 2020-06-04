<?php

namespace App\BotCommand;

use App\Repository\AgentRepository;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Doctrine\ORM\EntityManagerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class Start extends AbstractCommand implements PublicCommandInterface
{
    /**
     * @var AgentRepository
     */
    private $agentRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(AgentRepository $agentRepository, EntityManagerInterface $entityManager)
    {
        $this->agentRepository = $agentRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return '/start';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Start command';
    }

    /**
     * @inheritDoc
     */
    public function execute(BotApi $api, Update $update)
    {
        $response = [];

        $message = $update->getMessage();

        if (!$message) {
            $response[] = 'Missing message';
        } else {
            $tgUser = $message->getFrom();
            if (!$tgUser) {
                $response[] = 'Missing user';
            } elseif (!preg_match(
                self::REGEXP, $update->getMessage()->getText(), $matches
            )
            ) {
                $response[] = 'Missing code';
            } else {
                $agent = $this->agentRepository->findOneBy(['telegram_connection_secret' => $matches[3]]);

                if (!$agent) {
                    $response[] = 'Missing agent :(';
                } else {
                    $agent->setTelegramName($tgUser->getUsername());
                    $agent->setTelegramId($tgUser->getId());

                    $this->entityManager->persist($agent);
                    $this->entityManager->flush();

                    $response[] = 'You have been verified.';
                }
            }
        }

        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            implode("\n", $response),
            'markdown'
        );
    }
}
