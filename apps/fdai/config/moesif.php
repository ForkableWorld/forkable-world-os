<?php /** @noinspection SpellCheckingInspection */
return [
    'logBody' => true, // Optional, Default true, Set to false to remove logging request and response body to Moesif.
    'debug' => env('MOESIF_DEBUG', false), // If true, will print debug messages using Illuminate\Support\Facades\Log
    'applicationId' => 
        'eyJhcHAiOiI2MTc6MzciLCJ2ZXIiOiIyLjAiLCJvcmciOiI1ODY6MzUiLCJpYXQiOjE2MjUwOTc2MDB9.poImJX1SBDdz8pM9y0OZAhcyubLFf9RtF1lW7kYcMpI',
    'configClass' => \App\Logging\QMMoesif::class,
    'apiVersion' => env('GIT_COMMIT', "GIT_COMMIT not defined!"),
];