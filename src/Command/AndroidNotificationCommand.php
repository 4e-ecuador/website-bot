<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'android-app:notify',
    description: 'Send a notification to android devices'
)]
class AndroidNotificationCommand extends Command
{
    public function __construct(private readonly string $fcmKey)
    {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $title = 'Subir las estadísticas';
        $message = 'Recuerda mantener actualizados tus datos!!';

        $data = json_encode(
            [
                'to'   => '/topics/allDevices',
                'data' => [
                    'title'      => $title,
                    'message'    => $message,
                    'channel_id' => 'WEB APP CHANNEL ID',
                    'sound'      => 'default',
                ],
            ],
            JSON_THROW_ON_ERROR
        );

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        $header = [
            'Content-Type:application/json',
            'Authorization: key='.$this->fcmKey,
        ];

        if (!$ch) {
            throw new \UnexpectedValueException('Can not init Curl.');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);

        $io->success('Message has been sent!');

        return Command::SUCCESS;
    }
}
