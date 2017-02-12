<?php

require 'vendor/autoload.php';

$config = array(
    'cachePath' => './cache',
    'keyPrefix' => 'test',
);

$fileCache = new \PFinal\Cache\Redis($config);

$fileCache->set('name', 'Ethan');

echo $fileCache->get('name');