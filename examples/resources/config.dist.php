<?php
declare(strict_types=1);

use WebProject\Log4php\Appender\Slack;

return [
    'rootLogger' => [
        'appenders' => ['default'],
    ],
    'myLogger' => [
        'appenders' => ['default'],
    ],
    'appenders' => [
        'default' => [
            'class'  => Slack::class,
            'params' => [
                'endpoint'         => 'https://hooks.slack.com/services/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX',
                'channel'          => '#yourChannel',
                'username'         => 'log4php',
                'icon'             => ':ghost:', // emoji or an icon url
                'allowMarkdown'    => '1' ,
                'asAttachment'     => '1' ,
                'linkNames'        => '1' ,
                'unfurlLinks'      => '0' ,
                'unfurlMedia'      => '1' ,
                'addEmoji'         => '1' ,
            ]
        ]
    ]
];
