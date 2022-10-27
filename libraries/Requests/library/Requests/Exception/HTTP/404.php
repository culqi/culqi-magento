<?php
/**
 * Exception for 404 Not Found responses
 *
 * @package Requests
 */

/**
 * Exception for 404 Not Found responses
 *
 * @package Requests
 */
require_once(dirname(__FILE__).'/../HTTP.php');
class Requests_Exception_HTTP_404 extends Requests_Exception_HTTP {
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $code = 404;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	protected $reason = 'Not Found';
}