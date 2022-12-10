<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mailer')]
class MailController extends AbstractController
{
    #[Route(path: '/send-confirmation-mail/{id}', name: 'user_send_confirmation_mail', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function sendConfirmationMail(
        User $user,
        MailerHelper $mailerHelper,
        TranslatorInterface $translator
    ): Response {
        $subject = $translator->trans('email.confirmation.subject');
        $response = $mailerHelper->sendConfirmationMail($user, $subject);

        return new Response($response);
    }
}
