<?php

namespace alsmman\Translation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TranslationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('dynamic-translation')
            ->hasConfigFile()
            ->hasMigration('create_translations_table');
    }
}
