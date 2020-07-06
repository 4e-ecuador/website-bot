<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\GoogleApiClient;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenController extends AbstractController
{
    /**
     * This method will be called by the client to obtain an API token.
     * A query parameter 'idtoken' is expected containing the JWT IdToken
     * obtained from a Google OAuth request.
     *
     * @Route("/connect/google/api-token", name="connect_google_api_token", methods={"GET"})
     */
    public function getApiToken(
        Request $request,
        GoogleApiClient $client,
        UserRepository $userRepository
    ): ?JsonResponse {
        if ('https' !== $request->getScheme()) {
            // WTF!!!
            // return $this->json(['error' => 'Scheme not allowed - please use SSL!'.$request->getScheme()], 200);
        }

        // @TODO POSTPOSTPOST...
        $idToken = $request->query->get('idtoken');

        if ($idToken) {
            return $this->json(['error' => 'Please use POST...'], 200);
        }

        $idToken = $request->request->get('idtoken');

        if (!$idToken) {
            return $this->json(['error' => 'Missing token'], 200);
        }

        $client->setRedirectUri(
            $this->generateUrl(
                'connect_google_api_token',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        )
            ->addScope("email")
            ->addScope("profile");

        try {
            $email = $client->fetchEmailWithToken($idToken);

            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                throw new RuntimeException('User not found!');
            }

            if (!$this->isGranted('ROLE_X', $user)) {
                // @TODO check agent access
                // throw new \RuntimeException('User not permitted!');
            }

            $apiToken = $user->getApiToken();

            if (!$apiToken) {
                $apiToken = $userRepository->getApiToken($user);
            }

            return $this->json(['token' => $apiToken]);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 200);
        }
    }

    /**
     * @TODO remove
     *
     * Route("/connect/google/api-token-test-form", name="connect_google_api_token_test_form")
     */
    public function testForm(
        Request $request,
        GoogleApiClient $client
    ): Response {
        $client->setRedirectUri(
            $this->generateUrl('connect_google_api_token_test_form', [], 0)
        )
            ->addScope("email")
            ->addScope("profile");
        $code = $request->query->get('code');
        $email = '';

        if ($code) {
            try {
                $client->setAccessTokenWithAuthCode($code);
                $googleUser = $client->getUserInfo();
                $email = $googleUser->email;
                $id = $googleUser->id;

                // @TODO THIS IS JUST A TEST!!!

                // user = userRepo->getByEmail($email)
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render(
            'test/tokentest.html.twig',
            [
                'google_login_url' => $client->createAuthUrl(),
                'email'            => $email,
            ]
        );
    }
}
