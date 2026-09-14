<?php
ob_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (empty($_SESSION['user'])) {
    header("Location: /login/");
    exit;
}

$user = !empty($_SESSION['look']) ? $_SESSION['look'] : $_SESSION['user'];
$TAB = 'NODEJS';
$msg = '';
$error = '';

// AJAX Endpoint for directory scaffolding
if (!empty($_GET['ajax']) && $_GET['ajax'] === 'create_app') {
    header('Content-Type: application/json');
    $sel_domain = $_GET['domain'] ?? '';
    if (empty($sel_domain)) {
        echo json_encode(['success' => false]);
        exit;
    }
    exec(HESTIA_CMD . "v-node-manager create_app " . escapeshellarg($user) . " " . escapeshellarg($sel_domain), $out, $res);
    echo json_encode(['success' => ($res === 0), 'path' => "/home/$user/web/$sel_domain/public_html/app"]);
    exit;
}

// Multi-Language Strings
$lang = $_SESSION['language'] ?? 'en';
$i18n = [
    'en' => [
        'title' => 'Node.js Manager', 'user' => 'User', 'back' => 'Back', 'refresh' => 'Refresh',
        'node_v' => 'Node.js Version', 'npm_v' => 'NPM Version', 'pm2_v' => 'PM2 Engine', 'active_apps' => 'Active Apps',
        'app_name' => 'Application (Name)', 'status' => 'Status', 'cpu' => 'CPU', 'ram' => 'RAM', 'uptime' => 'Uptime',
        'manage' => 'Manage', 'no_apps' => 'No active services found', 'add_new' => 'Connect New Service',
        'select_domain' => 'Select Domain:', 'port' => 'Internal Port (Free):', 'app_path' => 'Project Path:',
        'entry_file' => 'Main File (Entry):', 'btn_run' => 'Run & Reverse Proxy', 'btn_create' => '+ Create app folder',
        'btn_fm' => 'File Manager', 'path_hint' => 'Leave empty or click "+ Create" to generate with correct permissions.',
        'alert_domain' => 'Please select a domain first!', 'alert_created' => 'Folder /public_html/app created successfully!',
        'err_token' => 'Invalid Security Token!', 'err_req' => 'Domain and port are required!', 'err_used' => 'Port is already in use!',
        'msg_success' => 'Operation completed successfully!', 'confirm_del' => 'Are you sure you want to delete?'
    ],
    'ka' => [
        'title' => 'Node.js მენეჯერი', 'user' => 'მომხმარებელი', 'back' => 'უკან', 'refresh' => 'განახლება',
        'node_v' => 'Node.js ვერსია', 'npm_v' => 'NPM ვერსია', 'pm2_v' => 'PM2 ძრავი', 'active_apps' => 'აქტიური აპები',
        'app_name' => 'აპლიკაცია (სახელი)', 'status' => 'სტატუსი', 'cpu' => 'CPU', 'ram' => 'RAM', 'uptime' => 'Uptime',
        'manage' => 'მართვა', 'no_apps' => 'აქტიური სერვისები არ მოიძებნა', 'add_new' => 'ახალი სერვისის დაკავშირება',
        'select_domain' => 'აირჩიეთ დომენი:', 'port' => 'შიდა პორტი (თავისუფალი):', 'app_path' => 'პროექტის გზა:',
        'entry_file' => 'მთავარი ფაილი (Entry):', 'btn_run' => 'გაშვება და Reverse Proxy', 'btn_create' => '+ app შექმნა',
        'btn_fm' => 'ფაილ მენეჯერი', 'path_hint' => 'დატოვეთ ცარიელი ან დააჭირეთ "+ app შექმნა"-ს ავტომატურად შესაქმნელად.',
        'alert_domain' => 'გთხოვთ ჯერ აირჩიოთ დომენი!', 'alert_created' => 'საქაღალდე /public_html/app წარმატებით შეიქმნა!',
        'err_token' => 'უსაფრთხოების ტოკენის შეცდომა!', 'err_req' => 'დომენი და პორტი სავალდებულოა!', 'err_used' => 'პორტი უკვე დაკავებულია!',
        'msg_success' => 'ოპერაცია წარმატებით შესრულდა!', 'confirm_del' => 'დარწმუნებული ხართ, რომ გსურთ წაშლა?'
    ]
];
$t = (strpos($lang, 'ka') !== false) ? $i18n['ka'] : $i18n['en'];

