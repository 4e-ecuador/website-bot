<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Form\ImportFormType;
use App\Repository\AgentRepository;
use App\Repository\FactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

class ImportController extends BaseController
{
    #[Route(path: '/import', name: 'import')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        Request $request,
        FactionRepository $factionRepository,
        AgentRepository $agentRepository
    ): RedirectResponse|\Symfony\Component\HttpFoundation\Response {
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
                } catch (UnexpectedValueException $exception) {
                    $this->addFlash('danger', $exception->getMessage());

                    return $this->render(
                        'import/index.html.twig',
                        [
                            'form' => $form->createView(),
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
        AgentRepository $agentRepository,
        EntityManagerInterface $entityManager
    ): int {
        $jsonData = json_decode($agentsJSON);
        $importCount = 0;

        if (!$jsonData) {
            throw new UnexpectedValueException('Invalid JSON data received');
        }

        // @todo faction select
        $faction = $factionRepository->findOneBy(['name' => 'ENL']);

        foreach ($jsonData as $entry) {
            $agent = new Agent();

            $agent->setNickname($entry->name);
            $agent->setLat($entry->lat);
            $agent->setLon($entry->lng);

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
