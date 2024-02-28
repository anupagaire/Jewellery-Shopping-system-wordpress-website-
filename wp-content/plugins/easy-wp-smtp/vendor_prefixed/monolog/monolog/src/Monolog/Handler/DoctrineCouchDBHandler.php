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

use EasyWPSMTP\Vendor\Monolog\Logger;
use EasyWPSMTP\Vendor\Monolog\Formatter\NormalizerFormatter;
use EasyWPSMTP\Vendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \EasyWPSMTP\Vendor\Monolog\Handler\AbstractProcessingHandler
{
    private $client;
    public function __construct(\EasyWPSMTP\Vendor\Doctrine\CouchDB\CouchDBClient $client, $level = \EasyWPSMTP\Vendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new \EasyWPSMTP\Vendor\Monolog\Formatter\NormalizerFormatter();
    }
}
