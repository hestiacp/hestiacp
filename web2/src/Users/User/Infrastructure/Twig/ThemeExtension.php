<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Twig;

use App\Users\User\Infrastructure\Security\HestiaUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use function sprintf;

class ThemeExtension extends AbstractExtension
{
    private const INTERNAL_THEME_PATH = __DIR__ . '/../../../../../public/css/themes/%s.css';
    private const PUBLIC_THEME_PATH = '/css/themes/%s.css';

    public function __construct(private TokenStorageInterface $token)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('theme_css', $this->themeLoader(...)),
        ];
    }

    public function getName(): string
    {
        return 'theme';
    }

    private function themeLoader(): string
    {
        $user = $this->token->getToken()?->getUser();

        if (!$user instanceof HestiaUser) {
            return '';
        }

        if (empty($user->theme) || $user->theme === 'default') {
            return '';
        }

        if (file_exists(sprintf(self::INTERNAL_THEME_PATH, $user->theme . '.min'))) {
            return sprintf(self::PUBLIC_THEME_PATH, $user->theme. '.min');
        }

        $customPath = 'custom/' . $user->theme;

        if (file_exists(sprintf(self::INTERNAL_THEME_PATH, $customPath))) {
            return sprintf(self::PUBLIC_THEME_PATH, $customPath);
        }

        $customPathMinified = $customPath . '.min';
        if (file_exists(sprintf(self::INTERNAL_THEME_PATH, $customPathMinified))) {
            return sprintf(self::PUBLIC_THEME_PATH, $customPathMinified);
        }

        return '';
    }
}
