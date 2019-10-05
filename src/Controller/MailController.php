<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/mailer")
 */
class MailController extends AbstractController
{
    /**
     * @Route("/send-confirmation-mail/{id}", name="user_send_confirmation_mail", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function sendConfirnationMail(User $user, MailerHelper $mailerHelper, TranslatorInterface $translator)
    {
        $email = $user->getEmail();
        $subject = $translator->trans('email.confirmation.subject');
        $body = $this->renderView(
            'emails/confirmation.html.twig',
            ['user' => $user]
        );

        $response = $mailerHelper->sendConfirmationMail($email, $subject, $body);

        return new Response($response);
    }
}
