<?php

namespace App\Service;

use App\Entity\User;
use Twig\Environment;

class MailerHelper
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $emailName;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var Environment
     */
    private $templating;

    public function __construct(string $email, string $emailName, \Swift_Mailer $mailer, Environment $templating)
    {
        $this->email = $email;
        $this->emailName = $emailName;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function sendConfirmationMail(string $recipient, string $subject, string $body): string
    {
        try {
            $message = (new \Swift_Message($subject))
                ->setFrom($this->email, $this->emailName)
                ->setTo($recipient)
                ->setBody($body, 'text/html');

            $logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $this->mailer->registerPlugin(
                new \Swift_Plugins_LoggerPlugin($logger)
            );

            $count = $this->mailer->send($message);

            if ($count) {
                $response = 'Confirmation mail has been sent to '.$recipient;
            } else {
                $response = 'There was an error sending your message :( '
                    .$logger->dump();
            }
        } catch (\InvalidArgumentException $exception) {
            $response = $exception->getMessage();
        }

        return $response;
    }

    public function sendNewCommentMail(string $body): string
    {
        $subject = 'New Comment';
        $recipient = $this->email;
        try {
            $message = (new \Swift_Message($subject))
                ->setFrom($this->email, $this->emailName)
                ->setTo($recipient)
                ->setBody($body, 'text/html');

            $logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $this->mailer->registerPlugin(
                new \Swift_Plugins_LoggerPlugin($logger)
            );

            $count = $this->mailer->send($message);

            if ($count) {
                $response = 'Confirmation mail has been sent to '.$recipient;
            } else {
                $response = 'There was an error sending your message :( '
                    .$logger->dump();
            }
        } catch (\InvalidArgumentException $exception) {
            $response = $exception->getMessage();
        }

        return $response;
    }

    public function sendNewUserMail(User $user)
    {
        $subject = 'New User';
        $recipient = $this->email;

        $body = $this->templating->render('emails/new_user.html.twig', ['user' => $user]);

        $message = (new \Swift_Message($subject))
            ->setFrom($this->email, $this->emailName)
            ->setTo($recipient)
            ->setBody($body, 'text/html');

        $count = $this->mailer->send($message);

        if ($count) {
            $response = 'Confirmation mail has been sent to '.$recipient;
        } else {
            $response = 'There was an error sending your message :( ';
        }

        return $response;
    }
}
