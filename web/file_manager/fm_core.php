<?php

class FileManager {
    
    protected $delimeter = '|';
    protected $info_positions = array(
        'TYPE'          => 0,
        'PERMISSIONS'   => 1,
        'DATE'          => 2,
        'TIME'          => 3,
        'OWNER'         => 4,
        'GROUP'         => 5,
        'SIZE'          => 6,
        'NAME'          => 7
    );
    
    protected $user  = null;
    public $ROOT_DIR = null;
    
    public function setRootDir($root = null) {
        if (null != $root) {
            $root = realpath($root);        
        }
        $this->ROOT_DIR = $root;
    }
    
    public function __construct($user) {
        $this->user = $user;
    }
    
    /*public function init() {
        $path = !empty($_REQUEST['dir']) ? $_REQUEST['dir'] : '';
        $start_url = !empty($path) ? $this->ROOT_DIR . '/' . $path : $this->ROOT_DIR;
        $listing = $this->getDirectoryListing($path);
        
        return $data = array(
            'result'     => true,
            'ROOT_DIR'   => $this->ROOT_DIR,
            'TAB_A_PATH' => $start_url,
            'TAB_B_PATH' => $this->ROOT_DIR, // second tab always loads home dir
            'listing'    => $listing
        );
    }*/
    
    public function formatFullPath($path_part = '') {
        if (substr($path_part, 0, strlen($this->ROOT_DIR)) === $this->ROOT_DIR) {
            $path = $path_part;
        }
        else {
            $path = $this->ROOT_DIR . '/' . $path_part;
        }
        //var_dump($path);die();
        return escapeshellarg($path);
    }
    
    function deleteItems($dir, $item) {
        if (is_readable($item)) {
            unlink($item);
        }
        if (is_readable($item)) {
            return array(
                'result'  => false,
                'message' => 'item was not deleted'
            );
        }
        return array(
            'result' => true
        );
    }
    
    function copyFile($dir, $target_dir, $filename) {
        // todo: checks
        // todo: vesta method "create file"
        if (empty($dir)) {
            $dir = $this->ROOT_DIR;
        }
        
        if (empty($target_dir)) {
            $target_dir = $this->ROOT_DIR;
        }
        copy($dir . '/' . $filename, $target_dir.'/'.$filename);
        
        if (!is_readable($target_dir . '/' .$filename)) {
            return array(
                'result'  => false,
                'message' => 'item was not created'
            );
        }
        
        return array(
            'result' => true,
            'bla' => $target_dir.'/'.$filename,
            'bla2' => $dir . '/' . $filename
        );
    }
    
    function createFile($dir, $filename) {
        // todo: checks
        // todo: vesta method "create file"
        if (empty($dir)) {
            $dir = $this->ROOT_DIR;
        }
        file_put_contents($dir . '/' . $filename, '');
        
        if (!is_readable($dir . '/' .$filename)) {
            return array(
                'result'  => false,
                'message' => 'item was not created'
            );
        }
        
        return array(
            'result' => true
        );
    }
    
    function renameItem($dir, $item, $target_name) {
        if (empty($dir)) {
            $dir = $this->ROOT_DIR;
        }
        if (is_readable($dir . '/' . $item)) {
            rename($dir . '/' . $item, $dir . '/' . $target_name);
        }
        if (!is_readable($dir . '/' .$target_name)) {
            return array(
                'result'  => false,
                'message' => 'item was not renamed'
            );
        }
        
        return array(
            'result' => true
        );
    }
    
    function createDir($dir, $dirname) {
        // todo: checks
        // todo: vesta method "create file"
        if (empty($dir)) {
            $dir = $this->ROOT_DIR;
        }

        mkdir($dir . '/' . $dirname);
        
        if (!is_readable($dir . '/' .$dirname)) {
            return array(
                'result'  => false,
                'message' => 'item was not created'
            );
        }
        
        return array(
            'result' => true
        );
    }
    
    function getDirectoryListing($dir = '') {
        $dir = $this->formatFullPath($dir);
        exec (VESTA_CMD . "v-list-fs-directory {$this->user} {$dir}", $output, $return_var);

        return $this->parseListing($output);
    }
    
    public function ls($dir = '') {
        $listing = $this->getDirectoryListing($dir);

        return $data = array(
            'result'  => true,
            'listing' => $listing
        );
    }
    
    public function open_file($dir = '') {
        $listing = $this->getDirectoryListing($dir);

        return $data = array(
            'result'  => true,
            'listing' => $listing
        );
    }
    
    public function parseListing($raw) {
        $data = array();
        foreach ($raw as $o) {
            $info = explode($this->delimeter, $o);
            $data[] = array(
                'type'          => $info[$this->info_positions['TYPE']],
                'permissions'   => $info[$this->info_positions['PERMISSIONS']],
                'date'          => $info[$this->info_positions['DATE']],
                'time'          => $info[$this->info_positions['TIME']],
                'owner'         => $info[$this->info_positions['OWNER']],
                'group'         => $info[$this->info_positions['GROUP']],
                'size'          => $info[$this->info_positions['SIZE']],
                'name'          => $info[$this->info_positions['NAME']]
            );
        }
        
        return $data;
    }

}
