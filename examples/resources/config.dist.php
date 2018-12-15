<?php
declare(strict_types=1);

use WebProject\Log4php\Appender\Settings\Config;
use WebProject\Log4php\Appender\Slack;

return [
    'rootLogger' => [
        'level'     => 'DEBUG',
        'appenders' => ['slack_appender'],
    ],
    'myLogger' => [
        'appenders' => ['slack_appender'],
    ],
    'appenders' => [
        'slack_appender' => [
            'class'  => Slack::class,
            'params' => [
                Config::KEY_ENDPOINT                             => 'https://hooks.slack.com/services/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX',
                Config::KEY_CHANNEL                              => '#general',
                Config::KEY_USERNAME                             => 'log4php',
                Config::KEY_ICON                                 => ':ghost:', // emoji or an icon url
                Config::KEY_ALLOW_MARKDOWN                       => true,
                Config::KEY_MARKDOWN_IN_ATTACHMENTS_FIELDS       => [
                    Config::VALUE_MARKDOWN_IN_ATTACHMENTS_PRETEXT,
                    Config::VALUE_MARKDOWN_IN_ATTACHMENTS_TEXT,
                    Config::VALUE_MARKDOWN_IN_ATTACHMENTS_TITLE,
                    Config::VALUE_MARKDOWN_IN_ATTACHMENTS_FIELDS,
                    Config::VALUE_MARKDOWN_IN_ATTACHMENTS_FALLBACK
                ],
                Config::KEY_AS_ATTACHMENT                 => true,
                Config::KEY_LINK_NAMES                    => true,
                Config::KEY_UNFURL_LINKS                  => false,
                Config::KEY_UNFURL_MEDIA                  => true,
                Config::KEY_SET_ICON_BY_LOG_LEVEL         => true,
                Config::KEY_ADD_LOGGER_TO_MESSAGE         => true
            ]
        ]
    ]
];
