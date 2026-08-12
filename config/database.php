<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'Cloud' => [
            'driver' => 'pgsql',
            'host' => env('CLOUD_DB_HOST'),
            'port' => env('CLOUD_DB_PORT'),
            'database' => env('CLOUD_DB_DATABASE'),
            'username' => env('CLOUD_DB_USERNAME'),
            'password' => env('CLOUD_DB_PASSWORD'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer'
        ],
        'pos101_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS101_DB_HOST', '127.0.0.1'),
            'port' => env('POS101_DB_PORT', '5432'),
            'database' => env('POS101_DB_DATABASE', 'forge'),
            'username' => env('POS101_DB_USERNAME', 'forge'),
            'password' => env('POS101_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos102_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS102_DB_HOST', '127.0.0.1'),
            'port' => env('POS102_DB_PORT', '5432'),
            'database' => env('POS102_DB_DATABASE', 'forge'),
            'username' => env('POS102_DB_USERNAME', 'forge'),
            'password' => env('POS102_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos103_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS103_DB_HOST', '127.0.0.1'),
            'port' => env('POS103_DB_PORT', '5432'),
            'database' => env('POS103_DB_DATABASE', 'forge'),
            'username' => env('POS103_DB_USERNAME', 'forge'),
            'password' => env('POS103_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos104_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS104_DB_HOST', '127.0.0.1'),
            'port' => env('POS104_DB_PORT', '5432'),
            'database' => env('POS104_DB_DATABASE', 'forge'),
            'username' => env('POS104_DB_USERNAME', 'forge'),
            'password' => env('POS104_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos105_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS105_DB_HOST', '127.0.0.1'),
            'port' => env('POS105_DB_PORT', '5432'),
            'database' => env('POS105_DB_DATABASE', 'forge'),
            'username' => env('POS105_DB_USERNAME', 'forge'),
            'password' => env('POS105_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos106_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS106_DB_HOST', '127.0.0.1'),
            'port' => env('POS106_DB_PORT', '5432'),
            'database' => env('POS106_DB_DATABASE', 'forge'),
            'username' => env('POS106_DB_USERNAME', 'forge'),
            'password' => env('POS106_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos107_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS107_DB_HOST', '127.0.0.1'),
            'port' => env('POS107_DB_PORT', '5432'),
            'database' => env('POS107_DB_DATABASE', 'forge'),
            'username' => env('POS107_DB_USERNAME', 'forge'),
            'password' => env('POS107_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos108_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS108_DB_HOST', '127.0.0.1'),
            'port' => env('POS108_DB_PORT', '5432'),
            'database' => env('POS108_DB_DATABASE', 'forge'),
            'username' => env('POS108_DB_USERNAME', 'forge'),
            'password' => env('POS108_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos109_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS109_DB_HOST', '127.0.0.1'),
            'port' => env('POS109_DB_PORT', '5432'),
            'database' => env('POS109_DB_DATABASE', 'forge'),
            'username' => env('POS109_DB_USERNAME', 'forge'),
            'password' => env('POS109_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos110_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS110_DB_HOST', '127.0.0.1'),
            'port' => env('POS110_DB_PORT', '5432'),
            'database' => env('POS110_DB_DATABASE', 'forge'),
            'username' => env('POS110_DB_USERNAME', 'forge'),
            'password' => env('POS110_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos112_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS112_DB_HOST', '127.0.0.1'),
            'port' => env('POS112_DB_PORT', '5432'),
            'database' => env('POS112_DB_DATABASE', 'forge'),
            'username' => env('POS112_DB_USERNAME', 'forge'),
            'password' => env('POS112_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos113_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS113_DB_HOST', '127.0.0.1'),
            'port' => env('POS113_DB_PORT', '5432'),
            'database' => env('POS113_DB_DATABASE', 'forge'),
            'username' => env('POS113_DB_USERNAME', 'forge'),
            'password' => env('POS113_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos114_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS114_DB_HOST', '127.0.0.1'),
            'port' => env('POS114_DB_PORT', '5432'),
            'database' => env('POS114_DB_DATABASE', 'forge'),
            'username' => env('POS114_DB_USERNAME', 'forge'),
            'password' => env('POS114_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos115_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS115_DB_HOST', '127.0.0.1'),
            'port' => env('POS115_DB_PORT', '5432'),
            'database' => env('POS115_DB_DATABASE', 'forge'),
            'username' => env('POS115_DB_USERNAME', 'forge'),
            'password' => env('POS115_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos201_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS201_DB_HOST', '127.0.0.1'),
            'port' => env('POS201_DB_PORT', '5432'),
            'database' => env('POS201_DB_DATABASE', 'forge'),
            'username' => env('POS201_DB_USERNAME', 'forge'),
            'password' => env('POS201_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos202_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS202_DB_HOST', '127.0.0.1'),
            'port' => env('POS202_DB_PORT', '5432'),
            'database' => env('POS202_DB_DATABASE', 'forge'),
            'username' => env('POS202_DB_USERNAME', 'forge'),
            'password' => env('POS202_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos203_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS203_DB_HOST', '127.0.0.1'),
            'port' => env('POS203_DB_PORT', '5432'),
            'database' => env('POS203_DB_DATABASE', 'forge'),
            'username' => env('POS203_DB_USERNAME', 'forge'),
            'password' => env('POS203_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos205_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS205_DB_HOST', '127.0.0.1'),
            'port' => env('POS205_DB_PORT', '5432'),
            'database' => env('POS205_DB_DATABASE', 'forge'),
            'username' => env('POS205_DB_USERNAME', 'forge'),
            'password' => env('POS205_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos504_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS504_DB_HOST', '127.0.0.1'),
            'port' => env('POS504_DB_PORT', '5432'),
            'database' => env('POS504_DB_DATABASE', 'forge'),
            'username' => env('POS504_DB_USERNAME', 'forge'),
            'password' => env('POS504_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'pos505_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS505_DB_HOST', '127.0.0.1'),
            'port' => env('POS505_DB_PORT', '5432'),
            'database' => env('POS505_DB_DATABASE', 'forge'),
            'username' => env('POS505_DB_USERNAME', 'forge'),
            'password' => env('POS505_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos509_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS509_DB_HOST', '127.0.0.1'),
            'port' => env('POS509_DB_PORT', '5432'),
            'database' => env('POS509_DB_DATABASE', 'forge'),
            'username' => env('POS509_DB_USERNAME', 'forge'),
            'password' => env('POS509_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos510_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS510_DB_HOST', '127.0.0.1'),
            'port' => env('POS510_DB_PORT', '5432'),
            'database' => env('POS510_DB_DATABASE', 'forge'),
            'username' => env('POS510_DB_USERNAME', 'forge'),
            'password' => env('POS510_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos511_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS511_DB_HOST', '127.0.0.1'),
            'port' => env('POS511_DB_PORT', '5432'),
            'database' => env('POS511_DB_DATABASE', 'forge'),
            'username' => env('POS511_DB_USERNAME', 'forge'),
            'password' => env('POS511_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos510temp_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS510TEMP_DB_HOST', '127.0.0.1'),
            'port' => env('POS510TEMP_DB_PORT', '5432'),
            'database' => env('POS510TEMP_DB_DATABASE', 'forge'),
            'username' => env('POS510TEMP_DB_USERNAME', 'forge'),
            'password' => env('POS510TEMP_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos511temp_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS511TEMP_DB_HOST', '127.0.0.1'),
            'port' => env('POS511TEMP_DB_PORT', '5432'),
            'database' => env('POS511TEMP_DB_DATABASE', 'forge'),
            'username' => env('POS511TEMP_DB_USERNAME', 'forge'),
            'password' => env('POS511TEMP_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'pos505temp_pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('POS505TEMP_DB_HOST', '127.0.0.1'),
            'port' => env('POS505TEMP_DB_PORT', '5432'),
            'database' => env('POS505TEMP_DB_DATABASE', 'forge'),
            'username' => env('POS505TEMP_DB_USERNAME', 'forge'),
            'password' => env('POS505TEMP_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')) . '-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
