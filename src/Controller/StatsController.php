<?php

namespace App\Controller;

use App\Repository\AgentStatRepository;
use App\Service\MedalChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    /**
     * @Route("/stats", name="stats")
     */
    public function index()
    {
        return $this->render('stats/index.html.twig', [
            'controller_name' => 'StatsController',
        ]);
    }

    /**
     * @Route("/stats-by-date", name="stats_by_date")
     * @IsGranted("ROLE_AGENT")
     */
    public function byDate(Request $request, AgentStatRepository $statRepository, MedalChecker $medalChecker): Response
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $stats = [];
        $medalsGained = [];

        if ($startDate && $endDate) {
            $entries = $statRepository->findByDate($startDate, $endDate);
            $previous = [];

            foreach ($entries as $entry) {
                $agentName = $entry->getAgent()->getNickname();

                if (false === isset($previous[$agentName])) {
                    $previousEntry = $statRepository->getPrevious($entry);

                    $previous[$agentName] = $previousEntry ? $medalChecker->checkLevels($previousEntry) : $medalChecker->checkLevels($entry);
                }
                $levels = $medalChecker->checkLevels($entry);
                $dateString = $entry->getDatetime()->format('Y-m-d');

                foreach ($levels as $name => $level) {
                    if (!$level) {
                        continue;
                    }
                    if (false === isset($previous[$agentName][$name])) {
                        $medalsGained[$dateString][$agentName][$name] = $level;
                        $previous[$name] = $level;
                    } elseif ($previous[$agentName][$name] < $level) {
                        $medalsGained[$dateString][$agentName][$name] = $level;
                        $previous[$agentName][$name] = $level;
                    }
                }
            }
        }

        return $this->render('stats/by_date.html.twig', [
            'startDate' => new \DateTime($startDate),
            'endDate' => new \DateTime($endDate),
            'stats' => $stats,

            'medalsGained' => $medalsGained,
        ]);
    }
}
