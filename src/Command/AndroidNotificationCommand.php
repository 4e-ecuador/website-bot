<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AndroidNotificationCommand extends Command
{
    protected static $defaultName = 'android-app:notify';

    public function __construct(private string $fcmKey)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument(
                'arg1',
                InputArgument::OPTIONAL,
                'Argument description'
            )
            ->addOption(
                'option1',
                null,
                InputOption::VALUE_NONE,
                'Option description'
            );
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
            ]
        );

        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        $header = [
            'Content-Type:application/json',
            'Authorization: key='.$this->fcmKey,
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);

        $io->success('Message has been sent!');

        return 0;
    }
}
