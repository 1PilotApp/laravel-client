<?php

namespace OnePilot\Client\Contracts;

use Illuminate\Support\Collection;

interface PackageDetector
{
    public function getPackages(): Collection;

    public function getPackagesConstraints(): Collection;
}
