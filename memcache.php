<?php

require 'vendor/autoload.php';

$config = array(
    'cachePath' => './cache',
    'keyPrefix' => 'test',
);

$fileCache = new \PFinal\Cache\MemCache($config);

$fileCache->set('name', 'Ethan');

echo $fileCache->get('name');