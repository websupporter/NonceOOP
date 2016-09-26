<?php // tests/bootstrap.php

namespace NonceOOP\Test;

use
     WpTestsStarter\WpTestsStarter;

/**
 * require composers autoload file, if it exists
 * __DIR__ points to the tests/ directroy
 */
$base_dir = dirname( __DIR__ );

require_once( dirname( __DIR__ ) . '/nonceoop.php' );

$autoload_file = "{$base_dir}/vendor/autoload.php";
if ( file_exists( $autoload_file ) ) {
    /**
     * this will load all your dependencies including WP Tests Starter
     * except the wordpress core as it does not support autoloading
     */
    require_once $autoload_file;
}

/**
 * the path is fine for the default configuration of composer
 * you only have to adapt it, when you configured composer to use
 * custom install paths
 */
$starter = new WpTestsStarter( "{$base_dir}/vendor/inpsyde/wordpress-dev" );

// phpunit defined these constants for you
$starter->defineDbName( DB_NAME );
$starter->defineDbUser( DB_USER );
$starter->defineDbPassword( DB_PASSWORD );
$starter->setTablePrefix( DB_TABLE_PREFIX );

// this will finally create the wp-tests-config.php and include the wordpress core tests bootstrap
$starter->bootstrap();