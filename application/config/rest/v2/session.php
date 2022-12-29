<?php

use \LimeSurvey\Api\Command\V2\SessionKeyCreate;
use \LimeSurvey\Api\Command\V2\SessionKeyRelease;

use LimeSurvey\Api\Rest\V2\SchemaFactory\SchemaFactoryError;
use LimeSurvey\Api\Rest\V2\SchemaFactory\SchemaFactoryAuthToken;

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Session
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$errorSchema = (new SchemaFactoryError)->create();

$rest = [];

$rest['v2/session'] = [
    'POST' => [
        'description' => 'Generate new authentication token',
        'commandClass' => SessionKeyCreate::class,
        'params' => [
            'username' => ['src' => 'form'],
            'password' => ['src' => 'form']
        ],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success - returns string access token for use in header '
                    . '"Authorization: Bearer $token"',
                'schema' => (new SchemaFactoryAuthToken)->create()
            ],
            'unauthorized' => [
                'code' => 403,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ],
    'DELETE' => [
        'description' => 'Destroy currently used authentication token',
        'commandClass' => SessionKeyRelease::class,
        'auth' => 'session',
        'params' => [],
        'bodyParams' => [],
        'responses' => [
            'success' => [
                'code' => 200,
                'description' => 'Success',
            ],
            'unauthorized' => [
                'code' => 403,
                'description' => 'Unauthorized',
                'schema' => $errorSchema
            ]
        ]
    ]
];

return $rest;
