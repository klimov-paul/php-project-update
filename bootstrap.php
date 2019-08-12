<?php

use KlimovPaul\PhpProjectUpdate\Helpers\Arr;

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/main.php';

$localConfigFilename = __DIR__ . '/config/local.php';
if (file_exists($localConfigFilename)) {
    $config = Arr::merge($config, require $localConfigFilename);
}
