<?php

/**
 * Protection exception
 *
 * Thrown if unexpected action or behaviour detected
 *
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @copyright vesta 2010-2011
 */
class ProtectionException extends Exception 
{
    const CODE_GENERAL = 0;

    /**
     * Protection exception
     */
    public function __construct($message, $code=self::CODE_GENERAL, $previous=null) 
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Renders error message
     */
    public function __toString() 
    {
        print $this->message;
    }

}

?>
