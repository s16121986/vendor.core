<?php
include __DIR__ . '/Autoload.php';

Autoload::addPath(__DIR__, 'Api\\');
Autoload::init(__DIR__ . '/Library/');

include __DIR__ . '/functions.php';
include __DIR__ . '/Api/enums.php';
include __DIR__ . '/Api/Api.php';