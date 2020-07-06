<?php

namespace App\BotCommand;

use App\Repository\AgentRepository;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;

class Agents extends AbstractCommand implements PublicCommandInterface
{
    private AgentRepository $repository;

    public function __construct(AgentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getName(): string
    {
        return '/agents';
    }

    public function getDescription(): string
    {
        return 'Agents lookup command';
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function execute(BotApi $api, Update $update): void
    {
        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            'not active'
        );

        return;

        $agents = $this->repository->findAll();
        $response = [];

        if (preg_match(
            self::REGEXP,
            $update->getMessage()->getText(),
            $matches
        )
        ) {
            $parts = explode(' ', $matches[3]);
            if (2 !== count($parts)) {
                $response[] = sprintf(
                    'We have %d agents in the database',
                    count($agents)
                );
                $response[] = '';
                $response[] = 'Usage:';
                $response[] = $this->getName().' like <agentname>';
                $response[] = $this->getName().' in <city>';
            } else {
                switch ($parts[0]) {
                    case 'like':
                        $response[] = sprintf(
                            'Agents with names like "%s"',
                            $parts[1]
                        );
                        $response[] = '';
                        $agentsLike = [];
                        foreach ($agents as $agent) {
                            if (strpos($agent->getNickname(), $parts[1])
                                !== false
                            ) {
                                $agentsLike[] = ' `'.$agent->getNickname()
                                    .'` - '.$agent->getRealName();
                            }
                        }

                        if ($agentsLike) {
                            $response = array_merge($response, $agentsLike);
                        } else {
                            $response[] = sprintf(
                                'There are no agents with nick names like "%s"',
                                $parts[1]
                            );
                        }
                        break;

                    case 'in':
                        $response[] = 'not supported yet :(';
                        break;

                    default:
                        $response[] = 'Unknown command';
                }
            }
        } else {
            $response[] = sprintf(
                'We have %d agents in the database',
                count($agents)
            );
        }

        $api->sendMessage(
            $update->getMessage()->getChat()->getId(),
            implode("\n", $response),
            'markdown'
        );
    }
}
