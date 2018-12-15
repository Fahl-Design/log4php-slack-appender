# Slack as appender for log4php

## Status
[![Dependency Status](https://www.versioneye.com/user/projects/583b5debe7cea0003d1985fb/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/583b5debe7cea0003d1985fb)
[![Latest Stable Version](https://poser.pugx.org/fahl-design/log4php-slack-appender/v/stable?format=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)
[![Total Downloads](https://poser.pugx.org/fahl-design/log4php-slack-appender/downloads?format=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)
[![Latest Unstable Version](https://poser.pugx.org/fahl-design/log4php-slack-appender/v/unstable?format=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)
[![License](https://poser.pugx.org/fahl-design/log4php-slack-appender/license?format=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)

[![Monthly Downloads](https://poser.pugx.org/fahl-design/log4php-slack-appender/d/monthly?format=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)
[![Daily Downloads](https://poser.pugx.org/fahl-design/log4php-slack-appender/d/daily?format=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)

[![Code Climate](https://img.shields.io/codeclimate/github/Fahl-Design/log4php-slack-appender.svg?style=flat-square)](https://codeclimate.com/github/Fahl-Design/log4php-slack-appender/)
[![Code Climate](https://img.shields.io/codeclimate/issues//github/Fahl-Design/log4php-slack-appender.svg?style=flat-square)](https://codeclimate.com/github/Fahl-Design/log4php-slack-appender/)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/13d67fe1145c4557a5ccb2ee07ec81e6)](https://www.codacy.com/app/Fahl-Design/log4php-slack-appender?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Fahl-Design/log4php-slack-appender&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/13d67fe1145c4557a5ccb2ee07ec81e6)](https://www.codacy.com/app/Fahl-Design/log4php-slack-appender?utm_source=github.com&utm_medium=referral&utm_content=Fahl-Design/log4php-slack-appender&utm_campaign=Badge_Coverage)

[![PHP-Eye](https://php-eye.com/badge/fahl-design/log4php-slack-appender/tested.svg?style=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)
[![PHP-Eye](https://php-eye.com/badge/fahl-design/log4php-slack-appender/not-tested.svg?style=flat-square)](https://packagist.org/packages/fahl-design/log4php-slack-appender)

### Master Branch
[![Build Status](https://img.shields.io/travis/Fahl-Design/log4php-slack-appender/master.svg?style=flat-square)](https://travis-ci.org/Fahl-Design/log4php-slack-appender)
[![StyleCI](https://styleci.io/repos/74897031/shield?branch=master&format=flat-square)](https://styleci.io/repos/74897031)
[![codecov](https://img.shields.io/codecov/c/github/Fahl-Design/log4php-slack-appender/master.svg?style=flat-square)](https://codecov.io/gh/Fahl-Design/log4php-slack-appender)

### Develop Branch
[![Build Status](https://img.shields.io/travis/Fahl-Design/log4php-slack-appender/develop.svg?style=flat-square)](https://travis-ci.org/Fahl-Design/log4php-slack-appender)
[![StyleCI](https://styleci.io/repos/74897031/shield?branch=develop&format=flat-square)](https://styleci.io/repos/74897031)
[![codecov](https://img.shields.io/codecov/c/github/Fahl-Design/log4php-slack-appender/develop.svg?style=flat-square)](https://codecov.io/gh/Fahl-Design/log4php-slack-appender)

## Description

This package allows you to use [Slack for PHP](https://github.com/maknz/slack) easily and elegantly in your app as an [log4php](https://logging.apache.org) appender. 
Read the instructions below to get it set up.

## Requirements

PHP >= 7.1

## Installation

You can install the package using the [Composer](https://getcomposer.org/) package manager. You can install it by running this command in your project root:

```sh
composer require fahl-design/log4php-slack-appender
```

Then [create an incoming webhook](https://my.slack.com/services/new/incoming-webhook) for each Slack team you'd like to send messages to. You'll need the webhook URL(s) in order to configure this package.

After you got your hook url add it as endpoint to your configuration

### XML appender config example
```xml
    <log4php:configuration xmlns:log4php="http://logging.apache.org/log4php/" threshold="all">
        <appender name="appender_slack" class="LoggerAppenderSlack">
            <!-- get endpoint url from https://my.slack.com/services/new/incoming-webhook -->
            <param name="endpoint" value="https://hooks.slack.com/services/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX" />
            <param name="channel" value="#yourChannel" />
            <param name="username" value="Log4php" />
            <!-- Url or emoji-->
            <param name="icon" value=":do_not_litter:" />
            <!-- flag to allow markdown (default 1) -->
            <param name="allowMarkdown" value="1" />
            <!-- flag to send log message as slack attachment (default 1) -->
            <param name="asAttachment" value="1" />
        </appender>
        <logger name="myLogger">
            <appender_ref ref="appender_slack" />
        </logger>
    </log4php:configuration>
```

### php (config.php) appender config example
```php
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
```
## Usage

Check example (src/examples)
```php
<?php
declare(strict_types=1);
/**
 * This script requires installation as composer package.
 */
require_once __DIR__.'/../../vendor/autoload.php';

try {
    if (!\is_file(__DIR__.'/../resources/config.local.php')) {
        throw new RuntimeException('local config file is missing');
    }

    Logger::configure(include __DIR__.'/../resources/config.local.php');

    Logger::getRootLogger()->fatal('root-logger-fatal-message');
    $logger = Logger::getLogger('myLogger');
    $logger->warn('warn-message @channel *WATTT*');

    $logger = Logger::getLogger('myLogger');
    $logger->debug('debug-message');
    $logger->info('info-message');
    $logger->warn('warn-message @channel *WATTT*');
    $logger->error('error-message');
    $logger->fatal('fatal-message');
} catch (\Throwable $e) {
    \print_r($e->getMessage());
    \print_r($e->getTraceAsString());
    exit(255);
}
```
