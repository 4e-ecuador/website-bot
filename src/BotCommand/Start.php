<?php

namespace App\BotCommand;

use App\Repository\AgentRepository;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
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

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        AgentRepository $agentRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->agentRepository = $agentRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
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
                self::REGEXP,
                $update->getMessage()->getText(),
                $matches
            )
            ) {
                $response[] = 'Missing code';
            } else {
                $code = preg_replace('/[^0-9a-z]+/', '', $matches[3]);
                $agent = $this->agentRepository->findOneBy(
                    ['telegram_connection_secret' => $code]
                );

                if (!$agent) {
                    $response[] = $this->translator
                        ->trans('bot.message.missing.agent');
                    $response[] = 'code: '.$code;
                } else {
                    $agent->setTelegramName($tgUser->getUsername());
                    $agent->setTelegramId($tgUser->getId());

                    $this->entityManager->persist($agent);
                    $this->entityManager->flush();

                    $response[] = $this->translator
                        ->trans('bot.message.agent.verified');
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
