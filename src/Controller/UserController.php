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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    use PaginatorTrait;

    /**
     * @Route("/", name="user_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $paginatorOptions = $this->getPaginatorOptions($request);

        $users = $userRepository->getPaginatedList($paginatorOptions);

        $paginatorOptions->setMaxPages(
            ceil(\count($users) / $paginatorOptions->getLimit())
        );

        $rolesList = [
            0 => '',
            'ROLE_USER' => 'User',
            'ROLE_AGENT' => 'Agent',
        ];

        return $this->render(
            'user/index.html.twig',
            [
                'users'            => $users,
                'rolesList'            => $rolesList,
                'paginatorOptions' => $paginatorOptions,
            ]
        );
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(
        Request $request,
        UserPasswordEncoderInterface $encoder
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPassword()) {
                $user->setPassword(
                    $encoder->encodePassword($user, $user->getPassword())
                );
            }

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
     * @Route("/{id}", name="user_show", methods={"GET"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(User $user): Response
    {
        return $this->render(
            'user/show.html.twig',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(
        Request $request,
        User $user,
        UserPasswordEncoderInterface $encoder
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $plainPass = $user->getPassword();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($plainPass) {
                $user->setPassword($encoder->encodePassword($user, $plainPass));
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'user_index',
                [
                    'id' => $user->getId(),
                ]
            );
        }

        $user->setPassword($plainPass);

        return $this->render(
            'user/edit.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
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
