<?php
declare(strict_types=1);

namespace App\Users\User;

readonly class PanelSettings
{
    public function __construct(
        private string $language,
        private string $theme,
        private string $sortOrder,
    ) {
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }
}
