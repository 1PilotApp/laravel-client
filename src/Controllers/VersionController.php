<?php

namespace CmsPilot\Client\Controllers;

use CmsPilot\Client\Classes\Composer;
use CmsPilot\Client\Classes\Files;
use CmsPilot\Client\Middelwares\Authentication;
use DB;
use Illuminate\Routing\Controller;

/**
 * Class VersionMonitoringController
 *
 * @package CmsPilot\Monitoring\Controllers
 */
class VersionController extends Controller
{
    const CONFIGS_TO_MONITOR = [
        'app.debug',
        'app.timezone',
        'app.env',
        'mail.driver',
        'mail.host',
    ];

    public function __construct()
    {
        $this->middleware(Authentication::class);
    }

    /**
     * Retrieve runtime and composer package version information
     */
    public function index()
    {
        return [
            'core'    => $this->getVersions(),
            'servers' => $this->getServers(),
            'plugins' => Composer::instance()->getPackagesData(),
            'extra'   => $this->getExtra(),
            'files'   => Files::instance()->getFilesProperties(),
        ];
    }

    public function getVersions()
    {
        return [
            'version'     => app()->version(),
            'new_version' => Composer::instance()->getLatestPackageVersion("laravel/framework"),
        ];
    }

    /**
     * Get Joomla and system versions
     *
     * @return array
     */
    public function getServers()
    {
        $serverWeb = $_SERVER['SERVER_SOFTWARE'] ?: getenv('SERVER_SOFTWARE') ?? null;
        $dbVersion = DB::select(DB::raw("select version() as version"))[0]->version ?? null;

        return [
            'php'   => phpversion(),
            'web'   => $serverWeb,
            'mysql' => $dbVersion,
        ];
    }

    /**
     * @return array
     */
    private function getExtra()
    {
        $extra = [
            'storage_dir_writable' => is_writable(base_path('storage')),
            'cache_dir_writable'   => is_writable(base_path('bootstrap/cache')),
        ];

        foreach (self::CONFIGS_TO_MONITOR as $config) {
            $extra[$config] = config($config);
        }

        return $extra;
    }

}