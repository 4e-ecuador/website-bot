<?php

namespace App\Controller;

use App\Exception\EmojiNotFoundException;
use App\Service\CsvParser;
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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use TelegramBot\Api\InvalidArgumentException;

/**
 * @IsGranted("ROLE_ADMIN")
 */
#[Route(path: '/test')]
class TestController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @throws Exception
     */
    #[Route(path: '/', name: 'test')]
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
     * @IsGranted("ROLE_ADMIN")
     *
     * @throws \TelegramBot\Api\Exception
     * @throws InvalidArgumentException
     */
    #[Route(path: '/bot', name: 'test_bot')]
    public function botTest(Request $request, TelegramBotHelper $telegramBotHelper): Response
    {
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
     * @IsGranted("ROLE_ADMIN")
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/mail', name: 'test_mail')]
    public function mailTest(Request $request, MailerHelper $mailerHelper): Response
    {
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
     * @IsGranted("ROLE_ADMIN")
     * @throws EmojiNotFoundException
     */
    #[Route(path: '/emojis✨', name: 'test_emojis')]
    public function testEmojis(EmojiService $emojiService): Response
    {
        return $this->render(
            'test/emojis.html.twig',
            [
                'emojis' => $emojiService->getAll(),
            ]
        );
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/modify-stats', name: 'test_modify_stats')]
    public function modifyStats(): Response
    {
        return $this->render('test/modify-stats.html.twig');
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/modify-stats/input', name: 'test_modify_stats_input')]
    public function modifyStatsInput(Request $request, CsvParser $csvParser): Response
    {
        try {
            $csv = $request->query->get('q');

            if (!$csv) {
                throw new \UnexpectedValueException('No CSV received!');
            }

            $lines = explode("\n", trim($csv));

            if (2 !== count($lines)) {
                throw new \UnexpectedValueException('CSV must have 2 lines!');
            }

            if (!str_contains($lines[0], "\t")) {
                throw new \UnexpectedValueException('We want TABS!!!');
            }

            $keys = explode("\t", trim($lines[0]));
            $values = explode("\t", trim($lines[1]));

            if (count($keys) !== count($values)) {
                throw new \UnexpectedValueException('Key/Value count mismatch!');
            }

            $data = [];
            foreach ($keys as $i => $key) {
                $data[$key] = $values[$i];
            }

            return $this->render(
                'test/_modify-stats-fields.html.twig',[
                    'data' => $data,
                ]
            );
        } catch (Exception $exception) {
            return new Response(
                $exception->getMessage(),
                Response::HTTP_NOT_ACCEPTABLE
            );
        }
    }
}
