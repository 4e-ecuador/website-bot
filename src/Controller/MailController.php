<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailController extends AbstractController
{
    public function __construct(
        private readonly MailerHelper $mailerHelper,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/mailer/send-confirmation-mail/{id}', name: 'user_send_confirmation_mail', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function sendConfirmationMail(User $user): Response
    {
        $subject = $this->translator->trans('email.confirmation.subject');
        $response = $this->mailerHelper->sendConfirmationMail($user, $subject);

        return new Response($response);
    }
}
