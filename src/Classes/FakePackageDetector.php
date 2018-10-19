<?php

namespace OnePilot\Client\Classes;

use Illuminate\Support\Collection;
use OnePilot\Client\Contracts\PackageDetector;

class FakePackageDetector implements PackageDetector
{
    /** @var Collection $packages */
    private static $packages;

    /** @var Collection $packagesConstraints */
    private static $packagesConstraints;

    public function getPackages(): Collection
    {
        return self::$packages;
    }

    public function getPackagesConstraints(): Collection
    {
        return self::$packagesConstraints;
    }

    public static function setPackages(Collection $collection)
    {
        self::$packages = $collection;
    }

    public static function setPackagesFromPath(string $path)
    {
        self::setPackages(collect(json_decode(file_get_contents($path))));
    }

    public static function setPackagesConstraints(Collection $collection)
    {
        self::$packagesConstraints = $collection;
    }

    public static function generatePackagesConstraints()
    {
        $composers = self::$packages
            ->filter()
            ->map(function ($package) {
                return $package->require ?? null;
            })
            ->filter();

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

        self::$packagesConstraints = collect($constraints);
    }
}
