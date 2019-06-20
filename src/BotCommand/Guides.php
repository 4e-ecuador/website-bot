<?php


namespace App\BotCommand;

use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class Guides extends AbstractCommand implements PublicCommandInterface
{
    public function __construct(ContainerInterface $container)
    {
//        parent::__construct();
        $this->container = $container;
    }
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return '/guias';
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return 'Example command';
    }

    /**
     * @inheritDoc
     */
    public function execute(BotApi $api, Update $update)
    {
//        preg_match(self::REGEXP, $update->getMessage()->getText(), $matches);
//        $who  = !empty($matches[3]) ? $matches[3] : "World";
//        $text = sprintf('Hello *%s*', $who);

        $text = "Guias blabla\n\n1. lala [aaa](https://aa.bb)\n1. lulu\n\nyau";
        $api->sendMessage($update->getMessage()->getChat()->getId(), $text, 'markdown');
    }
}
