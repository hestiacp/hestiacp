<?php

/**
 * System exception
 *
 * Thrown if:
 * - system error occured
 * - unpredictable scenarios
 *
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @copyright vesta 2010-2011
 */
class SystemException extends Exception {
    const CODE_GENERAL = 0;

    public function __construct($message, $code=self::CODE_GENERAL, $previous=null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        print $this->message;
    }

}

?>
