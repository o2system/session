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

namespace O2System\Session\Handlers;

// ------------------------------------------------------------------------

use O2System\Psr\Log\LoggerInterface;

/**
 * Class FileHandler
 *
 * @package O2System\Session\Handlers
 */
class FileHandler extends BaseHandler
{
	/**
	 * Platform Name
	 *
	 * @var string
	 */
	protected $platform = 'file';

	/**
	 * File Handle
	 *
	 * @var resource
	 */
	protected $file;

	/**
	 * File write path
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * File Name and Path
	 *
	 * @var resource
	 */
	protected $filePath;

	/**
	 * Is New File Flag
	 *
	 * @var bool
	 */
	protected $isNewFile;

	// ------------------------------------------------------------------------

	/**
	 * FileHandler::open
	 *
	 * Initialize session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.open.php
	 *
	 * @param string $save_path The path where to store/retrieve the session.
	 * @param string $name      The session name.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function open( $save_path, $name )
	{
		if ( isset( $this->config[ 'path' ] ) )
		{
			$this->config[ 'path' ] = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $this->config[ 'path' ] );

			if ( is_dir( $this->config[ 'path' ] ) )
			{
				$this->path = $this->config[ 'path' ];
			}
		}
		else
		{
			$this->path = dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR;
		}

		$this->path = rtrim( $this->path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		$this->path = str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $this->path );

		$this->filePath = $this->path
			. $name . '-' // we'll use the session cookie name as a prefix to avoid collisions
			. ( $this->config[ 'match' ]->ip ? md5( $_SERVER[ 'REMOTE_ADDR' ] ) . '-' : '' );

		if ( $this->isSupported() === FALSE )
		{
			if ( $this->logger instanceof LoggerInterface )
			{
				$this->logger->error( 'E_SESSION_FILE_UNSUPPORTED', [ $this->path ] );
			}

			return FALSE;
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * FileHandler::close
	 *
	 * Close the session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.close.php
	 * @return bool <p>
	 *        The return value (usually TRUE on success, FALSE on failure).
	 *        Note this value is returned internally to PHP for processing.
	 *        </p>
	 * @since 5.4.0
	 */
	public function close()
	{
		if ( is_resource( $this->file ) )
		{
			flock( $this->file, LOCK_UN );
			fclose( $this->file );

			$this->file = $this->isNewFile = $this->sessionId = NULL;

			return TRUE;
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * FileHandler::destroy
	 *
	 * Destroy a session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 *
	 * @param string $session_id The session ID being destroyed.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function destroy( $session_id )
	{
		if ( $this->close() )
		{
			return file_exists( $this->filePath . $session_id )
				? ( unlink( $this->filePath . $session_id ) && $this->_destroyCookie() )
				: TRUE;
		}
		elseif ( $this->filePath !== NULL )
		{
			clearstatcache();

			return file_exists( $this->filePath . $session_id )
				? ( unlink( $this->filePath . $session_id ) && $this->_destroyCookie() )
				: TRUE;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * FileHandler::gc
	 *
	 * Cleanup old sessions
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.gc.php
	 *
	 * @param int $maxlifetime <p>
	 *                         Sessions that have not updated for
	 *                         the last maxlifetime seconds will be removed.
	 *                         </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function gc( $maxlifetime )
	{
		if ( ! is_dir( $this->path ) || ( $directory = opendir( $this->path ) ) === FALSE )
		{
			if ( $this->logger instanceof LoggerInterface )
			{
				$this->logger->error( 'E_SESSION_FILE_ON_GC', [ $this->path ] );
			}

			return FALSE;
		}

		$ts = time() - $maxlifetime;

		while ( ( $file = readdir( $directory ) ) !== FALSE )
		{
			// If the filename doesn't match this pattern, it's either not a session file or is not ours
			if ( ! preg_match( '[' . $this->config[ 'name' ] . '-]+[0-9-a-f]+', $file )
				|| ! is_file( $this->path . '/' . $file )
				|| ( $mtime = filemtime( $this->path . '/' . $file ) ) === FALSE
				|| $mtime > $ts
			)
			{
				continue;
			}

			unlink( $this->path . '/' . $file );
		}

		closedir( $directory );

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * FileHandler::read
	 *
	 * Read session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.read.php
	 *
	 * @param string $session_id The session id to read data for.
	 *
	 * @return string <p>
	 * Returns an encoded string of the read data.
	 * If nothing was read, it must return an empty string.
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function read( $session_id )
	{
		// This might seem weird, but PHP 5.6 introduced session_reset(),
		// which re-reads session data
		if ( $this->file === NULL )
		{
			if ( ( $this->file = fopen( $this->filePath . $session_id, 'c+b' ) ) === FALSE )
			{
				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_FILE_ON_READ', [ $this->filePath . $session_id ] );
				}

				return FALSE;
			}

			if ( flock( $this->file, LOCK_EX ) === FALSE )
			{
				fclose( $this->file );
				$this->file = NULL;

				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_ON_LOCK', [ $this->filePath . $session_id ] );
				}

				return FALSE;
			}

			// Needed by write() to detect session_regenerate_id() calls
			$this->sessionId = $session_id;

			if ( $this->isNewFile )
			{
				chmod( $this->filePath . $session_id, 0600 );
				$this->fingerprint = md5( '' );

				return '';
			}
		}
		else
		{
			rewind( $this->file );
		}

		$session_data = '';
		for ( $read = 0, $length = filesize( $this->filePath . $session_id ); $read < $length; $read += strlen( $buffer ) )
		{
			if ( ( $buffer = fread( $this->file, $length - $read ) ) === FALSE )
			{
				break;
			}

			$session_data .= $buffer;
		}

		$this->fingerprint = md5( $session_data );

		return $session_data;
	}

	// ------------------------------------------------------------------------

	/**
	 * FileHandler::write
	 *
	 * Write session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.write.php
	 *
	 * @param string $session_id   The session id.
	 * @param string $session_data <p>
	 *                             The encoded session data. This data is the
	 *                             result of the PHP internally encoding
	 *                             the $_SESSION superglobal to a serialized
	 *                             string and passing it as this parameter.
	 *                             Please note sessions use an alternative serialization method.
	 *                             </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function write( $session_id, $session_data )
	{
		// If the two IDs don't match, we have a session_regenerate_id() call
		// and we need to close the old handle and open a new one
		if ( $session_id !== $this->sessionId && ( ! $this->close() || $this->read( $session_id ) === FALSE ) )
		{
			return FALSE;
		}

		if ( ! is_resource( $this->file ) )
		{
			return FALSE;
		}
		elseif ( $this->fingerprint === md5( $session_data ) )
		{
			return ( $this->isNewFile )
				? TRUE
				: touch( $this->filePath . $session_id );
		}

		if ( ! $this->isNewFile )
		{
			ftruncate( $this->file, 0 );
			rewind( $this->file );
		}

		if ( ( $length = strlen( $session_data ) ) > 0 )
		{
			for ( $written = 0; $written < $length; $written += $result )
			{
				if ( ( $result = fwrite( $this->file, substr( $session_data, $written ) ) ) === FALSE )
				{
					break;
				}
			}

			if ( ! is_int( $result ) )
			{
				$this->fingerprint = md5( substr( $session_data, 0, $written ) );

				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_FILE_ON_WRITE' );
				}

				return FALSE;
			}
		}

		$this->fingerprint = md5( $session_data );

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * FileHandler::isSupported
	 *
	 * Checks if this platform is supported on this system.
	 *
	 * @return bool Returns FALSE if unsupported.
	 */
	public function isSupported()
	{
		return (bool) is_writable( $this->path );
	}
}