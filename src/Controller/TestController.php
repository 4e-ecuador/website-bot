<?php

namespace App\Controller;

use App\Service\TelegramBotHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/test")
 * @IsGranted("ROLE_ADMIN")
 */
class TestController extends AbstractController
{
    /**
     * @Route("/", name="test")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index()
    {
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

    /**
     * @Route("/bot", name="test_bot")
     * @IsGranted("ROLE_ADMIN")
     */
    public function botTest(Request $request, TelegramBotHelper $telegramBotHelper)
    {
        $testtext = $request->get('testtext');

        if ($testtext) {
            $groupId = $_ENV['ANNOUNCE_GROUP_ID_1'];

            $telegramBotHelper->sendTestMessage($groupId, $testtext);
        }

        return $this->render('test/bottest.html.twig', [
            'testtext' => $testtext,
        ]);
    }
}
