<?php

declare(strict_types=1);

// config for TimoCuijpers/LaravelControllersGenerator

return [
    'clean_controllers_directory_before_generation' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable generation alias
    |--------------------------------------------------------------------------
    |
    | Enable generation alias command:
    | php artisan models:generate
    |
    */
    'enable_alias' => false,

    /*
    |--------------------------------------------------------------------------
    | Strict types
    |--------------------------------------------------------------------------
    |
    | Add declare(strict_types=1); to the top of each generated model file
    |
    */
    'strict_types' => true,

    /*
    |--------------------------------------------------------------------------
    | Models $table property
    |--------------------------------------------------------------------------
    |
    | Add $table model property
    |
    */
    'table' => true,

    /*
    |--------------------------------------------------------------------------
    | Models $connection property
    |--------------------------------------------------------------------------
    |
    | Add $connection model property
    |
    */
    'connection' => true,

    /*'phpdocs' => [
        'scopes' => true,
    ],*/

    /*
    |--------------------------------------------------------------------------
    | Models path
    |--------------------------------------------------------------------------
    |
    | Where the models will be created
    |
    */
    'path' => app_path('Http/Controllers/Generated'),

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace of the generated models
    |
    */
    'namespace' => 'App\Http\Controllers\Generated',

    /*
    |--------------------------------------------------------------------------
    | Parent
    |--------------------------------------------------------------------------
    |
    | The parent class of the generated models
    |
    */
    'parent' => App\Http\Controllers\Controller::class,

    /*
    |--------------------------------------------------------------------------
    | Base files
    |--------------------------------------------------------------------------
    |
    | If you want to generate a base file for each model, you can enable this.
    | The base file will be created within 'Base' directory inside the models' directory.
    | If you want your base files be abstract you can enable it.
    |
    */
    'base_files' => [
        'enabled' => false,
        'abstract' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Add comments in PHPDocs
    |--------------------------------------------------------------------------
    |
    | Add comments to PHPDocs column property (Ex. @property int $id (comment))
    |
    */
    'add_comments_in_phpdocs' => true,

    /*
    |--------------------------------------------------------------------------
    | Interfaces
    |--------------------------------------------------------------------------
    |
    | Interface(s) implemented by all models
    |
    */
    'interfaces' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Traits
    |--------------------------------------------------------------------------
    |
    | Trait(s) implemented by all models
    |
    */
    'traits' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Tables
    |--------------------------------------------------------------------------
    |
    | These models will not be generated
    |
    */
    'except' => [
        'migrations',
        'failed_jobs',
        'password_resets',
        'personal_access_tokens',
        'password_reset_tokens',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Columns
    |--------------------------------------------------------------------------
    |
    | These columns will not be added to $fillable array.
    |
    | You can use a string or any valid pattern for preg_match function.
    | Ex. '/your_pattern/'
    |     '/your_pattern/i' (case-insensitive)
    |     'column_not_to_generate'
    |
    */
    'exclude_columns' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Relationships
    |--------------------------------------------------------------------------
    |
    | These relationships will not be added to Model class.
    | Ex.
    |   'table_of_starting_relationship' => [
    |       'table_of_relationship',
    |   ],
    |
    */
    'exclude_relationships' => [
    ],

    /*
    | --------------------------------------------------------------------------
    | Define rules in array or in string
    | --------------------------------------------------------------------------
    |
    | This will define the format of the rules in the model.
    | Available formats:
    |  - 'array'
    |  - 'string'
    |
    */
    'rules_format' => 'array',
];
