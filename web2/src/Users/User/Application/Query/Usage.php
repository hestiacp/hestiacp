<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query;

readonly class Usage
{
    public function __construct(
        public string $disk,
        public string $bandwidth,
        public string $webDomains,
        public string $dnsZones,
        public string $mailDomains,
        public string $databases,
        public string $backups,
        public string $ips,
    ) {
    }
}
