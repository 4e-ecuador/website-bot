<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;

#[Route(path: '/user')]
class UserController extends AbstractController
{
    use PaginatorTrait;
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        // This is a Vue View ;)
        return $this->render('user/index.html.twig');
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/old', name: 'user_index_old', methods: ['GET', 'POST'])]
    public function indexOLD(UserRepository $userRepository, Request $request): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $users = $userRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            ceil(count($users) / $paginatorOptions->getLimit())
        );
        $rolesList = [
            0            => '',
            'ROLE_USER'  => 'User',
            'ROLE_AGENT' => 'Agent',
        ];
        return $this->render(
            'user/index_old.html.twig',
            [
                'users'            => $users,
                'rolesList'        => $rolesList,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }
        return $this->render(
            'user/new.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/{id}', name: 'user_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            // Ensure array keys are lost...
            $user->setRoles(array_values($user->getRoles()));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute(
                'user_index',
                [
                    'id' => $user->getId(),
                ]
            );
        }
        return $this->render(
            'user/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$user->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }
        return $this->redirectToRoute('user_index');
    }
}
