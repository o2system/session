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
 * Class RedisHandler
 *
 * @package O2System\Session\Handlers
 */
class RedisHandler extends BaseHandler
{
	/**
	 * Platform Name
	 *
	 * @access  protected
	 * @var string
	 */
	protected $platform = 'redis';

	/**
	 * Redis Object
	 *
	 * @var \Redis
	 */
	protected $redis;

	// ------------------------------------------------------------------------

	/**
	 * RedisHandler::open
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
		if ( class_exists( 'Redis', FALSE ) )
		{
			$this->redis = new \Redis();
		}
		else
		{
			if ( $this->logger instanceof LoggerInterface )
			{
				$this->logger->error( 'E_SESSION_PLATFORM_UNSUPPORTED', [ 'Redis' ] );
			}

			return FALSE;
		}

		try
		{
			if ( ! $this->redis->connect(
				$this->config[ 'host' ], ( $this->config[ 'host' ][ 0 ] === '/' ? 0
				: $this->config[ 'port' ] ), $this->config[ 'timeout' ] )
			)
			{
				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_REDIS_CONNECTION_FAILED', [ 'Redis' ] );
				}

				return FALSE;
			}

			if ( isset( $this->config[ 'password' ] ) AND ! $this->redis->auth( $this->config[ 'password' ] ) )
			{
				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_REDIS_AUTHENTICATION_FAILED', [ 'Redis' ] );
				}

				return FALSE;
			}

			return TRUE;
		}
		catch ( \RedisException $e )
		{
			if ( $this->logger instanceof LoggerInterface )
			{
				$this->logger->error( 'E_SESSION_REDIS_CONNECTION_REFUSED', $e->getMessage() );
			}

			return FALSE;
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * RedisHandler::close
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
		if ( isset( $this->redis ) )
		{
			try
			{
				if ( $this->redis->ping() === '+PONG' )
				{
					isset( $this->lockKey ) AND $this->redis->delete( $this->lockKey );

					if ( ! $this->redis->close() )
					{
						return FALSE;
					}
				}
			}
			catch ( \RedisException $e )
			{
				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_REDIS_ON_CLOSE', $e->getMessage() );
				}
			}

			$this->redis = NULL;

			return TRUE;
		}

		return TRUE;
	}

	// ------------------------------------------------------------------------

	/**
	 * RedisHandler::destroy
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
		if ( isset( $this->redis, $this->isLocked ) )
		{
			if ( ( $result = $this->redis->delete( $this->prefixKey . $session_id ) ) !== 1 )
			{
				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_REDIS_ON_DELETE', var_export( $result, TRUE ) );
				}
			}

			return $this->_destroyCookie();
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * RedisHandler::gc
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
		// Not necessary, Redis takes care of that.
		return TRUE;
	}

	/**
	 * RedisHandler::read
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
		if ( isset( $this->redis ) AND $this->_lockSession( $session_id ) )
		{
			// Needed by write() to detect session_regenerate_id() calls
			$this->sessionId = $session_id;

			$session_data      = (string) $this->redis->get( $this->prefixKey . $session_id );
			$this->fingerprint = md5( $session_data );

			return $session_data;
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * RedisHandler::write
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
		if ( ! isset( $this->redis ) )
		{
			return FALSE;
		}
		// Was the ID regenerated?
		elseif ( $session_id !== $this->sessionId )
		{
			if ( ! $this->_lockRelease() OR ! $this->_lockSession( $session_id ) )
			{
				return FALSE;
			}

			$this->fingerprint = md5( '' );
			$this->sessionId   = $session_id;
		}

		if ( isset( $this->lockKey ) )
		{
			$this->redis->setTimeout( $this->lockKey, 300 );

			if ( $this->fingerprint !== ( $fingerprint = md5( $session_data ) ) )
			{
				if ( $this->redis->set( $this->prefixKey . $session_id, $session_data, $this->config[ 'lifetime' ] ) )
				{
					$this->fingerprint = $fingerprint;

					return TRUE;
				}

				return FALSE;
			}

			return $this->redis->setTimeout( $this->prefixKey . $session_id, $this->config[ 'lifetime' ] );
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------

	/**
	 * RedisHandler::_lockSession
	 *
	 * Acquires an (emulated) lock.
	 *
	 * @param    string $session_id Session ID
	 *
	 * @return    bool
	 */
	protected function _lockSession( $session_id )
	{
		if ( isset( $this->lockKey ) )
		{
			return $this->redis->setTimeout( $this->lockKey, 300 );
		}

		// 30 attempts to obtain a lock, in case another request already has it
		$lock_key = $this->prefixKey . $session_id . ':lock';
		$attempt  = 0;

		do
		{
			if ( ( $ttl = $this->redis->ttl( $lock_key ) ) > 0 )
			{
				sleep( 1 );
				continue;
			}

			if ( ! $this->redis->setex( $lock_key, 300, time() ) )
			{
				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_OBTAIN_LOCK', [ $this->prefixKey . $session_id ] );
				}

				return FALSE;
			}

			$this->lockKey = $lock_key;
			break;
		}
		while ( ++$attempt < 30 );

		if ( $attempt === 30 )
		{
			if ( $this->logger instanceof LoggerInterface )
			{
				$this->logger->error( 'E_SESSION_OBTAIN_LOCK_30', [ $this->prefixKey . $session_id ] );
			}

			return FALSE;
		}
		elseif ( $ttl === -1 )
		{
			if ( $this->logger instanceof LoggerInterface )
			{
				$this->logger->error( 'E_SESSION_OBTAIN_LOCK_TTL', [ $this->prefixKey . $session_id ] );
			}
		}

		$this->isLocked = TRUE;

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * RedisHandler::_lockRelease
	 *
	 * Releases a previously acquired lock
	 *
	 * @return bool
	 */
	protected function _lockRelease()
	{
		if ( isset( $this->redis, $this->lockKey ) && $this->isLocked )
		{
			if ( ! $this->redis->delete( $this->lockKey ) )
			{
				if ( $this->logger instanceof LoggerInterface )
				{
					$this->logger->error( 'E_SESSION_FREE_LOCK', [ $this->lockKey ] );
				}

				return FALSE;
			}

			$this->lockKey  = NULL;
			$this->isLocked = FALSE;
		}

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * RedisHandler::isSupported
	 *
	 * Checks if this platform is supported on this system.
	 *
	 * @return bool Returns FALSE if unsupported.
	 */
	public function isSupported()
	{
		return (bool) extension_loaded( 'redis' );
	}
}