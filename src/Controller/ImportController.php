<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\AgentStat;
use App\Exception\StatsNotAllException;
use App\Form\ImportFormType;
use App\Repository\AgentStatRepository;
use App\Repository\FactionRepository;
use App\Service\CsvParser;
use App\Service\MedalChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ImportController extends AbstractController
{
    /**
     * @Route("/import", name="import")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(Request $request, FactionRepository $factionRepository)
    {
        $form = $this->createForm(ImportFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data  = $form->getData();
            $count = 0;

            if ($data['agentsJSON']) {
                try {
                    $count += $this->importJSON($data['agentsJSON'], $factionRepository);
                } catch (\UnexpectedValueException $exception) {
                    $this->addFlash('danger', $exception->getMessage());

                    return $this->render(
                        'import/index.html.twig',
                        [
                            'form' => $form->createView(),
                        ]
                    );
                }
            }

            if ($data['agentsCSV']) {
                try {
                    $count += $this->importCsv($data['csvRaw'], $data['province'], $data['city'], $wayPointHelper);
                } catch (\UnexpectedValueException $exception) {
                    $this->addFlash('danger', $exception->getMessage());

                    return $this->render(
                        'import/index.html.twig',
                        [
                            'form'   => $form->createView(),
                            'cities' => $waypointRepo->findCities(),
                        ]
                    );
                }
            }

            if ($count) {
                $this->addFlash('success', $count.' Waypoint(s) imported!');
            } else {
                $this->addFlash('warning', 'No Waypoints imported!');
            }

            return $this->redirectToRoute('default');
        }

        return $this->render(
            'import/index.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    private function importJSON(string $agentsJSON, FactionRepository $factionRepository)
    {
        $jsonData = json_decode($agentsJSON);

        if (!$jsonData) {
            throw new \UnexpectedValueException('Invalid JSON data received');
        }

//        var_dump($jsonData);

        $entityManager = $this->getDoctrine()->getManager();

        // @todo faction select
        $faction = $factionRepository->findOneBy(['name' => 'ENL']);

//        $factions = $factionRepository->findAll();
        foreach ($jsonData as $entry) {

            $agent = new Agent();

            $agent->setNickname($entry->name);
            $agent->setLat($entry->lat);
            $agent->setLon($entry->lng);

            $agent->setFaction($faction);

            $entityManager->persist($agent);

            $entityManager->flush();
        }

        return count($jsonData);
    }


    /**
     * @Route("/stat-import", name="stat_import", methods={"POST", "GET"})
     * @IsGranted("ROLE_AGENT")
     */
    public function StatImport(
        Request $request,
        CsvParser $csvParser,
        MedalChecker $medalChecker,
        AgentStatRepository $agentStatRepository,
        Security $security
    ): Response {
        $csv        = $request->get('csv');
        $importType = $request->get('type');
        $ups        = [];
        $currents   = [];
        $currentEntry = null;
        $apGain = 0;

        $user = $security->getUser();

        $agent = $user->getAgent();

        if (!$agent) {
            throw $this->createAccessDeniedException('No tiene un agente asignado a su usuario - contacte un admin!');
        }

        if ($csv) {
            try {
                $parsed = $csvParser->parse($csv, $importType);

                foreach ($parsed as $date => $values) {
                    $statEntry = new AgentStat();

                    $statEntry->setDatetime(new \DateTime($date))
                        ->setAgent($agent);

                    if (!$agentStatRepository->has($statEntry)) {

                        foreach ($values as $vName => $value) {
                            $methodName = $medalChecker->getMethodName($vName);
                            if (method_exists($statEntry, $methodName)) {
                                $statEntry->$methodName($value);
                            } else {
                                $this->addFlash('warning', 'method not found: '.$methodName.' '.$vName);
                            }
                        }

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($statEntry);
                        $entityManager->flush();

                        $currentEntry = $statEntry;
                    } else {
                        $this->addFlash('warning', 'Stat entry already added!');
                    }
                }
            } catch (StatsNotAllException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            } catch (\UnexpectedValueException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        if ($currentEntry) {
            $previousEntry = $agentStatRepository->getPrevious($currentEntry);

            $statEntries = $medalChecker->checkLevels($currentEntry);

            if ($previousEntry) {
                $ups = $medalChecker->getUpgrades($previousEntry, $currentEntry);
                $apGain = $currentEntry->getAp() - $previousEntry->getAp();
            } else {

//                if (!$currents) {
                $currents = $statEntries;
//
//                    continue;
//                }


            }
        }

        return $this->render(
            'import/agent_stats.html.twig',
            [
                'ups'      => $ups,
                'apGain' => $apGain,
                'currents' => $currents,
            ]
        );
    }
}
