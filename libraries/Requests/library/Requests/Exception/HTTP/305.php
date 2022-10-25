<?php
/**
 * Exception for 305 Use Proxy responses
 *
 * @package Requests
 */

/**
 * Exception for 305 Use Proxy responses
 *
 * @package Requests
 */
require_once(dirname(__FILE__).'/../HTTP.php');
class Requests_Exception_HTTP_305 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 305;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Use Proxy';
}
