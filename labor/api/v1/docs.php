<?php

/**
 * API Documentation
 */

$docs = [
    'title' => 'Laboratory Management System API',
    'version' => '1.0.0',
    'description' => 'RESTful API for managing laboratory operations',
    'base_url' => url('/api/v1'),
    'authentication' => [
        'type' => 'Bearer Token',
        'description' => 'Include Authorization header with Bearer token obtained from login endpoint'
    ],
    'endpoints' => [
        'Authentication' => [
            'POST /auth/login' => [
                'description' => 'Authenticate user and get access token',
                'parameters' => [
                    'email' => 'string (required) - User email',
                    'password' => 'string (required) - User password',
                    'type' => 'string (required) - User type: admin or employee'
                ],
                'response' => [
                    'success' => 'boolean',
                    'token' => 'string - Access token',
                    'user' => 'object - User information',
                    'expires_in' => 'integer - Token expiration in seconds'
                ]
            ],
            'POST /auth/logout' => [
                'description' => 'Logout user and invalidate token',
                'headers' => ['Authorization: Bearer {token}'],
                'response' => ['success' => 'boolean', 'message' => 'string']
            ],
            'GET /auth/me' => [
                'description' => 'Get current user information',
                'headers' => ['Authorization: Bearer {token}'],
                'response' => ['user' => 'object - Current user data']
            ]
        ],
        'Patients' => [
            'GET /patients' => [
                'description' => 'List all patients',
                'parameters' => [
                    'page' => 'integer (optional) - Page number',
                    'limit' => 'integer (optional) - Items per page (max 100)',
                    'search' => 'string (optional) - Search term',
                    'sort' => 'string (optional) - Sort field',
                    'order' => 'string (optional) - Sort order: asc or desc'
                ],
                'response' => [
                    'data' => 'array - Patient list',
                    'pagination' => 'object - Pagination info'
                ]
            ],
            'POST /patients' => [
                'description' => 'Create new patient',
                'parameters' => [
                    'name' => 'string (required) - Patient name',
                    'phone' => 'string (optional) - Phone number',
                    'email' => 'string (optional) - Email address',
                    'national_id' => 'string (optional) - National ID',
                    'date_of_birth' => 'date (optional) - Birth date (YYYY-MM-DD)',
                    'gender' => 'string (optional) - Gender: ذكر or أنثى',
                    'address' => 'string (optional) - Address',
                    'blood_type' => 'string (optional) - Blood type',
                    'medical_history' => 'string (optional) - Medical history',
                    'notes' => 'string (optional) - Additional notes'
                ],
                'response' => ['patient' => 'object - Created patient data']
            ],
            'GET /patients/{id}' => [
                'description' => 'Get patient details',
                'response' => ['patient' => 'object - Patient data']
            ],
            'PUT /patients/{id}' => [
                'description' => 'Update patient information',
                'parameters' => 'Same as POST /patients',
                'response' => ['patient' => 'object - Updated patient data']
            ],
            'DELETE /patients/{id}' => [
                'description' => 'Delete patient',
                'response' => ['success' => 'boolean', 'message' => 'string']
            ]
        ],
        'Exams' => [
            'GET /exams' => [
                'description' => 'List all exams',
                'parameters' => [
                    'category_id' => 'integer (optional) - Filter by category',
                    'active' => 'boolean (optional) - Filter by active status',
                    'search' => 'string (optional) - Search term'
                ],
                'response' => ['data' => 'array - Exam list']
            ],
            'POST /exams' => [
                'description' => 'Create new exam',
                'parameters' => [
                    'name' => 'string (required) - Exam name',
                    'code' => 'string (optional) - Exam code',
                    'category_id' => 'integer (optional) - Category ID',
                    'price' => 'decimal (required) - Exam price',
                    'description' => 'string (optional) - Description',
                    'normal_range' => 'string (optional) - Normal range',
                    'unit' => 'string (optional) - Unit of measurement',
                    'sample_type' => 'string (optional) - Sample type required'
                ],
                'response' => ['exam' => 'object - Created exam data']
            ],
            'GET /exams/{id}' => [
                'description' => 'Get exam details',
                'response' => ['exam' => 'object - Exam data']
            ]
        ],
        'Results' => [
            'POST /results' => [
                'description' => 'Submit exam result',
                'parameters' => [
                    'patient_id' => 'integer (required) - Patient ID',
                    'exam_id' => 'integer (required) - Exam ID',
                    'result_value' => 'string (required) - Result value',
                    'result_status' => 'string (optional) - normal, abnormal, or critical',
                    'notes' => 'string (optional) - Additional notes'
                ],
                'response' => ['result' => 'object - Created result data']
            ],
            'GET /results/{id}' => [
                'description' => 'Get result details',
                'response' => ['result' => 'object - Result data with patient and exam info']
            ]
        ],
        'Reports' => [
            'GET /reports/dashboard' => [
                'description' => 'Get dashboard statistics',
                'parameters' => [
                    'period' => 'string (optional) - today, week, month, year'
                ],
                'response' => [
                    'patients_count' => 'integer',
                    'exams_count' => 'integer',
                    'revenue' => 'decimal',
                    'pending_results' => 'integer'
                ]
            ],
            'GET /reports/revenue' => [
                'description' => 'Get revenue report',
                'parameters' => [
                    'start_date' => 'date (optional) - Start date',
                    'end_date' => 'date (optional) - End date',
                    'group_by' => 'string (optional) - day, week, month'
                ],
                'response' => ['data' => 'array - Revenue data']
            ]
        ]
    ],
    'error_codes' => [
        '400' => 'Bad Request - Invalid request syntax',
        '401' => 'Unauthorized - Authentication required',
        '403' => 'Forbidden - Insufficient permissions',
        '404' => 'Not Found - Resource not found',
        '422' => 'Unprocessable Entity - Validation errors',
        '429' => 'Too Many Requests - Rate limit exceeded',
        '500' => 'Internal Server Error - Server error'
    ],
    'rate_limiting' => [
        'limit' => '60 requests per minute per API key',
        'headers' => [
            'X-RateLimit-Limit' => 'Request limit per minute',
            'X-RateLimit-Remaining' => 'Remaining requests',
            'X-RateLimit-Reset' => 'Reset time (Unix timestamp)'
        ]
    ]
];