function getFirstFreePort($start = 3000) {
    for ($p = $start; $p <= 65535; $p++) {
        $fp = @fsockopen('127.0.0.1', $p, $e, $es, 0.05);
        if (!$fp) return $p;
        fclose($fp);
    }
    return 3000;
}

$suggested_port = getFirstFreePort(3000);
$node_v = trim(shell_exec('node -v 2>/dev/null') ?? 'N/A');
$npm_v = trim(shell_exec('npm -v 2>/dev/null') ?? 'N/A');
$pm2_v = preg_match('/([0-9]+\.[0-9]+\.[0-9]+)/', shell_exec('pm2 -v 2>/dev/null'), $m) ? $m[1] : 'Active';

// Process Actions
if (!empty($_GET['action']) && !empty($_GET['domain'])) {
    if (empty($_SESSION['token']) || !hash_equals($_SESSION['token'], $_GET['token'] ?? '')) {
        $error = $t['err_token'];
    } else {
        exec(HESTIA_CMD . "v-node-manager " . escapeshellarg($_GET['action']) . " " . escapeshellarg($user) . " " . escapeshellarg($_GET['domain']));
        header("Location: /add/node/?done=1");
        exit;
    }
}
if (!empty($_GET['done'])) $msg = $t['msg_success'];

// Add New App
if (!empty($_POST['btn_add'])) {
    if (empty($_SESSION['token']) || !hash_equals($_SESSION['token'], $_POST['token'] ?? '')) {
        $error = $t['err_token'];
    } else {
        $r_dom = trim($_POST['v_domain']);
        $r_port = intval($_POST['v_port']);
        $r_path = trim($_POST['v_path']);
        $r_entry = trim($_POST['v_entry']);
        $tgt = !empty($r_path) ? rtrim($r_path, '/') : "/home/$user/web/$r_dom/public_html/app";

        if (empty($r_dom) || empty($r_port)) {
            $error = $t['err_req'];
        } elseif (is_resource(@fsockopen('127.0.0.1', $r_port, $e, $es, 0.1))) {
            $error = $t['err_used'];
        } else {
            exec(HESTIA_CMD . "v-node-manager add " . escapeshellarg($user) . " " . escapeshellarg($r_dom) . " $r_port " . escapeshellarg($tgt) . " " . escapeshellarg($r_entry), $out, $res);
            if ($res === 0) {
                $msg = $t['msg_success'];
                $suggested_port = getFirstFreePort($r_port + 1);
            } else {
                $error = implode(" ", $out);
            }
        }
    }
}

exec(HESTIA_CMD . "v-node-manager list " . escapeshellarg($user), $pm2_raw);
$pm2_apps = (!empty($pm2_raw)) ? json_decode(implode('', $pm2_raw), true) : [];
exec(HESTIA_CMD . "v-list-web-domains " . escapeshellarg($user) . " json", $dom_raw);
$domains = json_decode(implode('', $dom_raw), true);

include($_SERVER['DOCUMENT_ROOT']."/templates/header.php");
?>

