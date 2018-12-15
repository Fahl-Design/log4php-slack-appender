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
    exit;
    $logger = Logger::getLogger('myLogger');
    $logger->debug('debug-message');
    $logger->info('info-message');
    $logger->warn('warn-message @channel *WATTT*');
    $logger->error('error-message');
    $logger->fatal('fatal-message');
} catch (\Throwable $e) {
    var_dump($e->getMessage());
    var_dump($e->getTraceAsString());
    exit;
    exit(255);
}
