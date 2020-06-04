<?php

namespace App\Controller;

use App\Form\AgentAccountType;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
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
    public function index(Request $request, Security $security, TranslatorInterface $translator, MedalChecker $medalChecker, TelegramBotHelper $telegramBotHelper): Response
    {
        $agent = $security->getUser()->getAgent();

        if (!$agent) {
            throw $this->createAccessDeniedException($translator->trans('user.not.verified.2'));
        }

        $telegramConnectLink = $telegramBotHelper->getConnectLink($agent);

        $agentAccount = $request->request->get('agent_account');

        $customMedals = json_decode($agent->getCustomMedals(), true);

        if ($agentAccount) {
            $customMedals = $request->request->get('customMedals');

            $agentAccount['customMedals'] = json_encode($customMedals);

            $request->request->set('agent_account', $agentAccount);
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
                'agent'               => $agent,
                'agentCustomMedals'   => $customMedals,
                'form'                => $form->createView(),
                'message'             => '',
                'telegramConnectLink' => $telegramConnectLink,
                'customMedals'        => $medalChecker->getCustomMedalGroups(),
            ]
        );
    }
}
