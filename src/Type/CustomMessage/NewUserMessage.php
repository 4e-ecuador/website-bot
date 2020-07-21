<?php

namespace App\Type\CustomMessage;

use App\Entity\User;
use App\Type\AbstractCustomMessage;

class NewUserMessage extends AbstractCustomMessage
{
    private User $user;

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

    public function setUser(User $user): NewUserMessage
    {
        $this->user = $user;

        return $this;
    }
}
