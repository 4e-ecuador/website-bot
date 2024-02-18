<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Helper\Paginator\PaginatorTrait;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function count;

#[Route(path: '/user')]
class UserController extends BaseController
{
    use PaginatorTrait;

    #[Route(path: '/', name: 'user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        // This is a Vue View ;)
        return $this->render('user/index.html.twig');
    }

    #[Route(path: '/jsonlist', name: 'user_index_json', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function indexJson(
        UserRepository $userRepository,
        Request $request
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $paginatorOptions = [
            'page'     => $page,
            'criteria' => [
                'email' => $request->query->get('email', ''),
            ],
        ];
        $modRequest = clone $request;
        $modRequest->query->set('paginatorOptions', $paginatorOptions);

        $paginatorOptions = $this->getPaginatorOptions($modRequest);

        /**
         * @var User[] $users
         */
        $users = $userRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(count($users) / $paginatorOptions->getLimit())
        );

        $list = [];

        foreach ($users as $user) {
            $a = $user->getAgent();
            $ag = null;
            if ($a) {
                $ag = new \stdClass();

                $ag->id = $a->getId();
                $ag->nickname = $a->getNickname();
            }

            $list[] = [
                'id'    => $user->getId(),
                'roles' => $user->getRoles(),
                'email' => $user->getEmail(),
                'agent' => $ag,
            ];
        }

        $response = new \stdClass();

        $response->{'hydra:member'} = $list;

        $view = new \stdClass();
        $view->{'hydra:previous'} = ($page > 1) ? 'X' : null;
        $view->{'hydra:next'} = ($page < $paginatorOptions->getMaxPages()) ? 'X'
            : null;
        $view->{'hydra:last'} = $paginatorOptions->getMaxPages();

        $response->{'hydra:view'} = $view;
        $response->{'hydra:totalItems'} = count($users);

        return $this->json($response);
    }

    #[Route(path: '/old', name: 'user_index_old', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function indexOLD(
        UserRepository $userRepository,
        Request $request
    ): Response {
        $paginatorOptions = $this->getPaginatorOptions($request);
        $users = $userRepository->getPaginatedList($paginatorOptions);
        $paginatorOptions->setMaxPages(
            (int)ceil(count($users) / $paginatorOptions->getLimit())
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

    #[Route(path: '/new', name: 'user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/new.html.twig',
            [
                'user' => $user,
                'form' => $form,
            ]
        );
    }

    #[Route(path: '/{id}', name: 'user_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    #[Route(path: '/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
                'form' => $form,
            ]
        );
    }

    #[Route(path: '/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$user->getId(),
            (string)$request->request->get('_token')
        )
        ) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
