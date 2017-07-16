<?php

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
 * @package    log4php
 * @subpackage layouts
 */
class LoggerLayoutSlack extends LoggerLayoutSimple
{
    /**
     * Returns the log statement in a format consisting of the
     * <b>message</b>. For example,
     * <samp> "A message" </samp>
     *
     * @param LoggerLoggingEvent $event
     *
     * @return string
     */
    public function format(LoggerLoggingEvent $event)
    {
        $message = $event->getRenderedMessage();

        return "$message" . PHP_EOL;
    }
}
