<?php

namespace OnePilot\Client\Controllers;

use DB;
use Illuminate\Routing\Controller;
use OnePilot\Client\Classes\Composer;
use OnePilot\Client\Classes\Files;
use OnePilot\Client\Middlewares\Authentication;

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
        $laravel = Composer::instance()->getLatestPackageVersion('laravel/framework', app()->version());

        return [
            'version'                => app()->version(),
            'new_version'            => $laravel['compatible'],
            'last_available_version' => $laravel['available'],
        ];
    }

    /**
     * Get system versions
     *
     * @return array
     */
    public function getServers()
    {
        $serverWeb = $_SERVER['SERVER_SOFTWARE'] ?? getenv('SERVER_SOFTWARE') ?? null;

        try {
            $dbVersion = DB::select(DB::raw("select version() as version"))[0]->version ?? null;
        } catch (\Exception $e) {
            // nothing
        }

        return [
            'php'   => phpversion(),
            'web'   => $serverWeb,
            'mysql' => $dbVersion ?? null,
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