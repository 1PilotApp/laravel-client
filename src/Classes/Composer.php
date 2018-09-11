<?php

namespace OnePilot\Client\Classes;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Illuminate\Support\Facades\Cache;
use OnePilot\Client\Contracts\PackageDetector;
use OnePilot\Client\Traits\Instantiable;

class Composer
{
    use Instantiable;

    /** @var array */
    protected static $installedPackages;

    /** @var \Illuminate\Support\Collection */
    protected static $packagesContraints;

    public function __construct()
    {
        /** @var PackageDetector $detector */
        $detector = app(PackageDetector::class);

        self::$installedPackages = $detector->getPackages();

        self::$packagesContraints = $detector->getPackagesConstraints();
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
            if (empty($package->version) || empty($package->name)) {
                continue;
            }

            $currentVersion = $this->removePrefix($package->version);
            $latestVersion = $this->getLatestPackageVersion($package->name, $currentVersion);

            $packages[] = [
                'name'                   => str_after($package->name, '/'),
                'code'                   => $package->name,
                'type'                   => 'package',
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
        $cacheKey = 'onepilot-getLatestPackageVersion-' . md5($packageName . $currentVersion);

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

            // only use version that follow constraint
            if (
                version_compare($normalizeVersionNumber, $lastCompatibleVersion->version_normalized ?? '', '>=')
                && $this->checkConstraints($normalizeVersionNumber, $packageConstraints)
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

    private function checkConstraints($version, $constraints)
    {
        foreach ($constraints as $constraint) {
            if (Semver::satisfies($version, $constraint) !== true) {
                return false;
            }
        }

        return true;
    }
}
