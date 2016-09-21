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

namespace O2System\Core\Session\Abstracts;
defined( 'ROOTPATH' ) || exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\Exception;

/**
 * Class CacheException
 *
 * @package O2System\Core\Cache\Interfaces
 */
abstract class BaseException extends Exception
{
	/**
	 * Library Description
	 *
	 * @var array
	 */
	public $library = [
		'name'        => 'O2System Session (O2Session)',
		'description' => 'Open Source PHP Session Management',
		'version'     => '2.0',
	];

	/**
	 * Custom view exception filename
	 *
	 * @var string
	 */
	public $viewFileName = 'session_exception.php';
}