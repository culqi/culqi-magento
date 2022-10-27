<?php

namespace Culqi;
require_once('Client.php');
/**
 * Class Resource
 *
 * @package Culqi
 */
class Resource extends Client {

    /**
     * Constructor.
     */
    public function __construct($culqi)
    {
        $this->culqi = $culqi;
    }

}
