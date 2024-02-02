<?php

return [
    'model_defaults' => [
        'namespace'       => 'App\\Models\\Clinical',
        'base_class_name' => \Illuminate\Database\Eloquent\Model::class,
        'output_path'     => '/vagrant/app/Models/Clinical',
        'no_timestamps'   => true,
        'date_format'     => null,
        'connection'      => 'clinical_trials',
        'backup'          => null,
    ],
];
