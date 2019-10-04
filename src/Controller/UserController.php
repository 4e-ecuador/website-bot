<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
    /**
     * @Route("/", name="user_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render(
            'user/index.html.twig',
            [
                'users' => $userRepository->findAll(),
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

    /**
     * @Route("/send-confirmation-mail/{id}", name="user_send_confirmation_mail", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function sendConfirnationMail(User $user, \Swift_Mailer $mailer)
    {
        $mailerUrl = $_ENV['MAILER_URL'];

        try {
            if (!$mailerUrl) {
                throw new \InvalidArgumentException('Mailer URL not set');
            }

            $parts = explode(':', $mailerUrl);

            if (count($parts) !== 3) {
                throw new \InvalidArgumentException('Invalid mailer URL');
            }

            $fromMail = trim($parts[1], '/').'@gmail.com';
            $fromName = '4E Enlightened Elite Echelon of Ecuador';

            $message = (new \Swift_Message('Welcome to 4E'))
                ->setFrom($fromMail, $fromName)
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/confirmation.html.twig',
                        ['user' => $user]
                    ),
                    'text/html'
                );

            $logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

            $count = $mailer->send($message);

            if ($count) {
                $response = 'Confirmation mail has been sent to '
                    .$user->getUsername();
            } else {
                $response = 'There was an error sending your message :( '
                    .$logger->dump();
            }
        } catch (\InvalidArgumentException $exception) {
            $response = $exception->getMessage();
        }

        return new Response($response);
    }
}
