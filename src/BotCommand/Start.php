<?php

namespace App\BotCommand;

use App\Repository\AgentRepository;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;

class Start extends AbstractCommand implements PublicCommandInterface
{
    private AgentRepository $agentRepository;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        AgentRepository $agentRepository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->agentRepository = $agentRepository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function getName(): string
    {
        return '/start';
    }

    public function getDescription(): string
    {
        return 'Start command';
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function execute(BotApi $api, Update $update): void
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
