<?php

namespace EasyWPSMTP\Vendor\GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(\EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(\EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() !== \EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(\EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface::FULFILLED;
    }
    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(\EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \EasyWPSMTP\Vendor\GuzzleHttp\Promise\PromiseInterface::REJECTED;
    }
}
