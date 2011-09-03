<?php

class Utils
{
    
    public static function getCheckboxBooleanValue($checkbox_value)
    {
	return $checkbox_value == 'on' ? true : false;
    }

}

?>