<?php

return [
    'paths' => [
        'migrations' => database_path('migrations/'),
        'factories' => database_path('factories/'),
        'models' => app_path('Models/'),
        'views' => resource_path('js/Pages/'),
        'components' => resource_path('js/Components/'),
        'layouts' => resource_path('js/Layouts/'),
        'controllers' => app_path('Http/Controllers/'),
        'requests' => app_path('Http/Requests/'),
        'resources' => app_path('Http/Resources/'),
        'services' => app_path('Services/'),
        'policies' => app_path('Policies/'),

        // Stubs paths are dynamically resolved by the AppDesignServiceProvider
        'stubs' => [
            'controllers' => 'Stubs/Controllers',
            'factories' => 'Stubs/Factories',
            'requests' => 'Stubs/Requests',
            'resources' => 'Stubs/Resources',
            'policies' => 'Stubs/Policies',
            'services' => 'Stubs/Services',
            'views' => 'Stubs/Vue',
            'components' => 'Stubs/Vue/Components',
            'layouts' => 'Stubs/Vue/Layouts',
            'models' => [
                'core' => 'Stubs/Models/model.php.stub',
                'relationships' => [
                    'belongsTo' => 'Stubs/Models/Relationships/belongs_to.stub',
                    'belongsToMany' => 'Stubs/Models/Relationships/belongs_to_many.stub',
                    'hasOne' => 'Stubs/Models/Relationships/has_one.stub',
                    'hasMany' => 'Stubs/Models/Relationships/has_many.stub',
                    'manyToMany' => 'Stubs/Models/Relationships/many_to_many.stub',
                    'hasManyThrough' => 'Stubs/Models/Relationships/has_many_through.stub',
                    'morphOne' => 'Stubs/Models/Relationships/morph_one.stub',
                    'morphMany' => 'Stubs/Models/Relationships/morph_many.stub',
                    'morphTo' => 'Stubs/Models/Relationships/morph_to.stub',
                ]
            ],
            'migrations' => 'Stubs/Migrations/migration.php.stub',
        ]
    ],
];
