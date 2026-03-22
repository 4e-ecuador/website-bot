<?php

namespace App\Tests\Type;

use App\Entity\IngressEvent;
use App\Repository\IngressEventRepository;
use App\Service\EmojiService;
use App\Service\MedalChecker;
use App\Type\CustomMessage\NotifyEventsMessage;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifyEventsMessageTest extends TestCase
{
    /**
     * @param array<IngressEvent> $futureFS
     * @param array<IngressEvent> $futureMD
     */
    private function buildMessage(
        array $futureFS = [],
        array $futureMD = [],
        bool $firstAnnounce = false
    ): NotifyEventsMessage {
        $emojiService = new EmojiService();

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(
            fn (string $key, array $params = []) => $key
        );

        $medalChecker = $this->createStub(MedalChecker::class);

        $repo = $this->createStub(IngressEventRepository::class);
        $repo->method('findFutureFS')->willReturn($futureFS);
        $repo->method('findFutureMD')->willReturn($futureMD);

        $message = new NotifyEventsMessage(
            $emojiService,
            $translator,
            $medalChecker,
            $repo,
            'admin@example.com',
            'https://example.com'
        );

        return $message->setFirstAnnounce($firstAnnounce);
    }

    public function testGetMessageReturnsEmptyWhenNoEvents(): void
    {
        $message = $this->buildMessage();

        self::assertSame([], $message->getMessage());
    }

    public function testGetMessageWithFutureFsEventWithinDays(): void
    {
        $event = new IngressEvent();
        $event->setName('Test FS Event');
        $event->setLink('https://example.com/event');
        $event->setDateStart(new DateTime('+3 days'));

        $message = $this->buildMessage(futureFS: [$event], firstAnnounce: false);
        $result = $message->getMessage();

        self::assertNotEmpty($result);
    }

    public function testGetMessageWithFutureFsEventFirstAnnounce(): void
    {
        $event = new IngressEvent();
        $event->setName('Test FS Event');
        $event->setLink('https://example.com/event');
        $event->setDateStart(new DateTime('+30 days'));

        $message = $this->buildMessage(futureFS: [$event], firstAnnounce: true);
        $result = $message->getMessage();

        self::assertNotEmpty($result);
    }

    public function testGetMessageSkipsFsEventBeyond8DaysWhenNotFirstAnnounce(): void
    {
        $event = new IngressEvent();
        $event->setName('Test FS Event');
        $event->setLink('https://example.com/event');
        $event->setDateStart(new DateTime('+30 days'));

        $message = $this->buildMessage(futureFS: [$event], firstAnnounce: false);
        $result = $message->getMessage();

        self::assertSame([], $result);
    }

    public function testGetMessageWithFutureMdEvent(): void
    {
        $mdEvent = new IngressEvent();
        $mdEvent->setName('Test MD Event');

        $message = $this->buildMessage(futureMD: [$mdEvent]);
        $result = $message->getMessage();

        self::assertContains('HAY MD!!! - contacte un dev =;)', $result);
    }

    public function testGetMessageWithFutureMdEventAndFirstAnnounce(): void
    {
        $mdEvent = new IngressEvent();
        $mdEvent->setName('Test MD Event');

        $message = $this->buildMessage(futureMD: [$mdEvent], firstAnnounce: true);
        $result = $message->getMessage();

        self::assertContains('HAY MD!!! - contacte un dev =;)', $result);
        self::assertContains('YAY!!!', $result);
    }

    public function testGetMessageWithFutureFsEventWithoutLinks(): void
    {
        $event = new IngressEvent();
        $event->setName('Test FS Event');
        $event->setLink('https://example.com/event');
        $event->setDateStart(new DateTime('+3 days'));

        $message = $this->buildMessage(futureFS: [$event], firstAnnounce: false);
        $result = $message->getMessage(false);

        self::assertNotEmpty($result);
    }
}
