<?php
/**
 * Application Routes Configuration
 * Defines all routes with middleware and security settings
 */

return [
    // Public routes (no authentication required)
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => 'DashboardController@index',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/login',
        'handler' => 'AuthController@showLogin',
        'middleware' => ['security']
    ],
    
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => 'AuthController@login',
        'middleware' => ['security']
    ],
    
    [
        'method' => 'GET',
        'path' => '/logout',
        'handler' => 'AuthController@logout',
        'middleware' => ['security', 'auth']
    ],
    
    // Dashboard routes
    [
        'method' => 'GET',
        'path' => '/dashboard',
        'handler' => 'DashboardController@index',
        'middleware' => ['security', 'auth']
    ],
    
    // Client management routes
    [
        'method' => 'GET',
        'path' => '/clients',
        'handler' => 'ClientController@index',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/clients/create',
        'handler' => 'ClientController@create',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/clients',
        'handler' => 'ClientController@store',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/clients/{id}',
        'handler' => 'ClientController@show',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/clients/{id}/edit',
        'handler' => 'ClientController@edit',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'PUT',
        'path' => '/clients/{id}',
        'handler' => 'ClientController@update',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'DELETE',
        'path' => '/clients/{id}',
        'handler' => 'ClientController@destroy',
        'middleware' => ['security', 'auth']
    ],
    
    // Cashbox routes
    [
        'method' => 'GET',
        'path' => '/cashbox',
        'handler' => 'CashboxController@index',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/cashbox/movement',
        'handler' => 'CashboxController@movement',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/cashbox/movement',
        'handler' => 'CashboxController@storeMovement',
        'middleware' => ['security', 'auth']
    ],
    
    // Loading routes
    [
        'method' => 'GET',
        'path' => '/loadings',
        'handler' => 'LoadingController@index',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/loadings/create',
        'handler' => 'LoadingController@create',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/loadings',
        'handler' => 'LoadingController@store',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/loadings/{id}',
        'handler' => 'LoadingController@show',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/loadings/{id}/edit',
        'handler' => 'LoadingController@edit',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'PUT',
        'path' => '/loadings/{id}',
        'handler' => 'LoadingController@update',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'DELETE',
        'path' => '/loadings/{id}',
        'handler' => 'LoadingController@destroy',
        'middleware' => ['security', 'auth']
    ],
    
    // Transaction routes
    [
        'method' => 'GET',
        'path' => '/transactions',
        'handler' => 'TransactionController@index',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/transactions/create',
        'handler' => 'TransactionController@create',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/transactions',
        'handler' => 'TransactionController@store',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/transactions/{id}',
        'handler' => 'TransactionController@show',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/transactions/{id}/edit',
        'handler' => 'TransactionController@edit',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'PUT',
        'path' => '/transactions/{id}',
        'handler' => 'TransactionController@update',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'DELETE',
        'path' => '/transactions/{id}',
        'handler' => 'TransactionController@destroy',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/transactions/{id}/approve',
        'handler' => 'TransactionController@approve',
        'middleware' => ['security', 'auth']
    ],
    
    // Report routes
    [
        'method' => 'GET',
        'path' => '/reports',
        'handler' => 'ReportController@index',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/reports/daily',
        'handler' => 'ReportController@daily',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/reports/cashbox',
        'handler' => 'ReportController@cashbox',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'GET',
        'path' => '/reports/client-statement',
        'handler' => 'ReportController@clientStatement',
        'middleware' => ['security', 'auth']
    ],
    
    // Settings routes
    [
        'method' => 'GET',
        'path' => '/settings',
        'handler' => 'SettingsController@index',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/settings',
        'handler' => 'SettingsController@update',
        'middleware' => ['security', 'auth']
    ],
    
    // Profile routes
    [
        'method' => 'GET',
        'path' => '/profile',
        'handler' => 'AuthController@profile',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/profile',
        'handler' => 'AuthController@updateProfile',
        'middleware' => ['security', 'auth']
    ],
    
    [
        'method' => 'POST',
        'path' => '/profile/change-password',
        'handler' => 'AuthController@changePassword',
        'middleware' => ['security', 'auth']
    ],
    
    // API routes
    [
        'method' => 'GET',
        'path' => '/api/sync',
        'handler' => 'Api\SyncController@index',
        'middleware' => ['security']
    ],
    
    [
        'method' => 'POST',
        'path' => '/api/sync',
        'handler' => 'Api\SyncController@sync',
        'middleware' => ['security']
    ],
    
    // Error pages
    [
        'method' => 'GET',
        'path' => '/error/403',
        'handler' => 'ErrorController@forbidden',
        'middleware' => ['security']
    ],
    
    [
        'method' => 'GET',
        'path' => '/error/404',
        'handler' => 'ErrorController@notFound',
        'middleware' => ['security']
    ],
    
    [
        'method' => 'GET',
        'path' => '/error/429',
        'handler' => 'ErrorController@tooManyRequests',
        'middleware' => ['security']
    ],
    
    [
        'method' => 'GET',
        'path' => '/error/500',
        'handler' => 'ErrorController@internalError',
        'middleware' => ['security']
    ],
    
    // Catch-all route for 404
    [
        'method' => 'GET',
        'path' => '/{any}',
        'handler' => 'ErrorController@notFound',
        'middleware' => ['security']
    ]
];
