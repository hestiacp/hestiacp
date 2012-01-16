<?php

class VestaSession
{
    
    static public $instance = null;
    
    static function start($request = null)
    {
        self::initSessionPath();
        session_start();        
        /*if ($request->hasParameter('v_sd')) {            
            session_id($request->getParameter('v_sd'));
        }*/
    }
    
    static function initSessionPath()
    {
        $sessions_dir = '/tmp/'.Config::get('session_dirname');
        if (!is_readable($sessions_dir)) {
            mkdir($sessions_dir);
        }
        session_save_path($sessions_dir);
    }
    
    /**
     * Grab current instance or create it
     *
     * @return AjaxHandler
     */
    static function getInstance() 
    {
        return null == self::$instance ? self::$instance = new self() : self::$instance;
    }
     
    static function authorize($username)
    {
        $_SESSION['user'] = $username;
        return session_id();
    }
    
    static function logoff()
    {
        session_destroy();
    }

    public function getUser()
    {       
        if (isset($_SESSION['user'])) {
            $user = array('uid' => $_SESSION['user']);
            $user['DISK'] = 10000;
            $user['BANDWIDTH'] = 10000;
            $user['role'] = $_SESSION['role'];

            return $user;
        }

        print json_encode(array('result' => "NOT_AUTHORISED"));
        exit;        
    }

    public function getUserRole()
    {   
      //        if (isset($_SESSION['user'])) {
            if($_SESSION['user'] == 'vesta'){
                return Vesta::ADMIN;
            }
            else{
                return Vesta::USER;
            }
            //        }

        print json_encode(array('result' => "NOT_AUTHORISED"));
        exit;        
    }

    public function loginAs($login)
    {     
        // TODO checkrights for login as   
        if(Vesta::hasRights(self::getUserRole(), 'login_as')){
            if(!$_SESSION['real_user']){
                $_SESSION['real_user'] = $_SESSION['user'];
            }
        }  

      $_SESSION['user'] = $login;
    }

    public function logoutAs()
    {       
        $_SESSION['user'] = $_SESSION['real_user'];
        $_SESSION['real_user'] = false;
    }
}
?>
