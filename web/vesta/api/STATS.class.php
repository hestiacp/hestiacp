<?php

/**
 * STATS
 * 
 * 
 * @author vesta, http://vestacp.com/
 * @author Dmitry Malishev <dima.malishev@gmail.com>
 * @author Dmitry Naumov-Socolov <naumov.socolov@gmail.com>
 * @copyright vesta 2010-2011
 */
 
class STATS extends AjaxHandler 
{
    const DAILY             = 'daily';
    const WEEKLY            = 'weekly';
    const MONTHLY           = 'monthly';
    const YEARLY            = 'yearly';
    const RRD_BASE_DIR      = '/rrd/';

    /**
     * Get STATS entries
     * 
     * @param Request $request
     * @return string - Ajax Reply
     */
    public function getListExecute(Request $request) 
    {
        $_period  = $request->getParameter('period');

        // if stats graph is requested not for today it should be regenerated manually
        if($_period && (in_array($_period, array(self::WEEKLY, self::MONTHLY, self::YEARLY)))){
          
            $result = Vesta::execute(Vesta:: V_UPDATE_SYS_RRD, array('PERIOD' => $_period));

            if (!$result['status']) {
                $this->errors[] = array($result['error_code'] => $result['error_message']);
                return $this->reply($result['status'], '');
            }
        }
        else{
          $_period = self::DAILY;
        }


        $result = Vesta::execute(Vesta::V_LIST_SYS_RRD,  array('PERIOD' => $_period));
        $reply = array();
        foreach ($result['data'] as $order => $details) {
            $reply[$order] = array(
                                   //                         'TYPE'         => $details['TYPE'],
                                   //                         'RRD'          => $details['RRD'],
                         'PERIOD'       => $_period,
                         'SRC'          => self::RRD_BASE_DIR.$details['TYPE'].'/'.$_period.'-'.$details['RRD'].'.png',
                         'TITLE'        => $details['TITLE'].' &nbsp; ('.$_period.')'
                      );
        }


        if (!$result['status']) {
            $this->errors[] = array($result['error_code'] => $result['error_message']);
        }
     
        return $this->reply($result['status'], $reply);
    }
}
