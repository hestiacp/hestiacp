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
    v_exec('v-list-sys-cpu-status', [], false, $output);
    echo $output . "\n";
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}

// Memory info
if (isset($_GET['mem'])) {
    $TAB = 'MEMORY';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    v_exec('v-list-sys-memory-status', [], false, $output);
    echo $output . "\n";
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}

// Disk info
if (isset($_GET['disk'])) {
    $TAB = 'MEMORY';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    v_exec('v-list-sys-disk-status', [], false, $output);
    echo $output . "\n";
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}

// Network info
if (isset($_GET['net'])) {
    $TAB = 'MEMORY';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    v_exec('v-list-sys-network-status', [], false, $output);
    echo $output . "\n";
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}

// Web info
if (isset($_GET['web'])) {
    $TAB = 'WEB';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    v_exec('v-list-sys-web-status', [], false, $output);
    echo $output . "\n";
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}


// DNS info
if (isset($_GET['dns'])) {
    $TAB = 'DNS';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    $return_var = v_exec('v-list-sys-dns-status', [], false, $output);
    echo $output . "\n";
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}

// Mail info
if (isset($_GET['mail'])) {
    $TAB = 'MAIL';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    $return_var = v_exec('v-list-sys-mail-status', [], false, $output);
    if ($return_var == 0) {
        echo $output . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}

// DB info
if (isset($_GET['db'])) {
    $TAB = 'DB';
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_server_info.html');
    $return_var = v_exec('v-list-sys-db-status', [], false, $output);
    if ($return_var == 0) {
        echo $output . "\n";
    }
    echo "    </pre>\n</body>\n</html>\n";
    exit;
}


// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
v_exec('v-list-sys-info', ['json'], false, $output);
$sys = json_decode($output, true);

v_exec('v-list-sys-services', ['json'], false, $output);
$data = json_decode($output, true);

include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_services.html');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
