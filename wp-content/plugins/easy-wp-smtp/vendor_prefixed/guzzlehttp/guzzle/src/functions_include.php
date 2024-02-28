<?php

namespace EasyWPSMTP\Vendor;

// Don't redefine the functions if included multiple times.
if (!\function_exists('EasyWPSMTP\\Vendor\\GuzzleHttp\\uri_template')) {
    require __DIR__ . '/functions.php';
}
