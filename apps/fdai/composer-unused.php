<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;
use ComposerUnused\ComposerUnused\Configuration\PatternFilter;
use Webmozart\Glob\Glob;

return static function (Configuration $config): Configuration {
    return $config
        ->addNamedFilter(NamedFilter::fromString("slim/slim"))
        ->addNamedFilter(NamedFilter::fromString("quantimodo/docs"))
        ->addNamedFilter(NamedFilter::fromString("mikepsinn/php-highcharts-exporter"))
        ->addNamedFilter(NamedFilter::fromString("ghunti/highcharts-php"))
        ->addNamedFilter(NamedFilter::fromString("cmfcmf/openweathermap-php-api"))
        ->addNamedFilter(NamedFilter::fromString("eristemena/dialogflow-fulfillment-webhook-php"))
        ->addNamedFilter(NamedFilter::fromString("phpunitgen/console"))
        ->addNamedFilter(NamedFilter::fromString("laravel/dusk"))
        ->addNamedFilter(NamedFilter::fromString("barryvdh/laravel-ide-helper"))
        // ->addPatternFilter(PatternFilter::fromString('/symfony\/.*/'))
        ->setAdditionalFilesFor('icanhazstring/composer-unused', [
            __FILE__,
            ...Glob::glob(__DIR__ . '/config/*.php'),
        ]);
};
