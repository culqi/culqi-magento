<?php
/**
 * Exception for 406 Not Acceptable responses
 *
 * @package Requests
 */

/**
 * Exception for 406 Not Acceptable responses
 *
 * @package Requests
 */
require_once(dirname(__FILE__).'/../HTTP.php');
class Requests_Exception_HTTP_406 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 406;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Not Acceptable';
}