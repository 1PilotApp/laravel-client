<?php

namespace OnePilot\Client\Tests\Integration;

use Illuminate\Support\Collection;
use OnePilot\Client\Classes\Composer;
use OnePilot\Client\Classes\FakePackageDetector;
use OnePilot\Client\Tests\TestCase;

class ComposerUpdatesTest extends TestCase
{
    /** @test */
    public function laravel_55()
    {
        FakePackageDetector::setPackagesFromPath(__DIR__ . '/../data/composer/laravel55-installed-packages.json');
        FakePackageDetector::generatePackagesConstraints();

        $packages = collect((new Composer)->getPackagesData());

        $laravelFramework = $this->findPackage($packages, 'laravel/framework');

        $this->assertVersionGreaterThan('5.5.40', $laravelFramework['new_version']);
        $this->assertVersionLessThan('5.6.0', $laravelFramework['new_version']);
        $this->assertVersionGreaterThan('5.7.0', $laravelFramework['last_available_version']);

        $symfonyConsole = $this->findPackage($packages, 'symfony/console');

        $this->assertVersionGreaterThan('3.4.1', $symfonyConsole['new_version']);
        $this->assertVersionLessThan('4.0.0', $symfonyConsole['new_version']);
        $this->assertVersionGreaterThan('4.1.0', $symfonyConsole['last_available_version']);
    }

    /** @test */
    public function laravel_56()
    {
        FakePackageDetector::setPackagesFromPath(__DIR__ . '/../data/composer/laravel56-installed-packages.json');
        FakePackageDetector::generatePackagesConstraints();

        $packages = collect((new Composer)->getPackagesData());

        $laravelFramework = $this->findPackage($packages, 'laravel/framework');

        $this->assertVersionGreaterThan('5.6.35', $laravelFramework['new_version']);
        $this->assertVersionLessThan('5.7.0', $laravelFramework['new_version']);
        $this->assertVersionGreaterThan('5.7.0', $laravelFramework['last_available_version']);

        $symfonyConsole = $this->findPackage($packages, 'symfony/console');

        $this->assertVersionGreaterThan('4.0.0', $symfonyConsole['new_version']);
        $this->assertVersionLessThan('5.0.0', $symfonyConsole['new_version']);

        $carbon = $this->findPackage($packages, 'nesbot/carbon');

        $this->assertEquals('1.25.0', $carbon['version']);
        $this->assertVersionLessThan('1.26.0', $carbon['new_version'] ?? 0);
        $this->assertVersionGreaterThan('2.0.0', $carbon['last_available_version']);
    }

    /** @test */
    public function laravel_57()
    {
        FakePackageDetector::setPackagesFromPath(__DIR__ . '/../data/composer/laravel57-installed-packages.json');
        FakePackageDetector::generatePackagesConstraints();

        $packages = collect((new Composer)->getPackagesData());

        $laravelFramework = $this->findPackage($packages, 'laravel/framework');

        $this->assertVersionGreaterThan('5.7.0', $laravelFramework['new_version']);
        $this->assertVersionLessThan('5.8.0', $laravelFramework['new_version']);

        $symfonyConsole = $this->findPackage($packages, 'symfony/console');

        $this->assertVersionGreaterThan('4.1.0', $symfonyConsole['new_version']);
        $this->assertVersionLessThan('5.0.0', $symfonyConsole['new_version']);

        $carbon = $this->findPackage($packages, 'nesbot/carbon');

        $this->assertVersionGreaterThan('1.26.3', $carbon['new_version']);
        $this->assertVersionLessThan('2.0.0', $carbon['new_version']);
        $this->assertVersionGreaterThan('2.0.0', $carbon['last_available_version']);
    }

    /** @test */
    public function packages_not_publicly_available_on_packagist_are_also_returned()
    {
        FakePackageDetector::setPackagesFromArray([
            [
                'name' => 'composer/semver',
                'version' => '1.4.0',
                'require' => ['php' => '>=7.0.0'],
            ],
            [
                'name' => 'laravel/socialite',
                'version' => '3.2.0',
            ],
            [
                'name' => 'laravel/nova',
                'version' => '1.0.1',
            ],
            [
                'name' => '1pilotapp/unknown',
                'version' => '1.0.1',
                'require' => ['laravel/socialite' => '^3.2'],
            ],
        ]);

        FakePackageDetector::generatePackagesConstraints();

        $packages = collect((new Composer)->getPackagesData());

        $this->assertCount(4, $packages);

        $composerSemver = $this->findPackage($packages, 'composer/semver');
        $this->assertVersionGreaterThanOrEqual('1.5.0', $composerSemver['new_version']);
        $this->assertNull($composerSemver['last_available_version']);

        $laravelSocialite = $this->findPackage($packages, 'laravel/socialite');
        $this->assertVersionGreaterThan('3.2.9', $laravelSocialite['new_version']);
        $this->assertVersionLessThan('4.0.0', $laravelSocialite['new_version']);
        $this->assertVersionGreaterThan('4.1.0', $laravelSocialite['last_available_version']);

        $laravelNova = $this->findPackage($packages, 'laravel/nova');
        $this->assertEquals('1.0.1', $laravelNova['version']);
        $this->assertNull($laravelNova['new_version']);
        $this->assertNull($laravelNova['last_available_version']);
    }

    private function findPackage(Collection $packages, string $name)
    {
        return $packages->first(function ($package) use ($name) {
            return $package['code'] === $name;
        });
    }

    private function assertVersionGreaterThan($expected, $actual)
    {
        $this->assertVersionCompare($expected, $actual, '>');
    }

    private function assertVersionGreaterThanOrEqual($expected, $actual)
    {
        $this->assertVersionCompare($expected, $actual, '>=');
    }

    private function assertVersionLessThan($expected, $actual)
    {
        $this->assertVersionCompare($expected, $actual, '<');
    }

    private function assertVersionCompare($expected, $actual, $operator)
    {
        $this->assertTrue(
            version_compare($actual, $expected, $operator),
            "Failed asserting that '{$actual}' is $operator than '$expected'"
        );
    }
}
