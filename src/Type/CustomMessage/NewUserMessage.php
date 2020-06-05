<?php

namespace App\Type\CustomMessage;

use App\Entity\User;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;

class NewUserMessage extends AbstractCustomMessage
{

    /**
     * @var User
     */
    private $user;

    public function __construct(TelegramBotHelper $telegramBotHelper, User $user)
    {
        $this->user = $user;

        parent::__construct($telegramBotHelper);
    }

    public function getMessage(): array
    {
        $message = [];

        $message[] = '** New User **';
        $message[] = '';
        $message[] = 'A new user has just registered: '.$this->user->getEmail();
        $message[] = '';
        $message[] = 'Please verify!';

        return $message;
    }
}
