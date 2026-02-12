<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Check environment - default to local if not specified
$env = isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : 'development';

// ── Development Config ──────────────────────────────────────────────────────
$developmentConfig = [
    'adapter' => $_ENV['LOCAL_DB_DRIVER'] ?? $_ENV['LOCAL_DB_ADAPTER'] ?? 'mysql',
    'host'    => $_ENV['LOCAL_DB_HOST'] ?? '127.0.0.1',
    'name'    => $_ENV['LOCAL_DB_DATABASE'] ?? 'eventic',
    'user'    => $_ENV['LOCAL_DB_USERNAME'] ?? 'root',
    'pass'    => $_ENV['LOCAL_DB_PASSWORD'] ?? '',
    'port'    => $_ENV['LOCAL_DB_PORT'] ?? '3306',
    'charset' => $_ENV['LOCAL_DB_CHARSET'] ?? 'utf8mb4',
];

// ── Production Config ───────────────────────────────────────────────────────
$prodAdapter = $_ENV['PROD_DB_DRIVER'] ?? $_ENV['PROD_DB_ADAPTER'] ?? 'mysql';
$prodHost    = $_ENV['PROD_DB_HOST'];
$prodSslMode = $_ENV['PROD_DB_SSL'] ?? 'require';

$productionConfig = [
    'adapter' => $prodAdapter,
    'host'    => $prodHost,
    'name'    => $_ENV['PROD_DB_DATABASE'],
    'user'    => $_ENV['PROD_DB_USERNAME'],
    'pass'    => $_ENV['PROD_DB_PASSWORD'],
    'port'    => $_ENV['PROD_DB_PORT'],
    'charset' => $prodAdapter === 'pgsql' ? 'utf8' : 'utf8mb4',
];

// For PostgreSQL (Neon): set libpq environment variables
if ($prodAdapter === 'pgsql') {
    $endpointId = explode('.', $prodHost)[0];
    putenv("PGSSLMODE=" . ($prodSslMode ?: 'require'));
    putenv("PGOPTIONS=endpoint={$endpointId}");
} else {
    // MySQL SSL configuration
    $prodCaCert = $_ENV['PROD_DB_CA_CERTIFICATE'] ?? null;

    if ($prodCaCert) {
        if (strpos($prodCaCert, '-----BEGIN CERTIFICATE-----') !== false) {
            $tempCertFile = sys_get_temp_dir() . '/phinx_ca_cert.pem';
            file_put_contents($tempCertFile, $prodCaCert);
            $productionConfig['ssl_ca'] = $tempCertFile;
        } else {
            $prodCaPath = null;
            if (file_exists($prodCaCert)) {
                $prodCaPath = $prodCaCert;
            } elseif (file_exists(__DIR__ . '/' . $prodCaCert)) {
                $prodCaPath = realpath(__DIR__ . '/' . $prodCaCert);
            }
            if ($prodCaPath) {
                $productionConfig['ssl_ca'] = $prodCaPath;
            }
        }
    }

    if ($prodSslMode) {
        $productionConfig['sslmode'] = $prodSslMode;
    }
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => $env,
        'development'             => $developmentConfig,
        'production'              => $productionConfig,
    ],
    'version_order' => 'creation'
];
