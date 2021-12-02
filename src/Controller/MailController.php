<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mailer')]
class MailController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/send-confirmation-mail/{id}', name: 'user_send_confirmation_mail', methods: ['GET'])]
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
