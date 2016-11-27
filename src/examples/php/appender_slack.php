<?php
/**
 * This script requires installation as composer package.
 */

require_once __DIR__.'/../../../vendor/autoload.php';

Logger::configure(__DIR__.'/../resources/appender_slack.xml');

$logger = Logger::getLogger('myLogger');
$logger->debug('Hello World!');
