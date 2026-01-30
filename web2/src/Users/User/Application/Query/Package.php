<?php
declare(strict_types=1);

namespace App\Users\User\Application\Query;

readonly class Package
{
    public function __construct(
        public string $name,
        public string $disk,
        public string $bandwidth,
    ) {
    }
}
