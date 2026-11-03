<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use UnexpectedValueException;

/**
 * FireBase Messaging helper.
 */
class FcmHelper
{
    /**
     * @var array<string>
     */
    public array $tokens = [];

    public string $type = '';

    public function __construct(
        #[Autowire(env: 'FCM_KEY')]
        private readonly string $fcmKey,
        #[Autowire(env: 'FCM_CHANNEL_ID')]
        private readonly string $channelId
    ) {
    }

    public function sendMessage(
        string $title,
        string $message,
        string $to = '/topics/allDevices'
    ): bool {
        $data = [
            'to' => $to,
            'data' => [
                'title' => $title,
                'message' => $message,
                'channel_id' => $this->channelId,
                'sound' => 'default',
            ],
        ];

        return $this->send($data);
    }

    /**
     * @param string|array<string> $to
     */
    public function sendMessageWithTokens(
        string $title,
        string $message,
        string|array $to
    ): bool {
        if (is_array($to)) {
            $data = [
                'registration_ids' => $to,
                'data' => [
                    'title' => $title,
                    'message' => $message,
                ],
            ];
        } else {
            $data = [
                'to' => $to,
                'data' => [
                    'title' => $title,
                    'message' => $message,
                ],
            ];
        }

        // $data = [
        //     'to'   => $to,
        //     'data' => [
        //         'title'      => $title,
        //         'message'    => $message,
        //         'channel_id' => $this->channelId,
        //         'sound'      => 'default',
        //     ],
        // ];

        $prio = [
            'priority' => 'high',
            // legacy HTTP protocol (this can also be set to 10)
            'android' => [
                'priority' => 'high', // HTTP v1 protocol
            ],
        ];

        $data = array_merge($data, $prio);

        return $this->send($data);
    }

    /**
     * @param array<string, array<string, string>|string> $dataArray
     */
    private function send(array $dataArray): bool
    {
        $data = json_encode($dataArray, JSON_THROW_ON_ERROR);

        $header = [
            'Content-Type:application/json',
            'accept:application/json',
            'Authorization: key='.$this->fcmKey,
        ];
        $ch = curl_init('https://fcm.googleapis.com/fcm/send');

        if (!$ch) {
            throw new UnexpectedValueException('Can not init Curl.');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);

        if (!$result) {
            throw new UnexpectedValueException('Curl operation failed.');
        }

        $decoded = json_decode(
            (string) $result,
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!$decoded) {
            throw new RuntimeException('Something went wrong with FCM :(');
        }

        return (bool) $result;
    }

    /**
     * @todo dummy
     */
    public function sendX(string $title, string $message): void
    {
        // now let's see data message
        $data = [
            'to' => '/topics/allDevices',
            'data' => [
                'title' => $title,
                'message' => $message,
                'channel_id' => 'WEB APP CHANNEL ID',
                'sound' => 'default',
            ],
        ];

        $this->send($data);
    }
}
