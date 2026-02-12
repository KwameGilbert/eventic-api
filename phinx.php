<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Check environment - default to local if not specified
$env = isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : 'development';

/**
 * Parse a database connection string (URI) into Phinx config components.
 * Supports: postgresql://user:pass@host:port/dbname?sslmode=require&...
 *           mysql://user:pass@host:port/dbname?charset=utf8mb4
 *
 * @param string $connectionString
 * @return array Phinx-compatible config array
 */
function parseConnectionString(string $connectionString): array
{
    $parsed = parse_url($connectionString);

    // Map scheme to PDO adapter name
    $schemeMap = [
        'postgresql' => 'pgsql',
        'postgres'   => 'pgsql',
        'pgsql'      => 'pgsql',
        'mysql'      => 'mysql',
    ];

    $scheme  = $parsed['scheme'] ?? 'pgsql';
    $adapter = $schemeMap[$scheme] ?? $scheme;

    $host    = $parsed['host'] ?? '127.0.0.1';
    $port    = $parsed['port'] ?? ($adapter === 'pgsql' ? '5432' : '3306');
    $user    = urldecode($parsed['user'] ?? '');
    $pass    = urldecode($parsed['pass'] ?? '');
    $dbName  = ltrim($parsed['path'] ?? '/eventic', '/');

    // Parse query string parameters (sslmode, charset, etc.)
    $queryParams = [];
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $queryParams);
    }

    $config = [
        'adapter' => $adapter,
        'host'    => $host,
        'name'    => $dbName,
        'user'    => $user,
        'pass'    => $pass,
        'port'    => (string) $port,
        'charset' => $queryParams['charset'] ?? ($adapter === 'pgsql' ? 'utf8' : 'utf8mb4'),
    ];

    // For PostgreSQL with Neon: set libpq environment variables
    // so the endpoint ID and sslmode are passed at the libpq level
    if ($adapter === 'pgsql') {
        $sslmode    = $queryParams['sslmode'] ?? 'require';
        $endpointId = explode('.', $host)[0];

        // libpq reads these environment variables automatically
        putenv("PGSSLMODE={$sslmode}");
        putenv("PGOPTIONS=endpoint={$endpointId}");
    }

    return $config;
}

// ── Development Config ──────────────────────────────────────────────────────
$localConnStr = $_ENV['LOCAL_DB_CONNECTION_STRING'] ?? '';

if (!empty($localConnStr)) {
    $developmentConfig = parseConnectionString($localConnStr);
} else {
    $developmentConfig = [
        'adapter' => $_ENV['LOCAL_DB_DRIVER'] ?? $_ENV['LOCAL_DB_ADAPTER'] ?? 'mysql',
        'host'    => $_ENV['LOCAL_DB_HOST'] ?? '127.0.0.1',
        'name'    => $_ENV['LOCAL_DB_DATABASE'] ?? 'eventic',
        'user'    => $_ENV['LOCAL_DB_USERNAME'] ?? 'root',
        'pass'    => $_ENV['LOCAL_DB_PASSWORD'] ?? '',
        'port'    => $_ENV['LOCAL_DB_PORT'] ?? '3306',
        'charset' => $_ENV['LOCAL_DB_CHARSET'] ?? 'utf8mb4',
    ];
}

// ── Production Config ───────────────────────────────────────────────────────
$prodConnStr = $_ENV['PROD_DB_CONNECTION_STRING'] ?? '';

if (!empty($prodConnStr)) {
    $productionConfig = parseConnectionString($prodConnStr);
} else {
    // Fall back to individual variables
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
        'charset' => 'utf8',
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
