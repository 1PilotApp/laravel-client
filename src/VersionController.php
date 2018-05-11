<?php

namespace CmsPilot\Client;

use App;
use Cache;
use CmsPilot\Client\Middelwares\Authentication;
use Composer\Semver\VersionParser;
use DB;
use Illuminate\Routing\Controller;

/**
 * Class VersionMonitoringController
 *
 * @package CmsPilot\Monitoring\Controllers
 */
class VersionController extends Controller
{

    private $laravelDependencies;

    const LARAVEL_FRAMEWORK = "laravel/framework";

    public function __construct()
    {
        $this->middleware(Authentication::class);
        $this->setAllLaravelDependencies();
    }

    /**
     * Retrieve runtime and composer package version information
     */
    public function index()
    {

        return [
            'core'    => $this->getVersions(),
            'servers' => $this->getServers(),
            'plugins' => $this->getComposerPackageData(),
            'extra'   => $this->getExtra(),
            'files'   => $this->getFilesProperties(),
        ];

    }

    public function getVersions()
    {
        return [
            'version'     => app()::VERSION,
            'new_version' => $this->getLatestPackageVersion(self::LARAVEL_FRAMEWORK),
        ];
    }

    /**
     * Get Joomla and system versions
     *
     * @return array
     */
    public function getServers()
    {
        $serverWeb = $_SERVER['SERVER_SOFTWARE'] ?: getenv('SERVER_SOFTWARE') ?: 'NOT_FOUND';

        return [
            'php'   => phpversion(),
            'web'   => $serverWeb,
            'mysql' => $this->DbVersion(),
        ];
    }

    /**
     * Get latest (stable) version number of composer package
     *
     * @param string $packageName , the name of the package as registered on packagist, e.g. 'laravel/framework'
     *
     * @return string|null
     */
    private function getLatestPackageVersion($packageName)
    {
        $lastVersion = Cache::get('key', function () use ($packageName) {
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

    /**
     * Get information for composer installed packages (currently installed version and latest stable version)
     *
     * @return array
     */
    private function getComposerPackageData()
    {
        $moduleVersions = [];

        $installedJsonFile = getcwd() . '/../vendor/composer/installed.json';
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


    private function DbVersion()
    {
        $result = DB::select(DB::raw("select version()"));

        return $result[0]->{'version()'};
    }

    /**
     * Get data for some important system files
     *
     * @return array
     */
    private function getFilesProperties()
    {
        $filesProperties = [];

        $files = [
            base_path('public/index.php'),
            base_path('public/.htaccess'),
            base_path('config/app.php'),
            base_path('config/cache.php'),
        ];

        foreach ($files as $file) {

            if (!file_exists($file)) {
                continue;
            }

            $fp = fopen($file, 'r');
            $fstat = fstat($fp);
            fclose($fp);

            $filesProperties[] = [
                'path'     => $file,
                'size'     => $fstat['size'],
                'mtime'    => $fstat['mtime'],
                'checksum' => md5_file($file),
            ];
        }

        return $filesProperties;
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

    /**
     * @return array
     */
    private function getExtra()
    {
        $extra = [
            'debug_mode'           => config('app.debug'),
            'storage_dir_writable' => is_writable(base_path('storage')),
            'cache_dir_writable'   => is_writable(base_path('bootstrap/cache')),
            'timezone'             => config('app.timezone'),
        ];

        return $extra;
    }

    private function setAllLaravelDependencies()
    {
        $initialDependencies = $this->getDependencies(self::LARAVEL_FRAMEWORK);

        $allDependencies = collect([]);
        foreach ($initialDependencies as $key => $dependency) {

            $allDependencies = $allDependencies->merge($this->getDependencies($key));
        }

        $this->laravelDependencies = $allDependencies;
    }
}