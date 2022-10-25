<?php
/**
 * Exception for 503 Service Unavailable responses
 *
 * @package Requests
 */

/**
 * Exception for 503 Service Unavailable responses
 *
 * @package Requests
 */
require_once(dirname(__FILE__).'/../HTTP.php');
class Requests_Exception_HTTP_503 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 503;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Service Unavailable';
}