<?php

namespace App\Controller;

use App\Form\AgentAccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @IsGranted("ROLE_USER")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="app_account")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request, Security $security): Response {
        $user = $security->getUser();

        $agent = $user->getAgent();

        if (!$agent) {
            throw $this->createAccessDeniedException(
                'No tiene un agente asignado a su usuario - contacte un admin!'
            );
        }

        $form = $this->createForm(AgentAccountType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'app_account',
                [
                    // 'id' => $agent->getId(),
                ]
            );
        }

        return $this->render(
            'account/index.html.twig',
            [
                'agent' => $agent,
                'form'  => $form->createView(),
            ]
        );
    }
}
