<?php

namespace CmsPilot\Client\Classes;

use CmsPilot\Client\Traits\Instantiable;
use Composer\Semver\VersionParser;
use Illuminate\Support\Facades\Cache;

class Composer
{
    use Instantiable;

    private $laravelDependencies;

    public function __construct()
    {
        $this->setAllLaravelDependencies();
    }

    /**
     * Get information for composer installed packages (currently installed version and latest stable version)
     *
     * @return array
     */
    public function getPackagesData()
    {
        $moduleVersions = [];

        $installedJsonFile = base_path('vendor/composer/installed.json');
        $packages = json_decode(file_get_contents($installedJsonFile));

        if (count($packages) == 0) {
            return [];
        }

        foreach ($packages as $package) {

            if (($this->laravelDependencies->get($package->name))) {
                continue;
            }

            $latestStable = $this->getLatestPackage($package->name);

            if (optional($latestStable)->version == $package->version) {
                $latestStable->version = null;
            }

            $moduleVersions[] = [
                'code'        => $package->name,
                'active'      => 1,
                'version'     => $package->version,
                'new_version' => optional($latestStable)->version,
            ];
        }

        return $moduleVersions;
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
        $cacheKey = 'cmspilot-latest-package-' . md5($packageName);

        $lastVersion = Cache::get($cacheKey, function () use ($packageName) {
            return $this->getLatestPackage($packageName);
        });

        if (is_object($lastVersion)) {
            return $lastVersion->version;
        }
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
        $lastVersion = null;

        // get version information from packagist
        $packagistUrl = 'https://packagist.org/packages/' . $packageName . '.json';

        try {
            $packagistInfo = json_decode(file_get_contents($packagistUrl));
            $versions = $packagistInfo->package->versions;
        } catch (\Exception $e) {
            $versions = [];
        }

        if (count($versions) > 0) {
            $latestStableNormVersNo = '';
            foreach ($versions as $versionData) {
                $versionNo = $versionData->version;
                $normVersNo = $versionData->version_normalized;
                $stability = VersionParser::normalizeStability(VersionParser::parseStability($versionNo));

                // only use stable version numbers
                if ($stability === 'stable' && version_compare($normVersNo, $latestStableNormVersNo) >= 0) {
                    $lastVersion = $versionData;
                    $latestStableNormVersNo = $normVersNo;
                }
            }
        }

        return $lastVersion;
    }

    private function setAllLaravelDependencies()
    {
        $initialDependencies = $this->getDependencies("laravel/framework");

        $allDependencies = collect([]);
        foreach ($initialDependencies as $key => $dependency) {

            $allDependencies = $allDependencies->merge($this->getDependencies($key));
        }

        $this->laravelDependencies = $allDependencies;
    }

    private function getDependencies($package)
    {
        $packageFile = base_path("/vendor/" . $package . "/composer.json");

        if (!file_exists($packageFile)) {
            return [];
        }

        $content = file_get_contents($packageFile);
        $dependenciesArray = json_decode($content, true);

        $dependencies = array_key_exists('require',
            $dependenciesArray) ? $dependenciesArray['require'] : 'No dependencies';

        return collect($dependencies);

        $devDependencies = array_key_exists('require-dev',
            $dependenciesArray) ? $dependenciesArray['require-dev'] : 'No dependencies';

        return collect($dependencies)->merge($devDependencies);
    }
}