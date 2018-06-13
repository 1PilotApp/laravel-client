<?php

namespace CmsPilot\Client\Classes;

use CmsPilot\Client\Traits\Instantiable;
use Composer\Semver\VersionParser;
use Illuminate\Support\Facades\Cache;

class Composer
{
    use Instantiable;

    /**
     * Get information for composer installed packages (currently installed version and latest stable version)
     *
     * @return array
     */
    public function getPackagesData()
    {
        $packages = [];

        $installedJsonFile = base_path('vendor/composer/installed.json');
        $installedPackages = json_decode(file_get_contents($installedJsonFile));

        if (count($installedPackages) == 0) {
            return [];
        }

        foreach ($installedPackages as $package) {
            $latestVersion = $this->getLatestPackageVersion($package->name);
            $currentVersion = $this->removePrefix($package->version);

            if ($latestVersion == $currentVersion) {
                $latestVersion = null;
            }

            $packages[] = [
                'code'        => $package->name,
                'active'      => 1,
                'version'     => $currentVersion,
                'new_version' => $latestVersion,
            ];
        }

        return $packages;
    }


    /**
     * Get latest (stable) version number of composer package
     *
     * @param string $packageName , the name of the package as registered on packagist, e.g. 'laravel/framework'
     *
     * @return string|null
     */
    public function getLatestPackageVersion($packageName)
    {
        $cacheKey = 'cmspilot-getLatestPackageVersion-' . md5($packageName);

        return Cache::remember($cacheKey, 10, function () use ($packageName) {
            $package = $this->getLatestPackage($packageName);

            return $this->removePrefix($package->version);
        });
    }

    /**
     * Get latest (stable) package from packagist
     *
     * @param string $packageName , the name of the package as registered on packagist, e.g. 'laravel/framework'
     *
     * @return object|null
     */
    private function getLatestPackage($packageName)
    {
        $packagistUrl = 'https://packagist.org/packages/' . $packageName . '.json';

        try {
            $packagistInfo = json_decode(file_get_contents($packagistUrl));
            $versions = $packagistInfo->package->versions;
        } catch (\Exception $e) {
            return null;
        }

        if (!is_object($versions)) {
            return null;
        }

        $lastVersion = null;

        foreach ($versions as $versionData) {
            $versionNumber = $versionData->version;
            $normalizeVersionNumber = $versionData->version_normalized;
            $stability = VersionParser::normalizeStability(VersionParser::parseStability($versionNumber));

            // only use stable version numbers
            if ($stability === 'stable'
                && version_compare($normalizeVersionNumber, $lastVersion->version_normalized ?? '', '>=')) {
                $lastVersion = $versionData;
            }
        }

        return $lastVersion;
    }

    /**
     * @param string $version
     *
     * @param string $prefix
     *
     * @return string
     */
    private function removePrefix($version, $prefix = 'v')
    {
        if (empty($version) || !starts_with($version, $prefix)) {
            return $version;
        }

        return substr($version, strlen($prefix));
    }
}