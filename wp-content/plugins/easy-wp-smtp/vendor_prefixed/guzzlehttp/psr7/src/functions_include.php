<?php

namespace EasyWPSMTP\Vendor;

// Don't redefine the functions if included multiple times.
if (!\function_exists('EasyWPSMTP\\Vendor\\GuzzleHttp\\Psr7\\str')) {
    require __DIR__ . '/functions.php';
}
