<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Check environment - default to local if not specified
$env = isset($_ENV['ENVIRONMENT']) ? $_ENV['ENVIRONMENT'] : 'development';
$prefix = $env === 'production' ? 'PROD_DB_' : 'LOCAL_DB_';

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => $env,
        'development' => [
            'adapter' => $_ENV[$prefix . 'DRIVER'] ?? 'mysql',
            'host' => $_ENV[$prefix . 'HOST'],
            'name' => $_ENV[$prefix . 'DATABASE'],
            'user' => $_ENV[$prefix . 'USERNAME'],
            'pass' => $_ENV[$prefix . 'PASSWORD'],
            'port' => $_ENV[$prefix . 'PORT'],
            'charset' => $_ENV[$prefix . 'CHARSET'] ?? 'utf8mb4',
        ],
        'production' => [
            'adapter' => $_ENV['PROD_DB_DRIVER'] ?? 'mysql',
            'host' => $_ENV['PROD_DB_HOST'],
            'name' => $_ENV['PROD_DB_DATABASE'],
            'user' => $_ENV['PROD_DB_USERNAME'],
            'pass' => $_ENV['PROD_DB_PASSWORD'],
            'port' => $_ENV['PROD_DB_PORT'],
            'charset' => $_ENV['PROD_DB_CHARSET'] ?? 'utf8mb4',
        ]
    ],
    'version_order' => 'creation'
];
