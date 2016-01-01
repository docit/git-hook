<?php

return [
    'local'                  => [

    ],
    'credentials'                => [
        'github'    => [
            'token'   => env('GITHUB_TOKEN', null)
        ],
        'bitbucket' => [
            'key'     => env('BITBUCKET_CLIENT_KEY', null),
            'secret'  => env('BITBUCKET_CLIENT_SECRET', null)
        ]
    ],
    'default_project_config' => [
        'enable_git_hook'   => false,
        'git_hook_settings' => [
            'owner'      => '',
            'repository' => '',
            'remote'     => 'github',
            'sync'       => [
                'constraints' => [
                    'branches' => [ 'master' ],
                    'versions' => '*', //1.x || >=2.5.0 || 5.0.0 - 7.2.3'
                ],
                'paths'       => [
                    'docs' => 'docs',
                    'menu' => 'docs/menu.yml'
                ]
            ],
            'webhook'     => [
                'enabled' => false,
                'secret'  => env('CODEX_PROJECT_GITHUB_WEBHOOK_SECRET', null),
            ],
        ]
    ]
];
