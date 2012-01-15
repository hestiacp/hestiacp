<?php

class Utils
{
    
    public static function getCheckboxBooleanValue($checkbox_value)
    {
      return ($checkbox_value == 'on' || $checkbox_value == 'yes' || $checkbox_value === TRUE) ? true : false;
    }

}

?>