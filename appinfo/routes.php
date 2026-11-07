<?php

return [
    'routes' => [
        // Admin routes
        [
            'name' => 'admin#index',
            'url' => '/admin',
            'verb' => 'GET',
            'requirements' => [
                'admin' => 'true',
            ],
        ],
        [
            'name' => 'admin#organizationForm',
            'url' => '/admin/organization',
            'verb' => 'GET',
            'requirements' => [
                'admin' => 'true',
            ],
        ],
        [
            'name' => 'admin#saveOrganization',
            'url' => '/admin/organization/save',
            'verb' => 'POST',
            'requirements' => [
                'admin' => 'true',
            ],
        ],
        [
            'name' => 'admin#calendarForm',
            'url' => '/admin/calendar',
            'verb' => 'GET',
            'requirements' => [
                'admin' => 'true',
            ],
        ],
        [
            'name' => 'admin#saveCalendar',
            'url' => '/admin/calendar/save',
            'verb' => 'POST',
            'requirements' => [
                'admin' => 'true',
            ],
        ],
    ],
];
