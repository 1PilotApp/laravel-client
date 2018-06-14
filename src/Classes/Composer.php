<?php

namespace CmsPilot\Client\Classes;

use CmsPilot\Client\Traits\Instantiable;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Illuminate\Support\Facades\Cache;

class Composer
{
    use Instantiable;

    /** @var array */
    protected static $installedPackages;

    /** @var \Illuminate\Support\Collection */
    protected static $packagesContraints;

    public function __construct()
    {
        $installedJsonFile = base_path('vendor/composer/installed.json');
        $installedPackages = json_decode(file_get_contents($installedJsonFile));

        self::$installedPackages = count($installedPackages) == 0 ? [] : $installedPackages;

        self::$packagesContraints = $this->getPackagesConstraints();
    }

    /**
     * Get information for composer installed packages (currently installed version and latest stable version)
     *
     * @return array
     */
    public function getPackagesData()
    {
        $packages = [];

        foreach (self::$installedPackages as $package) {
            $currentVersion = $this->removePrefix($package->version);
            $latestVersion = $this->getLatestPackageVersion($package->name, $currentVersion);

            $packages[] = [
                'code'                   => $package->name,
                'active'                 => 1,
                'version'                => $currentVersion,
                'new_version'            => $latestVersion['compatible'],
                'last_available_version' => $latestVersion['available'],
            ];
        }

        return $packages;
    }


    /**
     * Get latest (stable) version number of composer package
     *
     * @param string $packageName    The name of the package as registered on packagist, e.g. 'laravel/framework'
     * @param string $currentVersion If provided will ignore this version (if last one is $currentVersion will return null)
     *
     * @return array ['compatible' => $version, 'available' => $version]
     */
    public function getLatestPackageVersion($packageName, $currentVersion = null)
    {
        $cacheKey = 'cmspilot-getLatestPackageVersion-' . md5($packageName . $currentVersion);

        return Cache::remember($cacheKey, 10, function () use ($packageName, $currentVersion) {
            $packages = $this->getLatestPackage($packageName);

            return collect($packages)->map(function ($package) use ($currentVersion) {
                $version = $this->removePrefix(optional($package)->version);

                return $version == $currentVersion ? null : $version;
            });
        });
    }

    /**
     * Get latest (stable) package from packagist
     *
     * @param string $packageName , the name of the package as registered on packagist, e.g. 'laravel/framework'
     *
     * @return array ['compatible' => (object) $version, 'available' => (object) $version]
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

        $lastCompatibleVersion = null;
        $lastAvailableVersion = null;

        $packageConstraints = self::$packagesContraints->get($packageName);

        foreach ($versions as $versionData) {
            $versionNumber = $versionData->version;
            $normalizeVersionNumber = $versionData->version_normalized;
            $stability = VersionParser::normalizeStability(VersionParser::parseStability($versionNumber));

            // only use stable version numbers
            if ($stability !== 'stable') {
                continue;
            }

            if (version_compare($normalizeVersionNumber, $lastAvailableVersion->version_normalized ?? '', '>=')) {
                $lastAvailableVersion = $versionData;
            }

            if (empty($packageConstraints)) {
                continue;
            }

            // only use stable version that follow constraint
            if (
                version_compare($normalizeVersionNumber, $lastCompatibleVersion->version_normalized ?? '', '>=')
                && Semver::satisfies($normalizeVersionNumber, $packageConstraints)
            ) {
                $lastCompatibleVersion = $versionData;
            }
        }

        if ($lastCompatibleVersion === $lastAvailableVersion) {
            $lastAvailableVersion = null;
        }

        return [
            'compatible' => $lastCompatibleVersion,
            'available'  => $lastAvailableVersion,
        ];
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

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getPackagesConstraints()
    {
        $composers = collect()
            ->push(base_path('composer.json'))
            ->merge(glob(base_path('vendor/*/*/composer.json')))
            ->filter(function ($path) {
                return file_exists($path);
            })
            ->map(function ($path) {
                $content = file_get_contents($path);

                return json_decode($content)->require ?? null;
            });

        $constraints = [];

        foreach ($composers as $packages) {
            foreach ($packages as $package => $constraint) {
                if (strpos($package, '/') === false) {
                    continue;
                }

                if (!isset($constraints[$package])) {
                    $constraints[$package] = [];
                }

                $constraints[$package][] = $constraint;
            }
        }

        return collect($constraints)->map(function ($items) {
            return implode(',', $items);
        });
    }
}