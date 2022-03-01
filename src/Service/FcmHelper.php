<?php

namespace App\Service;

use RuntimeException;

/**
 * FireBase Messaging helper
 */
class FcmHelper
{
    public array $tokens = [];
    public string $type = '';

    public function __construct(
        private readonly string $fcmKey,
        private readonly string $channelId
    ) {
    }

    public function sendMessage(
        string $title,
        string $message,
        $to = '/topics/allDevices'
    ) {
        $data = [
            'to'   => $to,
            'data' => [
                'title'      => $title,
                'message'    => $message,
                'channel_id' => $this->channelId,
                'sound'      => 'default',
            ],
        ];

        return $this->send($data);
    }

    public function sendMessageWithTokens(string $title, string $message, $to)
    {
        if (is_array($to)) {
            $data = [
                'registration_ids' => $to,
                'data'             => [
                    'title'   => $title,
                    'message' => $message,
                ],
            ];
        } else {
            $data = [
                'to'   => $to,
                'data' => [
                    'title'   => $title,
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
            "priority" => "high",
            // legacy HTTP protocol (this can also be set to 10)
            "android"  => [
                "priority" => "high" // HTTP v1 protocol
            ],
        ];

        $data = array_merge($data, $prio);

        return $this->send($data);
    }

    private function send(array $dataArray)
    {
        $data = json_encode($dataArray);

        $header = array(
            'Content-Type:application/json',
            'accept:application/json',
            'Authorization: key='.$this->fcmKey,
        );
        $ch = curl_init('https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);

        curl_close($ch);

        $decoded = json_decode($result, false);

        if (!$decoded) {
            throw new RuntimeException('Something went wrong with FCM :(');
        }

        return $result;
    }

    /**
     * @todo dummy
     */
    public function sendX(string $title, string $message)
    {
        // $message = [
        //     'to' => '/topics/allDevices',
        // ];

        if (!$this->tokens) {
            if ($this->type === 'bigtext') {
                $message['data'] = [
                    'title'      => $title,
                    'message'    => $message,
                    'not_type'   => 'bigtext',
                    'extra_data' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book',
                ];
            }
            if ($this->type === 'bigimage') {
                $data = json_encode(
                    [
                        'to'   => '/topics/allDevices',
                        'data' => [
                            'title'      => $_REQUEST['title'],
                            'message'    => $_REQUEST['message'],
                            'not_type'   => 'bigimage',
                            'extra_data' => 'https://i.picsum.photos/id/638/200/200.jpg',
                        ],
                    ]
                );
            }
            if ($this->type === 'bigimage_withoutsideicon') {
                $data = json_encode(
                    [
                        'to'   => '/topics/allDevices',
                        'data' => [
                            'title'      => $_REQUEST['title'],
                            'message'    => $_REQUEST['message'],
                            'not_type'   => 'bigimage_withoutsideicon',
                            'extra_data' => 'https://i.picsum.photos/id/638/200/200.jpg',
                        ],
                    ]
                );
            }
            if ($this->type === 'inbox_style') {
                $array_message = [
                    'Rahul : Hi How Are You?',
                    'Aman : I am Fine ',
                    'Vishal : Are You Ok?',
                ];
                $json_message = json_encode($array_message);

                $data = json_encode(
                    [
                        'to'   => '/topics/allDevices',
                        'data' => [
                            'title'      => $_REQUEST['title'],
                            'message'    => $_REQUEST['message'],
                            'not_type'   => 'inbox_style',
                            'extra_data' => $json_message,
                        ],
                    ]
                );
            }
            if ($this->type === 'message_style') {
                $data = json_encode(
                    [
                        'to'   => '/topics/allDevices',
                        'data' => [
                            'title'      => $_REQUEST['title'],
                            'message'    => $_REQUEST['message'],
                            'not_type'   => 'message_style',
                            'extra_data' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s',
                        ],
                    ]
                );
            }
        } else {
            if (is_array($this->tokens)) {
                $data = json_encode(
                    [
                        'registation_ids' => [$this->tokens],
                        'data'            => [
                            'title'   => $_REQUEST['title'],
                            'message' => $_REQUEST['message'],
                        ],
                    ]
                );
            } else {
                $data = json_encode(
                    [
                        'to'   => $this->tokens,
                        'data' => [
                            'title'   => $title,
                            'message' => $message,
                        ],
                    ]
                );
            }
        }

        //now let's see data message
        $data = [
            'to'   => '/topics/allDevices',
            'data' => [
                'title'      => $title,
                'message'    => $message,
                'channel_id' => 'WEB APP CHANNEL ID',
                'sound'      => 'default',
            ],
        ];

        $this->send($data);
    }
}
