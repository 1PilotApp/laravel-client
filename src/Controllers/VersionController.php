<?php

namespace OnePilot\Client\Controllers;

use DB;
use Illuminate\Routing\Controller;
use OnePilot\Client\Classes\Composer;
use OnePilot\Client\Classes\Files;
use OnePilot\Client\Classes\LogsOverview;
use OnePilot\Client\Middlewares\Authentication;

class VersionController extends Controller
{
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
            'core' => $this->getVersions(),
            'servers' => $this->getServers(),
            'plugins' => Composer::instance()->getPackagesData(),
            'extra' => $this->getExtra(),
            'files' => Files::instance()->getFilesProperties(),
            'errors' => $this->errorsOverview(),
        ];
    }

    public function getVersions()
    {
        $laravel = Composer::instance()->getLatestPackageVersion('laravel/framework', app()->version());

        return [
            'version' => app()->version(),
            'new_version' => $laravel['compatible'] ?? null,
            'last_available_version' => $laravel['available'] ?? null,
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

        return [
            'php' => phpversion(),
            'web' => $serverWeb,
            'mysql' => $this->getDbVersion(),
        ];
    }

    /**
     * @return array
     */
    private function getExtra()
    {
        $configs = $this->config('app.debug', 'app.env', 'app.timezone');

        if (version_compare(app()->version(), '6', '<=')) {
            $configs += $this->config('mail.driver', 'mail.host');
        } else {
            $configs += $this->config('mail.default');

            if (config('mail.default') == 'smtp') {
                $configs += $this->config('mail.mailers.smtp.host');
            }
        }

        $configs += $this->config(
            'mail.from.address',
            'mail.from.name',
            'queue.default',
            'cache.default',
            'logging.default',
            'session.driver',
            'session.lifetime'
        );

        $configs['storage_dir_writable'] = is_writable(base_path('storage'));
        $configs['cache_dir_writable'] = is_writable(base_path('bootstrap/cache'));

        return $configs;
    }

    private function errorsOverview()
    {
        try {
            return (new LogsOverview())->get();
        } catch (\Exception $e) {
        }
    }

    /**
     * @return string|null
     */
    private function getDbVersion()
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        try {
            switch ($driver) {
                case 'mysql':
                    return $this->mysqlVersion();
                case 'sqlite':
                    return $this->sqliteVersion();
            }
        } catch (\Exception $e) {
            // nothing
        }
    }


    /**
     * @return string|null
     */
    private function mysqlVersion()
    {
        $connection = config('database.default');

        if (
            in_array(config("database.connections.{$connection}.database"), ['homestead', 'forge', '']) &&
            in_array(config("database.connections.{$connection}.username"), ['homestead', 'forge', '']) &&
            in_array(config("database.connections.{$connection}.password"), ['secret', ''])
        ) {
            // default config value, connection will not work
            return null;
        }

        $result = DB::select('SELECT VERSION() as version;');

        return $result[0]->version ?? null;
    }

    /**
     * @return string|null
     */
    private function sqliteVersion()
    {
        $result = DB::select('select "SQLite " || sqlite_version() as version');

        return $result[0]->version ?? null;
    }

    /**
     * @param ...$keys
     *
     * @return array
     */
    private function config(...$keys)
    {
        return collect($keys)->mapWithKeys(function ($item) {
            return [$item => config($item)];
        })->toArray();
    }
}
