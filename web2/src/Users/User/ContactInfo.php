<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class ContactInfo
{
    public function __construct(
        private string $contactName,
        private string $email,
    ) {
    }

    public function getContactName(): string
    {
        return $this->contactName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
