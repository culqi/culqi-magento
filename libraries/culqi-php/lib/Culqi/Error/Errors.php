<?php
namespace Culqi\Error;

/**
 * Culqi Exceptions
 */

/**
 * Base Culqi Exception
 */
class CulqiException extends \Exception {
    protected $message = "Base Culqi Exception";
}
/**
 * Input validation error
 */

class InputValidationError extends CulqiException {
    protected $message = "Error de validacion en los campos";
}
/**
 * Authentication error
 */

class AuthenticationError extends CulqiException {
    protected $message = "Error de autenticación";
}
/**
 * Resource not found
 */

class NotFound extends CulqiException {
    protected $message = "Recurso no encontrado";
}
/**
 * Method not allowed
 */

class MethodNotAllowed extends CulqiException {
    protected $message = "Method not allowed";
}
/**
 * Unhandled error
 */

class UnhandledError extends CulqiException {
    protected $message = "Unhandled error";
}
/**
 * Invalid API Key
 */

class InvalidApiKey extends CulqiException {
    protected $message = "API Key invalido";
}
/**
 * Unable to connect to Culqi API
 */
class UnableToConnect extends CulqiException {
    protected $message = "Imposible conectar a Culqi API";
}
