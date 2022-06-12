<?php
declare(strict_types = 1);

use Abrouter\LaravelClient\Bridge\KvStorage;
use Abrouter\LaravelClient\Bridge\ParallelRunning\TaskManager;

return [
    'token' => 'add73bda37106bbddf2e6b3f61c6ed197c2250e99df9474ad01b9afb2035af33cf66c292fdf6a6e8',
    'host' => 'https://abrouter.com',
    'kvStorage' => KvStorage::class,
    'parallelRunning' => [
        'enabled' => true,
        'taskManager' => TaskManager::class,
    ],
];
