<?php

declare(strict_types=1);

namespace WebProject\Log4php\Slack;

use LoggerLoggingEvent as LogEvent;
use Maknz\Slack\Attachment;
use Maknz\Slack\Client as SlackApiClient;
use Maknz\Slack\Message;
use WebProject\Log4php\Appender\Settings\Config;

/**
 * Class Client.
 */
class Client
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var SlackApiClient
     */
    protected $_slackApiClient;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_text;

    /**
     * Get LevelName.
     *
     * @param LogEvent $event
     *
     * @return string
     */
    public function getLevelName(LogEvent $event): string
    {
        return $event->getLevel()->toString();
    }

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Set Name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * @param LogEvent $event
     *
     * @return string
     */
    protected function _getIcon(LogEvent $event): string
    {
        if ($this->_getConfig()->get(Config::KEY_SET_ICON_BY_LOG_LEVEL)) {
            $icon = $this->_getConfig()->getIconByLogEvent($event);
        } else {
            $icon = (string) $this->_getConfig()->get(Config::KEY_ICON);
        }

        return $icon;
    }

    /**
     * Get slack api client.
     *
     * @return SlackApiClient
     */
    protected function _getSlackApiClient(): SlackApiClient
    {
        return $this->_slackApiClient ?? $this->_initSlackApiClient();
    }

    /**
     * Set Text.
     *
     * @param string $text
     *
     * @return Client
     */
    public function setText(string $text): self
    {
        $this->_text = $text;

        return $this;
    }

    /**
     * Get Title with markdown.
     *
     * @param LogEvent $event
     * @param string   $logMessage
     *
     * @return string
     */
    protected function _getMarkdownTitleText(
        LogEvent $event, string $logMessage
    ): string {
        return '*'.$this->getLevelName($event).'* '
            .'_( Logger: *'.$this->getName().'* )_: '.$logMessage.'';
    }

    /**
     * Get Text.
     *
     * @return string
     */
    protected function _getText(): string
    {
        return $this->_text ?? '';
    }

    /**
     * Get color by level name.
     *
     * @param LogEvent $event
     *
     * @return string
     */
    protected function _getColor(LogEvent $event): string
    {
        $levelAsInt = $event->getLevel()->toInt();

        return \array_key_exists($levelAsInt, Config::COLORS) ?
            Config::COLORS[$levelAsInt] : Config::COLOR_DEFAULT;
    }

    /**
     * Get Config.
     *
     * @return Config
     */
    protected function _getConfig(): Config
    {
        return $this->_config;
    }

    /**
     * Get Slack ApiSettings.
     *
     * @return array
     */
    protected function _getSlackApiSettings(): array
    {
        $linkNames = $this->_getConfig()->get(Config::KEY_LINK_NAMES);
        $media = $this->_getConfig()->get(Config::KEY_UNFURL_MEDIA);
        $links = $this->_getConfig()->get(Config::KEY_UNFURL_LINKS);

        return [
            'link_names'   => $linkNames,
            'unfurl_media' => $media,
            'unfurl_link'  => $links,
        ];
    }

    /**
     * Client constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @param Message $message
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function sendMessage(Message $message): bool
    {
        $this->_slackApiClient->sendMessage($message);

        return true;
    }

    /**
     * Generate message to send.
     *
     * @param LogEvent $event
     *
     * @throws \Exception
     *
     * @return Message
     */
    public function generateMessage(LogEvent $event): Message
    {
        // create message
        $message = $this->_getSlackApiClient()->createMessage();
        // set username
        $message->from((string) $this->_getConfig()->get(Config::KEY_USERNAME));
        // set icon
        $message->setIcon($this->_getIcon($event));
        // set channel
        $message->setChannel(
            (string) $this->_getConfig()->get(Config::KEY_CHANNEL)
        );
        // allow markdown in message
        $message->setAllowMarkdown(
            (bool) $this->_getConfig()->get(Config::KEY_ALLOW_MARKDOWN)
        );
        // send log message as attachment
        if ((bool) $this->_getConfig()->get(Config::KEY_AS_ATTACHMENT)) {
            // inject formatted message from event
            $message->attach($this->_generateAttachment($event));
        }

        // set name of logger as text
        return $this->_addMessageTitle($message, $event);
    }

    /**
     * Generate attachment.
     *
     * @param LogEvent $event
     *
     * @throws \Exception
     *
     * @return Attachment
     */
    protected function _generateAttachment(LogEvent $event): Attachment
    {
        $attachment = new Attachment([]);
        $attachment->setAuthorName(
            'Full '.\ucfirst($this->getLevelName($event)).' Message'
        );
        $attachment->setAuthorIcon(':ghost:');

        if ((bool) $this->_getConfig()->get(Config::KEY_ALLOW_MARKDOWN)) {
            $attachment->setMarkdownFields(
                $this->_getConfig()->get(
                    Config::KEY_MARKDOWN_IN_ATTACHMENTS_FIELDS
                )
            );
        }
        // add text to attachment
        $attachment->setText($this->_getText());
        // inject color to attachment
        $attachment->setColor($this->_getColor($event));
        // add footer
        $attachment->setFooter(
            'Logger: *'.$this->getName().'* '.
            '| Date: *'.(new \DateTime())->format('Y-m-d H:i:s').'*'
        );

        return $attachment;
    }

    /**
     * Set message title.
     *
     * @param Message  $message
     * @param LogEvent $event
     *
     * @return Message
     */
    protected function _addMessageTitle(
        Message $message, LogEvent $event
    ): Message {
        $logMessage = $this->_getText();

        $maxLength = $this->_getConfig()->get(Config::KEY_MAX_MESSAGE_LENGTH);
        if (\strlen($logMessage) > $maxLength) {
            $logMessage = \substr($logMessage, 0, $maxLength);
        }

        if ((bool) $this->_getConfig()->get(Config::KEY_ALLOW_MARKDOWN)) {
            $message->setText(
                $this->_getMarkdownTitleText($event, $logMessage)
            );
        } else {
            $message->setText(
                $this->getLevelName($event).' '.$this->_name.' '.$logMessage
            );
        }

        return $message;
    }

    /**
     * Add logger name as attachment field.
     *
     * @param Attachment $attachment
     *
     * @return Attachment
     */
    protected function _addFieldLoggerName(Attachment $attachment): Attachment
    {
        $loggerField = new \Maknz\Slack\AttachmentField([]);
        $loggerField
            ->setTitle('Logger')
            ->setValue($this->getName())
            ->setShort(true);

        $attachment->addField($loggerField);

        return $attachment;
    }

    /**
     * Add date as attachment field.
     *
     * @param Attachment $attachment
     *
     * @throws \Exception
     *
     * @return Attachment
     */
    protected function _addFieldDate(Attachment $attachment): Attachment
    {
        $dateField = new \Maknz\Slack\AttachmentField([]);
        $dateField
            ->setTitle('Date')
            ->setValue(
                (new \DateTime())->format('Y-m-d H:i:s')
            )
            ->setShort(true);

        $attachment->addField($dateField);

        return $attachment;
    }

    /**
     * Init php slack client.
     *
     * @return SlackApiClient
     */
    protected function _initSlackApiClient(): SlackApiClient
    {
        $slackClient = new SlackApiClient(
            (string) $this->_getConfig()->get(Config::KEY_ENDPOINT),
            $this->_getSlackApiSettings()
        );

        $this->_slackApiClient = $slackClient;

        return $this->_slackApiClient;
    }

    /**
     * Factory.
     *
     * @param Config $config
     *
     * @return Client
     */
    public static function factory(Config $config): self
    {
        return new self($config);
    }
}
