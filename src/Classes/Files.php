<?php

namespace OnePilot\Client\Classes;

use OnePilot\Client\Traits\Instantiable;

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

        $files += glob(base_path('config/*'));

        $configFiles = $this->getConfigFiles();

        foreach ($files + $configFiles as $absolutePath => $relativePath) {

            if (is_int($absolutePath)) {
                $absolutePath = base_path($relativePath);
            }

            if (!file_exists($absolutePath)) {
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
        return collect(glob(base_path('config/*')))->mapWithKeys(function ($absolutePath) {
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $absolutePath);

            return [
                $absolutePath => $relativePath,
            ];
        })->toArray();
    }
}