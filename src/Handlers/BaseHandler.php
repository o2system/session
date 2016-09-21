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

use O2System\Core\Metadata\Config;
use O2System\Psr\Log\LoggerAwareInterface;
use O2System\Psr\Log\LoggerInterface;

/**
 * Class BaseHandler
 *
 * Base class of session platform handlers.
 *
 * @package O2System\Session\Handler
 */
abstract class BaseHandler implements \SessionHandlerInterface, LoggerAwareInterface
{
	/**
	 * Session Handler Platform Name
	 *
	 * @var string
	 */
	protected $platform;

	/**
	 * Session Handler Config
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * Session Cache Key Prefix
	 *
	 * @var string
	 */
	protected $prefixKey = 'o2session:';

	/**
	 * Session Lock Key
	 *
	 * @var string
	 */
	protected $lockKey;

	/**
	 * Session Data Fingerprint
	 *
	 * @var bool
	 */
	protected $fingerprint;

	/**
	 * Session Is Locked Flag
	 *
	 * @var bool
	 */
	protected $isLocked = FALSE;

	/**
	 * Current session ID
	 *
	 * @var string
	 */
	protected $sessionId;

	/**
	 * Logger Instance
	 *
	 * @var LoggerInterface
	 */
	protected $logger;

	//--------------------------------------------------------------------

	/**
	 * BaseHandler::__construct
	 *
	 * @param \O2System\Core\Metadata\Config $config
	 *
	 * @return BaseHandler
	 */
	public function __construct( Config $config )
	{
		$this->config = $config;
		$this->config->offsetUnset( 'handler' );
		$this->setPrefixKey( $this->config[ 'name' ] );
	}

	//--------------------------------------------------------------------

	/**
	 * BaseHandler::setLogger
	 *
	 * Sets a logger instance on the object
	 *
	 * @param LoggerInterface $logger
	 *
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger )
	{
		$this->logger =& $logger;
	}

	// ------------------------------------------------------------------------

	/**
	 * BaseHandler::setPrefixKey
	 *
	 * Sets cache prefix key
	 *
	 * @param $prefixKey
	 */
	public function setPrefixKey( $prefixKey )
	{
		$this->prefixKey = rtrim( $prefixKey, ':' ) . ':';
	}

	/**
	 * BaseHandler::getPlatform
	 *
	 * Get Current Platform
	 *
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	//--------------------------------------------------------------------

	/**
	 * BaseHandler::open
	 *
	 * Initialize session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.open.php
	 *
	 * @param string $savePath The path where to store/retrieve the session.
	 * @param string $name     The session name.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	abstract public function open( $savePath, $name );

	/**
	 * BaseHandler::close
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
	abstract public function close();

	/**
	 * BaseHandler::destroy
	 *
	 * Destroy a session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 *
	 * @param string $sessionId The session ID being destroyed.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	abstract public function destroy( $sessionId );

	/**
	 * BaseHandler::gc
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
	abstract public function gc( $maxlifetime );

	/**
	 * BaseHandler::read
	 *
	 * Read session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.read.php
	 *
	 * @param string $sessionId The session id to read data for.
	 *
	 * @return string <p>
	 * Returns an encoded string of the read data.
	 * If nothing was read, it must return an empty string.
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	abstract public function read( $sessionId );

	/**
	 * BaseHandler::write
	 *
	 * Write session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.write.php
	 *
	 * @param string $sessionId    The session id.
	 * @param string $sessionData  <p>
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
	abstract public function write( $sessionId, $sessionData );

	/**
	 * BaseHandler::_lockSession
	 *
	 * A dummy method allowing drivers with no locking functionality
	 * (databases other than PostgreSQL and MySQL) to act as if they
	 * do acquire a lock.
	 *
	 * @param string $sessionId
	 *
	 * @return bool
	 */
	protected function _lockSession( $sessionId )
	{
		$this->isLocked = TRUE;

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * BaseHandler::_lockRelease
	 *
	 * Releases the lock, if any.
	 *
	 * @return bool
	 */
	protected function _lockRelease()
	{
		$this->isLocked = FALSE;

		return TRUE;
	}

	//--------------------------------------------------------------------

	/**
	 * BaseHandler::_destroyCookie
	 *
	 * Internal method to force removal of a cookie by the client
	 * when session_destroy() is called.
	 *
	 * @return bool
	 */
	protected function _destroyCookie()
	{
		return setcookie(
			$this->config[ 'name' ],
			NULL,
			1,
			$this->config[ 'cookie' ]->path,
			$this->config[ 'cookie' ]->domain,
			$this->config[ 'cookie' ]->secure,
			TRUE
		);
	}

	//--------------------------------------------------------------------

	/**
	 * BaseHandler::isSupported
	 *
	 * Checks if this platform is supported on this system.
	 *
	 * @return bool Returns FALSE if unsupported.
	 */
	abstract public function isSupported();
}