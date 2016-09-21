<?php
/**
 * This file is part of the O2System PHP Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) Steeve Andrian Salim
 */
// ------------------------------------------------------------------------

namespace O2System\Session\Metadata;

// ------------------------------------------------------------------------

/**
 * Class Config
 *
 * @package O2System\Session\Metadata
 */
class Config extends \O2System\Core\Metadata\Config
{
	public function __construct( array $config )
	{
		// Define Session Name
		$config[ 'name' ] = isset( $config[ 'name' ] ) ? $config[ 'name' ] : 'o2session';

		// Define Session Match IP
		$config[ 'match' ][ 'ip' ] = isset( $config[ 'match' ][ 'ip' ] ) ? $config[ 'match' ][ 'ip' ] : FALSE;

		// Re-Define Session Name base on Match IP
		$config[ 'name' ] = $config[ 'name' ] . ':' . ( $config[ 'match' ][ 'ip' ] ? $_SERVER[ 'REMOTE_ADDR' ] . ':' : '' );
		$config[ 'name' ] = rtrim( $config[ 'name' ], ':' );

		if ( isset( $config[ 'handler' ] ) )
		{
			$config[ 'handler' ] = $config[ 'handler' ] === 'files' ? 'file' : $config[ 'handler' ];
			$config[ 'handler' ] = $config[ 'handler' ] === 'memcache' ? 'memcached' : $config[ 'handler' ];
		}

		if ( empty( $config[ 'cookie' ] ) AND php_sapi_name() !== 'cli' )
		{
			$config[ 'cookie' ] = [
				'name'     => 'o2session',
				'lifetime' => 7200,
				'domain'   => isset( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ],
				'path'     => '/',
				'secure'   => FALSE,
				'httpOnly' => FALSE,
			];
		}

		if ( ! isset( $config[ 'regenerate' ] ) )
		{
			$config[ 'regenerate' ][ 'destroy' ]  = FALSE;
			$config[ 'regenerate' ][ 'lifetime' ] = 600;
		}

		if ( ! isset( $config[ 'lifetime' ] ) )
		{
			$config[ 'lifetime' ] = $config[ 'cookie' ][ 'lifetime' ];
		}

		parent::__construct( $config, Config::CAMELCASE_OFFSET );
	}
}