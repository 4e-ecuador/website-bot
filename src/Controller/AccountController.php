<?php

namespace App\Controller;

use App\Form\AgentAccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="app_account")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, Security $security, TranslatorInterface $translator): Response {
        $agent = $security->getUser()->getAgent();

        // $agent = $user->getAgent();

        if (!$agent) {
            throw $this->createAccessDeniedException(
                'No tiene un agente asignado a su usuario - contacte un admin!'
            );
        }

        $form = $this->createForm(AgentAccountType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $translator->trans('Your profile has been updated.'));
            return $this->redirectToRoute('default');
        }

        return $this->render(
            'account/index.html.twig',
            [
                'agent' => $agent,
                'form'  => $form->createView(),
                'message' => '',
            ]
        );
    }
}
