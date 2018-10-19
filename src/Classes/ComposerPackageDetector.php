<?php

namespace OnePilot\Client\Classes;

use Illuminate\Support\Collection;
use OnePilot\Client\Contracts\PackageDetector;

class ComposerPackageDetector implements PackageDetector
{
    /** @var string */
    private $appRoot;

    public function __construct(string $projectRoot)
    {
        $this->appRoot = $projectRoot;
    }

    public function getPackages(): Collection
    {
        $installedJsonFile = $this->appRoot . '/vendor/composer/installed.json';
        $installedPackages = json_decode(file_get_contents($installedJsonFile));

        return collect($installedPackages);
    }

    public function getPackagesConstraints(): Collection
    {
        $composers = $this->getPackages()
            ->push($this->appComposerData())
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

        return collect($constraints);
    }

    private function appComposerData()
    {
        if (!file_exists($appComposer = $this->appRoot . '/composer.json')) {
            return null;
        }

        $content = file_get_contents($appComposer);

        return json_decode($content) ?? null;
    }
}
