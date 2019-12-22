<?php

namespace App\Controller;

use App\Service\MailerHelper;
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
        $testText = $request->get('testtext');

        if ($testText) {

            $groupId = $telegramBotHelper->getGroupId($request->get('group'));

            $telegramBotHelper->sendMessage($groupId, $testText);

            $this->addFlash('success', 'Message has been sent.');
        }

        return $this->render('test/bottest.html.twig', [
            'testtext' => $testText,
        ]);
    }

    /**
     * @Route("/mail", name="test_mail")
     * @IsGranted("ROLE_ADMIN")
     */
    public function mailTest(Request $request, MailerHelper $mailerHelper)
    {
        $testtext = $request->get('testtext');

        if ($testtext) {
            $mailerHelper->sendTestMail('elkuku.n7@gmail.com');
        }

        return $this->render('test/mailtest.html.twig', [
            'testtext' => $testtext,
        ]);
    }
}
