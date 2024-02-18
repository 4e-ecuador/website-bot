<?php

namespace App\Type\CustomMessage;

use App\Entity\User;
use App\Type\AbstractCustomMessage;

class NewUserMessage extends AbstractCustomMessage
{
    private User $user;

    public function getMessage(): array
    {
        return ['** New User **', '', 'A new user has just registered: '.$this->user->getEmail(), '', 'Please verify!'];
    }

    public function setUser(User $user): NewUserMessage
    {
        $this->user = $user;

        return $this;
    }
}
