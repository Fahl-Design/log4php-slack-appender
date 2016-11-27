# Slack as appender for log4php

## Status badges
[![Build Status Master](https://travis-ci.org/Fahl-Design/log4php-slack-appender.svg?branch=master)](https://travis-ci.org/Fahl-Design/log4php-slack-appender
[![Build Status Develop](https://travis-ci.org/Fahl-Design/log4php-slack-appender.svg?branch=develop)](https://travis-ci.org/Fahl-Design/log4php-slack-appender
[![StyleCI](https://styleci.io/repos/74897031/shield?branch=master)](https://styleci.io/repos/74897031)

## Description

This package allows you to use [Slack for PHP](https://github.com/maknz/slack) easily and elegantly in your app as an [log4php](https://logging.apache.org) appender. 
Read the instructions below to get it set up.

## Requirements

PHP >= 5.2 || ^7.0

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
        <appender name="default" class="LoggerAppenderSlack">
            <!-- get endpoint url from https://my.slack.com/services/new/incoming-webhook -->
            <param name="endpoint" value="https://hooks.slack.com/services/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX" />
            <param name="channel" value="#yourChannel" />
            <param name="username" value="Log4php" />
            <param name="icon" value=":ghost:" />
        </appender>
        <logger name="myLogger">
            <appender_ref ref="default" />
        </logger>
        <root>
            <level value="DEBUG" />
        </root>
    </log4php:configuration>
```

### php (config.php) appender config example

```php
    <?php 
    return [
        'rootLogger' => [
            'appenders' => ['default'],
        ],
        'myLogger' => [
            'appenders' => ['default'],
        ],
        'appenders' => [
            'default' => [
                'class' => 'LoggerAppenderSlack',
                'params' => [
                    'endpoint' => 'https://hooks.slack.com/services/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX/XXXXXXXXXXXXXXX',
                    'channel' => '#yourChannel',
                    'username' => 'log4php',
                    'icon' => ':ghost:', // emoji or an icon url
                ]
            ]
        ]
    ];
```

## Usage

Check example (src/examples)


```php
Logger::configure(__DIR__.'/../resources/appender_slack.xml');

$logger = Logger::getLogger('myLogger');
$logger->debug('Hello World!');

```

## ToDo
- Unit testing
- Slack-Layout to handle attachments

