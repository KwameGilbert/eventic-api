<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

/**
 * Eloquent ORM Bootstrap
 * This class initializes Eloquent ORM for use outside of Laravel
 */
class EloquentBootstrap
{
    private static $capsule = null;

    /**
     * Initialize Eloquent ORM
     * @return Capsule
     */
    public static function boot(): Capsule
    {
        if (self::$capsule !== null) {
            return self::$capsule;
        }

        $capsule = new Capsule;

        // Check environment - default to local if not specified
        $env = isset($_ENV['ENVIRONMENT']) ? $_ENV['ENVIRONMENT'] : 'development';
        $prefix = $env === 'production' ? 'PROD_DB_' : 'LOCAL_DB_';

        // Add default database connection
        $capsule->addConnection([
            'driver'    => $_ENV[$prefix . 'DRIVER'] ?? 'mysql',
            'host'      => $_ENV[$prefix . 'HOST'],
            'port'      => $_ENV[$prefix . 'PORT'],
            'database'  => $_ENV[$prefix . 'DATABASE'],
            'username'  => $_ENV[$prefix . 'USERNAME'],
            'password'  => $_ENV[$prefix . 'PASSWORD'],
            'charset'   => $_ENV[$prefix . 'CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV[$prefix . 'COLLATION'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);

        // Set the event dispatcher used by Eloquent models
        $capsule->setEventDispatcher(new Dispatcher(new Container));

        // Make this Capsule instance available globally via static methods
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM
        $capsule->bootEloquent();

        self::$capsule = $capsule;

        return $capsule;
    }

    /**
     * Get the Capsule instance
     * @return Capsule|null
     */
    public static function getCapsule(): ?Capsule
    {
        return self::$capsule;
    }

    /**
     * Get the database connection
     * @return \Illuminate\Database\Connection
     */
    public static function getConnection()
    {
        if (self::$capsule === null) {
            self::boot();
        }
        return self::$capsule->getConnection();
    }
}
