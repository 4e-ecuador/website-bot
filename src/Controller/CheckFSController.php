<?php

namespace App\Controller;

use App\Repository\AgentRepository;
use App\Repository\FsDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckFSController extends AbstractController
{
    //#[Route('/check/fs', name: 'app_check_fs')]
    public function index(
        AgentRepository $agentRepository,
        FsDataRepository $fsDataRepository
    ): Response {
        /*
        $latest = $fsDataRepository->findLatest();

        $a = $latest[0];

        $data = json_decode($a->getData(), true, 512, JSON_THROW_ON_ERROR);

        $agents = $data['agents'];
        ksort($agents);

        $systemAgents = $agentRepository->findAll();

        $arr = [];

        if (array_key_exists('nikp3h', $agents)) {
            $ppp = 'zzz';
        }
        foreach ($systemAgents as $systemAgent) {
            if (array_key_exists($systemAgent->getNickname(), $agents)) {
                $arr[] = $systemAgent;
            }
        }

        foreach ($agents as $agent) {
            $ff = $agent;
            // if ($agent->)
}
        */
        return $this->render('check_fs/index.html.twig', [
        ]);
    }
}
