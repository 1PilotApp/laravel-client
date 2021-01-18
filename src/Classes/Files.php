<?php

namespace OnePilot\Client\Classes;

use OnePilot\Client\Traits\Instantiable;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Files
{
    use Instantiable;

    /**
     * Get data for some important system files
     *
     * @return array
     */
    public function getFilesProperties()
    {
        $filesProperties = [];

        $files = [
            '.env',
            'public/index.php',
            'public/.htaccess',
        ];

        $configFiles = $this->getConfigFiles();

        foreach ($files + $configFiles as $absolutePath => $relativePath) {

            if (is_int($absolutePath)) {
                $absolutePath = base_path($relativePath);
            }

            if (!file_exists($absolutePath) || !is_file($absolutePath)) {
                continue;
            }

            $fp = fopen($absolutePath, 'r');
            $fstat = fstat($fp);
            fclose($fp);

            $filesProperties[] = [
                'path'     => $relativePath,
                'size'     => $fstat['size'],
                'mtime'    => $fstat['mtime'],
                'checksum' => md5_file($absolutePath),
            ];
        }

        return $filesProperties;
    }

    /**
     * @return array
     */
    private function getConfigFiles()
    {
        /** @var SplFileInfo[] $iterator */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path('config')),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $files = [];
        $basePath = realpath(base_path()) . DIRECTORY_SEPARATOR;

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $absolutePath = $file->getRealPath();
            $relativePath = str_replace($basePath, '', $absolutePath);

            $files[$absolutePath] = $relativePath;
        }

        return $files;
    }
}
