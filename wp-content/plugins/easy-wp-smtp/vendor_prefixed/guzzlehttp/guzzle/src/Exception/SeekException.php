<?php

namespace EasyWPSMTP\Vendor\GuzzleHttp\Exception;

use EasyWPSMTP\Vendor\Psr\Http\Message\StreamInterface;
/**
 * Exception thrown when a seek fails on a stream.
 */
class SeekException extends \RuntimeException implements \EasyWPSMTP\Vendor\GuzzleHttp\Exception\GuzzleException
{
    private $stream;
    public function __construct(\EasyWPSMTP\Vendor\Psr\Http\Message\StreamInterface $stream, $pos = 0, $msg = '')
    {
        $this->stream = $stream;
        $msg = $msg ?: 'Could not seek the stream to position ' . $pos;
        parent::__construct($msg);
    }
    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }
}
