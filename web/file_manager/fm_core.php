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
    
    public function checkFileType($dir) {
        $dir = $this->formatFullPath($dir);
        exec(VESTA_CMD . "v-get-fs-file-type {$this->user} {$dir}", $output, $return_var);
        $error = self::check_return_code($return_var, $output);
        if (empty($error)) {
            return array(
                'result' => true,
                'data'   => implode('', $output)
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    
    public function formatFullPath($path_part = '') {
        if (substr($path_part, 0, strlen($this->ROOT_DIR)) === $this->ROOT_DIR) {
            $path = $path_part;
        }
        else {
            $path = $this->ROOT_DIR . '/' . $path_part;
        }
        //var_dump($path);die();
        //$path = str_replace(' ', '\ ', $path);
        return escapeshellarg($path);
    }
    
    function deleteItem($dir, $item) {
        $dir = $this->formatFullPath($item);
        exec (VESTA_CMD . "v-delete-fs-directory {$this->user} {$dir}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
        
        /*if (is_readable($item)) {
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
        );*/
    }
    
    function copyFile($item, $dir, $target_dir, $filename) {
        $src = $this->formatFullPath($item);
        $dst = $this->formatFullPath($target_dir);
    
        exec (VESTA_CMD . "v-copy-fs-file {$this->user} {$src} {$dst}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    
    
    function copyDirectory($item, $dir, $target_dir, $filename) {
        $src = $this->formatFullPath($item);
        $dst = $this->formatFullPath($target_dir);
    
        exec (VESTA_CMD . "v-copy-fs-directory {$this->user} {$src} {$dst}", $output, $return_var);


        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    
    static function check_return_code($return_var, $output) {
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            return $error;
            //if (empty($error)) $error = __('Error code:',$return_var);
            //$_SESSION['error_msg'] = $error;
        }
        
        return null;
    }
    
    function createFile($dir, $filename) {
        $dir = $this->formatFullPath($dir . '/' . $filename);

        exec (VESTA_CMD . "v-add-fs-file {$this->user} {$dir}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    
    function packItem($item, $dir, $target_dir, $filename) {
        $item     = $this->formatFullPath($item);
        $dst_item = $this->formatFullPath($target_dir);
        
        $dst_item = str_replace('.tar.gz', '', $dst_item);
        
        //$item = str_replace($dir . '/', '', $item);
//var_dump(VESTA_CMD . "v-add-fs-archive {$this->user} {$dst_item} {$item}");die();
        exec (VESTA_CMD . "v-add-fs-archive {$this->user} {$dst_item} {$item}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }

    function backupItem($item) {
        
        $src_item     = $this->formatFullPath($item);
        
        $dst_item_name = $item . '~' . date('Ymd_His');

        $dst_item = $this->formatFullPath($dst_item_name);

//print VESTA_CMD . "v-add-fs-archive {$this->user} {$item} {$dst_item}";die();
        exec (VESTA_CMD . "v-copy-fs-file {$this->user} {$src_item} {$dst_item}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result'   => true,
                'filename' => $dst_item_name
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    
    function unpackItem($item, $dir, $target_dir, $filename) {
        $item     = $this->formatFullPath($item);
        $dst_item = $this->formatFullPath($target_dir);

        exec (VESTA_CMD . "v-extract-fs-archive {$this->user} {$item} {$dst_item}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    
    function renameFile($dir, $item, $target_name) {
        $item     = $this->formatFullPath($dir . '/' . $item);
        $dst_item = $this->formatFullPath($dir . '/' . $target_name);
        
//        var_dump(VESTA_CMD . "v-move-fs-file {$this->user} {$item} {$dst_item}");die();

        exec (VESTA_CMD . "v-move-fs-file {$this->user} {$item} {$dst_item}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    function renameDirectory($dir, $item, $target_name) {
        $item     = $this->formatFullPath($dir . $item);
        $dst_item = $this->formatFullPath($dir . $target_name);

        if ($item == $dst_item) {
            return array(
                'result' => true
            );
        }


        exec (VESTA_CMD . "v-move-fs-directory {$this->user} {$item} {$dst_item}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
    }
    
    function createDir($dir, $dirname) {
        $dir = $this->formatFullPath($dir . '/' . $dirname);

        exec (VESTA_CMD . "v-add-fs-directory {$this->user} {$dir}", $output, $return_var);

        $error = self::check_return_code($return_var, $output);
        
        if (empty($error)) {
            return array(
                'result' => true
            );
        }
        else {
            return array(
                'result'   => false,
                'message'  => $error
            );
        }
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
