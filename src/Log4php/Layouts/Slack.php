<?php

declare(strict_types=1);

namespace WebProject\Log4php\Layouts;

use LoggerLayoutSimple;
use LoggerLoggingEvent;
use WebProject\Log4php\Appender\Settings\Config;

/**
 * A simple slack layout.
 *
 * Returns the log statement in a format consisting of the
 * <b>level</b>, followed by " - " and then the <b>message</b>.
 *
 * For example the following php and properties files
 *
 * {@example ../../examples/php/layout_simple.php 19}<br>
 *
 * {@example ../../examples/resources/layout_simple.properties 18}<br>
 *
 * would result in:
 *
 * <samp>Hello World!</samp>
 *
 * @version    $Revision$
 */
class Slack extends LoggerLayoutSimple
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * Slack constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Get AddLoggerName
     *
     * @return bool
     */
    protected function _isActiveAddLoggerName(): bool
    {
        return (bool) $this->_config->get(Config::KEY_ADD_LOGGER_TO_MESSAGE);
    }

    /**
     * Returns the log statement in a format consisting of the
     * <b>message</b>. For example,
     * <samp> "A message" </samp>.
     *
     * @param LoggerLoggingEvent $event
     *
     * @return string
     */
    public function format(LoggerLoggingEvent $event): string
    {
        $message = '';
        if ($this->_isActiveAddLoggerName()) {
            $message .= 'Logger ('.$event->getLoggerName().') ';
        }
        $message .= $event->getRenderedMessage().\PHP_EOL;

        return $message;
    }
}
