<?php

declare(strict_types=1);

namespace Hestia\WebApp\Installers\BookStack;

use Hestia\System\Util;
use Hestia\WebApp\BaseSetup;
use Hestia\WebApp\InstallationTarget\InstallationTarget;

class BookStackSetup extends BaseSetup {
    protected array $info = [
        "name" => "BookStack",
        "group" => "cms",
        "version" => "latest",
        "thumbnail" => "bookstack-logo.svg",
    ];

    protected array $config = [
        "form" => [
            "site_name" => ["type" => "text", "value" => "BookStack"],
            "username" => ["value" => "admin"],
            "email" => "text",
            "password" => "password",
        ],
        "database" => true,
        "resources" => [
            'archive' => [
                'src' => 'https://codeberg.org/bookstack/bookstack/archive/v26.05.5.tar.gz',
                'dst' => '/'
            ],
        ],
        "server" => [
            "nginx" => [
                "template" => "bookstack",
            ],
            "php" => [
                "supported" => ["8.1", "8.2", "8.3", "8.4", "8.5"],
            ],
        ],
    ];

    protected function setupApplication(InstallationTarget $target, array $options = null): void {
        $docroot = $target->getDocRoot();
        $phpVersion = $options['php_version'];

        $this->moveNestedDirectory($docroot);
        $this->setupEnvFile($target, $options);

        $this->appcontext->runPHP($phpVersion, $docroot . '/bookstack-system-cli', [
            'download-vendor'
        ]);

        $this->appcontext->runPHP($phpVersion, $docroot . '/artisan', [
            'key:generate',
            '--no-interaction',
            '--force'
        ]);

        $this->appcontext->runPHP($phpVersion, $docroot . '/artisan', [
            'migrate',
            '--no-interaction',
            '--force'
        ]);

        $this->updateAdmin($docroot, $phpVersion, $options);
    }

    private function updateAdmin(string $docroot, string $phpVersion, array $options): void {
        $email    = trim($options['email'] ?? '');
        $username = trim($options['username'] ?? '');
        $password = trim($options['password'] ?? '');

        if (empty($email))    { $email    = 'admin@admin.com'; }
        if (empty($username)) { $username = 'Admin'; }
        if (empty($password)) { $password = 'password'; }

        $script = $docroot . '/update_admin.php';
        $emailEsc    = addslashes($email);
        $usernameEsc = addslashes($username);
        $passwordEsc = addslashes($password);

        $content = '<?php
require "' . $docroot . '/vendor/autoload.php";
$app = require "' . $docroot . '/bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = \BookStack\Users\Models\User::where("email", "admin@admin.com")->first();

if (!$user) {
    $user = \BookStack\Users\Models\User::where("email", "' . $emailEsc . '")->first();
}

if ($user) {
    if ("' . $emailEsc . '" !== "admin@admin.com" && "' . $emailEsc . '" !== $user->email) {
        $exists = \BookStack\Users\Models\User::where("email", "' . $emailEsc . '")->first();
        if ($exists) {
            echo "Email already used: ' . $emailEsc . '\n";
        } else {
            $user->email = "' . $emailEsc . '";
        }
    }

    $user->name = "' . $usernameEsc . '";
    $user->password = bcrypt("' . $passwordEsc . '");
    $user->email_confirmed = 1;
    $user->save();

    $adminRole = \BookStack\Users\Models\Role::getSystemRole("admin");
    if ($adminRole) {
        $user->attachRole($adminRole);
        $user->save();
    }

    echo "Admin updated\n";
    echo "Email: " . $user->email . "\n";
    echo "Password: ' . $passwordEsc . '\n";
} else {
    echo "Admin not found\n";
}
';

        $this->appcontext->createFile($script, $content);
        $this->appcontext->runPHP($phpVersion, $script, []);
        $this->appcontext->deleteFile($script);
    }

    private function moveNestedDirectory(string $docroot): void {
        $dirs = glob($docroot . '/bookstack-*');
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $this->moveFiles($dir, $docroot);
                $this->removeDir($dir);
                break;
            }
        }
    }

    private function setupEnvFile(InstallationTarget $target, array $options): void {
        $docroot = $target->getDocRoot();
        $env_file = $docroot . '/.env.example';

        if (!file_exists($env_file)) {
            return;
        }

        $env_content = file_get_contents($env_file);
        $env_content = preg_replace('/^APP_URL=.*$/m', 'APP_URL=' . $target->getUrl(), $env_content);
        $env_content = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $target->database->name, $env_content);
        $env_content = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $target->database->user, $env_content);
        $env_content = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $target->database->password, $env_content);

        $this->appcontext->createFile($docroot . '/.env', $env_content);
    }

    private function moveFiles(string $src, string $dst): void {
        $files = $this->appcontext->listFiles($src);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $src_path = $src . '/' . $file;
            $dst_path = $dst . '/' . $file;

            if (is_dir($src_path)) {
                if (!is_dir($dst_path)) {
                    mkdir($dst_path, 0755, true);
                }
                $this->moveFiles($src_path, $dst_path);
            } else {
                rename($src_path, $dst_path);
            }
        }
    }

    private function removeDir(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}