<?php

/**
 * Protection exception
 *
 * Thrown if unexpected action or behaviour detected
 *
 * @author vesta, http://vestacp.com/
 * @copyright vesta 2010
 */
class ProtectionException extends Exception {
    const CODE_GENERAL = 0;

    public function __construct($message, $code=self::CODE_GENERAL, $previous=null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        print $this->message;
    }

}

?>
