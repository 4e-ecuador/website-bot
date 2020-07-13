<?php

namespace App\Controller;

use App\Service\EmojiService;
use App\Service\MailerHelper;
use App\Service\TelegramBotHelper;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
     * @throws Exception
     */
    public function index(KernelInterface $kernel): Response
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'about',
            ]
        );

        $output = new BufferedOutput();

        $application->run($input, $output);

        return $this->render(
            'test/index.html.twig',
            [
                'controller_name' => 'TestController',
                'sysInfo'         => $output->fetch(),
            ]
        );
    }

    /**
     * @Route("/bot", name="test_bot")
     * @IsGranted("ROLE_ADMIN")
     */
    public function botTest(
        Request $request,
        TelegramBotHelper $telegramBotHelper
    ): Response {
        $testText = $request->get('testtext');

        if ($testText) {
            $groupId = $telegramBotHelper->getGroupId($request->get('group'));

            $telegramBotHelper->sendMessage($groupId, $testText);

            $this->addFlash('success', 'Message has been sent.');
        }

        return $this->render(
            'test/bottest.html.twig',
            [
                'testtext' => $testText,
            ]
        );
    }

    /**
     * @Route("/mail", name="test_mail")
     * @IsGranted("ROLE_ADMIN")
     */
    public function mailTest(
        Request $request,
        MailerHelper $mailerHelper
    ): Response {
        $testtext = $request->get('testtext');

        if ($testtext) {
            $mailerHelper->sendTestMail('elkuku.n7@gmail.com');
        }

        return $this->render(
            'test/mailtest.html.twig',
            [
                'testtext' => $testtext,
            ]
        );
    }

    /**
     * @Route("/emojis", name="test_emojis")
     * @IsGranted("ROLE_ADMIN")
     */
    public function testEmojis(EmojiService $emojiService)
    {
        // $keys = $emojiService->getKeys();
        //
        // $emojis = $emojiService->getAll();
        return $this->render(
            'test/emojis.html.twig',
            [
                'emojis' => $emojiService->getAll(),
            ]
        );

    }
}
