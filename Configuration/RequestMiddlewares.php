<?php

declare(strict_types=1);

return [
    'frontend' => [
        'Leuchtfeuer/mautic/authorize' => [
            'target' => \Leuchtfeuer\Mautic\Middleware\AuthorizeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/backend-user-authentication',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
        ],
    ],
];
