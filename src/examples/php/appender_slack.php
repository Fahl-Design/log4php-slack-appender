<?php
/**
 * This script requires installation as composer package.
 */

require_once __DIR__.'/../../../vendor/autoload.php';

Logger::configure(__DIR__.'/../resources/appender_slack.local.xml');

$logger = Logger::getLogger('myLogger');
$logger->debug('debug-message');
$logger->info('info-message');
$logger->warn('warn-message');
$logger->error('error-message');
$logger->fatal('fatal-message');
