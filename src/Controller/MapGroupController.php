<?php

namespace App\Controller;

use App\Entity\MapGroup;
use App\Form\MapGroupType;
use App\Repository\MapGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/map/group')]
class MapGroupController extends BaseController
{
    #[Route(path: '/', name: 'map_group_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(MapGroupRepository $mapGroupRepository): Response
    {
        return $this->render(
            'map_group/index.html.twig',
            [
                'map_groups' => $mapGroupRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/new', name: 'map_group_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $mapGroup = new MapGroup();
        $form = $this->createForm(MapGroupType::class, $mapGroup);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($mapGroup);
            $entityManager->flush();

            return $this->redirectToRoute('map_group_index');
        }

        return $this->render(
            'map_group/new.html.twig',
            [
                'map_group' => $mapGroup,
                'form'      => $form,
            ]
        );
    }

    #[Route(path: '/{id}/edit', name: 'map_group_edit', methods: [
        'GET',
        'POST',
    ])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        MapGroup $mapGroup,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(MapGroupType::class, $mapGroup);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('map_group_index');
        }

        return $this->render(
            'map_group/edit.html.twig',
            [
                'map_group' => $mapGroup,
                'form'      => $form,
            ]
        );
    }

    #[Route(path: '/{id}', name: 'map_group_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        MapGroup $mapGroup,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$mapGroup->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($mapGroup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('map_group_index');
    }
}
