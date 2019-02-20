<?php

declare(strict_types=1);

namespace WebProject\Log4php\Appender;

use LoggerAppender;
use LoggerLoggingEvent as LogEvent;
use WebProject\Log4php\Appender\Settings\Config;
use WebProject\Log4php\Layouts\Slack as SlackLayout;
use WebProject\Log4php\Slack\Client as SClient;

/**
 * Slack appends log events to a Slack channel.
 *
 * @since      2.4.0
 *
 * @author     Benjamin Fahl <ben@webproject.xyz>
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, v2.0
 *
 * @link       http://logging.apache.org/log4php
 */
class Slack extends LoggerAppender
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var SClient
     */
    protected $_slackClient;

    /**
     * Overwrite layout with LoggerLayoutSlack.
     *
     * @return \LoggerLayout|SlackLayout
     */
    public function getDefaultLayout(): \LoggerLayout
    {
        return new SlackLayout($this->_config);
    }

    /**
     * Set LinkNames.
     *
     * @param bool $linkNames
     *
     * @return Slack
     */
    public function setLinkNames(bool $linkNames): self
    {
        $this->_config->set(
            Config::KEY_LINK_NAMES, $linkNames
        );

        return $this;
    }

    /**
     * Set markdownInAttachments Fields.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function setMarkdownInAttachmentsFields(array $fields): self
    {
        $this->_config->set(
            Config::KEY_MARKDOWN_IN_ATTACHMENTS_FIELDS, $fields
        );

        return $this;
    }

    /**
     * Set IconByLevel.
     *
     * @param bool $setIconByLevel
     *
     * @return Slack
     */
    public function setIconByLevel(bool $setIconByLevel): self
    {
        $this->_config->set(
            Config::KEY_SET_ICON_BY_LOG_LEVEL, $setIconByLevel
        );

        return $this;
    }

    /**
     * Set UnfurlLinks.
     *
     * @param bool $unfurlLinks
     *
     * @return Slack
     */
    public function setUnfurlLinks(bool $unfurlLinks): self
    {
        $this->_config->set(
            Config::KEY_UNFURL_LINKS, $unfurlLinks
        );

        return $this;
    }

    /**
     * Set UnfurlMedia.
     *
     * @param bool $unfurlMedia
     *
     * @return Slack
     */
    public function setUnfurlMedia(bool $unfurlMedia): self
    {
        $this->_config->set(
            Config::KEY_UNFURL_MEDIA, $unfurlMedia
        );

        return $this;
    }

    /**
     * Set Icon.
     *
     * @param string $icon
     *
     * @return Slack
     */
    public function setIcon(string $icon): self
    {
        if (!empty($icon)) {
            $this->_config->set(
                Config::KEY_ICON, $icon
            );

            return $this;
        }

        throw new \InvalidArgumentException('icon invalid');
    }

    /**
     * Set Endpoint.
     *
     * @param string $endpoint
     *
     * @throws \InvalidArgumentException
     *
     * @return Slack
     */
    public function setEndpoint(string $endpoint): self
    {
        if (0 === \strpos($endpoint, Config::ENDPOINT_VALIDATION_STRING)) {
            $this->_config->set(Config::KEY_ENDPOINT, $endpoint);

            return $this;
        }

        throw new \InvalidArgumentException('invalid endpoint');
    }

    /**
     * Set Username.
     *
     * @param string $username
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setUsername(string $username): self
    {
        if (!empty($username)) {
            $this->_config->set(Config::KEY_USERNAME, $username);

            return $this;
        }

        throw new \InvalidArgumentException('username invalid');
    }

    /**
     * Set Channel.
     *
     * @param string $channel
     *
     * @return Slack
     */
    public function setChannel(string $channel): self
    {
        if (!empty($channel)) {
            $this->_config->set(Config::KEY_CHANNEL, $channel);

            return $this;
        }

        throw new \InvalidArgumentException('channel invalid');
    }

    /**
     * Set AllowMarkdown.
     *
     * @param bool|string $allowMarkdown
     *
     * @return Slack
     */
    public function setAllowMarkdown(bool $allowMarkdown): self
    {
        $this->_config->set(Config::KEY_ALLOW_MARKDOWN, $allowMarkdown);

        return $this;
    }

    /**
     * Set SendLogAsAttachment.
     *
     * @param bool|string $sendLogAsAttachment
     *
     * @return Slack
     */
    public function setAsAttachment(bool $sendLogAsAttachment): self
    {
        $this->_config->set(
            Config::KEY_AS_ATTACHMENT, $sendLogAsAttachment
        );

        return $this;
    }

    /**
     * Set AddLoggerNameToMessage.
     *
     * @param bool $value
     *
     * @return Slack
     */
    public function setAddLoggerNameToMessage(bool $value): self
    {
        $this->_config->set(
            Config::KEY_ADD_LOGGER_TO_MESSAGE, $value
        );

        return $this;
    }

    /**
     * Get SlackClient.
     *
     * @return SClient
     */
    protected function _getSlackClient(): SClient
    {
        return SClient::factory($this->_config);
    }

    /**
     * Slack constructor.
     *
     * @param string       $name
     * @param null|SClient $slackClient
     * @param null|Config  $config
     */
    public function __construct(
        string $name = '', SClient $slackClient = null, Config $config = null
    ) {
        $this->_config = $config ?? new Config();
        $this->_slackClient = $slackClient;
        parent::__construct($name);
    }

    /**
     * Forwards the logging event to the destination.
     *
     * Derived appenders should implement this method to perform actual logging.
     *
     * @param LogEvent $event
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function append(LogEvent $event)
    {
        try {
            $client = $this->_getSlackClient();

            $client->setName($event->getLoggerName());
            $client->setLogMessage(
                \trim($this->layout->format($event))
            );

            // generate message
            $message = $client->generateMessage($event);

            // send message
            $client->sendMessage($message);

            return true;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // we need to silent curl and Guzzle errors, to prevent a loop
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
