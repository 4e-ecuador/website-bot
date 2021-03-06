<?php

namespace App\Controller;

use App\Form\AgentAccountType;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use JsonException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use UnexpectedValueException;

class AccountController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     * @throws JsonException
     */
    #[Route(path: '/account', name: 'app_account')]
    public function account(Request $request, Security $security, TranslatorInterface $translator, MedalChecker $medalChecker, TelegramBotHelper $telegramBotHelper): Response
    {
        $user = $security->getUser();
        if (!$user) {
            throw new UnexpectedValueException('User not found');
        }
        $agent = $user->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException(
                $translator->trans('user.not.verified.2')
            );
        }
        $agentAccount = $request->request->get('agent_account');
        $customMedals = json_decode($agent->getCustomMedals(), true);
        if ($agentAccount) {
            $customMedals = $request->request->get('customMedals');

            $agentAccount['customMedals'] = json_encode(
                $customMedals,
                JSON_THROW_ON_ERROR
            );

            $request->request->set('agent_account', $agentAccount);
        }
        $form = $this->createForm(AgentAccountType::class, $agent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                'success',
                $translator->trans('Your profile has been updated.')
            );

            return $this->redirectToRoute('default');
        }
        return $this->render(
            'account/index.html.twig',
            [
                'agent'                => $agent,
                'agentCustomMedals'    => $customMedals,
                'form'                 => $form->createView(),
                'message'              => '',
                'telegramConnectLink'  => $telegramBotHelper->getConnectLink(
                    $agent
                ),
                'telegramConnectLink2' => $telegramBotHelper->getConnectLink2(
                    $agent
                ),
                'customMedals'         => $medalChecker->getCustomMedalGroups(),
            ]
        );
    }

    /**
     * @IsGranted("ROLE_INTRO_AGENT")
     */
    #[Route(path: '/account/tg-disconnect', name: 'tg_disconnect')]
    public function telegramDisconnect(Security $security): RedirectResponse
    {
        $agent = $security->getUser()->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException('not allowed');
        }
        $agent->setTelegramId(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($agent);
        $em->flush();
        return $this->redirectToRoute('app_account');
    }

    /**
     * @IsGranted("ROLE_INTRO_AGENT")
     */
    #[Route(path: '/account/tg-connect', name: 'tg_connect')]
    public function telegramConnect(Security $security, TelegramBotHelper $telegramBotHelper): RedirectResponse
    {
        $agent = $security->getUser()->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException('not allowed');
        }
        return $this->redirect($telegramBotHelper->getConnectLink($agent));
    }
}
