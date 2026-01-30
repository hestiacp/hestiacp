<?php

declare(strict_types=1);

namespace App\System\Hestia\Infrastructure\Twig;

use App\Users\User\Infrastructure\Security\HestiaUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use function sprintf;

class UnitsExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('humanize_time', $this->humanizeTime(...)),
            new TwigFunction('humanize_usage_size', $this->humanizeUsageSize(...)),
            new TwigFunction('humanize_usage_measure', $this->humanizeUsageMeasure(...)),
        ];
    }

    public function getName(): string
    {
        return 'units';
    }

    private function humanizeTime(int $usage): string
    {
        if ($usage > 60) {
            $usage = $usage / 60;
            if ($usage > 24) {
                $usage = $usage / 24;
                $usage = number_format($usage);
                return sprintf(ngettext('%d day', '%d days', $usage), $usage);
            } else {
                $usage = round($usage);
                return sprintf(ngettext('%d hour', '%d hours', $usage), $usage);
            }
        } else {
            $usage = round($usage);
            return sprintf(ngettext('%d minute', '%d minutes', $usage), $usage);
        }
    }

    private function humanizeUsageSize(string|int $usage, int $round = 2): string
    {
        if ($usage === 'unlimited') {
            return '∞';
        }

        if ($usage < 1) {
            $usage = '0';
        }

        $display_usage = $usage;
        if ($usage > 1024) {
            $usage = $usage / 1024;
            if ($usage > 1024) {
                $usage = $usage / 1024;
                if ($usage > 1024) {
                    $usage = $usage / 1024;
                    $display_usage = number_format((float) $usage, $round);
                } else {
                    if ($usage > 999) {
                        $usage = $usage / 1024;
                    }
                    $display_usage = number_format((float) $usage, $round);
                }
            } else {
                if ($usage > 999) {
                    $usage = $usage / 1024;
                }
                $display_usage = number_format((float) $usage, $round);
            }
        } else {
            if ($usage > 999) {
                $usage = $usage / 1024;
            }
            $display_usage = number_format((float) $usage, $round);
        }

        return $display_usage;
    }

    private function humanizeUsageMeasure(string|int $usage): string
    {
        if ($usage === 'unlimited') {
            return '';
        }

        $measure = 'kb';
        if ($usage > 1024) {
            $usage = $usage / 1024;
            if ($usage > 1024) {
                $usage = $usage / 1024;
                $measure = $usage < 1024 ? 'tb' : 'pb';
                if ($usage > 999) {
                    $usage = $usage / 1024;
                    $measure = 'pb';
                }
            } else {
                $measure = $usage < 1024 ? 'gb' : 'tb';
                if ($usage > 999) {
                    $usage = $usage / 1024;
                    $measure = 'tb';
                }
            }
        } else {
            $measure = $usage < 1024 ? 'mb' : 'gb';
            if ($usage > 999) {
                $measure = 'gb';
            }
        }

        return $measure;
    }
}
