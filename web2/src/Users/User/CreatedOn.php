<?php
declare(strict_types=1);

namespace App\Users\User;

use DateTimeImmutable;

readonly class CreatedOn
{
    public function __construct(
        private string $date,
        private string $time,
    ) {
    }

    public static function now()
    {
        $dateTime = new DateTimeImmutable('now');

        return new self($dateTime->format('Y-m-d'), $dateTime->format('H:i:s'));
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
