<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Repository\AgentRepository;
use App\Repository\MapGroupRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;

class MapController extends AbstractController
{
    #[Route(path: '/map', name: 'agent-map', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function map(
        MapGroupRepository $mapGroupRepository,
        #[Autowire('%env(APP_DEFAULT_LAT)%')] float $defaultLat,
        #[Autowire('%env(APP_DEFAULT_LON)%')] float $defaultLon,
    ): Response
    {
        return $this->render(
            'map/index.html.twig',
            [
                'mapGroups' => array_column(
                    $mapGroupRepository->getNames(),
                    'name'
                ),
                'defaultLat' => $defaultLat,
                'defaultLon' => $defaultLon,
            ]
        );
    }

    #[Route(path: '/map_json', name: 'map-json', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function mapJson(
        AgentRepository $agentRepository,
        MapGroupRepository $mapGroupRepository,
        Request $request
    ): JsonResponse {
        $mapGroup = $mapGroupRepository->findOneBy(
            ['name' => $request->get('group', '4E')]
        );
        if (!$mapGroup) {
            throw new UnexpectedValueException('Map group not found!');
        }
        $agents = $agentRepository->findMapAgents($mapGroup);
        $array = [];
        foreach ($agents as $agent) {
            $a = [];

            $a['name'] = $agent->getNickname();
            $a['lat'] = $agent->getLat();
            $a['lng'] = $agent->getLon();
            $a['id'] = $agent->getId();

            $array[] = $a;
        }

        return $this->json($array);
    }

    #[Route(path: '/map/agent-info/{id}', name: 'agent-info', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function mapAgentInfo(
        Agent $agent,
        Packages $assetsManager,
        UserRepository $userRepository
    ): Response {
        $response = [];
        $statsLink = $imgPath = '';
        switch ($agent->getFaction()->getName()) {
            case 'ENL':
                $statsLink = $this->generateUrl(
                    'agent_stats',
                    ['id' => $agent->getId()]
                );
                $imgPath = $assetsManager->getUrl(
                    'build/images/logos/ENL.svg'
                );
                break;
            case 'RES':
                $imgPath = $assetsManager->getUrl(
                    'build/images/logos/RES.svg'
                );
                break;
            default:
                throw new UnexpectedValueException('Unknown faction');
        }
        $user = $userRepository->findByAgent($agent);
        $userPic = $user && $user->getAvatarEncoded()
            ? sprintf(
                '<img src="%s" alt="Avatar" style="height: 32px;">',
                $user->getAvatarEncoded()
            )
            : '';
        $link = $this->generateUrl('agent_show', ['id' => $agent->getId()]);
        $response[] = sprintf(
            '<a href="%s"><img src="%s" alt="logo" style="height: 32px;">%s %s</a>',
            $link,
            $imgPath,
            $userPic,
            $agent->getNickname()
        );
        if ($agent->getRealName()) {
            $response[] = $agent->getRealName();
        }
        if ($statsLink) {
            $response[] = sprintf('<a href="%s">Stats</a>', $statsLink);
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            // $response[] = '';
            // $response[] = 'More ADMIN info... TBD';
        }

        return new Response(implode('<br>', $response));
    }
}
