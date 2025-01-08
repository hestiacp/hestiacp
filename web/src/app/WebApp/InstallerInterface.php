<?php
declare(strict_types=1);

namespace Hestia\WebApp;

interface InstallerInterface {
	public function getInstallationTarget(): InstallationTarget;
	public function install(InstallationTarget $target, array $options = null): void;
	public function withDatabase(): bool;
}
