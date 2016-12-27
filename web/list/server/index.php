<?php
error_reporting(NULL);
$TAB = 'SERVER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// CPU info
if (isset($_GET['cpu'])) {
    $TAB = 'CPU';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-cpu-status', $output, $return_var);
    foreach($output as $file) {
        echo $file . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}

// Memory info
if (isset($_GET['mem'])) {
    $TAB = 'MEMORY';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-memory-status', $output, $return_var);
    foreach($output as $file) {
        echo $file . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}

// Disk info
if (isset($_GET['disk'])) {
    $TAB = 'DISK';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-disk-status', $output, $return_var);
    foreach($output as $file) {
        echo $file . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}

// Network info
if (isset($_GET['net'])) {
    $TAB = 'NETWORK';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-network-status', $output, $return_var);
    foreach($output as $file) {
        echo $file . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}

// Web info
if (isset($_GET['web'])) {
    $TAB = 'WEB';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-web-status', $output, $return_var);
    foreach($output as $file) {
        $file=str_replace('border="0"', 'border="1"', $file);
        echo $file . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}


// DNS info
if (isset($_GET['dns'])) {
    $TAB = 'DNS';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-dns-status', $output, $return_var);
    foreach($output as $file) {
        echo $file . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}

// Mail info
if (isset($_GET['mail'])) {
    $TAB = 'MAIL';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-mail-status', $output, $return_var);
    if ($return_var == 0 ) {
        foreach($output as $file) {
            echo $file . "\n";
        }
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}

// DB info
if (isset($_GET['db'])) {
    $TAB = 'DB';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    exec (VESTA_CMD.'v-list-sys-db-status', $output, $return_var);
    if ($return_var == 0 ) {
        foreach($output as $file) {
            echo $file . "\n";
        }
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit();
}

// Data
exec (VESTA_CMD."v-list-sys-info json", $output, $return_var);
$sys = json_decode(implode('', $output), true);
unset($output);
exec (VESTA_CMD."v-list-sys-services json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);

// Render page
render_page($user, $TAB, 'list_services');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
