<?php

/**
 * CRUD Bootstrap File
 *
 * This file loads all the custom functionality for the Books CRUD system.
 * It's included in functions.php to ensure all our custom code is loaded.
 *
 * Files loaded:
 * - PostTypes/Book.php: Registers the Book custom post type
 * - Ajax/BookAjax.php: Handles all AJAX requests for CRUD operations
 *
 * @package App
 */

/**
 * Load the Book custom post type
 * This registers the 'book' post type in WordPress
 */
require_once __DIR__ . '/PostTypes/Book.php';

/**
 * Load the Book AJAX handlers
 * This sets up all the AJAX endpoints for Create, Read, Update, Delete operations
 */
require_once __DIR__ . '/Ajax/BookAjax.php';
