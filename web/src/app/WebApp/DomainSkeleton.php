<?php

declare(strict_types=1);

namespace Hestia\WebApp;

use Hestia\System\HestiaApp;
use Hestia\System\Util;
use RuntimeException;

use function count;
use function defined;
use function dirname;
use function in_array;
use function is_array;
use function is_dir;
use function is_link;
use function is_readable;
use function rtrim;
use function scandir;

final class DomainSkeleton
{
    private const DEFAULT_FILES = ['index.html', 'robots.txt'];

    public static function defaultSkeletonDocRoot(): string
    {
        $hestiaDir = defined('HESTIA_DIR_BIN')
            ? dirname(rtrim((string) HESTIA_DIR_BIN, '/\\'))
            : '/usr/local/hestia';

        return Util::joinPaths($hestiaDir, 'data/templates/web/skel/public_html');
    }

    public static function isClean(
        HestiaApp $appcontext,
        string $docRoot,
        ?string $skeletonDocRoot = null,
    ): bool {
        $skelRoot = $skeletonDocRoot ?? self::defaultSkeletonDocRoot();
        if (!is_dir($skelRoot) || !is_readable($skelRoot)) {
            $entries = $appcontext->listDirectoryEntries($docRoot);
            if ($entries === null) {
                return false;
            }

            foreach ($entries as $file => $type) {
                if ($type !== 'f' || !in_array($file, self::DEFAULT_FILES, true)) {
                    return false;
                }
            }

            return true;
        }

        return self::isDirectoryClean($appcontext, $docRoot, $skelRoot);
    }

    public static function cleanup(
        HestiaApp $appcontext,
        string $docRoot,
        ?string $skeletonDocRoot = null,
    ): void {
        $skelRoot = $skeletonDocRoot ?? self::defaultSkeletonDocRoot();
        if (!is_dir($skelRoot) || !is_readable($skelRoot)) {
            $entries = $appcontext->listDirectoryEntries($docRoot);
            if ($entries === null) {
                return;
            }

            foreach (self::DEFAULT_FILES as $defaultFile) {
                if (($entries[$defaultFile] ?? null) === 'f') {
                    $appcontext->deleteFile(Util::joinPaths($docRoot, $defaultFile));
                }
            }

            return;
        }

        self::cleanupDirectory($appcontext, $docRoot, $skelRoot);
    }

    private static function isDirectoryClean(
        HestiaApp $appcontext,
        string $currentDocDir,
        string $currentSkelDir,
    ): bool {
        $docEntries = $appcontext->listDirectoryEntries($currentDocDir);
        if ($docEntries === null) {
            return false;
        }

        $skelEntries = self::readSkeletonEntries($currentSkelDir);
        if ($skelEntries === null) {
            return count($docEntries) === 0;
        }

        foreach ($docEntries as $entry => $docType) {
            if (!isset($skelEntries[$entry]) || $docType !== $skelEntries[$entry]) {
                return false;
            }

            if ($skelEntries[$entry] === 'd') {
                $skelEntryPath = rtrim($currentSkelDir, '/\\') . '/' . $entry;
                $nestedDocDir = Util::joinPaths($currentDocDir, $entry);
                if (!self::isDirectoryClean($appcontext, $nestedDocDir, $skelEntryPath)) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function cleanupDirectory(
        HestiaApp $appcontext,
        string $currentDocDir,
        string $currentSkelDir,
    ): void {
        $skelEntries = self::readSkeletonEntries($currentSkelDir);
        if ($skelEntries === null) {
            return;
        }

        $docEntries = $appcontext->listDirectoryEntries($currentDocDir);
        if ($docEntries === null) {
            return;
        }

        foreach ($skelEntries as $entry => $skelType) {
            if (!isset($docEntries[$entry]) || $docEntries[$entry] !== $skelType) {
                continue;
            }

            $skelEntryPath = rtrim($currentSkelDir, '/\\') . '/' . $entry;
            $docEntryPath = Util::joinPaths($currentDocDir, $entry);
            if ($skelType === 'd') {
                if (self::isDirectoryClean($appcontext, $docEntryPath, $skelEntryPath)) {
                    $appcontext->deleteDirectory($docEntryPath);
                } else {
                    self::cleanupDirectory($appcontext, $docEntryPath, $skelEntryPath);
                }
            } elseif ($skelType === 'l') {
                try {
                    $appcontext->deleteFile($docEntryPath);
                } catch (RuntimeException) {
                    // Skip symlinks rejected by v-delete-fs-file boundary checks.
                }
            } else {
                $appcontext->deleteFile($docEntryPath);
            }
        }
    }

    /**
     * @return array<string, string>|null
     */
    private static function readSkeletonEntries(string $skelDir): ?array
    {
        if (!is_dir($skelDir) || !is_readable($skelDir)) {
            return null;
        }

        $entries = scandir($skelDir);
        if (!is_array($entries)) {
            return null;
        }

        $filtered = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $entryPath = rtrim($skelDir, '/\\') . '/' . $entry;
            if (is_link($entryPath)) {
                $filtered[$entry] = 'l';
            } elseif (is_dir($entryPath)) {
                $filtered[$entry] = 'd';
            } else {
                $filtered[$entry] = 'f';
            }
        }

        return $filtered;
    }
}
