<?php

declare(strict_types=1);

namespace DevIT\System;

class WebDomain
{
    public function __construct(
        public readonly string $domainName,
        public readonly string $domainPath,
        public readonly string $ipAddress,
        public readonly bool $isSslEnabled,
    ) {
    }
}
