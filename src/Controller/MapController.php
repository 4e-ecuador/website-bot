<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Repository\AgentRepository;
use App\Repository\MapGroupRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class MapController extends AbstractController
{
    /**
     * @Route("/map", name="agent-map")
     * @IsGranted("ROLE_AGENT")
     */
    public function map(AgentRepository $agentRepository, MapGroupRepository $mapGroupRepository): Response
    {
        $mapGroups = [];

        foreach ($mapGroupRepository->findAll() as $group) {
            $mapGroups[] = $group->getName();
        }

        return $this->render(
            'map/index.html.twig',
            [
                'agents'    => $agentRepository->findAll(),
                'mapGroups' => $mapGroups,
            ]
        );
    }

    /**
     * @Route("/map_json", name="map-json")
     * @IsGranted("ROLE_AGENT")
     */
    public function mapJson(AgentRepository $agentRepository, MapGroupRepository $mapGroupRepository, Request $request): JsonResponse
    {
        $mapGroup = $mapGroupRepository->findOneBy(['name' => $request->get('group', '4E')]);

        if (!$mapGroup) {
            throw new \UnexpectedValueException('Map group not found!');
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

    /**
     * @Route("/map/agent-info/{id}", name="agent-info")
     * @IsGranted("ROLE_AGENT")
     */
    public function mapAgentInfo(Agent $agent, TranslatorInterface $translator, Packages $assetsManager): Response
    {
        $response = [];

        if ($this->isGranted('ROLE_AGENT')) {
            $statsLink = $imgPath = '';
            switch ($agent->getFaction()->getName()) {
                case 'ENL':
                    $statsLink = $this->generateUrl('agent_stats', ['id' => $agent->getId()]);
                    $imgPath = $assetsManager->getUrl('build/images/logos/ENL.svg');
                    break;
                case 'RES':
                    $imgPath = $assetsManager->getUrl('build/images/logos/RES.svg');
                    break;
                default:
                    throw new \UnexpectedValueException('Unknown faction');
            }
            $link = $this->generateUrl('agent_show', ['id' => $agent->getId()]);
            $response[] = sprintf(
                '<img src="%s" alt="logo" style="height: 32px;"><a href="%s">%s</a>',
                $imgPath,
                $link,
                $agent->getNickname()
            );
            if ($agent->getRealName()) {
                $response[] = $agent->getRealName();
            }
            if ($statsLink) {
                $response[] = sprintf('<a href="%s">Stats</a>', $statsLink);
            }
        } else {
            $response[] = $translator->trans('Please log in');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $response[] = '';
            $response[] = 'More ADMIN info... TBD';
        }

        return new Response(implode('<br>', $response));
    }
}