<style>
.node-dashboard { padding: 24px; max-width: 1400px; margin: 0 auto; }
.node-header { display: flex; justify-content: space-between; margin-bottom: 24px; align-items: center; }
.node-header-title { font-size: 22px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 12px; }
.node-btn { display: inline-flex; align-items: center; gap: 8px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; padding: 8px 16px; border: none; text-decoration: none; transition: 0.2s; }
.btn-outline { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); color: inherit; }
.btn-outline:hover { background: rgba(255,255,255,0.15); color: #fff; }
.node-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 28px; }
.node-stat-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 18px; display: flex; gap: 16px; align-items: center; }
.node-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; margin-bottom: 28px; }
.node-card-header { padding: 18px 22px; border-bottom: 1px solid rgba(255,255,255,0.08); font-weight: 600; font-size: 15px; }
.node-table { width: 100%; border-collapse: collapse; text-align: left; }
.node-table th { padding: 12px 20px; font-size: 12px; opacity: 0.6; border-bottom: 1px solid rgba(255,255,255,0.08); }
.node-table td { padding: 14px 20px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.04); }
.node-input-group label { display: block; font-size: 12px; font-weight: 600; opacity: 0.8; margin-bottom: 8px; }
.node-input-group input, .node-input-group select { width: 100%; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); padding: 10px 14px; border-radius: 8px; color: inherit; box-sizing: border-box; }
.btn-fm { background: rgba(46,204,113,0.15); color: #2ecc71; border: 1px solid #2ecc71; }
.btn-fm:hover { background: #2ecc71; color: #fff; }
.btn-create { background: rgba(52,152,219,0.15); color: #3498db; border: 1px solid #3498db; }
.btn-create:hover { background: #3498db; color: #fff; }
</style>

<div class="app-content">
    <div class="node-dashboard">
        <div class="node-header">
            <div style="display: flex; gap: 16px; align-items: center;">
                <a href="/list/web/" class="node-btn btn-outline"><i class="fas fa-chevron-left"></i> <?= $t['back'] ?></a>
                <h1 class="node-header-title"><i class="fab fa-node-js" style="color:#68a063;"></i> <?= $t['title'] ?> <span style="font-size:13px;opacity:0.6;font-weight:normal;">(<?= $t['user'] ?>: <?= htmlspecialchars($user) ?>)</span></h1>
            </div>
            <a href="/add/node/" class="node-btn btn-outline"><i class="fas fa-sync-alt"></i> <?= $t['refresh'] ?></a>
        </div>

        <div class="node-stats-grid">
            <div class="node-stat-card"><div style="font-size:24px;color:#68a063;"><i class="fab fa-node-js"></i></div><div><div style="font-size:11px;opacity:0.6;"><?= $t['node_v'] ?></div><div style="font-size:18px;font-weight:bold;"><?= $node_v ?></div></div></div>
            <div class="node-stat-card"><div style="font-size:24px;color:#cb3837;"><i class="fab fa-npm"></i></div><div><div style="font-size:11px;opacity:0.6;"><?= $t['npm_v'] ?></div><div style="font-size:18px;font-weight:bold;">v<?= $npm_v ?></div></div></div>
            <div class="node-stat-card"><div style="font-size:24px;color:#3498db;"><i class="fas fa-microchip"></i></div><div><div style="font-size:11px;opacity:0.6;"><?= $t['pm2_v'] ?></div><div style="font-size:18px;font-weight:bold;">v<?= $pm2_v ?></div></div></div>
            <div class="node-stat-card"><div style="font-size:24px;color:#2ecc71;"><i class="fas fa-cubes"></i></div><div><div style="font-size:11px;opacity:0.6;"><?= $t['active_apps'] ?></div><div style="font-size:18px;font-weight:bold;"><?= is_array($pm2_apps)?count($pm2_apps):0 ?></div></div></div>
        </div>

        <?php if($msg): ?><div style="color:#2ecc71; background:rgba(46,204,113,0.1); padding:12px 18px; border-radius:8px; margin-bottom:20px;"><i class="fas fa-check"></i> <?= $msg ?></div><?php endif; ?>
        <?php if($error): ?><div style="color:#e74c3c; background:rgba(231,76,60,0.1); padding:12px 18px; border-radius:8px; margin-bottom:20px;"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div><?php endif; ?>

        <div class="node-card">
            <div class="node-card-header"><i class="fas fa-layer-group" style="color:#3498db;"></i> <?= $t['active_apps'] ?></div>
            <table class="node-table">
                <thead><tr><th><?= $t['app_name'] ?></th><th><?= $t['status'] ?></th><th><?= $t['cpu'] ?></th><th><?= $t['ram'] ?></th><th><?= $t['uptime'] ?></th><th style="text-align:right;"><?= $t['manage'] ?></th></tr></thead>
                <tbody>
                    <?php if(empty($pm2_apps)): ?><tr><td colspan="6" style="text-align:center;padding:30px;opacity:0.5;"><?= $t['no_apps'] ?></td></tr>
                    <?php else: foreach($pm2_apps as $a): $n=$a['name']; $s=$a['pm2_env']['status']; ?>
                    <tr>
                        <td style="font-weight:bold;"><i class="fab fa-node-js" style="color:#68a063;"></i> <?= htmlspecialchars($n) ?></td>
                        <td><span style="color:<?= $s==='online'?'#2ecc71':'#e74c3c' ?>;text-transform:uppercase;font-size:11px;font-weight:bold;"><?= $s ?></span></td>
                        <td><?= $a['monit']['cpu'] ?? 0 ?>%</td>
                        <td><?= round(($a['monit']['memory']??0)/1024/1024,1) ?> MB</td>
                        <td><?= isset($a['pm2_env']['pm_uptime']) ? round((time() - ($a['pm2_env']['pm_uptime']/1000))/60).' min' : '-' ?></td>
                        <td style="text-align:right;">
                            <a href="?action=restart&domain=<?= urlencode($n) ?>&token=<?= $_SESSION['token'] ?>" style="color:#3498db;margin:0 8px;"><i class="fas fa-redo"></i></a>
                            <a href="?action=stop&domain=<?= urlencode($n) ?>&token=<?= $_SESSION['token'] ?>" style="color:#f39c12;margin:0 8px;"><i class="fas fa-pause"></i></a>
                            <a href="?action=delete&domain=<?= urlencode($n) ?>&token=<?= $_SESSION['token'] ?>" onclick="return confirm('<?= $t['confirm_del'] ?>')" style="color:#e74c3c;margin:0 8px;"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <div class="node-card" style="max-width:850px;">
            <div class="node-card-header"><i class="fas fa-plus-circle" style="color:#2ecc71;"></i> <?= $t['add_new'] ?></div>
            <div style="padding:22px;">
                <form method="post" action="/add/node/">
                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div class="node-input-group">
                            <label><?= $t['select_domain'] ?></label>
                            <select name="v_domain" id="v_domain" required>
                                <option value="">-- select --</option>
                                <?php if(!empty($domains)) foreach($domains as $d => $v): ?><option value="<?= $d ?>"><?= $d ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="node-input-group"><label><?= $t['port'] ?></label><input type="number" name="v_port" value="<?= $suggested_port ?>" required></div>
                    </div>

                    <div class="node-input-group" style="margin-bottom:16px;">
                        <label><?= $t['app_path'] ?></label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" id="v_path" name="v_path" placeholder="/home/<?= htmlspecialchars($user) ?>/web/domain/public_html/app" style="flex:1;">
                            <button type="button" onclick="createAppDir()" class="node-btn btn-create"><i class="fas fa-folder-plus"></i> <?= $t['btn_create'] ?></button>
                            <button type="button" onclick="openFM()" class="node-btn btn-fm"><i class="fas fa-folder-open"></i> <?= $t['btn_fm'] ?></button>
                        </div>
                        <small style="opacity:0.6;margin-top:6px;display:block;"><?= $t['path_hint'] ?></small>
                    </div>

                    <div class="node-input-group" style="margin-bottom:24px;">
                        <label><?= $t['entry_file'] ?></label>
                        <input type="text" name="v_entry" value="app.js" required>
                    </div>

                    <button type="submit" name="btn_add" value="1" class="node-btn" style="background:#27ae60;color:#fff;font-size:14px;"><i class="fas fa-plug"></i> <?= $t['btn_run'] ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function getDomain() { return document.getElementById('v_domain').value; }

function createAppDir() {
    const dom = getDomain();
    if (!dom) return alert("<?= $t['alert_domain'] ?>");
    fetch(`?ajax=create_app&domain=${encodeURIComponent(dom)}`).then(r=>r.json()).then(d=>{
        if(d.success) {
            document.getElementById('v_path').value = d.path;
            alert("<?= $t['alert_created'] ?>");
        }
    });
}

function openFM() {
    const dom = getDomain();
    const url = dom ? `/fm/?path=/web/${encodeURIComponent(dom)}/public_html/app` : `/fm/`;
    window.open(url, '_blank');
}
</script>

<?php include($_SERVER['DOCUMENT_ROOT']."/templates/footer.php"); ?>
