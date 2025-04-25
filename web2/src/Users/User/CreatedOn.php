<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class CreatedOn
{
    public function __construct(
        private string $date,
        private string $time,
    ) {
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getTime(): string
    {
        return $this->time;
    }
}
