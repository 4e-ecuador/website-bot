<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerHelper
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%env(APP_EMAIL)%')] private readonly string $email,
        #[Autowire('%env(APP_EMAIL_NAME)%')] private readonly string $emailName,
    ) {
    }

    public function sendConfirmationMail(User $user, string $subject): string
    {
        try {
            $message = $this->createNewTwigMessage()
                ->to(new Address($user->getEmail(), $user->getUserAgentName()))
                ->subject($subject)
                ->htmlTemplate('emails/confirmation.html.twig')
                ->context(['user' => $user,]);

            $this->mailer->send($message);

            $response = 'Confirmation mail has been sent to '.$user->getEmail();
        } catch (TransportExceptionInterface $exception) {
            $response = $exception->getMessage();
        }

        return $response;
    }

    public function sendNewCommentMail(Comment $comment): string
    {
        try {
            $message = $this->createNewTwigMessage()
                ->to($this->email)
                ->subject('New Comment')
                ->htmlTemplate('emails/new_comment.html.twig')
                ->context(['comment' => $comment,]);

            $this->mailer->send($message);

            $response = 'Confirmation mail has been sent to '.$this->email;
        } catch (TransportExceptionInterface $exception) {
            $response = $exception->getMessage();
        }

        return $response;
    }

    public function sendNewUserMail(User $user): string
    {
        try {
            $message = $this->createNewTwigMessage()
                ->to($this->email)
                ->subject('New User')
                ->htmlTemplate('emails/new_user.html.twig')
                ->context(['user' => $user,]);

            $this->mailer->send($message);

            $response = 'Mail has been sent to '.$this->email;
        } catch (TransportExceptionInterface $exception) {
            $response = $exception->getMessage();
        }

        return $response;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendTestMail(string $email): void
    {
        $message = $this->createNewMessage()
            ->to($email)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $this->mailer->send($message);
    }

    private function createNewMessage(): Email
    {
        return (new Email())
            ->from(new Address($this->email, $this->emailName));
    }

    private function createNewTwigMessage(): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from(new Address($this->email, $this->emailName));
    }
}
