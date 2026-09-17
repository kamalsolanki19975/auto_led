<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiDocsController extends Controller
{
    public function ui()
    {
        return view('api.docs', ['specUrl' => url('/api/openapi.json')]);
    }

    public function spec()
    {
        return response()->json($this->build());
    }

    protected function build(): array
    {
        $base = rtrim(url('/'), '/');

        $ok = fn (string $desc, array $props = []) => [
            'description' => $desc,
            'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => array_merge(['success' => ['type' => 'boolean', 'example' => true]], $props)]]],
        ];

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'AutoAds Network API',
                'version' => '1.0.0',
                'description' => "REST & Device APIs for the AutoAds DOOH advertising network.\n\n".
                    "**Two authentication contexts:**\n".
                    "- **User/Partner API** (`bearerAuth`): obtain a token from `POST /api/v1/auth/login` (Sanctum personal access token). Send as `Authorization: Bearer <token>`.\n".
                    "- **Device API** (`deviceAuth`): each Android player pairs via `POST /api/v1/device/authenticate` using its `device_uuid` + `pairing_token`, receiving a rotating `device_token` used as `Authorization: Bearer <device_token>` for all device endpoints.",
            ],
            'servers' => [['url' => $base, 'description' => 'Current environment']],
            'tags' => [
                ['name' => 'Authentication', 'description' => 'User/partner token authentication'],
                ['name' => 'Resources', 'description' => 'Read-only REST access to network & advertising entities'],
                ['name' => 'Device', 'description' => 'Android player runtime API (offline-first, proof-of-play)'],
                ['name' => 'Documentation', 'description' => 'OpenAPI specification'],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'Sanctum', 'description' => 'User API token from /api/v1/auth/login'],
                    'deviceAuth' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'DeviceToken', 'description' => 'Device token from /api/v1/device/authenticate'],
                ],
                'schemas' => [
                    'Error' => ['type' => 'object', 'properties' => [
                        'success' => ['type' => 'boolean', 'example' => false],
                        'message' => ['type' => 'string', 'example' => 'The given data was invalid.'],
                        'request_id' => ['type' => 'string', 'format' => 'uuid'],
                        'errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]],
                    ]],
                    'Pagination' => ['type' => 'object', 'properties' => [
                        'current_page' => ['type' => 'integer', 'example' => 1],
                        'last_page' => ['type' => 'integer', 'example' => 4],
                        'total' => ['type' => 'integer', 'example' => 87],
                    ]],
                ],
                'responses' => [
                    'Unauthorized' => ['description' => 'Missing or invalid token', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]],
                    'ValidationError' => ['description' => 'Validation failed', 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Error']]]],
                ],
            ],
            'paths' => [
                '/api/v1/auth/login' => ['post' => [
                    'tags' => ['Authentication'], 'summary' => 'Login and obtain an API token',
                    'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['email', 'password'], 'properties' => [
                        'email' => ['type' => 'string', 'format' => 'email', 'example' => 'admin@autoads.test'],
                        'password' => ['type' => 'string', 'format' => 'password', 'example' => 'Admin@123'],
                    ]]]]],
                    'responses' => [
                        '200' => $ok('Authenticated', [
                            'token' => ['type' => 'string', 'example' => '3|abc123...'],
                            'user' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer'], 'name' => ['type' => 'string'], 'email' => ['type' => 'string'], 'portal' => ['type' => 'string', 'example' => 'admin']]],
                        ]),
                        '422' => ['$ref' => '#/components/responses/ValidationError'],
                    ],
                ]],
                '/api/v1/auth/me' => ['get' => [
                    'tags' => ['Authentication'], 'summary' => 'Current authenticated user', 'security' => [['bearerAuth' => []]],
                    'responses' => ['200' => $ok('Current user', ['user' => ['type' => 'object']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/auth/logout' => ['post' => [
                    'tags' => ['Authentication'], 'summary' => 'Revoke the current token', 'security' => [['bearerAuth' => []]],
                    'responses' => ['200' => $ok('Logged out', ['message' => ['type' => 'string']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/{resource}' => ['get' => [
                    'tags' => ['Resources'], 'summary' => 'List records for a resource', 'security' => [['bearerAuth' => []]],
                    'parameters' => [
                        ['name' => 'resource', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'enum' => ['autos', 'owners', 'drivers', 'screens', 'devices', 'sims', 'advertisers', 'advertisements', 'campaigns', 'playlists', 'invoices', 'payments', 'expenses', 'settlements', 'proof-of-play', 'runtime']]],
                        ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 25, 'maximum' => 100]],
                    ],
                    'responses' => [
                        '200' => $ok('Paginated list', ['data' => ['type' => 'array', 'items' => ['type' => 'object']], 'meta' => ['$ref' => '#/components/schemas/Pagination']]),
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                    ],
                ]],
                '/api/v1/{resource}/{id}' => ['get' => [
                    'tags' => ['Resources'], 'summary' => 'Fetch a single record', 'security' => [['bearerAuth' => []]],
                    'parameters' => [
                        ['name' => 'resource', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'enum' => ['autos', 'owners', 'drivers', 'screens', 'devices', 'sims', 'advertisers', 'advertisements', 'campaigns', 'playlists', 'invoices', 'payments', 'expenses', 'settlements']]],
                        ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ],
                    'responses' => ['200' => $ok('Record', ['data' => ['type' => 'object']]), '401' => ['$ref' => '#/components/responses/Unauthorized'], '404' => ['description' => 'Not found']],
                ]],
                '/api/v1/device/authenticate' => ['post' => [
                    'tags' => ['Device'], 'summary' => 'Pair/authenticate a player device',
                    'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['device_uuid', 'pairing_token'], 'properties' => [
                        'device_uuid' => ['type' => 'string', 'format' => 'uuid'],
                        'pairing_token' => ['type' => 'string', 'description' => 'Pairing token issued when the device was registered'],
                    ]]]]],
                    'responses' => [
                        '200' => $ok('Device session issued', [
                            'device_token' => ['type' => 'string'],
                            'device' => ['type' => 'object', 'properties' => ['code' => ['type' => 'string'], 'status' => ['type' => 'string']]],
                            'configuration' => ['type' => 'object'],
                        ]),
                        '401' => ['$ref' => '#/components/responses/Unauthorized'],
                    ],
                ]],
                '/api/v1/device/heartbeat' => ['post' => [
                    'tags' => ['Device'], 'summary' => 'Report device health / telemetry', 'security' => [['deviceAuth' => []]],
                    'requestBody' => ['content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => [
                        'app_version' => ['type' => 'string'], 'android_version' => ['type' => 'string'],
                        'storage_free' => ['type' => 'integer'], 'temperature' => ['type' => 'number'],
                        'network' => ['type' => 'string'], 'signal_strength' => ['type' => 'integer'],
                        'current_campaign_id' => ['type' => 'integer'], 'current_advertisement_id' => ['type' => 'integer'],
                        'uptime' => ['type' => 'integer'], 'errors' => ['type' => 'string'],
                    ]]]]],
                    'responses' => ['200' => $ok('Recorded', ['server_time' => ['type' => 'string', 'format' => 'date-time']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/configuration' => ['get' => [
                    'tags' => ['Device'], 'summary' => 'Fetch device runtime configuration', 'security' => [['deviceAuth' => []]],
                    'responses' => ['200' => $ok('Configuration', ['configuration' => ['type' => 'object']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/campaigns' => ['get' => [
                    'tags' => ['Device'], 'summary' => 'Active campaigns + advertisements for this device', 'security' => [['deviceAuth' => []]],
                    'responses' => ['200' => $ok('Campaign playlist', ['campaigns' => ['type' => 'array', 'items' => ['type' => 'object']], 'fallback' => ['type' => 'string', 'example' => 'house']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/content' => ['get' => [
                    'tags' => ['Device'], 'summary' => 'Content manifest for offline caching', 'security' => [['deviceAuth' => []]],
                    'responses' => ['200' => $ok('Content manifest', ['content' => ['type' => 'array', 'items' => ['type' => 'object', 'properties' => ['advertisement_id' => ['type' => 'integer'], 'content_type' => ['type' => 'string'], 'url' => ['type' => 'string'], 'checksum' => ['type' => 'string']]]]]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/playback-events' => ['post' => [
                    'tags' => ['Device'], 'summary' => 'Report a single playback event', 'security' => [['deviceAuth' => []]],
                    'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => [
                        'campaign_id' => ['type' => 'integer'], 'advertisement_id' => ['type' => 'integer'],
                        'started_at' => ['type' => 'string', 'format' => 'date-time'], 'ended_at' => ['type' => 'string', 'format' => 'date-time'],
                        'duration' => ['type' => 'integer'], 'latitude' => ['type' => 'number'], 'longitude' => ['type' => 'number'],
                        'event_id' => ['type' => 'string', 'description' => 'Client-generated idempotency key'],
                    ]]]]],
                    'responses' => ['200' => $ok('Ingested', ['status' => ['type' => 'string', 'example' => 'valid'], 'event_id' => ['type' => 'string']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/sync-events' => ['post' => [
                    'tags' => ['Device'], 'summary' => 'Batch-sync buffered offline events', 'security' => [['deviceAuth' => []]],
                    'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => [
                        'events' => ['type' => 'array', 'items' => ['type' => 'object'], 'description' => 'Array of playback events captured while offline'],
                    ]]]]],
                    'responses' => ['200' => $ok('Sync result', ['result' => ['type' => 'object']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/errors' => ['post' => [
                    'tags' => ['Device'], 'summary' => 'Report client-side errors/diagnostics', 'security' => [['deviceAuth' => []]],
                    'responses' => ['200' => $ok('Logged'), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/status' => ['post' => [
                    'tags' => ['Device'], 'summary' => 'Report/query device status', 'security' => [['deviceAuth' => []]],
                    'responses' => ['200' => $ok('Status', ['status' => ['type' => 'string'], 'online' => ['type' => 'boolean']]), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/v1/device/acknowledgement' => ['post' => [
                    'tags' => ['Device'], 'summary' => 'Acknowledge pending commands (clears queue)', 'security' => [['deviceAuth' => []]],
                    'responses' => ['200' => $ok('Acknowledged'), '401' => ['$ref' => '#/components/responses/Unauthorized']],
                ]],
                '/api/openapi.json' => ['get' => [
                    'tags' => ['Documentation'], 'summary' => 'This OpenAPI 3.0 specification',
                    'responses' => ['200' => ['description' => 'OpenAPI document']],
                ]],
            ],
        ];
    }
}
