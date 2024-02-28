<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace EasyWPSMTP\Vendor\Monolog\Handler;

use EasyWPSMTP\Vendor\Gelf\IMessagePublisher;
use EasyWPSMTP\Vendor\Gelf\PublisherInterface;
use EasyWPSMTP\Vendor\Gelf\Publisher;
use InvalidArgumentException;
use EasyWPSMTP\Vendor\Monolog\Logger;
use EasyWPSMTP\Vendor\Monolog\Formatter\GelfMessageFormatter;
/**
 * Handler to send messages to a Graylog2 (http://www.graylog2.org) server
 *
 * @author Matt Lehner <mlehner@gmail.com>
 * @author Benjamin Zikarsky <benjamin@zikarsky.de>
 */
class GelfHandler extends \EasyWPSMTP\Vendor\Monolog\Handler\AbstractProcessingHandler
{
    /**
     * @var Publisher|PublisherInterface|IMessagePublisher the publisher object that sends the message to the server
     */
    protected $publisher;
    /**
     * @param PublisherInterface|IMessagePublisher|Publisher $publisher a publisher object
     * @param int                                            $level     The minimum logging level at which this handler will be triggered
     * @param bool                                           $bubble    Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($publisher, $level = \EasyWPSMTP\Vendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        parent::__construct($level, $bubble);
        if (!$publisher instanceof \EasyWPSMTP\Vendor\Gelf\Publisher && !$publisher instanceof \EasyWPSMTP\Vendor\Gelf\IMessagePublisher && !$publisher instanceof \EasyWPSMTP\Vendor\Gelf\PublisherInterface) {
            throw new \InvalidArgumentException('Invalid publisher, expected a Gelf\\Publisher, Gelf\\IMessagePublisher or Gelf\\PublisherInterface instance');
        }
        $this->publisher = $publisher;
    }
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->publisher->publish($record['formatted']);
    }
    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new \EasyWPSMTP\Vendor\Monolog\Formatter\GelfMessageFormatter();
    }
}
