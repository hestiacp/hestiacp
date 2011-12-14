<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vesta' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'VestaSession.class.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vesta' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'Config.class.php';

define('V_ROOT_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vesta' . DIRECTORY_SEPARATOR);

class Captcha
{
    protected $width   = 200;
    protected $image   = null;
    protected $color1  = null;
    protected $color2  = null;    
    protected $color3  = null; 
    protected $keyword = '';
    public    $key_len = 7;
    protected $chars   = 'qw1e2r3t4y5u67o8p9as9d38f6g4h3j2k1l3z5x7c8v3b5n781234567890';
    
    public function __construct()
    {
        VestaSession::start();
        //var_dump(Config::get('session_dirname'));die();
        $this->image  = imagecreatetruecolor($this->width, 50);
        $this->color1 = imagecolorallocate($this->image, 57, 58, 52);
        $this->color2 = imagecolorallocate($this->image, 45, 44, 40);        
        $this->color3 = imagecolorallocate($this->image, 255, 255, 255);        
        imagefilledrectangle($this->image, 0, 0, 249, 249, $this->color1);
    }
    
    
    public function generateImage($offset = 0)
    {
        $values = array(
                $offset,  15,
                $offset,  40,
                $offset + 14, 32,
                $offset + 14, 8,
                $offset,  15,
                $offset,  15
            );
        
        imagefilledpolygon($this->image, $values, 6, $this->color2);        
    }
    
    public function draw()
    {
        $this->generateKeyword();       
        for ($i = 0; $i < strlen($this->keyword) -1; $i++) {
            $this->generateImage($i * 15);
        }
        
        $font_file = dirname(__FILE__).DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'arialbd.ttf';
        imagefttext($this->image, 17, 0, 2, 31, $this->color3, $font_file, $this->keyword);        
        $this->slice();
    }
    
    public function slice()
    {
        $width  = 15;
        $height = 50;        
        $dest = imagecreatetruecolor(15 * $this->key_len + 2 * $this->key_len + 8, $height);
        imagefilledrectangle($dest, 0, 0, 249, 249, $this->color1);

        for ($i = 0; $i < $this->key_len; $i++) {
            $dest_x = $i == 0 ? $i * 15 : $i * 15 + $i * 4;
            imagecopy($dest, $this->image, $dest_x, 0, $i * 15, 0, $width, $height);
        }        
        
        header('Content-type: image/jpeg');
        imagepng($dest);        
    }

    /**
     * 
     */
    protected function generateKeyword()
    {
        $this->keyword = '';
        for ($i = 0; $i < $this->key_len; $i++) {
            $this->keyword .= $this->chars[rand(0, strlen($this->chars)-1)];
        }
        
        $_SESSION['captcha_key'] = $this->keyword;      
        return $this->keyword;
    }
    
}

$c = new Captcha();
$c->draw();



?>
