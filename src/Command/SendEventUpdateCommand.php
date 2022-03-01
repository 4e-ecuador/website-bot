<?php

namespace App\Command;

use App\Repository\AgentStatRepository;
use App\Repository\EventRepository;
use App\Service\EventHelper;
use App\Service\TelegramBotHelper;
use CURLFile;
use DateTime;
use DateTimeZone;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnexpectedValueException;

#[AsCommand(
    name: 'app:send:eventUpdate',
    description: 'Send event updates'
)]
class SendEventUpdateCommand extends Command
{
    public function __construct(
        private readonly string $rootDir,
        private readonly TelegramBotHelper $telegramBotHelper,
        private readonly EventHelper $eventHelper,
        private readonly EventRepository $eventRepository,
        private readonly AgentStatRepository $statRepository,
        private readonly string $defaultTimeZone
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'group',
                null,
                InputOption::VALUE_OPTIONAL,
                'Group name'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $dateNow = new DateTime(
            'now',
            new DateTimeZone($this->defaultTimeZone)
        );

        if ($input->getOption('group')) {
            if ('test' === $input->getOption('group')) {
                $groupId = $this->telegramBotHelper->getGroupId('test');
            } else {
                throw new UnexpectedValueException('Unknown group');
            }

            $io->writeln('group set to: '.$input->getOption('group'));
        } else {
            $groupId = $this->telegramBotHelper->getGroupId('test');
        }

        $events = $this->eventRepository->findAll();
        $currentEvents = [];

        foreach ($events as $event) {
            if ($event->getDateStart() > $dateNow) {
                // $futureEvents[] = $event;
            } elseif ($event->getDateEnd() < $dateNow) {
                // $pastEvents[] = $event;
            } else {
                $currentEvents[] = $event;
            }
        }

        var_dump(count($currentEvents));

        foreach ($currentEvents as $event) {
            $entries = $this->statRepository->findByDate(
                $event->getDateStart(),
                $event->getDateEnd()
            );

            $results = $this->eventHelper->calculateResults($event, $entries);

            var_dump($results);
        }

        $image = $this->createImage();

        // $caption = 'lalala [aaa](http://example.com}'
        $caption = [];

        $caption[] = '';
        $caption[] = 'lalala <a href="http://example.com">aaa</a>.';
        $caption[] = '';
        $caption[] = 'lolo';
        // $this->telegramBotHelper->sendMessage($groupId,'lalala');
        // $image = '/home/elkuku/repos/symf-postgre-heroku-test/assets/images/error_frox/dead-frog-clipart-1.jpg';
        // $curlFile = new \CURLFile('/home/elkuku/repos/symf-postgre-heroku-test/assets/images/error_frox/dead-frog-clipart-1.jpg');
        $this->telegramBotHelper->sendPhoto(
            $groupId,
            $image,
            implode("\n", $caption)
        );
        // $this->telegramBotHelper->sendPhoto($groupId, $image, 'lalala');

        $io->warning($this->rootDir);
        $io->success('Finished!');

        return Command::SUCCESS;
    }

    private function createImage(): CURLFile
    {
        $my_img = imagecreate(230, 140);

        $medal1 = imagecreatefrompng(
            $this->rootDir.'/assets/images/medals/1st-place-medal_36.png'
        );
        $medal2 = imagecreatefrompng(
            $this->rootDir.'/assets/images/medals/2nd-place-medal_36.png'
        );
        $medal3 = imagecreatefrompng(
            $this->rootDir.'/assets/images/medals/3rd-place-medal_36.png'
        );

        $background = imagecolorallocate($my_img, 255, 255, 255);
        // $text_colour = imagecolorallocate( $my_img, 0, 0, 0 );
        // $line_colour = imagecolorallocate( $my_img, 128, 255, 0 );

        // $grey = imagecolorallocate($my_img, 128, 128, 128);
        $black = imagecolorallocate($my_img, 0, 0, 0);

        // imagestring( $my_img, 5, 40, 20, "AlexinhoTreSant", $text_colour );
        // imagestring( $my_img, 5, 40, 61, "crispin14cpw", $text_colour );
        // imagestring( $my_img, 5, 40, 102, "morroshikamaru", $text_colour );

        // The text to draw
        $name1 = 'AlexinhoTreSant';
        $name2 = 'RazorEc';
        $name3 = 'nikp3h';

        // $font = '/home/elkuku/repos/symf-postgre-heroku-test/assets/fonts/RemachineScript_Personal_Use.ttf';
        // $font2 = '/home/elkuku/repos/symf-postgre-heroku-test/assets/fonts/SouthDjakartaDemo.ttf';
        $font3 = $this->rootDir.'/assets/fonts/Clone Machine.otf';
        // Add some shadow to the text
        //imagettftext($my_img, 20, 0, 11, 21, $grey, $font, $text);

        // Add the text
        imagettftext($my_img, 16, 0, 40, 35, $black, $font3, $name1);
        imagettftext($my_img, 16, 0, 40, 76, $black, $font3, $name2);
        imagettftext($my_img, 16, 0, 40, 117, $black, $font3, $name3);

        imagecopy($my_img, $medal1, 3, 5, 0, 0, 36, 36);
        imagecopy($my_img, $medal2, 3, 46, 0, 0, 36, 36);
        imagecopy($my_img, $medal3, 3, 87, 0, 0, 36, 36);
        //imagesetthickness ( $my_img, 5 );
        //imageline( $my_img, 30, 45, 165, 45, $line_colour );

        //$text_colour = imagecolorallocate( $my_img, 255, 255, 0 );
        //imagestring( $my_img, 4, 30, 25, "thesitewizard.com", $text_colour );

        // header( "Content-type: image/png" );
        // imagepng( $my_img );
        // //imagepng( $medal1 );
        // imagecolordeallocate( $line_color );
        // imagecolordeallocate( $text_color );
        // imagecolordeallocate($my_img, $background );
        // imagedestroy( $my_img );

        $fileName = $this->rootDir.'/var/cache/filename.png';
        // chmod($fileName,0755);
        // imagepng($my_img, $fileName, 0, NULL);
        imagepng($my_img, $fileName);

        // ob_start();
        // imagejpeg($my_img);
        // $contents =  ob_get_clean();

        // imagecolordeallocate($background );
        // imagecolordeallocate($my_img, $background );
        // imagedestroy($my_img);

        // $tmp = tmpfile();
        // fwrite($tmp, $contents);
        // fseek($tmp, 0);
        // $meta = stream_get_meta_data($tmp);

        // return $meta['uri'];
        // $ffile = $this->rootDir.'/var/cache/test.jpg';
        // copy($meta['uri'], $ffile);
        // // return new \CURLFile('/home/elkuku/repos/symf-postgre-heroku-test/assets/images/error_frox/dead-frog-clipart-1.jpg', 'image/jpeg', 'image');
        return new CURLFile($fileName, 'image/jpeg', 'image');
        // return new \CURLFile($ffile , 'image/jpeg', 'image');
        // return $meta['uri'];
        // $cFile = curl_file_create($meta['uri'], 'application/pdf', $name);
        //
        // return $contents;
    }
}
