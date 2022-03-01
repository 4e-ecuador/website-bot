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
use UnexpectedValueException;

class Start extends AbstractCommand implements PublicCommandInterface
{
    public function __construct(
        private readonly AgentRepository $agentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator
    ) {
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
        try {
            $message = $update->getMessage();
            if (!$message) {
                throw new UnexpectedValueException('Missing message');
            }

            $tgUser = $message->getFrom();
            if (!$tgUser) {
                throw new UnexpectedValueException('Missing user');
            }

            if (!preg_match(
                self::REGEXP,
                $update->getMessage()->getText(),
                $matches
            )
            ) {
                throw new UnexpectedValueException('Missing code');
            }

            $code = preg_replace('/[^0-9a-z]+/', '', $matches[3]);
            $agent = $this->agentRepository
                ->findOneBy(['telegram_connection_secret' => $code]);

            if (!$agent) {
                throw new UnexpectedValueException(
                    $this->translator
                        ->trans('bot.message.missing.agent')
                );
            }

            $agent->setTelegramName($tgUser->getUsername())
                ->setTelegramId($tgUser->getId());

            $this->entityManager->persist($agent);
            $this->entityManager->flush();

            $response = $this->translator
                ->trans('bot.message.agent.verified');
        } catch (UnexpectedValueException $exception) {
            $response = $exception->getMessage();
        }

        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            $response,
            'markdown'
        );
    }
}
