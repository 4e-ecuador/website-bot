<?php

namespace App\Controller;

use App\Form\AgentAccountType;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use UnexpectedValueException;

class AccountController extends BaseController
{
    #[Route(path: '/account', name: 'app_account', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function account(
        Request $request,
        TranslatorInterface $translator,
        MedalChecker $medalChecker,
        TelegramBotHelper $telegramBotHelper,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            throw new UnexpectedValueException('User not found');
        }

        $agent = $user->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException(
                $translator->trans('user.not.verified.2')
            );
        }

        $agentAccount = $request->request->all('agent_account');
        $customMedals = json_decode(
            (string)$agent->getCustomMedals(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        if ($agentAccount !== []) {
            $customMedals = $request->request->all('customMedals');

            $agentAccount['customMedals'] = json_encode(
                $customMedals,
                JSON_THROW_ON_ERROR
            );

            $request->request->set('agent_account', $agentAccount);
        }

        $form = $this->createForm(AgentAccountType::class, $agent);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash(
                'success',
                $translator->trans('Your profile has been updated.')
            );

            return $this->redirectToRoute('default');
        }

        $lat = $agent->getLat() ?: -1.262326;
        $lon = $agent->getLon() ?: -79.09357;
        $zoom = $agent->getLat() ? 12 : 5;

        $map = (new Map('default'))
            ->center(new Point($lat, $lon))
            ->zoom($zoom)
                /*
            ->addMarker(
                new Marker(
                    position: new Point($lat, $lon),
                    title: 'Lyon',
                    infoWindow: new InfoWindow(
                        content: '<p>Thank you <a href="https://github.com/Kocal">@Kocal</a> for this component!</p>',
                    )
                )
            )
            */
            ->options(
                (new LeafletOptions())
                    ->tileLayer(
                        new TileLayer(
                            url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                            options: ['maxZoom' => 19]
                        )
                    )
            );

        return $this->render(
            'account/index.html.twig',
            [
                'agent'                => $agent,
                'agentCustomMedals'    => $customMedals,
                'form'                 => $form,
                'telegramConnectLink'  => $telegramBotHelper
                    ->getConnectLink($agent),
                'telegramConnectLink2' => $telegramBotHelper
                    ->getConnectLink2($agent),
                'customMedals'         => $medalChecker->getCustomMedalGroups(),
                'map'                  => $map,
            ]
        );
    }

    #[Route(path: '/account/tg-disconnect', name: 'tg_disconnect', methods: ['GET'])]
    #[IsGranted('ROLE_INTRO_AGENT')]
    public function telegramDisconnect(EntityManagerInterface $entityManager
    ): RedirectResponse {
        $agent = $this->getUser()?->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException('not allowed');
        }

        $agent->setTelegramId(null);
        $entityManager->persist($agent);
        $entityManager->flush();

        return $this->redirectToRoute('app_account');
    }

    #[Route(path: '/account/tg-connect', name: 'tg_connect', methods: ['GET'])]
    #[IsGranted('ROLE_INTRO_AGENT')]
    public function telegramConnect(TelegramBotHelper $telegramBotHelper
    ): RedirectResponse {
        $agent = $this->getUser()?->getAgent();
        if (!$agent) {
            throw $this->createAccessDeniedException('not allowed');
        }

        return $this->redirect($telegramBotHelper->getConnectLink($agent));
    }
}
