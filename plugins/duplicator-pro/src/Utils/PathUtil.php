<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Utils;

use DUP_PRO_Archive;
use DUP_PRO_U;
use Duplicator\Libs\Snap\SnapIO;

class PathUtil
{
    /**
     * Checks if path is one of the WordPress core dirs
     *
     * @param string $path path to check
     *
     * @return bool Whether the storage path is one of the WP core dirs or not
     */
    public static function isPathInCoreDirs($path)
    {
        $coreDirs       = array_map(array(SnapIO::class, 'safePathTrailingslashit'), DUP_PRO_U::getWPCoreDirs(true));
        $localPaths     = [
            SnapIO::safePathTrailingslashit($path)
        ];
        $removeTempFile = false;
        if (!file_exists($path)) {
            // create temp file for realpath function
            $removeTempFile = SnapIO::touch($path);
        }
        $realPath = SnapIO::safePathTrailingslashit($path, true);
        if ($removeTempFile) {
            SnapIO::unlink($path);
        }
        if ($localPaths[0] !== $realPath) {
            $localPaths[] = $realPath;
        }
        if ((count(array_intersect($coreDirs, $localPaths)) > 0)) {
            return true;
        }

        $originalPaths = array_map('untrailingslashit', DUP_PRO_Archive::getOriginalPaths());
        $archivePaths  = array_map('untrailingslashit', DUP_PRO_Archive::getArchiveListPaths());
        $mainPathsList = [
            $originalPaths['abs'] . '/wp-includes',
            $originalPaths['abs'] . '/wp-admin',
            $originalPaths['themes'],
            $originalPaths['plugins'],
            $originalPaths['uploads'],
            $originalPaths['wpcontent'] . '/upgrade',
            $originalPaths['wpcontent'] . '/backups-dup-lite',
            $originalPaths['wpcontent'] . '/backups-dup-pro',
            $archivePaths['abs'] . '/wp-includes',
            $archivePaths['abs'] . '/wp-admin',
            $archivePaths['themes'],
            $archivePaths['plugins'],
            $archivePaths['uploads'],
            $archivePaths['wpcontent'] . '/upgrade',
            $archivePaths['wpcontent'] . '/backups-dup-lite',
            $archivePaths['wpcontent'] . '/backups-dup-pro'
        ];
        $mainPathsList = array_values(array_unique($mainPathsList));

        foreach ($mainPathsList as $mainPath) {
            foreach ($localPaths as $localPath) {
                if (SnapIO::isChildPath($localPath, $mainPath)) {
                    return true;
                }
            }
        }

        return false;
    }
}
