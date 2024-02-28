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

use EasyWPSMTP\Vendor\Monolog\Formatter\NormalizerFormatter;
use EasyWPSMTP\Vendor\Monolog\Logger;
/**
 * Handler sending logs to Zend Monitor
 *
 * @author  Christian Bergau <cbergau86@gmail.com>
 * @author  Jason Davis <happydude@jasondavis.net>
 */
class ZendMonitorHandler extends \EasyWPSMTP\Vendor\Monolog\Handler\AbstractProcessingHandler
{
    /**
     * Monolog level / ZendMonitor Custom Event priority map
     *
     * @var array
     */
    protected $levelMap = array();
    /**
     * Construct
     *
     * @param  int                       $level
     * @param  bool                      $bubble
     * @throws MissingExtensionException
     */
    public function __construct($level = \EasyWPSMTP\Vendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        if (!\function_exists('EasyWPSMTP\\Vendor\\zend_monitor_custom_event')) {
            throw new \EasyWPSMTP\Vendor\Monolog\Handler\MissingExtensionException('You must have Zend Server installed with Zend Monitor enabled in order to use this handler');
        }
        //zend monitor constants are not defined if zend monitor is not enabled.
        $this->levelMap = array(\EasyWPSMTP\Vendor\Monolog\Logger::DEBUG => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \EasyWPSMTP\Vendor\Monolog\Logger::INFO => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \EasyWPSMTP\Vendor\Monolog\Logger::NOTICE => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_INFO, \EasyWPSMTP\Vendor\Monolog\Logger::WARNING => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_WARNING, \EasyWPSMTP\Vendor\Monolog\Logger::ERROR => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \EasyWPSMTP\Vendor\Monolog\Logger::CRITICAL => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \EasyWPSMTP\Vendor\Monolog\Logger::ALERT => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR, \EasyWPSMTP\Vendor\Monolog\Logger::EMERGENCY => \EasyWPSMTP\Vendor\ZEND_MONITOR_EVENT_SEVERITY_ERROR);
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->writeZendMonitorCustomEvent(\EasyWPSMTP\Vendor\Monolog\Logger::getLevelName($record['level']), $record['message'], $record['formatted'], $this->levelMap[$record['level']]);
    }
    /**
     * Write to Zend Monitor Events
     * @param string $type Text displayed in "Class Name (custom)" field
     * @param string $message Text displayed in "Error String"
     * @param mixed $formatted Displayed in Custom Variables tab
     * @param int $severity Set the event severity level (-1,0,1)
     */
    protected function writeZendMonitorCustomEvent($type, $message, $formatted, $severity)
    {
        zend_monitor_custom_event($type, $message, $formatted, $severity);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefaultFormatter()
    {
        return new \EasyWPSMTP\Vendor\Monolog\Formatter\NormalizerFormatter();
    }
    /**
     * Get the level map
     *
     * @return array
     */
    public function getLevelMap()
    {
        return $this->levelMap;
    }
}
