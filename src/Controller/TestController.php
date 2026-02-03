<?php

namespace App\Controller;

use App\Exception\EmojiNotFoundException;
use App\Service\EmojiService;
use App\Service\MailerHelper;
use App\Service\TelegramBotHelper;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use TelegramBot\Api\InvalidArgumentException;
use UnexpectedValueException;

#[IsGranted('ROLE_ADMIN')]
class TestController extends AbstractController
{
    public function __construct(
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly MailerHelper $mailerHelper,
        private readonly EmojiService $emojiService,
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/test/', name: 'test', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('test/index.html.twig');
    }

    /**
     *
     * @throws \TelegramBot\Api\Exception
     * @throws InvalidArgumentException
     */
    #[Route(path: '/test/bot', name: 'test_bot', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function botTest(
        Request $request
    ): Response {
        $testText = $request->query->get('testtext');
        if ($testText) {
            $groupId = $this->telegramBotHelper->getGroupId(
                $request->query->get('group')
            );

            $this->telegramBotHelper->sendMessage($groupId, $testText);

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
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/test/mail', name: 'test_mail', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function mailTest(
        Request $request
    ): Response {
        $testtext = $request->query->get('testtext');
        if ($testtext) {
            $this->mailerHelper->sendTestMail('elkuku.n7@gmail.com');
        }

        return $this->render(
            'test/mailtest.html.twig',
            [
                'testtext' => $testtext,
            ]
        );
    }

    /**
     * @throws EmojiNotFoundException
     */
    #[Route(path: '/test/emojis✨', name: 'test_emojis', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function testEmojis(): Response
    {
        return $this->render(
            'test/emojis.html.twig',
            [
                'emojis' => $this->emojiService->getAll(),
            ]
        );
    }

    #[Route(path: '/test/modify-stats', name: 'test_modify_stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function modifyStats(): Response
    {
        return $this->render('test/modify-stats.html.twig');
    }

    #[Route(path: '/test/modify-stats/input', name: 'test_modify_stats_input', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function modifyStatsInput(
        Request $request
    ): Response {
        try {
            $csv = $request->query->get('q');

            if (!$csv) {
                throw new UnexpectedValueException('No CSV received!');
            }

            $lines = explode("\n", trim($csv));

            if (2 !== count($lines)) {
                throw new UnexpectedValueException('CSV must have 2 lines!');
            }

            if (!str_contains($lines[0], "\t")) {
                throw new UnexpectedValueException('We want TABS!!!');
            }

            $keys = explode("\t", trim($lines[0]));
            $values = explode("\t", trim($lines[1]));

            if (count($keys) !== count($values)) {
                throw new UnexpectedValueException(
                    'Key/Value count mismatch!'
                );
            }

            $data = [];
            foreach ($keys as $i => $key) {
                $data[$key] = $values[$i];
            }

            return $this->render(
                'test/_modify-stats-fields.html.twig',
                [
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