// Output formatted documentation
if (isset($_GET['format']) && $_GET['format'] === 'html') {
    // HTML documentation
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>API Documentation - <?= $docs['title'] ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
            h2 { color: #4CAF50; margin-top: 30px; }
            h3 { color: #666; }
            .endpoint { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #4CAF50; }
            .method { background: #4CAF50; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
            .params { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; margin: 10px 0; }
            code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
            .error-code { background: #ffebee; padding: 10px; margin: 5px 0; border-left: 4px solid #f44336; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><?= $docs['title'] ?></h1>
            <p><strong>Version:</strong> <?= $docs['version'] ?></p>
            <p><strong>Base URL:</strong> <code><?= $docs['base_url'] ?></code></p>
            <p><?= $docs['description'] ?></p>
            
            <h2>Authentication</h2>
            <p><strong>Type:</strong> <?= $docs['authentication']['type'] ?></p>
            <p><?= $docs['authentication']['description'] ?></p>
            
            <?php foreach ($docs['endpoints'] as $section => $endpoints): ?>
                <h2><?= $section ?></h2>
                <?php foreach ($endpoints as $endpoint => $details): ?>
                    <div class="endpoint">
                        <h3><?= $endpoint ?></h3>
                        <p><?= $details['description'] ?></p>
                        
                        <?php if (isset($details['parameters'])): ?>
                            <h4>Parameters:</h4>
                            <div class="params">
                                <?php if (is_array($details['parameters'])): ?>
                                    <?php foreach ($details['parameters'] as $param => $desc): ?>
                                        <p><code><?= $param ?></code>: <?= $desc ?></p>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p><?= $details['parameters'] ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($details['response'])): ?>
                            <h4>Response:</h4>
                            <div class="params">
                                <pre><?= json_encode($details['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            
            <h2>Error Codes</h2>
            <?php foreach ($docs['error_codes'] as $code => $description): ?>
                <div class="error-code">
                    <strong><?= $code ?></strong>: <?= $description ?>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
} else {
    // JSON documentation
    echo json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}