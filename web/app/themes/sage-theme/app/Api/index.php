<?php

namespace App\Api;

/**
 * Load REST API Controllers
 */
require_once __DIR__ . '/BooksController.php';
require_once __DIR__ . '/AuthorsController.php';

/**
 * Register REST API routes
 */
\add_action('rest_api_init', function () {
    // Debug: Log that this action is being called
    error_log('REST API Init: Registering sage/v1 routes');

    BooksController::register_routes();
    AuthorsController::register_routes();

    // Debug: Confirm routes registered
    error_log('REST API Init: Routes registered successfully');
});

