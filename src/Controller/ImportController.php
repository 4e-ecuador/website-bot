<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Form\ImportFormType;
use App\Repository\AgentRepository;
use App\Repository\FactionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    /**
     * @Route("/import", name="import")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(
        Request $request,
        FactionRepository $factionRepository,
        AgentRepository $agentRepository
    ) {
        $form = $this->createForm(ImportFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $count = 0;

            if ($data['agentsJSON']) {
                try {
                    $count += $this->importJSON(
                        $data['agentsJSON'],
                        $factionRepository,
                        $agentRepository
                    );
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
                    $count += $this->importCsv(
                        $data['csvRaw'],
                        $data['province'],
                        $data['city'],
                        $wayPointHelper
                    );
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

    private function importJSON(
        string $agentsJSON,
        FactionRepository $factionRepository,
        AgentRepository $agentRepository
    ) {
        $jsonData = json_decode($agentsJSON);
        $importCount = 0;

        if (!$jsonData) {
            throw new \UnexpectedValueException('Invalid JSON data received');
        }

        $entityManager = $this->getDoctrine()->getManager();

        // @todo faction select
        $faction = $factionRepository->findOneBy(['name' => 'ENL']);

        //        $factions = $factionRepository->findAll();
        foreach ($jsonData as $entry) {
            $agent = new Agent();

            $agent->setNickname($entry->name);
            $agent->setLat($entry->lat);
            $agent->setLon($entry->lng);
            $agent->setHasMap(true);

            $agent->setFaction($faction);

            if (!$agentRepository->has($agent)) {
                $entityManager->persist($agent);

                $entityManager->flush();

                $importCount++;
            }
        }

        return $importCount;
    }
}
