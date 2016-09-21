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

// Define SESSION_PATH
define( 'SESSION_PATH', __DIR__ . DIRECTORY_SEPARATOR );

// ------------------------------------------------------------------------

/**
 * O2System Psr Autoload
 *
 * @param $className
 */
spl_autoload_register(
	function ( $className )
	{
		if ( $className === 'O2System\Session' )
		{
			require __DIR__ . '/Session.php';
		}
		elseif ( strpos( $className, 'O2System\Session\\' ) === FALSE )
		{
			return;
		}

		$className = ltrim( $className, '\\' );
		$filePath  = '';

		if ( $lastNsPos = strripos( $className, '\\' ) )
		{
			$namespace = substr( $className, 0, $lastNsPos );
			$className = substr( $className, $lastNsPos + 1 );
			$filePath  = str_replace( '\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
		}

		$filePath .= str_replace( '_', DIRECTORY_SEPARATOR, $className ) . '.php';

		// Fixed Path
		$filePath = str_replace( 'O2System\Session\\', SESSION_PATH, $filePath );

		if ( file_exists( $filePath ) )
		{
			require $filePath;
		}

	}, TRUE, TRUE );
