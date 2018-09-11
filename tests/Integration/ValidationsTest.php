<?php

namespace OnePilot\Client\Tests\Integration;

use App;
use OnePilot\Client\Classes\FakePackageDetector;
use OnePilot\Client\Tests\TestCase;

class ValidationsTest extends TestCase
{
    /** @var \Illuminate\Foundation\Testing\TestResponse */
    private static $response;

    public function setUp()
    {
        parent::setUp();

        if (empty(self::$response)) {
            self::$response = $this->getJson('onepilot/validate', $this->authenticationHeaders());
        }
    }

    /** @test */
    public function response_is_success()
    {
        self::$response->assertStatus(200);
    }

    /** @test */
    public function core_and_php_versions_are_right()
    {
        $data = self::$response->getOriginalContent();

        $this->assertEquals(app()->version(), array_get($data, 'core.version'));

        $this->assertEquals(phpversion(), array_get($data, 'servers.php'));
    }

    /** @test */
    public function all_real_packages_are_returned()
    {
        // ignore the project package (if it not have a version number)
        $packages = (new FakePackageDetector)->getPackages()
            ->filter(function ($package) {
                return !empty($package->version);
            })
            ->count();

        $data = self::$response->getOriginalContent();
        $this->assertEquals($packages, count($data['plugins']));
    }

    /** @test */
    public function extra_parameters()
    {
        $data = self::$response->getOriginalContent();

        $this->assertEquals(App::environment(), $data['extra']['app.env'] ?? null);

        $this->assertEquals(config('app.debug'), $data['extra']['app.debug'] ?? null);
    }

    /** @test */
    public function files_contains_config_app()
    {
        $files = collect(self::$response->getOriginalContent()['files'] ?? []);

        $configApp = $files->first(function ($file) {
            return $file['path'] == 'config/app.php';
        });

        $this->assertNotNull($configApp['checksum']);
    }
}
